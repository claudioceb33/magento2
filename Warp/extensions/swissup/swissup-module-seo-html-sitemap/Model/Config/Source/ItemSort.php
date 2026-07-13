<?php
namespace Swissup\SeoHtmlSitemap\Model\Config\Source;

class ItemSort implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'name', 'label' => __('Name')],
            ['value' => 'position', 'label' => __('Position')],
        ];
    }
}
