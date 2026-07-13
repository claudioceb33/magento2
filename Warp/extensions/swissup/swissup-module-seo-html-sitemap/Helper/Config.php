<?php
namespace Swissup\SeoHtmlSitemap\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    /**
     * Path to store config for module to enable
     *
     * @var string
     */
    const XML_PATH_ENABLE = 'seohtmlsitemap/general/enabled';
    /**
     * Path to store config for site map page title
     *
     * @var string
     */
    const XML_PATH_PAGE_TITLE = 'seohtmlsitemap/general/page_title';
    /**
     * Path to store config for site map meta description
     *
     * @var string
     */
    const XML_PATH_META_DESCRIPTION = 'seohtmlsitemap/general/meta_description';
    /**
     * Path to store config for site map meta keywords
     *
     * @var string
     */
    const XML_PATH_META_KEYWORDS = 'seohtmlsitemap/general/meta_keywords';
    /**
     * Path to store config for showing stores on site map
     *
     * @var string
     */
    const XML_PATH_SHOW_STORES = 'seohtmlsitemap/content_settings/show_stores';
    /**
     * Path to store config for showing categories on site map
     *
     * @var string
     */
    const XML_PATH_SHOW_CATEGORIES = 'seohtmlsitemap/content_settings/show_categories';
    /**
     * Path to store config for showing max categories depth
     *
     * @var string
     */
    const XML_PATH_MAX_CATEGORIES_DEPTH = 'seohtmlsitemap/content_settings/max_categories_depth';
    /**
     * Path to store config for showing products on site map
     *
     * @var string
     */
    const XML_PATH_SHOW_PRODUCTS = 'seohtmlsitemap/content_settings/show_products';
    /**
     * Path to store config for showing out of stock products on site map
     *
     * @var string
     */
    const XML_PATH_SHOW_OUT_OF_STOCK = 'seohtmlsitemap/content_settings/show_out_of_stock';
    /**
     * Path to store config for sorting site map pages
     *
     * @var string
     */
    const XML_PATH_SORT_BY = 'seohtmlsitemap/content_settings/sort_by';
    /**
     * Path to store config for site map columns number
     *
     * @var string
     */
    const XML_PATH_COLUMNS_NUMBER = 'seohtmlsitemap/content_settings/columns_number';
    /**
     * Path to store config for grouping site map pages by first letter
     *
     * @var string
     */
    const XML_PATH_GROUP_BY_FIRST_LETTER = 'seohtmlsitemap/content_settings/group_by_first_letter';
    /**
     * Path to store config for showing CMS pages on site map
     *
     * @var string
     */
    const XML_PATH_SHOW_CMS = 'seohtmlsitemap/content_settings/show_cms';
    /**
     * Path to store config for exclude certain CMS pages from Sitemap
     *
     * @var string
     */
    const XML_PATH_EXCLUDE_CMS = 'seohtmlsitemap/content_settings/exclude_cms';
    /**
     * Path to store config for list of excluded CMS pages
     *
     * @var string
     */
    const XML_PATH_EXCLUDE_CMS_LIST = 'seohtmlsitemap/content_settings/exclude_cms_list';
    /**
     * Path to store config for showing custom links
     *
     * @var string
     */
    const XML_PATH_SHOW_CUSTOM_LINKS = 'seohtmlsitemap/content_settings/show_custom_links';

    protected function getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }
    public function isEnabled()
    {
        return (bool)$this->getConfig(self::XML_PATH_ENABLE);
    }
    public function getPageTitle()
    {
        return (String)$this->getConfig(self::XML_PATH_PAGE_TITLE);
    }
    public function getMetaDescription()
    {
        return (String)$this->getConfig(self::XML_PATH_META_DESCRIPTION);
    }
    public function getMetaKeywords()
    {
        return (String)$this->getConfig(self::XML_PATH_META_KEYWORDS);
    }
    public function showStores()
    {
        return (bool)$this->getConfig(self::XML_PATH_SHOW_STORES);
    }
    public function showCategories()
    {
        return (bool)$this->getConfig(self::XML_PATH_SHOW_CATEGORIES);
    }
    public function getMaxCategoriesDepth()
    {
        return abs((int)$this->getConfig(self::XML_PATH_MAX_CATEGORIES_DEPTH));
    }
    public function showProducts()
    {
        return (bool)$this->getConfig(self::XML_PATH_SHOW_PRODUCTS);
    }
    public function showOutOfStockProducts()
    {
        return (bool)$this->getConfig(self::XML_PATH_SHOW_OUT_OF_STOCK);
    }
    public function getSortBy()
    {
        return (String)$this->groupByFirstLetter() ? 'name' : $this->getConfig(self::XML_PATH_SORT_BY);
    }
    public function getColumnsNumber()
    {
        return (String)$this->getConfig(self::XML_PATH_COLUMNS_NUMBER);
    }
    public function groupByFirstLetter()
    {
        return (bool)$this->getConfig(self::XML_PATH_GROUP_BY_FIRST_LETTER);
    }
    public function showCms()
    {
        return (bool)$this->getConfig(self::XML_PATH_SHOW_CMS);
    }
    public function excludeCmsPages()
    {
        return (bool)$this->getConfig(self::XML_PATH_EXCLUDE_CMS);
    }
    public function getExcludedCmsPages()
    {
        return explode(',', $this->getConfig(self::XML_PATH_EXCLUDE_CMS_LIST));
    }
    public function showCustomLinks()
    {
        return (bool)$this->getConfig(self::XML_PATH_SHOW_CUSTOM_LINKS);
    }
}
