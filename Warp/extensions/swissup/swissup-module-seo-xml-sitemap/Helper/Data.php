<?php

namespace Swissup\SeoXmlSitemap\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Sitemap\Helper\Data
{
    /**
     * Include images policy xpath config for categories
     */
    const XML_PATH_CATEGORY_IMAGES_INCLUDE = 'swissup_xmlsitemap/category/image_include';

    /**
     * Change frequency xpath config settings for other links
     */
    const XML_PATH_OTHER_CHANGEFREQ = 'swissup_xmlsitemap/other/changefreq';

    /**
     * Priority xpath config settings for other links
     */
    const XML_PATH_OTHER_PRIORITY = 'swissup_xmlsitemap/other/priority';

    /**
     * Get other links change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getOtherChangefreq($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_OTHER_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get other links priority
     *
     * @param int $storeId
     * @return string
     */
    public function getOtherPriority($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_OTHER_PRIORITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get category image include policy
     *
     * @param int $storeId
     * @return string
     */
    public function getCategoryImageIncludePolicy($storeId)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_IMAGES_INCLUDE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
