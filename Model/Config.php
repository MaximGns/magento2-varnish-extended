<?php

declare(strict_types=1);

namespace Elgentos\VarnishExtended\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\PageCache\Model\Varnish\VclGeneratorFactory;
use Magento\Store\Model\ScopeInterface;

class Config extends PageCacheConfig
{
    private ScopeConfigInterface $scopeConfig;

    private Json $serializer;

    public const XML_PATH_VARNISH_ENABLE_BFCACHE = 'system/full_page_cache/varnish/enable_bfcache';

    public const XML_PATH_VARNISH_ENABLE_MEDIA_CACHE = 'system/full_page_cache/varnish/enable_media_cache';

    public const XML_PATH_VARNISH_ENABLE_STATIC_CACHE = 'system/full_page_cache/varnish/enable_static_cache';

    public const XML_PATH_VARNISH_TRACKING_PARAMETERS = 'system/full_page_cache/varnish/tracking_parameters';

    public const XML_PATH_VARNISH_USE_XKEY_VMOD = 'system/full_page_cache/varnish/use_xkey_vmod';

    public const XML_PATH_VARNISH_USE_SOFT_PURGING = 'system/full_page_cache/varnish/use_soft_purging';

    public const XML_PATH_VARNISH_PASS_ON_COOKIE_PRESENCE = 'system/full_page_cache/varnish/pass_on_cookie_presence';

    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\PageCache\Model\Varnish\VclGeneratorFactory $vclGeneratorFactory,
        Json $serializer
    ) {
        parent::__construct(
            $readFactory,
            $scopeConfig,
            $cacheState,
            $reader,
            $vclGeneratorFactory,
            $serializer
        );
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
    }

    public function getTrackingParameters(): string
    {
        return $this->scopeConfig->getValue(static::XML_PATH_VARNISH_TRACKING_PARAMETERS);
    }

    public function getUseXkeyVmod(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_VARNISH_USE_XKEY_VMOD);
    }

    public function getUseSoftPurging(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_VARNISH_USE_SOFT_PURGING);
    }

    public function getPassOnCookiePresence(): array
    {
        return $this->serializer->unserialize($this->scopeConfig->getValue(static::XML_PATH_VARNISH_PASS_ON_COOKIE_PRESENCE) ?? '{}');
    }

    public function getEnableBfcache(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_VARNISH_ENABLE_BFCACHE);
    }

    public function getSslOffloadedHeader()
    {
        return $this->scopeConfig->getValue(Request::XML_PATH_OFFLOADER_HEADER);
    }

    public function getBackendHost()
    {
        return $this->scopeConfig->getValue(static::XML_VARNISH_PAGECACHE_BACKEND_HOST);
    }

    public function getBackendPort()
    {
        return $this->scopeConfig->getValue(static::XML_VARNISH_PAGECACHE_BACKEND_PORT);
    }

    public function getAccessList()
    {
        $accessList = $this->_scopeConfig->getValue(static::XML_VARNISH_PAGECACHE_ACCESS_LIST);
        return array_map('trim', explode(',', $accessList));
    }

    public function getGracePeriod()
    {
        return $this->scopeConfig->getValue(static::XML_VARNISH_PAGECACHE_GRACE_PERIOD);
    }

    public function getDesignExceptions()
    {
        $expressions = $this->scopeConfig->getValue(
            \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
            ScopeInterface::SCOPE_STORE
        );

        return $expressions ? $this->serializer->unserialize($expressions) : [];
    }

    /**
     * @return bool
     */
    public function getEnableMediaCache(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_VARNISH_ENABLE_MEDIA_CACHE);
    }

    /**
     * @return bool
     */
    public function getEnableStaticCache(): bool
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_VARNISH_ENABLE_STATIC_CACHE);
    }
}
