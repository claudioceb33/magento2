<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block;

use Magento\Widget\Block\BlockInterface;

class Form extends \Magento\Framework\View\Element\Template implements BlockInterface
{
    protected $_template = "form.phtml";

    /**
     * @var \Hexamarvel\FlexibleForm\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $contentProcessor;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldSetFactory
     */
    protected $fieldSetFactory;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var \Hexamarvel\FlexibleForm\Model\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $countryCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Hexamarvel\FlexibleForm\Helper\Data $helper
     * @param \Magento\Cms\Model\Template\FilterProvider $contentProcessor
     * @param \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory
     * @param \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory
     * @param \Hexamarvel\FlexibleForm\Model\FormFactory $formFactory
     * @param \Magento\Directory\Model\Config\Source\Country $countryCollection
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hexamarvel\FlexibleForm\Helper\Data $helper,
        \Magento\Cms\Model\Template\FilterProvider $contentProcessor,
        \Hexamarvel\FlexibleForm\Model\FieldSetFactory $fieldSetFactory,
        \Hexamarvel\FlexibleForm\Model\FieldFactory $fieldFactory,
        \Hexamarvel\FlexibleForm\Model\FormFactory $formFactory,
        \Magento\Directory\Model\Config\Source\Country $countryCollection
    ) {
        $this->helper      = $helper;
        $this->contentProcessor = $contentProcessor;
        $this->fieldSetFactory = $fieldSetFactory;
        $this->fieldFactory = $fieldFactory;
        $this->formFactory = $formFactory;
        $this->countryCollection = $countryCollection;
        parent::__construct($context);
    }

    protected function _prepareLayout()
    {
        $block = $this->getLayout()->createBlock(
            \Magento\Captcha\Block\Captcha::class
        )->setFormId(
            'form_builder'
        )->setImgWidth(230)->setImgHeight(50);

        $this->setChild('form.additional.info', $block);

        parent::_prepareLayout();
    }

    /**
     * @return obj $formId
     */
    public function getForm()
    {
        if ($this->getRequest()->getParam('form_object')) {
            return $this->getRequest()->getParam('form_object');
        }

        $formId = $this->helper->getConfig('hexaform/product_inquiry_settings/form_lists');
        if ($this->getData('form_id')) {
            $formId = $this->getData('form_id');
        }

        return $this->formFactory->create()->load($formId);
    }

    /**
     * @return string $url
     */
    public function getFormUrl()
    {
        return $this->getUrl("hexaform/index/post");
    }

    /**
     * @return string
     */
    public function formTopContent()
    {
        if ($content = $this->getForm()->getTopContent()) {
            return $this->contentProcessor->getPageFilter()->filter(
                $content
            );
        }
    }

    /**
     * @return string
     */
    public function formBottomContent()
    {
        if ($content = $this->getForm()->getBottomContent()) {
            return $this->contentProcessor->getPageFilter()->filter(
                $this->getForm()->getBottomContent()
            );
        }
    }

    /**
     * @return obj
     */
    public function getFieldSet()
    {
        return $this->fieldSetFactory->create()->getCollection()->addFieldToFilter(
            'form_id',
            $this->getForm()->getId()
        )->addFieldToFilter(
            'is_active',
            '1'
        )->setOrder(
            'position',
            'asc'
        );
    }

    /**
     * @return obj
     */
    public function getDefaultFieldsetFields()
    {
        return $this->fieldFactory->create()->getCollection()->addFieldToFilter(
            'form_id',
            $this->getForm()->getId()
        )->addFieldToFilter(
            'fieldset',
            '0'
        )->addFieldToFilter(
            'is_active',
            '1'
        )->setOrder(
            'position',
            'asc'
        );
    }

    /**
     * @param int $fieldSetId
     * @return obj
     */
    public function getFieldsetField($fieldSetId)
    {
        return $this->fieldFactory->create()->getCollection()->addFieldToFilter(
            'form_id',
            $this->getForm()->getId()
        )->addFieldToFilter(
            'fieldset',
            $fieldSetId
        )->addFieldToFilter(
            'is_active',
            '1'
        )->setOrder(
            'position',
            'asc'
        );
    }

    /**
     * @param int $field
     * @return string
     */
    public function getFieldHtml($field)
    {
        $html = '';
        if ($field->getFieldType() == 'text') {
            $html = '<input type="text" ';
            $html .= 'name="field[' . $field->getId() . ']" ';
            $html .= 'class="input-text" ';
            $html .= 'id="field_id_' . $field->getId() . '" ';
            $html .= 'placeholder=" '.$field->getPlaceholder() . '" ';
            $html .= 'title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '/>';
        } elseif ($field->getFieldType() == 'email') {
            $html = '<input type="email" ';
            $html .= 'name="field[' . $field->getId() . ']" ';
            $html .= 'class="input-text" ';
            $html .= 'id="field_id_' . $field->getId() . '" ';
            $html .= 'placeholder=" '.$field->getPlaceholder() . '" ';
            $html .= 'title="' . $field->getTitle() . '"';
            $html .= 'data-validate="{\'validate-email\':true';
            if ($field->getIsRequired()) {
                $html .= ', \'required\': true';
            }

            $html .= '} " />';
        } elseif ($field->getFieldType() == 'number') {
            $html = '<input type="text" ';
            $html .= 'name="field[' . $field->getId() . ']" ';
            $html .= 'class="input-text" ';
            $html .= 'id="field_id_' . $field->getId() . '" ';
            $html .= 'placeholder=" '.$field->getPlaceholder() . '" ';
            $html .= 'title="' . $field->getTitle() . '" ';
            $html .= 'data-validate="{\'validate-number\':true';
            if ($field->getIsRequired()) {
                $html .= ', \'required\': true';
            }

            $html .= '} " />';
        } elseif ($field->getFieldType() == 'textarea') {
            $html .= '<textarea rows="5" cols="25" ';
            $html .= 'name="field[' . $field->getId() . ']" ';
            $html .= 'class="input-text" ';
            $html .= 'id="field_id_' . $field->getId() . '" ';
            $html .= 'placeholder=" '.$field->getPlaceholder() . '" ';
            $html .= 'title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required:true}" ';
            }

            $html .= '></textarea>';
        } elseif ($field->getFieldType() == 'select') {
            $options = explode(PHP_EOL, $field->getOptionValues());

            $html = '<select name="option[' . $field->getId() . ']" ';
            $html .= 'class="input-text" ';
            $html .= 'id="field_id_' . $field->getId() . '" ';
            $html .= 'title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '>';
            $html .= '<option value="">--Please Select--</option>';
            foreach ($options as $option) {
                $html .= '<option value="' . trim($option) . '">' . trim($option) . '</option>';
            }

            $html .='</select>';
        } elseif ($field->getFieldType() == 'multiselect') {
            $options = explode(PHP_EOL, $field->getOptionValues());

            $html = '<select name="option[' . $field->getId() . '][]" ';
            $html .= 'class="input-text" ';
            $html .= 'id="field_id_' . $field->getId() . '" ';
            $html .= 'title="' . $field->getTitle() . '" ';
            $html .= 'multiple="multiple" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '>';
            foreach ($options as $option) {
                $html .= '<option value="' . trim($option) . '">' . trim($option) . '</option>';
            }

            $html .='</select>';
        } elseif ($field->getFieldType() == 'checkbox') {
            $options = explode(PHP_EOL, $field->getOptionValues());
            $html = '<div class="options-list nested">';
            foreach ($options as $key => $option) {
                $html .= '<div class="field choice">';
                $html .= '<input type="checkbox" name="option[' . $field->getId() . '][]" ';
                $html .= 'id="field_id_' . $field->getId() . '_'.$key.'" ';
                $html .= 'class="checkbox checkbox-field-val" ';
                $html .= 'title="'. $option . '" value="'. $option . '"';
                if ($field->getIsRequired()) {
                    $html .= 'data-validate="{required: true}"';
                }
                $html .='>';
                $html .='<label class="label admin__field-label" for="field_id_' . $field->getId() . '_'.$key.'">';
                $html .='<span>'. $option . '</span>';
                $html .='</label>';
                $html .='</div>';
            }

            $html .= '</div>';
        } elseif ($field->getFieldType() == 'radio') {
            $options = explode(PHP_EOL, $field->getOptionValues());
            $html = '<div class="options-list nested">';
            foreach ($options as $key => $option) {
                $html .= '<div class="field choice">';
                $html .='<input type="radio" name="option[' . $field->getId() . '][]" ';
                $html .='id="field_id_' . $field->getId() . '_'.$key.'" ';
                $html .='class="checkbox checkbox-field-val "';
                $html .='title="'. $option . '" value="'. $option . '" ';
                if ($field->getIsRequired()) {
                    $html .= 'data-validate="{required: true}" ';
                }

                $html .='>';
                $html .='<label class="label admin__field-label" for="field_id_' . $field->getId() . '_'.$key.'">';
                $html .='<span>'. $option . '</span>';
                $html .='</label>';
                $html .='</div>';
            }

            $html .= '</div>';
        } elseif ($field->getFieldType() == 'date') {
            $html = '<input type="text" ';
            $html .='name="field[' . $field->getId() . ']" ';
            $html .='class="input-text datepicker"';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='placeholder=" '.$field->getPlaceholder() . '" ';
            $html .='title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}"';
            }

            $html .= '/>';
        } elseif ($field->getFieldType() == 'time') {
            $html = '<input type="text"';
            $html .='name="field[' . $field->getId() . ']"';
            $html .='class="input-text timepicker"';
            $html .='id="field_id_' . $field->getId() . '"';
            $html .='placeholder=" '.$field->getPlaceholder() . '"';
            $html .='title="' . $field->getTitle() . '"';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '/>';
        } elseif ($field->getFieldType() == 'datetime') {
            $html = '<input type="text" ';
            $html .='name="field[' . $field->getId() . ']" ';
            $html .='class="input-text datetimepicker" ';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='placeholder=" '.$field->getPlaceholder() . '" ';
            $html .='title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '/>';
        } elseif ($field->getFieldType() == 'file' || $field->getFieldType() == 'image' || $field->getFieldType() == 'video' || $field->getFieldType() == 'all_files') {
            $html = '<input type="file" ';
            $html .='name="' . $field->getFieldType() . '_option[' . $field->getId() . ']" ';
            $html .='class="input-text" ';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '/>';
            if ($field->getFieldType() == 'file') {
                $html .= '<p class="note">Allow only doc, docx, xls, xlsx, pdf files. Allowed Max Size 5 MB.</p>';
            } elseif ($field->getFieldType() == 'image') {
                $html .= '<p class="note">Allow only jpg, jpeg, gif, png files. Allowed Max Size 1024 KB</p>';
            } elseif ($field->getFieldType() == 'video') {
                $html .= '<p class="note">Allow only mp4, webm, flv,  mov, avi, mpeg files. Allowed Max Size 30 MB</p>';
            } else {
                $html .= '<p class="note">Allow minimum types of image, video and files. Allowed Max Size 30 MB</p>';
            }
        } elseif ($field->getFieldType() == 'terms') {
            $html = '<div class="options-list nested">';
            $html .= '<div class="field choice">';
            $html .='<input type="checkbox" name="field[' . $field->getId() . '][]" ';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='class="checkbox checkbox-field-val" value="yes"';

            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}"';
            }

            $html .='>';
            $html .='<label class="label admin__field-label" for="field_id_' . $field->getId() . '">';
            $html .='<span>'. $field->getOptionValues() . '</span>';
            $html .='</label>';
            $html .='</div>';
            $html .= '</div>';
        } elseif ($field->getFieldType() == 'hidden') {
            $html = '<input type="hidden"';
            $html .='name="field[' . $field->getId() . ']" ';
            $html .='class="input-text" ';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='title="' . $field->getTitle() . '" ';
            $html .='value="dynamic" />';
        } elseif ($field->getFieldType() == 'country') {
            $options = $this->countryCollection->toOptionArray();

            $html = '<select name="option[' . $field->getId() . ']" ';
            $html .='class="input-text country" ';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '>';
            foreach ($options as $option) {
                $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
            }

            $html .='</select>';
        } elseif ($field->getFieldType() == 'state') {
            $html = '<select name="option[' . $field->getId() . ']" ';
            $html .='class="input-text region_select" ';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='placeholder=" '.$field->getPlaceholder() . '" ';
            $html .='title="' . $field->getTitle() . '" ';
            $html .='style="display:none;">';
            $html .='<option value="">Please select a region, state or province.</option>';
            $html .='</select>';
            $html .= '<input type="text" ';
            $html .='name="field[' . $field->getId() . ']" ';
            $html .='class="input-text region_input" ';
            $html .='id="field_id_' . $field->getId() . '" ';
            $html .='title="' . $field->getTitle() . '" ';
            if ($field->getIsRequired()) {
                $html .= 'data-validate="{required: true}" ';
            }

            $html .= '/>';
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getSubmitButton()
    {
        $submitButton = $this->getForm()->getSubmitButton();
        return ($submitButton) ? $submitButton : __('Submit');
    }

    /**
     * @return bool
     */
    public function isCaptchaEnabled()
    {
        return $this->getForm()->getCaptcha();
    }

    /**
     * @return string
     */
    public function getGoogleSiteKey()
    {
        return $this->helper->getConfig('hexaform/captcha_settings/site_key');
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = $this->helper->getConfig('hexaform/custom_option/date_order');
        return str_replace(',', '/', $dateFormat);
    }

    public function buttonCanDisplay()
    {
        $fieldSize = $this->fieldFactory->create()->getCollection()->addFieldToFilter(
            'form_id',
            $this->getForm()->getId()
        )->addFieldToFilter(
            'is_active',
            '1'
        )->getSize();

        return ($fieldSize) ? true : false;
    }
}
