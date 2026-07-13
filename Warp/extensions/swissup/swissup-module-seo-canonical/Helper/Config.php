<?php

namespace Swissup\SeoCanonical\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH_CATEGORY_ENABLED = 'swissup_seocanonical/category/enabled';

    const XML_PATH_CATEGORY_FORCE_STORE_ID = 'swissup_seocanonical/category/force_store_id';

    const XML_PATH_CATEGORY_CUSTOM_BASE_URL = 'swissup_seocanonical/category/custom_base_url';

    const XML_PATH_PRODUCT_ENABLED = 'swissup_seocanonical/product/enabled';

    const XML_PATH_PRODUCT_FORCE_STORE_ID = 'swissup_seocanonical/product/force_store_id';

    const XML_PATH_PRODUCT_CUSTOM_BASE_URL = 'swissup_seocanonical/product/custom_base_url';

    const XML_PATH_PRODUCT_USE_PARENT = 'swissup_seocanonical/product/use_parent';

    /**
     * Get ID of Store View to force it for category canonical URL
     *
     * @param  null|string|bool|int $store
     * @return string
     */
    public function getCategoryForcedStoreId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_FORCE_STORE_ID,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param  null|string|bool|int $store
     * @return string
     */
    public function getCategoryCustomBaseUrl($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_CUSTOM_BASE_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Can use parent product for product canonical URL
     *
     * @param  null|string|bool|int $store
     * @return boolean
     */
    public function canProductUseParent($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_PRODUCT_USE_PARENT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get ID of Store View to force it for product canonical URL
     *
     * @param  null|string|bool|int $store
     * @return string
     */
    public function getProductForcedStoreId($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_FORCE_STORE_ID,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param  null|string|bool|int $store
     * @return string
     */
    public function getProductCustomBaseUrl($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_CUSTOM_BASE_URL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
