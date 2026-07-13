<?php
namespace Swissup\SeoTemplates\Model\Template\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EntityTypeWithAll extends EntityType
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        $value = implode(
            ',',
            array_map(
                array($this, 'getOptionValue'),
                $options
            )
        );

        return array_merge(
            [
                [
                    'label' => __('All entities'),
                    'value' => $value
                ]
            ],
            $options
        );
    }

    /**
     * Get value from option
     *
     * @param  array $option
     * @return int|null
     */
    private function getOptionValue($option)
    {
        return isset($option['value']) ? $option['value'] : null;
    }
}
