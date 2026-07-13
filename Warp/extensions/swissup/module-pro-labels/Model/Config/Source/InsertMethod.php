<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\ProLabels\Model\Config\Source;

class InsertMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => '', 'label' => '< no method >'],
            ['value' => 'appendTo', 'label' => __('appendTo')],
            ['value' => 'prependTo', 'label' => __('prependTo')],
            ['value' => 'insertAfter', 'label' => __('insertAfter')],
            ['value' => 'insertBefore', 'label' => __('insertBefore')]
        ];
        return $result;
    }
}
