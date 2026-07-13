<?php

namespace Swissup\SeoCanonical\Model\System\Config\Source;

class Storeview extends \Magento\Cms\Ui\Component\Listing\Column\Cms\Options
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        $options[0]['label'] = __('Current Store View');

        return $options;
    }
}
