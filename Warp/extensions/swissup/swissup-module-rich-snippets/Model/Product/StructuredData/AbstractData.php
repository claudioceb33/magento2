<?php

namespace Swissup\RichSnippets\Model\Product\StructuredData;

use Magento\Catalog\Api\Data\ProductInterface;

abstract class AbstractData
{
    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $attributeOutput;

    /**
     * @param \Magento\Catalog\Helper\Output $attributeOutput
     */
    public function __construct(
        \Magento\Catalog\Helper\Output $attributeOutput
    ) {
        $this->attributeOutput = $attributeOutput;
    }

    /**
     * @param  array            $map
     * @param  ProductInterface $product
     * @return array
     */
    public function buildAttributeBasedData(array $map, ProductInterface $product)
    {
        $data = [];
        foreach ($map as $property => $value) {
            if (is_array($value)) {
                continue;
            }

            $attributeCode = $value;
            $attribute = $product->getResource()->getAttribute($attributeCode);
            if (!$attribute) {
                continue;
            }

            $content = $attribute->getFrontend()->getValue($product);
            $data[$property] = $this->attributeOutput
                ->productAttribute($product, $content, $attributeCode);
        }

        return array_filter($data);
    }
}
