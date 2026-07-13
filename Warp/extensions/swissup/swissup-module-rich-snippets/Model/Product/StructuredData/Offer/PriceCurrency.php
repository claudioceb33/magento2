<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData\Offer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\Product\Config;

class PriceCurrency implements DataSnippetInterface
{
    private ProductInterface $product;
    private StoreManagerInterface $storeManager;

    public function __construct(
        ProductInterface $product,
        StoreManagerInterface $storeManager
    ) {
        $this->product = $product;
        $this->storeManager = $storeManager;
    }

    public function get()
    {
        $store = $this->storeManager->getStore($this->product->getStoreId());
        $currency = $store->getCurrentCurrency();

        return $currency->getCode();
    }
}
