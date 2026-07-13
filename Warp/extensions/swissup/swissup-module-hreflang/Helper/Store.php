<?php

namespace Swissup\Hreflang\Helper;

use Magento\Store\Model\ScopeInterface;
use Swissup\Hreflang\Model\Config\Source\StoreView;
use Swissup\Hreflang\Model\Config\Source\ValueStrategy;

class Store
{
    const XML_PATH_LOCALE_IN_URL = 'swissup_hreflang/url/add_locale';
    const XML_PATH_REMOVE_REGION = 'swissup_hreflang/url/remove_region';
    const XML_PATH_REMOVE_STORECODE = 'swissup_hreflang/url/remove_store_code';
    const XML_PATH_HREFLANG_IN_PAGE = 'swissup_hreflang/general/enabled';
    const XML_PATH_HREFLANG_IN_XMLSITEMAP = 'swissup_hreflang/general/enabled_xml';
    const XML_PATH_IS_ALL_WEBSITES = 'swissup_hreflang/general/all_websites';
    const XML_PATH_ALLOWED_WEBSITES = 'swissup_hreflang/general/allowed_websites';
    const XML_PATH_EXCLUDE_STORE = 'swissup_hreflang/general/excluded';
    const XML_PATH_VALUE_STRATEGY = 'swissup_hreflang/general/value_strategy';
    const XML_PATH_CUSTOM_LOCALE = 'swissup_hreflang/general/custom_locale';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var boolean
     */
    protected $localeInUrlProcessed = false;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $currentStore;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get locale for store
     *
     * @param  \Magento\Store\Model\Store $store
     * @return string
     */
    public function getLocale(\Magento\Store\Model\Store $store)
    {
        $locale = '';
        switch ($store->getConfig(self::XML_PATH_VALUE_STRATEGY)) {
            case ValueStrategy::CUSTOM_LOCALE:
                $locale = str_replace(
                    '-',
                    '_',
                    $store->getConfig(self::XML_PATH_CUSTOM_LOCALE).''
                );
                break;

            default:
                $localePath = \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE;
                $locale = $store->getConfig($localePath);
                break;
        }

        return $locale;
    }

    /**
     * Get hreflang for store
     *
     * @param  \Magento\Store\Model\Store $store
     * @return string
     */
    public function getHreflang(\Magento\Store\Model\Store $store)
    {
        $locale = $this->getLocale($store);
        $parts = explode('_', $locale);
        if (count($parts)) {
            $parts = array_map('strtolower', $parts);
            $isRemoveRegion = $this->scopeConfig->isSetFlag(
                self::XML_PATH_REMOVE_REGION,
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
            if ($isRemoveRegion) {
                $parts = array_slice($parts, 0, 1);
            }

            return implode('-', $parts);
        }

        return '';
    }

    /**
     * Is add locale in url enabled
     *
     * @param  \Magento\Store\Model\Store $store
     * @return boolean
     */
    public function isLocaleInUrl(\Magento\Store\Model\Store $store)
    {
        return $this->scopeConfig->isSetFlag(
                self::XML_PATH_LOCALE_IN_URL,
                ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
    }

    /**
     * Get store manager
     *
     * @return return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Is $store Admin
     *
     * @param  \Magento\Store\Model\Store $store
     * @return boolean
     */
    public function isAdmin(\Magento\Store\Model\Store $store)
    {
        return $store->getCode() == \Magento\Store\Model\Store::ADMIN_CODE;
    }

    /**
     * Is redirect allowed
     *
     * @return boolean
     */
    public function isRedirectAllowed()
    {
        return $this->isLocaleInUrl($this->storeManager->getStore())
            && !$this->localeInUrlProcessed;
    }

    /**
     * Set value for flag locale_in_url_processed
     *
     * @param boolean $flag
     */
    public function setLocaleInUrlProcessed($flag = true)
    {
        $this->localeInUrlProcessed = $flag;
        return $this;
    }

    /**
     * Is add hreflang to page head for $store
     *
     * @param  \Magento\Store\Model\Store $store
     * @return boolean
     */
    public function isEnabledInPage(\Magento\Store\Model\Store $store)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_HREFLANG_IN_PAGE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Is add hreflang to XML Sitemap for $store
     *
     * @param  \Magento\Store\Model\Store $store
     * @return boolean
     */
    public function isEnabledInXmlSitemap(\Magento\Store\Model\Store $store)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_HREFLANG_IN_XMLSITEMAP,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Get x-default store
     *
     * @return \Magento\Store\Model\Store|null
     */
    public function getXDefaultStore(\Magento\Store\Model\Store $store)
    {
        $id = (int)$this->scopeConfig->getValue(
            'swissup_hreflang/general/default_store',
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        return $id === StoreView::NOT_SPECIFIED ?
            null :
            $this->storeManager->getStore($id);
    }

    /**
     * Is $store excluded from hreflang data
     *
     * @param  \Magento\Store\Model\Store $store
     * @return boolean
     */
    public function isExcluded(\Magento\Store\Model\Store $store)
    {
        // check if store view is disabled
        if (!$store->isActive()) {
            return true;
        }

        // check module config Excluded for this store
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EXCLUDE_STORE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Is store_code parameter remove enabled from URL for $store.
     *
     * @param  \Magento\Store\Model\Store $store
     * @return boolean
     */
    public function isRemoveStorecode(\Magento\Store\Model\Store $store)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_REMOVE_STORECODE,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * @param \Magento\Store\Model\Store $store
     */
    public function setCurrentStore(\Magento\Store\Model\Store $store = null)
    {
        $this->currentStore = $store;
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->currentStore;
    }

    /**
     * @param  \Magento\Store\Model\Store                 $store
     * @return \Magento\Store\Api\Data\WebsiteInterface[]
     */
    public function getAllowedWebsites(\Magento\Store\Model\Store $store)
    {
        $websites = $this->storeManager->getWebsites();
        $isAll = $this->scopeConfig->isSetFlag(
            self::XML_PATH_IS_ALL_WEBSITES,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        if ($isAll) {
            return $websites;
        }

        $allowedIds = (string)$this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_WEBSITES,
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
        $allowedIds = explode(',', $allowedIds);

        foreach ($websites as $key => $website) {
            if (!in_array($website->getId(), $allowedIds)) {
                unset($websites[$key]);
            }
        }

        return $websites;
    }
}
