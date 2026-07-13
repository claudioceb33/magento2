<?php

namespace Ecommerce66\Widgets\Model\Config\Source;

class Hours implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];

        for ($i=0; $i<24; $i++) {
            $options[] = [
                'label' => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00',
                'value' => str_pad($i, 2, '0', STR_PAD_LEFT)
            ];
        }

        return $options;
    }
}
