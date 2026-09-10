<?php

namespace Ceb\CheckoutLayoutProcessor\Plugin\Checkout;

class LayoutProcessorPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->checkoutSession = $checkoutSession;
        $this->customerAddressFactory = $customerAddressFactory;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $attributesConfig = [
            'field_custom' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => '.custom_attributes',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'options' => [],
                    'id' => 'field_custom'
                ],
                'dataScope' => '.custom_attributes.field_custom',
                'label' => 'Field Custom',
                'ceb' => 'checkoutCeb',
                'visible' => true,
                'validation' => [
                    'validate-digits' => true,
                    'max_text_length' => 8,
                    'min_text_length' => 6,
                    'validate-number' => true,
                    'required-entry' => true
                ],
                'sortOrder' => 10,
                'id' => 'field_custom'
            ]
        ];

        $steps = &$jsLayout['components']['checkout']['children']['steps']['children'];
        $shippingFields = &$steps['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];
        $shippingFields['telephone']['validation']['required-entry'] = false;

        foreach ($attributesConfig as $attributeCode => $attributeValue) {
            if (isset($steps['billing-step']['children']['payment']['children']['payments-list']['children'])) {
                $payments = &$steps['billing-step']['children']['payment']['children']['payments-list']['children'];
                foreach ($payments as $key => $payment) {
                    $paymentCode = 'billingAddress' . str_replace('-form', '', $key);
                    $attributeValue['config']['customScope'] = $paymentCode . '.custom_attributes';
                    $attributeValue['dataScope'] = $paymentCode . '.custom_attributes.' . $attributeCode;
                    $payments[$key]['children']['form-fields']['children'][$attributeCode] = $attributeValue;
                    $payments[$key]['dataScopePrefix'] = 'billingAddress' . $attributeCode;
                }
                unset($payments);
            }

            $attributeValue['config']['customScope'] = 'shippingAddress.custom_attributes';
            $attributeValue['dataScope'] = 'shippingAddress.custom_attributes.' . $attributeCode;
            $shippingFields[$attributeCode] = $attributeValue;
        }

        if (!isset($shippingFields['celular']['children'][0]['tooltip'])) {
            $shippingFields['celular']['tooltip'] = ['description' => 'Para preguntas de entrega.'];
        }

        unset($shippingFields, $steps);

        return $jsLayout;
    }
}
