<?php
namespace Ceb\Gtm\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class BrandAttribute implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    public function __construct(CollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    public function toOptionArray(): array
    {
        $options = [
            ['value' => '', 'label' => __('-- None --')]
        ];

        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToFilter('frontend_input', ['in' => ['text', 'select', 'multiselect']])
                   ->addFieldToFilter('is_visible', 1)
                   ->setOrder('frontend_label', 'ASC');

        foreach ($collection as $attribute) {
            $options[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel() . ' (' . $attribute->getAttributeCode() . ')'
            ];
        }

        return $options;
    }
}
