<?php

namespace Ecommerce66\Widgets\Model\Config\Source;

class Animationtype implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];

        $options[] = [
            'label' => 'By word',
            'value' => 1
        ];
        $options[] = [
            'label' => 'Type Script',
            'value' => 2
        ];
        $options[] = [
            'label' => 'Slide Text',
            'value' => 3
        ];
        $options[] = [
            'label' => 'Fade Text',
            'value' => 4
        ];

        return $options;
    }
}
