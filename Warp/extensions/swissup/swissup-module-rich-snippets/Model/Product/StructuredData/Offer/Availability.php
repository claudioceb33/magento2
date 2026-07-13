<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData\Offer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Output as Helper;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\BackOrderNotifyCustomerCondition;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\Product\Config;

class Availability implements DataSnippetInterface
{
    private Config $config;
    private Helper $helper;
    private ObjectManagerInterface $objectManager;
    private ProductInterface $product;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Config $config,
        Helper $helper,
        ObjectManagerInterface $objectManager,
        ProductInterface $product,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->product = $product;
        $this->storeManager = $storeManager;
    }

    public function get()
    {
        $storeId = $this->product->getStoreId();
        if (!$this->config->isAvailabilityEnabled($storeId)) {
            return '';
        }

        $availability = 'http://schema.org/OutOfStock';
        if ($this->product->isSaleable()){
            $availability = 'http://schema.org/InStock';
            if ($this->isBackorderAllowed()) {
                $availability = 'http://schema.org/PreOrder';
            }
        }

        return $availability;
    }

    private function isBackorderAllowed()
    {
        if (!$this->helper->isModuleOutputEnabled('Magento_InventorySales')) {
            return false;
        }

        $store = $this->storeManager->getStore($this->product->getStoreId());
        $stockResolver = $this->objectManager->get(
            StockResolverInterface::class
        );
        $stock = $stockResolver->execute(
            SalesChannelInterface::TYPE_WEBSITE,
            $store->getWebsite()->getCode()
        );

        $backOrderNotifyCustomerCondition = $this->objectManager->get(
            BackOrderNotifyCustomerCondition::class
        );

        try {
            $productSalableResult = $backOrderNotifyCustomerCondition->execute(
                $this->product->getSku(),
                (int)$stock->getId(),
                1
            );
        } catch (\Magento\Framework\Exception\InputException $e) {
            // Such exception occurs when product type doesn't support backorder.
            // Though such exception should not be raised.
            return false;
        }

        if ($productSalableResult->isSalable()) {
            // product is available for sale in quantity = 1
            // no need for backorder
            return false;
        };

        // product not availbale in qty = 1
        // backorder is the only option
        return true;
    }
}
