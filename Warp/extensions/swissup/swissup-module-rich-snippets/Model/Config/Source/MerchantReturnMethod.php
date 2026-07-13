<?php

namespace Swissup\RichSnippets\Model\Config\Source;

class MerchantReturnMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    const KIOSK = 'AtKiosk';
    const MAIL = 'ByMail';
    const STORE = 'InStore';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::KIOSK, 'label' => __('Return at a kiosk')],
            ['value' => self::MAIL,  'label' => __('Return by mail')],
            ['value' => self::STORE, 'label' => __('Return in store')]
        ];
    }
}
