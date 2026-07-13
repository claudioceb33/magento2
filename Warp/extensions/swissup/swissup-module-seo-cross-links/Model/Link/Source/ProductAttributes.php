<?php

namespace Swissup\SeoCrossLinks\Model\Link\Source;

class ProductAttributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attrCollection;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollection
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrCollection
    ) {
        $this->attrCollection = $attrCollection;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = $this->attrCollection->create()
            ->addFieldToFilter('is_visible_on_front', 1)
            ->setOrder('frontend_label', 'asc')
            ->load();

        $optionArray = [];

        foreach ($attributes as $attribute) {
            $optionArray[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return array_merge(
            [['value' => ' ', 'label' => __('None')]],
            $optionArray
        );
    }
}
