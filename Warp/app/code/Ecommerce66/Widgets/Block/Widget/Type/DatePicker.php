<?php


namespace Ecommerce66\Widgets\Block\Widget\Type;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Exception\LocalizedException;

class DatePicker extends Template
{

    /**
     * @var Factory
     */
    private $elementFactory;

    /**
     * DatePicker constructor.
     *
     * @param Context $context
     * @param Factory $elementFactory
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        $data = []
    ) {
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        /** @var \Magento\Framework\Data\Form\Element\Text $input */
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->addCustomAttribute('style', 'width: auto');
        $input->setClass('widget-option input-text admin__control-text');
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $calendarScript = '
        <script>require([
            "jquery",
            "mage/translate",
            "mage/calendar"
            ], function ($, $t) {
              $("#' . $element->getId() . '").calendar({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                currentText: $t("Go Today"),
                closeText: $t("Close"),
                dateFormat: "yyyy-MM-dd"
              });
            })</script>';
        $element->setData('after_element_html', $input->getElementHtml() . $calendarScript);
        $element->setValue('');

        return $element;
    }
}
