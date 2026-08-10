<?php
namespace Ceb\ButtonSystem\Block\Adminhtml\Button;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Update extends \Magento\Config\Block\System\Config\Form\Field
{
    public function _getElementHtml(AbstractElement $element)
    {
        $element = null;

        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock  */
        $buttonBlock = $this->getForm()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');

        $url = $this->getUrl("buttonsystem/action/action");

        $data = [
            'class'   => 'Ceb-ButtonSystem-admin-button-update',
            'label'   => __('Button System'),
            'onclick' => "setLocation('" . $url . "')",
        ];

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }
}