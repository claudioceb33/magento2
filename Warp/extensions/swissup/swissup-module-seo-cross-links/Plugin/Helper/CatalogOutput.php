<?php

namespace Swissup\SeoCrossLinks\Plugin\Helper;

use Swissup\SeoCrossLinks\Helper\Data;
use Swissup\SeoCrossLinks\Model\Filter;
use Swissup\SeoCrossLinks\Model\Link;
use Swissup\SeoCrossLinks\Model\AttributeModel\ProductAttributes;
use Swissup\SeoCrossLinks\Model\AttributeModel\ExtraProductAttributes;

class CatalogOutput
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var ProductAttributes
     */
    private $productAttr;

    /**
     * @var ExtraProductAttributes
     */
    private $extraProductAttr;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Data $helper
     * @param Filter $filter
     */
    public function __construct(
        Data $helper,
        Filter $filter,
        ProductAttributes $productAttr,
        ExtraProductAttributes $extraProductAttr,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->filter = $filter;
        $this->productAttr = $productAttr;
        $this->extraProductAttr = $extraProductAttr;
        $this->storeManager = $storeManager;
    }

    /**
     * @param mixed $subject
     * @param mixed $result
     * @return mixed
     */
    public function aroundCategoryAttribute(
        \Magento\Catalog\Helper\Output $subject,
        $proceed,
        $category,
        $attributeHtml,
        $attributeName
    ) {
        $result = $proceed($category, $attributeHtml, $attributeName);
        $supportedAttributes = [
            'description'
        ];

        if (!$this->helper->IsEnabled() || !in_array($attributeName, $supportedAttributes)) {
            return $result;
        }

        if (!empty($result) && is_string($result)) {
            $result = $this->filter
                ->setMode(Link::SEARCH_IN_СATEGORY)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->filter($result);
        }

        return $result;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed $result
     */
    public function afterProductAttribute(
        \Magento\Catalog\Helper\Output $subject,
        $result,
        $product,
        $attributeHtml,
        $attributeName
    ) {
        $arrayAttributes = $this->productAttr->afterGetProductAttributes();
        $arrayLinksProductAttributes  = $this->extraProductAttr->getExtraProductAttributes();

        $supportedAttributes = [
            'description',
            'short_description',
        ];
        if ($arrayLinksProductAttributes) {
            foreach($arrayLinksProductAttributes as $item) {
                if (in_array($item,$arrayAttributes)) {
                    $supportedAttributes[] = $item;
                }
            }
        }

        if (!$this->helper->IsEnabled() || !in_array($attributeName, $supportedAttributes)) {
            return $result;
        }

        if (!empty($result) && is_string($result)) {
            $result = $this->filter
                ->setMode(Link::SEARCH_IN_PRODUCT)
                ->setStoreId($this->storeManager->getStore()->getId())
                ->filter($result);
        }

        return $result;
    }
}
