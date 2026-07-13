<?php

namespace Ecommerce66\Widgets\Model\Config\Source;

class HorizontalPosition implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [
            [
                'label' => 'Left',
                'value' => 'left'],
            [
                'label' => 'Center',
                'value' => 'center'],
            [
                'label' => 'Right',
                'value' => 'right'],
        ];

        return $options;
    }
}
