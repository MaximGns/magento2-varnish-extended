<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Observer\PurgeCache;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\Manager;

class ReadVarnishFlushResult implements ObserverInterface
{
    public function __construct(private readonly Manager $manager)
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
        $event = $observer->getEvent();
        $result = $event->getData('result');

        if (empty($result)) {
            return;
        }

        $result = $this->readInvalidatedResults($result);
        $this->addConnectedHostsToResult($result, $event->getData('connected_to'));;

        $this->manager->addSuccessMessage($result);
    }

    protected function readInvalidatedResults(string $read): string
    {
        $result = '';
        try {
            list($headers, $body) = explode("\r\n\r\n", $read, 2);

            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($body);
            libxml_clear_errors();

            $paragraphs = $dom->getElementsByTagName('p');
            if ($paragraphs->length > 0) {
                $firstParagraph = trim($paragraphs->item(0)->textContent);
                if (!empty($firstParagraph)) {
                    return $firstParagraph;
                }
            }
        } catch (\Throwable $exception) {
            return $result;
        }

        return $result;
    }

    public function addConnectedHostsToResult(string &$result, array $connectedTo): void
    {
        $result.= sprintf(' (%s)', implode(':', $connectedTo));
    }
}
