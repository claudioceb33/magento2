<?php
declare(strict_types=1);

namespace Ecommerce66\AiCore\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context; // <-- backend context

class TestButton extends Field
{
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Ecommerce66_AiCore::system/config/test_button.phtml');
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = isset($originalData['button_label'])
            ? (string)$originalData['button_label']
            : (string)__('Test Connection');

        // Use backend getUrl()
        $ajaxUrl = $this->getUrl('ecommerce66_aicore/test/connection');

        $this->addData([
            'button_label' => $buttonLabel,
            'html_id'      => $element->getHtmlId(),
            'ajax_url'     => $ajaxUrl
        ]);

        return $this->_toHtml();
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
}
