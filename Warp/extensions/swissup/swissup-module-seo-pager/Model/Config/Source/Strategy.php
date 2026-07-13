<?php

namespace Swissup\SeoPager\Model\Config\Source;

class Strategy implements \Magento\Framework\Option\ArrayInterface
{

    const LEAVE_AS_IS            = 0;
    const REL_CANONICAL          = 1;
    const REL_NEXT_REL_PREV      = 2;
    const REL_CANONICAL_PER_PAGE = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::LEAVE_AS_IS,
                'label' => __('Leave as-is')
            ],
            [
                'value' => self::REL_CANONICAL,
                'label' => __('Use rel="canonical" to view all page')
            ],
            [
                'value' => self::REL_CANONICAL_PER_PAGE,
                'label' => __('Unique rel="canonical" to each page')
            ],
            [
                'value' => self::REL_NEXT_REL_PREV,
                'label' => __('Use rel="next" and rel="prev"')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $option) {
            $array[$option['value']] = $option['label'];
        }

        return $array;
    }
}
