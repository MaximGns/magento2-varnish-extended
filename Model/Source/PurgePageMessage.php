<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PurgePageMessage implements OptionSourceInterface
{
    public const VARNISH_PURGE_NOTIFICATION_NEVER = 0;
    public const VARNISH_PURGE_NOTIFICATION_ONLY_ALL = 1;
    public const VARNISH_PURGE_NOTIFICATION_ALWAYS = 2;

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::VARNISH_PURGE_NOTIFICATION_NEVER,
                'label' => __('Never'),
            ],
            [
                'value' => self::VARNISH_PURGE_NOTIFICATION_ONLY_ALL,
                'label' => __('Only full purge'),
            ],
            [
                'value' => self::VARNISH_PURGE_NOTIFICATION_ALWAYS,
                'label' => __('Always'),
            ],
        ];
    }
}
