<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Model\PurgeCache;

use Laminas\Http\Client\Adapter\Socket;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Manager;

class ExtendedLaminasSocket extends Socket
{
    public const HEADER_X_MAGENTO_PURGE_SOFT = 'X-Magento-Purge-Soft';

    protected ?Manager $eventManager = null;
    protected ?ScopeConfigInterface $scopeConfig = null;

    /**
     * @param Manager $eventManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Manager $eventManager, ScopeConfigInterface $scopeConfig)
    {
        parent::__construct();
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function read(): string
    {
        $read = parent::read();
        $this->eventManager->dispatch('varnish_cache_invalidate_result', ['result' => $read]);
        return $read;
    }

    public function write($method, $uri, $httpVer = '1.1', $headers = [], $body = '')
    {
        if ($this->scopeConfig->isSetFlag('system/full_page_cache/varnish/use_soft_purging')) {
            if (!isset($headers[self::HEADER_X_MAGENTO_PURGE_SOFT])) {
                $headers[self::HEADER_X_MAGENTO_PURGE_SOFT] = '1';
            }
        }

        return parent::write($method, $uri, $httpVer, $headers, $body);
    }
}
