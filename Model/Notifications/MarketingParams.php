<?php

namespace Elgentos\VarnishExtended\Model\Notifications;

use Magento\Framework\FlagManager;

class MarketingParams implements NotificationInterface
{

    public const VARNISH_MARKETING_PARAMS = 'varnish-marketing-params';

    public function __construct(
        private readonly FlagManager $flagManager,
    ) {}

    public function getIdentity()
    {

    }

    public function isDisplayed()
    {
        return (bool)$this->flagManager->getFlagData(self::VARNISH_MARKETING_PARAMS);
    }

    public function getText()
    {
        return '<p>' . __('We found marketing parameter(s) which are stripped in Varnish, this could lead to filtering not working properly on category pages.') . '</p>';
    }

    public function getSeverity()
    {
    }
}