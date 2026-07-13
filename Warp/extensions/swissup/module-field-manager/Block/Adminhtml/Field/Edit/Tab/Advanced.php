<?php
namespace Swissup\FieldManager\Block\Adminhtml\Field\Edit\Tab;

class Advanced extends \Swissup\FieldManager\Block\Adminhtml\Field\Edit\Tab\AbstractTab
{
    /**
     * {@inheritdoc}
     * @return $this
     */
    protected function _prepareForm()
    {
        $fieldObject = $this->getFieldObject();
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            ['legend' => __('Advanced Field Properties')]
        );

        $codePrefixLength = !is_null($this->getData('config')) ? strlen($this->getData('config')['codePrefix']) : 0;
        $maxCodeLength = \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH - $codePrefixLength;
        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d', $maxCodeLength
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                    $maxCodeLength
                ),
                'class' => $validateClass,
                'scope_label' => '[global]'
            ]
        );

        $prefix = $fieldObject->getWebsite()->getId() ? 'scope_' : '';
        $fieldset->addField(
            'default_value_text',
            'text',
            [
                'name' => $prefix . 'default_value_text',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $fieldObject->getDefaultValue(),
                'scope_label' => '[website]'
            ]
        );
        $fieldset->addField(
            'default_value_yesno',
            'select',
            [
                'name' =>  $prefix . 'default_value_yesno',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $this->yesnoSource,
                'value' => $fieldObject->getDefaultValue(),
                'scope_label' => '[website]'
            ]
        );
        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name' => $prefix . 'default_value_date',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $fieldObject->getDefaultValue(),
                'date_format' => $dateFormat,
                'scope_label' => '[website]'
            ]
        );
        $fieldset->addField(
            'default_value_textarea',
            'textarea',
            [
                'name' => $prefix . 'default_value_textarea',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $fieldObject->getDefaultValue(),
                'scope_label' => '[website]'
            ]
        );

        if ($fieldObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
        }
        $this->setForm($form);
        $this->helper->addScopeHtml($this, [
            'default_value_text',
            'default_value_textarea',
            'default_value_date',
            'default_value_yesno'
        ]);

        return $this;
    }
}
