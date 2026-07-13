<?php

namespace Swissup\ProLabels\Model\Config\Source;

class OutputStrategy implements \Magento\Framework\Option\ArrayInterface
{
    const ORDERED = 'ordered';
    const SINGLE  = 'single';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ORDERED,
                'label' => __('Ordered')
            ],
            [
                'value' => self::SINGLE,
                'label' => __('Single')
            ]
        ];
    }
}
