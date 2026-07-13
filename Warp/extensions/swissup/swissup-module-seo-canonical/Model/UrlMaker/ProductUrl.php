<?php

namespace Swissup\SeoCanonical\Model\UrlMaker;

use Magento\Framework\DataObject;
use Magento\Catalog\Api\Data\ProductInterface;

class ProductUrl extends AbstractUrl
{
    public function getUrl(DataObject $entity): string
    {
        $product = $entity;
        $canonicalUrlAttribute = $this->scopeConfig->getValue(
            'swissup_seocanonical/product/use_attribute',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $product->getStoreId()
        );
        $hasAttribute = $canonicalUrlAttribute ?
            !!$product->getData($canonicalUrlAttribute) :
            false;

        return $hasAttribute ?
            $this->getUrlFromAttribute($product, $canonicalUrlAttribute) :
            $this->_getProductUrl($product);
    }

    private function _getProductUrl(ProductInterface $product): string
    {
        return $product
            ? $product->getUrlModel()->getUrl(
                $product,
                ['_ignore_category' => true]
            )
            : '';
    }
}
