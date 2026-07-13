<?php

namespace Ecommerce66\Widgets\Model\Config\Source;

class Columns implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];

        $options[] = [
            'label' => '1',
            'value' => 1
        ];
        $options[] = [
            'label' => '2',
            'value' => 2
        ];
        $options[] = [
            'label' => '3',
            'value' => 3
        ];
        $options[] = [
            'label' => '4',
            'value' => 4
        ];
        $options[] = [
            'label' => '5',
            'value' => 5
        ];
        $options[] = [
            'label' => '6',
            'value' => 6
        ];



        return $options;
    }
}
