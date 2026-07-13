<?php

namespace Swissup\AddressFieldManager\Plugin\Block;

use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Ui\Component\Form\AttributeMapper;
use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckoutLayoutProcessor
{
    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    protected $attributeMapper;

    /**
     * @var AttributeMerger
     */
    protected $merger;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    protected $helper;

    /**
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        TimezoneInterface $localeDate,
        \Swissup\FieldManager\Helper\Data $helper
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
        $this->localeDate = $localeDate;
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    private function getAddressAttributes()
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $elements[$attribute->getAttributeCode()]['sortOrder'] =
                $attribute->getSortOrder();
        }
        return $elements;
    }

    /**
     * @return array
     */
    private function getCustomAddressAttributes()
    {
        /** @var \Magento\Eav\Api\Data\AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if (!$attribute->getIsUserDefined()) {
                continue;
            }
            $elements[$code] = $this->attributeMapper->map($attribute);

            $type = $this->helper->getFieldDisplayType($attribute);
            if ($type === 'checkbox-set') {
                $elements[$code]['formElement'] = $type;
                $elements[$code]['config']['multiple'] = $attribute->getFrontendInput() === 'multiselect';
                $elements[$code]['options'] = array_values(array_filter(
                    $elements[$code]['options'],
                    function ($option) {
                        $value = trim((string) $option['value']);
                        $label = trim((string) $option['label']);

                        return strlen($value) || strlen($label);
                    }
                ));
            }

            $elements[$code]['config']['additionalClasses'] = 'sfm-field';

            if (isset($elements[$code]['label'])) {
                $label = $elements[$code]['label'];
                $elements[$code]['label'] = __($label);
            }
            if (isset($elements[$code]['dataType']) && $elements[$code]['dataType'] == 'date') {
                $format = $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT);
                $elements[$code]['options']['dateFormat'] = $format;
                $elements[$code]['config']['inputDateFormat'] = $format;
            }
        }

        return $elements;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $jsLayout
    ) {
        $this->applySortOrder($jsLayout)->addCustomFields($jsLayout);

        return $jsLayout;
    }

    /**
     * Override sortOrder values from luma theme
     *
     * @param $jsLayout
     * @return mixed
     */
    private function applySortOrder(&$jsLayout)
    {
        $this->updateFieldsSortOrder(
            $jsLayout['components']['checkout']['children']['steps']
            ['children']['shipping-step']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children']
        );

        // update payment forms when DisplayBillingOnPaymentMethod is used
        $paymentForms = $jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']
            ['children']['payments-list']['children'];

        foreach ($paymentForms as $form => $values) {
            if (strpos($form, '-form') === false || !isset($values['children']['form-fields'])) {
                continue;
            }

            $this->updateFieldsSortOrder(
                $jsLayout['components']['checkout']['children']['steps']
                ['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'][$form]
                ['children']['form-fields']['children']
            );
        }

        // update payment page when DisplayBillingOnPaymentPage is used
        if (isset($jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']['children']
            ['afterMethods']['children']['billing-address-form']['children']
            ['form-fields']['children'])) {

            $this->updateFieldsSortOrder(
                $jsLayout['components']['checkout']['children']
                ['steps']['children']['billing-step']['children']['payment']
                ['children']['afterMethods']['children']
                ['billing-address-form']['children']['form-fields']['children']
            );
        }

        return $this;
    }

    /**
     * Apply sort order to each of the field
     *
     * @param  array &$fieldsContainer
     * @return void
     */
    private function updateFieldsSortOrder(&$fieldsContainer)
    {
        $fields = $this->getAddressAttributes();

        foreach ($fieldsContainer as $code => $field) {
            if (!isset($fields[$code]['sortOrder'])) {
                continue;
            }
            $fieldsContainer[$code]['sortOrder'] = $fields[$code]['sortOrder'];
        }
    }

    /**
     * Add user defined address fields
     *
     * @param $jsLayout
     * @return mixed
     */
    private function addCustomFields(&$jsLayout)
    {
        $customFields = $this->getCustomAddressAttributes();

        // update shipping form
        $fields = $jsLayout['components']['checkout']['children']['steps']
            ['children']['shipping-step']['children']['shippingAddress']
            ['children']['shipping-address-fieldset']['children'];

        $fields = $this->prepareFieldsForMerger($fields, $customFields);

        $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] =
                $this->merger->merge(
                    $customFields,
                    'checkoutProvider',
                    'shippingAddress.custom_attributes',
                    $fields
                );

        // update payment forms when DisplayBillingOnPaymentMethod is used
        $paymentForms = $jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']
            ['children']['payments-list']['children'];
        foreach ($paymentForms as $form => $values) {
            if (strpos($form, '-form') === false || !isset($values['children']['form-fields'])) {
                continue;
            }

            $fields = $values['children']['form-fields']['children'];
            $fields = $this->prepareFieldsForMerger($fields, $customFields);
            $jsLayout['components']['checkout']['children']['steps']
                ['children']['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$form]['children']['form-fields']
                ['children'] =
                    $this->merger->merge(
                        $customFields,
                        'checkoutProvider',
                        $values['dataScopePrefix'] . '.custom_attributes',
                        $fields
                    );
        }

        // update payment page when DisplayBillingOnPaymentPage is used
        if (isset($jsLayout['components']['checkout']['children']['steps']
            ['children']['billing-step']['children']['payment']['children']
            ['afterMethods']['children']['billing-address-form']['children']
            ['form-fields']['children'])) {

            $dataScopePrefix = $jsLayout['components']['checkout']['children']
                ['steps']['children']['billing-step']['children']['payment']
                ['children']['afterMethods']['children']
                ['billing-address-form']['dataScopePrefix'];
            $fields = $jsLayout['components']['checkout']['children']
                ['steps']['children']['billing-step']['children']['payment']
                ['children']['afterMethods']['children']
                ['billing-address-form']['children']['form-fields']['children'];

            $fields = $this->prepareFieldsForMerger($fields, $customFields);
            $jsLayout['components']['checkout']['children']
                ['steps']['children']['billing-step']['children']['payment']
                ['children']['afterMethods']['children']
                ['billing-address-form']['children']['form-fields']['children'] =
                    $this->merger->merge(
                        $customFields,
                        'checkoutProvider',
                        $dataScopePrefix . '.custom_attributes',
                        $fields
                    );
        }

        // Fix for fields with options in Magento 2.3.4
        if (!isset($jsLayout['components']['checkoutProvider']['customAttributes'])) {
            $jsLayout['components']['checkoutProvider']['customAttributes'] = [];
        }
        foreach ($customFields as $code => $field) {
            if (isset($field['options'])) {
                $jsLayout['components']['checkoutProvider']['customAttributes'][$code] = $field['options'];
            }
        }

        return $this;
    }

    /**
     * merger fix:
     * 1. keep 'config' section
     * 2. change component to 'Magento_Ui/js/form/element/checkbox-set'
     */
    private function prepareFieldsForMerger($fields, $customFields)
    {
        foreach ($customFields as $key => $field) {
            if (isset($field['config'])) {
                $fields[$key]['config'] = array_merge($fields[$key]['config'] ?? [], $field['config']);
            }
            if ($field['formElement'] === 'checkbox-set') {
                $fields[$key]['component'] = 'Magento_Ui/js/form/element/checkbox-set';
            }
        }
        return $fields;
    }
}
