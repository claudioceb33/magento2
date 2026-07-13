<?php

namespace Swissup\FieldManager\Model\Config\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const HIDDEN   = 'hidden';
    const OPTIONAL = 'optional';
    const REQUIRED = 'required';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::HIDDEN,   'label' => __('Hidden')],
            ['value' => self::OPTIONAL, 'label' => __('Optional')],
            ['value' => self::REQUIRED, 'label' => __('Required')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $row) {
            $result[$row['value']] = $row['label'];
        }
        return $result;
    }
}
