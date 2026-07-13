<?php
namespace Swissup\SeoHtmlSitemap\Block\Adminhtml\Link\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save extends Generic implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save Link'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']
                ],
            ],
            'sort_order' => 90
        ];
    }
}
