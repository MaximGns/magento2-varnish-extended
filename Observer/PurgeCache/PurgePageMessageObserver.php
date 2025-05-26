<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Observer\PurgeCache;

use Elgentos\VarnishExtended\Model\Source\PurgePageMessage;
use Laminas\Http\Header\ContentType;
use Laminas\Http\Response;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\Manager;

class PurgePageMessageObserver implements ObserverInterface
{
    public function __construct(
        private readonly Manager $manager,
        private readonly ScopeConfigInterface $scopeConfig,
    )
    {
    }

    /**
     * Observer for varnish_cache_invalidate_result
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $flushMessageConfig = (int)$this->scopeConfig->getValue('system/full_page_cache/varnish/flush_page_message');

        dd($observer->getData());
        if ($flushMessageConfig === PurgePageMessage::VARNISH_PURGE_NOTIFICATION_NEVER){
            return;
        }

        $event = $observer->getEvent();

        if ($flushMessageConfig === PurgePageMessage::VARNISH_PURGE_NOTIFICATION_ONLY_ALL
            && $event->getData('tags') !== '.*'
        ) {
            return;
        }

        $result = $event->getData('result');

        if (false === $result instanceof Response) {
            return;
        }

        $result = $this->readInvalidatedResults($result);

        if (empty($result)) {
            return;
        }

        $this->addConnectedHostsToResult($result, $event->getData('connected_to') ?? []);
        $this->manager->addSuccessMessage($result);
    }

    protected function readInvalidatedResults(Response $response): ?string
    {
        $contentType = $response->getHeaders()->get('Content-Type');
        if (false === $contentType instanceof ContentType) {
            return null;
        }

        $mediaType = $contentType->getMediaType();
        if ($mediaType === 'application/json') {
            dd($response->getBody());
        }

        if ($mediaType === 'text/html') {
            try {
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($response->getBody());
                libxml_clear_errors();

                $paragraphs = $dom->getElementsByTagName('p');
                if ($paragraphs->length > 0) {
                    $firstParagraph = trim($paragraphs->item(0)->textContent);
                    if (!empty($firstParagraph)) {
                        return $firstParagraph;
                    }
                }
                return null;
            }catch (\Throwable $exception){
                return null;
            }
        }

        return null;
    }

    public function addConnectedHostsToResult(string &$result, array $connectedTo): void
    {
        if (empty($connectedTo)) {
            return;
        }

        $result .= sprintf(' (%s)', implode(':', $connectedTo));
    }
}
