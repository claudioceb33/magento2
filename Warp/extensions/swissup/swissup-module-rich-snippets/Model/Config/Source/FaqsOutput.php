<?php

namespace Swissup\RichSnippets\Model\Config\Source;

class FaqsOutput implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'jsonhtml', 'label' => __('JSON and HTML block for storefront')],
            ['value' => 'json', 'label' => __('JSON only (not visible on storefront)')]
        ];
    }
}
