<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData\Offer;

use Magento\Catalog\Api\Data\ProductInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;

class Price implements DataSnippetInterface
{
    private ProductInterface $product;

    public function __construct(
        ProductInterface $product
    ) {
        $this->product = $product;
    }

    public function get()
    {
        $product = $this->product;
        $priceModel  = $product->getPriceModel();
        $productType = $product->getTypeInstance();
        if ('bundle' === $product->getTypeId()) {
            return min($priceModel->getTotalPrices($product));
        }

        if ('grouped' === $product->getTypeId()) {
            return $product->getPriceInfo()->getPrice('final_price')->getValue();
        }

        $minPrice   = $product->getMinimalPrice();
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')
            ->getAmount()->getValue();
        if ($minPrice) {
            return min($minPrice, $finalPrice);
        }

        return $finalPrice;
    }
}
