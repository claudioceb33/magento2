<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Helper;

use Magento\Store\Model\ScopeInterface;

class Feeds extends Data
{
    private const XML_SECTION = 'ecommerce66_ai/feeds/';

    public const XML_PATH_CATALOG_CRON  = self::XML_SECTION . 'catalog_cron';
    public const XML_PATH_CATALOG_TYPE  = self::XML_SECTION . 'catalog_type';
    public const XML_PATH_CATALOG_ATTRS = self::XML_SECTION . 'catalog_attributes';
    public const XML_PATH_BRAND_SOURCE = self::XML_SECTION . 'brand_attribute';

    public const XML_PATH_STOCK_CRON    = self::XML_SECTION . 'stock_cron';
    public const XML_PATH_STOCK_TYPE    = self::XML_SECTION . 'stock_type';

    /** Base fields always exported (NOW includes 'categories') */
    public const DEFAULT_FIELDS = [
        'sku','name','price','special_price','minimal_price','small_image',
        'brand','url_key','stock_status','categories'
    ];

    public function getBrandSourceAttribute(?int $storeId = null): string
    {
        $code = (string)$this->scopeConfig->getValue(self::XML_PATH_BRAND_SOURCE, ScopeInterface::SCOPE_STORE, $storeId);
        return $code !== '' ? $code : 'manufacturer';
    }

    public function getCatalogCronExpr(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CATALOG_CRON, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCatalogFormat(?int $storeId = null): string
    {
        $val = (string)$this->scopeConfig->getValue(self::XML_PATH_CATALOG_TYPE, ScopeInterface::SCOPE_STORE, $storeId);
        return in_array($val, ['csv','json'], true) ? $val : 'csv';
    }

    /**
     * @return string[] Extra attribute codes
     */
    public function getCatalogExtraAttributes(?int $storeId = null): array
    {
        $raw = (string)$this->scopeConfig->getValue(self::XML_PATH_CATALOG_ATTRS, ScopeInterface::SCOPE_STORE, $storeId);
        if ($raw === '') {
            return [];
        }
        $codes = array_filter(array_map('trim', explode(',', $raw)));
        // Remove duplicates and excluded (defaults go always)
        $codes = array_values(array_diff(array_unique($codes), self::DEFAULT_FIELDS));
        return $codes;
    }

    public function getStockCronExpr(?int $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_STOCK_CRON, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getStockFormat(?int $storeId = null): string
    {
        $val = (string)$this->scopeConfig->getValue(self::XML_PATH_STOCK_TYPE, ScopeInterface::SCOPE_STORE, $storeId);
        return in_array($val, ['csv','json'], true) ? $val : 'csv';
    }
}
