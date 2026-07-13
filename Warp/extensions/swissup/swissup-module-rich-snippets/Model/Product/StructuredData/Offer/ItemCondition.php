<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData\Offer;

use Magento\Catalog\Api\Data\ProductInterface;
use Swissup\RichSnippets\Model\DataSnippetInterface;
use Swissup\RichSnippets\Model\Product\Config;

class ItemCondition implements DataSnippetInterface
{
    private ProductInterface $product;
    private Config $config;

    public function __construct(
        Config $config,
        ProductInterface $product
    ) {
        $this->config = $config;
        $this->product = $product;
    }

    public function get()
    {
        $itemCondition = 'http://schema.org/NewCondition';
        $storeId = $this->product->getStoreId();
        $conditionAttributeCode = $this->config->getConditionAttributeCode($storeId);
        $frontendValue = $this->getAttributeFrontendValue(
            $conditionAttributeCode,
            true
        );
        if (!$frontendValue) {
            return $itemCondition;
        }

        foreach ($this->config->getConditionOptions($storeId) as $condition => $configValue) {
            if ($configValue == $frontendValue) {
                $condition = ucfirst($condition);
                $itemCondition = "http://schema.org/{$condition}Condition";
                break;
            }
        }

        return $itemCondition;
    }

    private function getAttributeFrontendValue(
        $attributeCode,
        $defaultValue = false
    ): string {
        if (!$attributeCode) {
            return '';
        }

        $value = $this->product->getData($attributeCode);
        if (!$attributeCode || !$value) {
            return '';
        }

        $attribute = $this->product->getResource()->getAttribute($attributeCode);
        /** @var options array Default (admin) labels */
        $options = $attribute->getSource()->getAllOptions(false, $defaultValue);
        foreach ($options as $option) {
            if (isset($option['value']) && $option['value'] == $value) {
                return (string)(isset($option['label']) ? $option['label'] : $option['value']);
            }
        }

        return '';
    }
}
