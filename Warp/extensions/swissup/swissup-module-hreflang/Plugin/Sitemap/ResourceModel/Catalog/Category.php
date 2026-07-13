<?php
/**
 * Plugins for methods in \Magento\Sitemap\Model\ResourceModel\Catalog\Category
 */
namespace Swissup\Hreflang\Plugin\Sitemap\ResourceModel\Catalog;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Category extends AbstractEntity
{
    /**
     * {@inheritdoc}
     */
    protected $entityType = 'category';

    /**
     * {@inheritdoc}
     */
    protected function prepareData(array $items)
    {
        $this->hreflangData->preloadCategoryStatusData($items);

        return parent::prepareData($items);
    }

    /**
     * {@inheritdoc}
     */
    public function isItemEnabled(\Magento\Framework\DataObject $item, $store): bool
    {
        return $this->hreflangData->isCategoryEnabled($item, $store);
    }
}
