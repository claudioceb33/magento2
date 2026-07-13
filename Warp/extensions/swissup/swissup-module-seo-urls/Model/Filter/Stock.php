<?php

namespace Swissup\SeoUrls\Model\Filter;

use \Magento\CatalogInventory\Model\Stock as InventoryStock;

class Stock extends AbstractPredefined
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultLabel()
    {
        return $this->helper->getPredefinedFilterLabel('stock_filter');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions()
    {
        return [
                (string)InventoryStock::STOCK_IN_STOCK => $this->helper->getSeoFriendlyString(__('In')),
                (string)InventoryStock::STOCK_OUT_OF_STOCK => $this->helper->getSeoFriendlyString(__('Out'))
            ];
    }
}
