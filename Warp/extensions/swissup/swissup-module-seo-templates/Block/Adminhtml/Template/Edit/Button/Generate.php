<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Template\Edit\Button;

class Generate extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Generate metadata'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'save'
                    ]
                ],
                'form-role' => 'save',
            ],
            'sort_order' => 90
        ];
    }
}
