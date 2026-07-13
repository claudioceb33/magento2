<?php

namespace Swissup\ProLabels\Observer;

use Swissup\ProLabels\Model\Stock;
use Swissup\ProLabels\Model\LabelsProvider;

class PreloadLabels implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Stock
     */
    protected $stock;

    /**
     * @var LabelsProvider
     */
    protected $labelsProvider;

    /**
     * @param Stock          $stock
     * @param LabelsProvider $labelsProvider
     */
    public function __construct(
        Stock $stock,
        LabelsProvider $labelsProvider
    ) {
        $this->stock = $stock;
        $this->labelsProvider = $labelsProvider;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($collection->getFlag(SetFlagPreloadLabels::FLAG_NAME)) {
            $products = $collection->getItems();
            if ($this->stock->isStockProlabelEnabled('category')) {
                $this->stock->preloadStockForProducts($products);
            }

            $this->labelsProvider->preloadManualForProducts($products, 'category');

            // unset flag
            $collection->getFlag(SetFlagPreloadLabels::FLAG_NAME, null);
        }
    }
}
