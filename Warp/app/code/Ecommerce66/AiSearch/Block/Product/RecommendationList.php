<?php
/**
 * Copyright © Ecommerce66. All rights reserved.
 */
declare(strict_types=1);

namespace Ecommerce66\AiSearch\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class RecommendationList extends ListProduct
{
    /**
     * @var Collection|null
     */
    private ?Collection $aiProductCollection = null;

    /**
     * @param Collection $collection
     * @return $this
     */
    public function setProductCollection(Collection $collection): self
    {
        $this->aiProductCollection = $collection;
        $this->_productCollection = $collection;
        return $this;
    }

    /**
     * @return Collection
     */
    protected function _getProductCollection()
    {
        if ($this->aiProductCollection) {
            $this->_productCollection = $this->aiProductCollection;
            return $this->_productCollection;
        }

        return parent::_getProductCollection();
    }

    /**
     * @return Collection
     */
    public function getLoadedProductCollection()
    {
        $collection = $this->_getProductCollection();

        if (!$collection->isLoaded()) {
            $collection->load();
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return 'grid';
    }

    /**
     * @return bool
     */
    public function isEnabledViewSwitcher()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getToolbarHtml()
    {
        return '';
    }

    /**
     * Ensure a price renderer is available when rendering outside regular layout handles.
     */
    protected function getPriceRender()
    {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        return $priceRender ? $priceRender->setData('is_product_list', true) : null;
    }
}
