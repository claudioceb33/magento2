<?php

namespace Swissup\SeoCanonical\Helper;

use Magento\Store\Model\ScopeInterface;

class Category extends \Magento\Catalog\Helper\Category
{
    /**
     * Is allowed to build canonical url with this module
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function canUseAdvancedCanonicalTag($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            Config::XML_PATH_CATEGORY_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function canUseCanonicalTag($store = null)
    {
        if ($this->canUseAdvancedCanonicalTag()) {
            /* Disable Magento canonical URL to use this module canonical. */
            return false;
        }

        return parent::canUseCanonicalTag($store);
    }
}
