<?php

namespace Ecommerce66\Widgets\Model\Config\Source;

class Padding implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];

        for ($i=0; $i<=35; $i++) {
            $options[] = [
                'label' => $i.'% of width',
                'value' => $i
            ];
        }

        return $options;
    }
}
