<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData\Offer;

use Magento\Catalog\Api\Data\ProductInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\Product\Config;

class PriceValidUntil implements DataSnippetInterface
{
    private Config $config;
    private ProductInterface $product;

    public function __construct(
        Config $config,
        ProductInterface $product
    ) {
        $this->config = $config;
        $this->product = $product;
    }

    public function get(array $dataMap = [])
    {
        $product = $this->product;
        $storeId = $product->getStoreId();
        $validUntil = '';
        $specialPrice = $product->getPriceInfo()->getPrice('special_price');
        if ($specialPrice) {
            $validUntil = $specialPrice->getSpecialToDate();
        }

        return $validUntil ?: $this->config->getValidUntil($storeId);
    }
}
