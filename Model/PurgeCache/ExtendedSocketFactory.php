<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Model\PurgeCache;

use Magento\CacheInvalidate\Model\SocketFactory;
use Elgentos\VarnishExtended\Model\PurgeCache\ExtendedLaminasSocket;
use Elgentos\VarnishExtended\Model\PurgeCache\ExtendedLaminasSocketFactory;

class ExtendedSocketFactory extends SocketFactory
{
    protected ExtendedLaminasSocketFactory $socketFactory;

    /**
     * @param ExtendedLaminasSocketFactory $socketFactory
     */
    public function __construct(ExtendedLaminasSocketFactory $socketFactory)
    {
        $this->socketFactory = $socketFactory;
    }

    /**
     * Create extended socket
     *
     * @return ExtendedLaminasSocket
     */
    public function create(): ExtendedLaminasSocket
    {
        return $this->socketFactory->create();
    }
}
