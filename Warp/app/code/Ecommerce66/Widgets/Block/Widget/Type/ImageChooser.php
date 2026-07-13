<?php

namespace Ecommerce66\Widgets\Block\Widget\Type;

use Magento\Framework\Data\Form\Element\AbstractElement as Element;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Data\Form\Element\Factory as FormElementFactory;
use Magento\Backend\Block\Template;

class ImageChooser extends Template
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @param TemplateContext    $context
     * @param FormElementFactory $elementFactory
     * @param array              $data
     */
    public function __construct(
        TemplateContext $context,
        FormElementFactory $elementFactory,
        $data = []
    ) {
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param Element $element
     *
     * @return Element
     */
    public function prepareElementHtml(Element $element)
    {
        $config = $this->_getData('config');
        $sourceUrl = $this->getUrl(
            'cms/wysiwyg_images/index',
            ['target_element_id' => $element->getId(), 'type' => 'file']
        );

        /** @var \Magento\Backend\Block\Widget\Button $chooser */
        $chooser = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setType('button')
            ->setClass('btn-chooser')
            ->setLabel($config['button']['open'])
            ->setOnClick('MediabrowserUtility.openDialog(\'' . $sourceUrl . '\')')
            ->setDisabled($element->getReadonly());

        /** @var \Magento\Framework\Data\Form\Element\Text $input */
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text");

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $customScript = '<script type="text/javascript">
            require(["jquery","mage/adminhtml/browser"], function ($) {
                $(document).ready(function () {
                    var $pickerInput = $("#'.$element->getHtmlId().'");
                    $pickerInput.css("visibility","hidden");
                    $pickerInput.css("height","1px");
                    $pickerInput.css("padding","0");
                    var $preview = $pickerInput.closest(".admin__field").find(".control-value").html("<img />");
                    $preview.css("max-width", "200px");
                    $preview.css("max-height", "200px");
                    $preview.css("margin-bottom", "-5px");
                    $preview.css("border", "1px solid #878787");

                    $pickerInput.on("change", function(el){
                        el.stopPropagation();
                        el.preventDefault();
                        var value = $(this).val();
                        var mediaPath = proccessImage(value);
                        $(this).val(mediaPath);
                        previewImage(mediaPath)
                    });

                    $pickerInput.each(function (el){
                        var initImage = $(this).val();
                        if (initImage.length > 0) {
                            previewImage(initImage);
                        }
                    });

                    function previewImage(image) {
                        $pickerInput.closest(".admin__field").find(".control-value img").attr("src", "/media/" + image);
                    }

                    function proccessImage(value) {
                        var string64 = value.match(/_directive\/(.*)\/key/);
                        if (string64 !== null) {
                            var mediaUrl = decodeBase64(string64[1]);
                            var mediaPath = mediaUrl.match(/"(.*)"/);
                            value = mediaPath[1];
                        }
                        return value;
                    }

                    function decodeBase64(s) {
                        var e={},i,b=0,c,x,l=0,a,r="",w=String.fromCharCode,L=s.length;
                        var A="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
                        for(i=0;i<64;i++){e[A.charAt(i)]=i;}
                        for(x=0;x<L;x++){
                            c=e[s.charAt(x)];b=(b<<6)+c;l+=6;
                            while(l>=8){((a=(b>>>(l-=8))&0xff)||(x<(L-2)))&&(r+=w(a));}
                        }
                        return r;
                    }
                });
            });
            </script>';
        $element->setData('after_element_html', $input->getElementHtml() . $chooser->toHtml() . $customScript);

            return $element;
    }
}
