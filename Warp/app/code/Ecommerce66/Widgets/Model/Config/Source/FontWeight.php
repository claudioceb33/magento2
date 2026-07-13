<?php

namespace Ecommerce66\Widgets\Model\Config\Source;

class FontWeight implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];

        for ($i=1; $i<10; $i++) {
            $options[] = [
                'label' => $i * 100,
                'value' => $i * 100
            ];
        }

        return $options;
    }
}
