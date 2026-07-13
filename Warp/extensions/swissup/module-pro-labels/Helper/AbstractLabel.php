<?php

namespace Swissup\ProLabels\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * ProLabels Abstract Label Helper
 */
class AbstractLabel extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Swissup\ProLabels\Model\Stock
     */
    protected $stock;

    /**
     * @var \Swissup\ProLabels\Model\Price
     */
    protected $price;

    /**
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Swissup\ProLabels\Model\Stock                       $stock
     * @param \Swissup\ProLabels\Model\Price                       $price
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Swissup\ProLabels\Model\Stock $stock,
        \Swissup\ProLabels\Model\Price $price
    ) {
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->stock = $stock;
        $this->price = $price;
        parent::__construct($context);
    }

    /**
     * @param array $config
     * @return \Magento\Framework\DataObject
     */
    public function getLabelOutputObject($config)
    {
        return new \Magento\Framework\DataObject(
            array_filter($config, function ($key) {
                return in_array(
                    $key,
                    [
                        'sort_order',
                        'position',
                        'text',
                        'image',
                        'custom',
                        'custom_url',
                        'round_method',
                        'round_value',
                        'target_element',
                        'insert_method'
                    ]
                );
            }, ARRAY_FILTER_USE_KEY)
        );
    }

    /**
     * @param  ProductInterface $product
     * @return float
     */
    public function getStockQty(ProductInterface $product)
    {
        return $this->stock->getQty($product);
    }

    /**
     * @param  ProductInterface $product
     * @return boolean
     */
    public function isInStock(ProductInterface $product)
    {
        return $this->stock->isInStock($product);
    }

    /**
     * Check If Product Is New
     * @param  ProductInterface $product
     * @return bool
     */
    public function isNew(ProductInterface $product)
    {
        $store           = $this->storeManager->getStore()->getId();
        $specialNewsFrom = $product->getNewsFromDate();
        $specialNewsTo   = $product->getNewsToDate();
        if ($specialNewsFrom ||  $specialNewsTo) {
            return $this->localeDate->isScopeDateInInterval($store, $specialNewsFrom, $specialNewsTo);
        }

        return false;
    }

    public function getUploadedLabelImage($imagePath, $mode)
    {
        $baseMediaUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $baseMediaUrl . "prolabels/{$mode}/" . $imagePath;
    }
}
