<?php

namespace Ecommerce66\Widgets\Block\Widget\Type;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Exception\LocalizedException;

class ColorPicker extends Template
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
        $config = $this->_getData('config');
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);

        $defaultColor = "#111111";
        $value = isset($config['value']) ? $config['value'] : $defaultColor;
        $value = !empty($element->getValue()) ? $element->getValue() : $value;
        $input->setId($element->getHtmlId());
        $input->setForm($element->getForm());
        $input->setName($element->getName());
        $input->setValue($value);
        $input->addCustomAttribute('style', 'width: 100px');
        $input->setClass('widget-option input-text admin__control-text ddg-colpicker');
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $colorScript = '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var $el = $("#'.$input->getHtmlId().'");
                    $el.css("font-weight", "600");
                    $el.css("font-family", "monospace");
                    $el.css("letter-spacing", "1px");
                    $el.css("text-transform", "uppercase");
                    $el.css("color", invertColor("'.$input->getValue().'"));
                    $el.css("backgroundColor", "'.$input->getValue().'");

                    $el.ColorPicker({
                        color: invertColor("'.$input->getValue().'"),
                        onChange: function (hsb, hex, rgb) {
                            $el.css("color", invertColor("#" + hex));
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });

                    $el.on("change", function(el){
                        var value = $el.val();
                        $el.css("color", invertColor(value));
                        $el.css("backgroundColor", value);
                    });

                    function invertColor(hex, bw) {
                        if (hex.indexOf("#") === 0) {
                            hex = hex.slice(1);
                        }
                        // convert 3-digit hex to 6-digits.
                        if (hex.length === 3) {
                            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
                        }
                        if (hex.length !== 6) {
                            throw new Error("Invalid HEX color.");
                        }
                        var r = parseInt(hex.slice(0, 2), 16),
                            g = parseInt(hex.slice(2, 4), 16),
                            b = parseInt(hex.slice(4, 6), 16);
                        if (bw) {
                            // https://stackoverflow.com/a/3943023/112731
                            return (r * 0.299 + g * 0.587 + b * 0.114) > 186
                                ? "#000000"
                                : "#FFFFFF";
                        }
                        // invert color components
                        r = (255 - r).toString(16);
                        g = (255 - g).toString(16);
                        b = (255 - b).toString(16);
                        // pad each with zeros and return
                        return "#" + padZero(r) + padZero(g) + padZero(b);
                    }
                    function padZero(str, len) {
                        len = len || 2;
                        var zeros = new Array(len).join("0");
                        return (zeros + str).slice(-len);
                    }
                });
            });
            </script><style>.colorpicker {z-index: 10010}</style>';
        $element->setData('after_element_html', $input->getElementHtml() . $colorScript);
        $element->setValue('');

        return $element;
    }
}
