<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\NotFoundException;
use Swissup\RichSnippets\Model\DataSnippetInterface;

class Brand implements DataSnippetInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ProductInterface                                   $product
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ProductInterface $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->product = $product;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get 'brand' for product structured data
     *
     * @return array
     */
    public function get()
    {
        if (!$this->product->getId()) {
            throw new NotFoundException(__('Product not found.'));
        }

        $store = $this->storeManager->getStore($this->product->getStoreId());
        $attributeCode = $this->scopeConfig->getValue(
            'richsnippets/product/brand/attribute',
            ScopeInterface::SCOPE_STORE,
            $store
        );
        $attribute = $this->product->getResource()->getAttribute($attributeCode);
        $value = $attribute
            ? $attribute->getFrontend()->getValue($this->product)
            : null;

        return $value
            ? ['@type' => 'Brand', 'name' => $value]
            : [];
    }
}
