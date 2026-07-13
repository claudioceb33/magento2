<?php

namespace Swissup\SeoTemplates\Block\Adminhtml\Template\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Back extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'class' => 'back',
            'on_click' => sprintf("location.href = '%s';", $this->context->getUrl('*/template/')),
            'sort_order' => 10
        ];
    }
}
