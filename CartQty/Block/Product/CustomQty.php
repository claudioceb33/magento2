<?php

namespace Ceb\CartQty\Block\Product;

use Magento\Catalog\Block\Product\ProductList\Item\Block;

/**
 * Class AddButton
 * @package Aheadworks\Ctq\Block\QuoteList\ProductList\Item
 */
class CustomQty extends Block
{

    /**
     * @inheritDoc
     */
    public function getProduct()
    {
        $product = parent::getProduct();

        return $product ?: $this->_coreRegistry->registry('product');
    }
}
