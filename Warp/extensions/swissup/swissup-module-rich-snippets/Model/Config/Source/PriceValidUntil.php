<?php

namespace Swissup\RichSnippets\Model\Config\Source;

class PriceValidUntil implements \Magento\Framework\Option\ArrayInterface
{
    const STATIC_DATE = 0;
    const DYNAMIC_DATE = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATIC_DATE, 'label' => __('Static Date')],
            ['value' => self::DYNAMIC_DATE, 'label' => __('Dynamic Date')],
        ];
    }
}
