<?php
namespace Swissup\CustomerFieldManager\Model\Config\Source;

use Swissup\CustomerFieldManager\Model\ResourceModel\Customer\Form\Attribute\Collection;

class Fields implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(
        Collection $collection
    ) {
        $this->collection = $collection;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->collection as $field) {
            if ($field->getIsUserDefined()) {
                $options[] = [
                    'value' => $field->getAttributeCode(),
                    'label' => $field->getFrontendLabel()
                ];
            }
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
}
