<?php

namespace Swissup\SeoUrls\Model\Config\Source;

class NofollowStategy implements \Magento\Framework\Data\OptionSourceInterface
{
    const MAGENTO_DEFAULT = 0;
    const FORCE_NOFOLLOW  = 1;
    const REMOVE_NOFOLLOW = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MAGENTO_DEFAULT,
                'label' => __('Do nothing')
            ],
            [
                'value' => self::FORCE_NOFOLLOW,
                'label' => __('Force "nofollow"')
            ],
            [
                'value' => self::REMOVE_NOFOLLOW,
                'label' => __('Remove "nofollow"')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach ($this->toOptionArray() as $item) {
            $result[$item['value']] = $item['label'];
        }
        return $result;
    }
}
