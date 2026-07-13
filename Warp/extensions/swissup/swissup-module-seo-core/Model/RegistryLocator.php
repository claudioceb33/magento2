<?php

namespace Swissup\SeoCore\Model;

class RegistryLocator
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\Data\CategoryInterface
     */
    protected $category;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $product;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }

        return $this->product;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getCategory()
    {
        if (!$this->category) {
            $this->category = $this->registry->registry('current_category');
        }

        return $this->category;
    }
}
