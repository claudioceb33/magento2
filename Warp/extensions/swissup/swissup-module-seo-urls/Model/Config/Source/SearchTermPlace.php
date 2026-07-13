<?php

namespace Swissup\SeoUrls\Model\Config\Source;

class SearchTermPlace implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('In URL body')
            ],
            [
                'value' => 0,
                'label' => __('As request parameter')
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
        return [1 => __('In URL body'), 0 => __('As request parameter')];
    }
}
