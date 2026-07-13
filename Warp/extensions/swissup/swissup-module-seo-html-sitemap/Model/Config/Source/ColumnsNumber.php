<?php
namespace Swissup\SeoHtmlSitemap\Model\Config\Source;

class ColumnsNumber implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('1 Column')],
            ['value' => 2, 'label' => __('2 Columns')],
            ['value' => 3, 'label' => __('3 Columns')],
            ['value' => 4, 'label' => __('4 Columns')],
        ];
    }
}
