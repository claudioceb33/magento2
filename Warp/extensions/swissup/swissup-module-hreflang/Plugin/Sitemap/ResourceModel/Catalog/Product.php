<?php
/**
 * Plugins for methods in \Magento\Sitemap\Model\ResourceModel\Catalog\Product
 */
namespace Swissup\Hreflang\Plugin\Sitemap\ResourceModel\Catalog;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Swissup\Hreflang\Model\ResourceModel\Product as ProductResource;

class Product extends AbstractEntity
{
    /**
     * {@inheritdoc}
     */
    protected $entityType = 'product';

    /**
     * {@inheritdoc}
     */
    protected function prepareData(array $items)
    {
        $this->hreflangData->preloadProductStatusData($items);

        return parent::prepareData($items);
    }

    /**
     * {@inheritdoc}
     */
    public function isItemEnabled(\Magento\Framework\DataObject $item, $store): bool
    {
        return $this->hreflangData->isProductEnabled($item, $store);
    }
}
