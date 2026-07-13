<?php

namespace Swissup\RichSnippets\Model\Config\Source;

class MerchantReturnCategory implements \Magento\Framework\Data\OptionSourceInterface
{
    const FINIT_WINDOW = 'FiniteReturnWindow';
    const UNLIM_WINDOW = 'UnlimitedWindow';
    const NOT_PERMITTED = 'NotPermitted';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('Not specified')],
            ['value' => self::FINIT_WINDOW, 'label' => __('Number of days to return a product')],
            ['value' => self::UNLIM_WINDOW, 'label' => __('Unlimited time to return a product')],
            ['value' => self::NOT_PERMITTED, 'label' => __('Returns aren\'t permitted')]
        ];
    }
}
