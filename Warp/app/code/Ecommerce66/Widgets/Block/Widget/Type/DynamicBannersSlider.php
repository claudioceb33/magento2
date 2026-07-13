<?php

namespace Ecommerce66\Widgets\Block\Widget\Type;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class DynamicBannersSlider extends Template
{
    /**
     * @var Factory
     */
    private $elementFactory;
    /**
     * @var JsonHelper|null
     */
    protected $jsonHelper;

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
        $data = [],
        JsonHelper $jsonHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper);
        $this->elementFactory = $elementFactory;
        $this->data = $data;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param AbstractElement $element
     *
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $fields = '';
        $config = $this->_getData('config');


        $fields .=$this->renderForm($element, $config, $fields);

        $input = $this->elementFactory->create('textarea', ['data' => $element->getData()]);
        $input->setForm($element->getForm());
        $input->setConfig($config);
        $input->addClass('banners_widget_field');
        $fields .= '<div style="height: 5px;">'.$input->getElementHtml().'</div>';

        $customScript = $this->renderScript();
        $customStyle = $this->renderStyles();

        $element->setData(
            'after_element_html',
            $fields . $customScript . $customStyle . "<script>require(['mage/adminhtml/browser','ecomm66Widgets']);</script>"
        );

        return $element;
    }

    public function renderForm($element, $config, $fields)
    {
        $columns = [1,2,3,4,5,6,7,8,9,10];
        $data = json_decode(base64_decode($element->getValue()), true); //phpcs:ignore
        $fields .= '<div class="container col-banners-admin">';
        foreach ($columns as $column) {

            $fields .= '<div style="display: flex" class="columns col-'.$column.'">';

            $sourceUrl_mob = $this->getUrl(
                'cms/wysiwyg_images/index',
                ['target_element_id' => 'mob-'. $column, 'type' => 'file']
            );

            $chooser_mb = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setType('button')
            ->setClass('btn-chooser')
            ->setLabel(__('Open'))
            ->setId('col-m-'.$column)
            ->setOnClick('MediabrowserUtility.openDialog(\'' . $sourceUrl_mob . '\')')
            ->setDisabled($element->getReadonly());

            $remove_mb = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setType('button')
            ->setClass('btn-chooser')
            ->setLabel(__('Remove'))
            ->setId('rm-m-'.$column)
            ->setOnClick('removeBanners(\'mob-'.$column.'\')')
            ->setDisabled($element->getReadonly());

            $sourceUrl = $this->getUrl(
                'cms/wysiwyg_images/index',
                ['target_element_id' => 'desk-'. $column, 'type' => 'file']
            );

            $chooser = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setType('button')
            ->setClass('btn-chooser')
            ->setLabel(__('Open'))
            ->setId('col-d-'.$column)
            ->setOnClick('MediabrowserUtility.openDialog(\'' . $sourceUrl . '\')')
            ->setDisabled($element->getReadonly());

            $remove = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setType('button')
            ->setClass('btn-chooser')
            ->setLabel(__('Remove'))
            ->setId('rm-d-'.$column)
            ->setOnClick('removeBanners(\'desk-'.$column.'\')')
            ->setDisabled($element->getReadonly());

            //Banners Desktop
            $banner_desktop = (isset($data['row'.$column]['column-banner-mob'])) ?  $data['row'.$column]['column-banner-desk']: '';
            $input = $this->elementFactory->create('text', ['data' => $element->getData()]);
            $input->setForm($element->getForm());
            $input->setName('row'. $column . '[column-banner-desk]');
            $input->setValue($banner_desktop);
            $input->setConfig($config);
            $input->addClass('col-ban desk-'. $column);
            $input->setId('desk-'. $column);
            $fields .= '<div id="d'.$column.'" class="block-data"><label>Banner Desktop </label> '. $input->getElementHtml() .'<div class="image-prev" ></div>'. $chooser->toHtml() .  $remove->toHtml() .'</div>';

            //Banners Mobile
            $banner_mob = (isset($data['row'.$column]['column-banner-mob'])) ?  $data['row'.$column]['column-banner-mob']: '';
            $input = $this->elementFactory->create('text', ['data' => $element->getData()]);
            $input->setForm($element->getForm());
            $input->setName('row'. $column . '[column-banner-mob]');
            $input->setValue($banner_mob);
            $input->setConfig($config);
            $input->addClass('col-ban mob-'.$column);
            $input->setId('mob-'. $column);
            $fields .= '<div id="m'.$column.'" class="block-data"><label> Banner Mobile </label>'. $input->getElementHtml() .'<div class="image-prev"></div> ' . $chooser_mb->toHtml() . $remove_mb->toHtml() .' </div>';

            //Banners Links
            $banner_url = (isset($data['row'.$column]['column-banner-mob'])) ?  $data['row'.$column]['column-banner-url']: '';
            $input = $this->elementFactory->create('text', ['data' => $element->getData()]);
            $input->setForm($element->getForm());
            $input->setName('row'. $column . '[column-banner-url]');
            $input->setValue($banner_url);
            $input->setConfig($config);
            $input->addClass('columns-banners');
            $fields .= '<div><label>Link Banner </label>'.$input->getElementHtml().'</div>';

            //Text Banners
            $banner_text = (isset($data['row'.$column]['column-banner-text'])) ?  $data['row'.$column]['column-banner-text']: '';
            $input = $this->elementFactory->create('text', ['data' => $element->getData()]);
            $input->setForm($element->getForm());
            $input->setName('row'. $column . '[column-banner-text]');
            $input->setValue($banner_text);
            $input->setConfig($config);
            $input->addClass('columns-banners-text');
            $fields .= '<div><label>Banner Text </label>'.$input->getElementHtml().'</div>';

            //Open in New Tab Checkbox
            $banner_target = (isset($data['row'.$column]['column-banner-target'])) ?  $data['row'.$column]['column-banner-target']: '0';
            $checkbox = $this->elementFactory->create('checkbox', ['data' => $element->getData()]);
            $checkbox->setForm($element->getForm());
            $checkbox->setName('row'. $column . '[column-banner-target]');
            $checkbox->setValue('1');
            $checkbox->setChecked($banner_target == '1');
            $checkbox->setConfig($config);
            $checkbox->addClass('columns-banners-target');
            $fields .= '<div><label><input type="checkbox" name="row'. $column . '[column-banner-target]" value="1" class="columns-banners-target" ' . ($banner_target == '1' ? 'checked="checked"' : '') . ' /> Open in new tab</label></div>';

            $fields .= '</div>';
        }
        $fields .= '</div>';

        return $fields;
    }

    public function renderScript()
    {
        $script = '
        <script type="text/javascript">

            require(["jquery"], function ($) {

                $(document).ready(function () {
                    removeBanners =  function (elid) {
                        console.log("entra" + elid);
                        $("input#"+elid).attr("value","").trigger("change");
                        var colid =  $("#"+elid).closest(".block-data").attr("id");
                       $("input#"+elid + "+ .image-prev img").attr("src","");
                    }
                    var $pickerInput = $(".col-ban");
                    $pickerInput.css("visibility","hidden");
                    $pickerInput.css("height","1px");
                    $pickerInput.css("padding","0");
                    $pickerInput.closest(".admin__field").find(".control-value").hide();
                    var $preview = $pickerInput.closest(".admin__field").find(".image-prev").html("<img />");


                    $pickerInput.on("change", function(el){
                        el.stopPropagation();
                        el.preventDefault();
                        var value = $(this).val();
                        var colid = $(this).closest(".block-data").attr("id");
                        var mediaPath = proccessImage(value);
                        $(this).val(mediaPath);
                        previewImage(mediaPath,colid)
                    });

                    $pickerInput.each(function (el){
                        var initImage = $(this).val();
                        var colid = $(this).closest(".block-data").attr("id");
                        if (initImage.length > 0) {
                            previewImage(initImage,colid);
                        }
                    });

                    function previewImage(image,colid) {
                        $pickerInput.closest(".admin__field").find("#"+colid + " .image-prev img").attr("src", "/media/" + image);
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
                function encodeBase64(input) {
                        var output = "";
                        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
                        var i = 0;

                        input = Base64._utf8_encode(input);

                        while (i < input.length) {

                            chr1 = input.charCodeAt(i++);
                            chr2 = input.charCodeAt(i++);
                            chr3 = input.charCodeAt(i++);

                            enc1 = chr1 >> 2;
                            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                            enc4 = chr3 & 63;

                            if (isNaN(chr2)) {
                                enc3 = enc4 = 64;
                            } else if (isNaN(chr3)) {
                                enc4 = 64;
                            }

                            output = output +
                            this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                            this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
                        }
                        return output;
                    }
                    var $landingsInput = $(".banners_widget_field");
                    $landingsInput.css("visibility","hidden");
                    $landingsInput.css("height","1px");
                   var currentVal =  $("select[name=\'parameters[banner_columns]\']").val();
                   if (currentVal == 0) {
                          $(".columns").show();
                      } else {
                          $(".columns").hide().slice(0, currentVal).show();
                      }
                    $("select[name=\'parameters[banner_columns]\']").on("change", function() {
                      var currentVal = $(this).val();

                      if (currentVal == 0) {
                          $(".columns").show();
                      } else {
                          $(".columns").hide().slice(0, currentVal).show();
                      }
                    });

                    $(".columns input").on("change input keyup blur", function(el){
                        var self = this;
                        setTimeout(function(){
                            $landingsInput.val(btoa(JSON.stringify( $(".columns :input").serializeControls())));
                        }, 100);
                    });
                    
                    $("body").on("beforeSubmit", function() {
                        $landingsInput.val(btoa(JSON.stringify( $(".columns :input").serializeControls())));
                    });
                    
                    $(document).on("submit", "form", function() {
                        $landingsInput.val(btoa(JSON.stringify( $(".columns :input").serializeControls())));
                    });
                    
                    $(".columns input").on("change", function(el){
                        setTimeout(function(){
                         $landingsInput.val(btoa(JSON.stringify( $(".columns :input").serializeControls())));
                        },1000);
                    });
                    $.fn.serializeControls = function() {
                      var data = {};

                      function buildInputObject(arr, val) {
                        if (arr.length < 1)
                          return val;
                        var objkey = arr[0];
                        if (objkey.slice(-1) == "]") {
                          objkey = objkey.slice(0,-1);
                        }
                        var result = {};
                        if (arr.length == 1){
                          result[objkey] = val;
                        } else {
                          arr.shift();
                          var nestedVal = buildInputObject(arr,val);
                          result[objkey] = nestedVal;
                        }
                        return result;
                      }

                      $.each(this.serializeArray(), function() {
                        var val = this.value;
                        var c = this.name.split("[");
                        var a = buildInputObject(c, val);
                        $.extend(true, data, a);
                      });

                      return data;
                    }





            })
            </script>';
        return $script;
    }

    public function renderStyles()
    {
        $styles = '
        <style>
        .col-banners-admin{
            padding: 10px;
        }
        .col-banners-admin .columns{
            padding: 20px 10px;
            justify-content: space-around;
            align-items: center;
        }

        .col-banners-admin  .block-data label,.col-banners-admin  label {
            font-weight: bold;
            display: block;
          }
        .col-banners-admin .columns:nth-child(odd) {background: #e3e3e3}
        .col-banners-admin .columns:nth-child(even) {background: #FFF}
        .col-banners-admin .btn-chooser{
            margin:10px auto;
            display:inline-block;

        }
        .image-prev{
            width: 140px;
            height: 140px;
            margin: 0 auto;
            display:flex;
            align-content: flex-start;
            align-items: center;
            border: 1px dashed;
        }
        .image-prev img {
            max-width: 100%;
            width: 140px;
            max-height: 140px;
            min-height: auto;
            border: 0px solid transparent;
            display: block;
            margin: 0 auto;
          }
        </style>
        ';

        return $styles;
    }
}
