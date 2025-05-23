<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Model;

use Exception;
use Generator;
use Laminas\Http\Client\Adapter\Socket;
use Laminas\Uri\Uri;
use Magento\CacheInvalidate\Model\SocketFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\EntityManager\EventManager;
use Magento\PageCache\Model\Cache\Server;

class PurgeCache extends \Magento\CacheInvalidate\Model\PurgeCache
{
    public const HEADER_X_MAGENTO_TAGS_PATTERN = 'X-Magento-Tags-Pattern';
    public const HEADER_X_MAGENTO_PURGE_SOFT = 'X-Magento-Purge-Soft';


    /**
     * @var Server
     */
    protected $cacheServer;

    /**
     * @var SocketFactory
     */
    protected $socketAdapterFactory;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * Batch size of the purge request.
     *
     * Based on default Varnish 6 http_req_hdr_len size minus a 512 bytes margin for method,
     * header name, line feeds etc.
     *
     * @see https://varnish-cache.org/docs/6.0/reference/varnishd.html
     *
     * @var int
     */
    private $maxHeaderSize;

    private $eventManager;

    private ?bool $isSoftPurgeEnabled = null;

    private ?array$defaultHeaders;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param Server $cacheServer
     * @param SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
     * @param EventManager $eventManager
     * @param int $maxHeaderSize
     */
    public function __construct(
        Server $cacheServer,
        SocketFactory $socketAdapterFactory,
        InvalidateLogger $logger,
        EventManager $eventManager,
        ScopeConfigInterface $scopeConfig,
        int $maxHeaderSize = 7680,
    ) {
        $this->cacheServer = $cacheServer;
        $this->socketAdapterFactory = $socketAdapterFactory;
        $this->logger = $logger;
        $this->maxHeaderSize = $maxHeaderSize;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
    }

    /**
     * Send curl purge request to invalidate cache by tags pattern
     *
     * @param array|string $tags
     * @return bool Return true if successful; otherwise return false
     */
    public function sendPurgeRequest($tags): bool
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        $successful = true;
        $socketAdapter = $this->socketAdapterFactory->create();

        $servers = $this->cacheServer->getUris();
        $socketAdapter->setOptions(['timeout' => 10]);

        $formattedTagsChunks = $this->chunkTags($tags);
        foreach ($formattedTagsChunks as $formattedTagsChunk) {
            if (!$this->sendPurgeRequestToServers($socketAdapter, $servers, $formattedTagsChunk)) {
                $successful = false;
            }
        }

        return $successful;
    }

    /**
     * Split tags into batches to suit Varnish max. header size
     *
     * @param array $tags
     * @return Generator
     */
    private function chunkTags(array $tags): Generator
    {
        $currentBatchSize = 0;
        $formattedTagsChunk = [];
        foreach ($tags as $formattedTag) {
            // Check if (currentBatchSize + length of next tag + number of pipe delimiters) would exceed header size.
            if ($currentBatchSize + strlen($formattedTag ?: '') + count($formattedTagsChunk) > $this->maxHeaderSize) {
                yield implode('|', $formattedTagsChunk);
                $formattedTagsChunk = [];
                $currentBatchSize = 0;
            }

            $currentBatchSize += strlen($formattedTag ?: '');
            $formattedTagsChunk[] = $formattedTag;
        }
        if (!empty($formattedTagsChunk)) {
            yield implode('|', $formattedTagsChunk);
        }
    }

    /**
     *
     * Send curl purge request to servers to invalidate cache by tags pattern
     *
     * @param Socket $socketAdapter
     * @param Uri[] $servers
     * @param string $formattedTagsChunk
     * @return bool Return true if successful; otherwise return false
     */
    public function sendPurgeRequestToServers(Socket $socketAdapter, array $servers, string $formattedTagsChunk): bool
    {
        $headers = $this->getDefaultHeaders();
        $headers[self::HEADER_X_MAGENTO_TAGS_PATTERN] = $formattedTagsChunk;
        $unresponsiveServerError = [];
        foreach ($servers as $server) {
            $headers['Host'] = $server->getHost();
            try {
                $socketAdapter->connect($server->getHost(), $server->getPort());
                $socketAdapter->write(
                    'PURGE',
                    $server,
                    '1.1',
                    $headers
                );

                $read = $socketAdapter->read();

                $this->eventManager->dispatch('varnish_cache_purge_invalidate_result', [
                    'result' => $read,
                    'connected_to' => [$server->getHost(), $server->getPort()],
                ]);

                $socketAdapter->close();

            } catch (Exception $e) {
                $unresponsiveServerError[] = "Cache host: " . $server->getHost() . ":" . $server->getPort() .
                    "resulted in error message: " . $e->getMessage();

                $this->eventManager->dispatch('varnish_cache_purge_unresponsive_server', [
                    'server' => $server->getHost(),
                    'port' => $server->getPort(),
                ]);
            }
        }

        $errorCount = count($unresponsiveServerError);

        if ($errorCount > 0) {
            $loggerMessage = implode(" ", $unresponsiveServerError);

            if ($errorCount == count($servers)) {
                $this->logger->critical(
                    'No cache server(s) could be purged ' . $loggerMessage,
                    compact('servers', 'formattedTagsChunk')
                );
                return false;
            }

            $this->logger->warning(
                'Unresponsive cache server(s) hit' . $loggerMessage,
                compact('servers', 'formattedTagsChunk')
            );
        }

        $this->logger->execute(compact('servers', 'formattedTagsChunk'));
        return true;
    }

    public function getDefaultHeaders(): array
    {
        $headers = [];

        if ($this->isSoftPurgeEnabled()) {
            $headers[self::HEADER_X_MAGENTO_PURGE_SOFT] = '1';
        }

        return $headers;
    }

    public function isSoftPurgeEnabled(): bool
    {
        if (null === $this->isSoftPurgeEnabled){
           $this->isSoftPurgeEnabled = $this->scopeConfig->isSetFlag('system/full_page_cache/varnish/use_soft_purging');
        }

        return (bool) $this->isSoftPurgeEnabled;
    }
}
