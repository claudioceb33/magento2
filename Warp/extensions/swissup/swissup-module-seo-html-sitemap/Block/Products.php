<?php
namespace Swissup\SeoHtmlSitemap\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Catalog\Helper\Product as ProductHelper;
use \Magento\Catalog\Model\Product\Visibility;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\CatalogInventory\Helper\Stock;
use \Magento\Framework\View\Element\Template;
use \Swissup\SeoHtmlSitemap\Helper\Config;
use \Swissup\SeoHtmlSitemap\Model\Link as LinkModel;

class Products extends Template implements \Magento\Framework\DataObject\IdentityInterface
{
    private $collectionFactory;
    private $config;
    private $productHelper;
    private $stockFilter;
    private $visibility;
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Config $config,
        ProductHelper $productHelper,
        Stock $stockFilter,
        Visibility $visibility
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->productHelper = $productHelper;
        $this->stockFilter = $stockFilter;
        $this->visibility = $visibility;
        parent::__construct($context);
    }

    public function getCollection()
    {
        if (!$this->config->showProducts()) {
            return false;
        }

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addStoreFilter();
        $collection->setVisibility($this->visibility->getVisibleInSiteIds());

        if (!$this->config->showOutOfStockProducts()) {
            $this->stockFilter->addInStockFilterToCollection($collection);
        }

        $sortBy = $this->config->getSortBy();
        $collection->addAttributeToSort($sortBy);

        return $collection;
    }

    public function getItemUrl($product)
    {
        return $this->productHelper->getProductUrl($product);
    }

    public function getItemName($product)
    {
        return $product->getName();
    }

    public function getIdentities()
    {
        return [LinkModel::CACHE_TAG . '_' . 'products'];
    }
}
