<?php
namespace Ceb\InvoiceA\Block;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    public function process($jsLayout)
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['afterMethods']['children']
        ['ceb-custom-checkout'] =
            [
                'component' => 'uiComponent',
                'config' => [
                    'template' => 'Ceb_InvoiceA/container'
                ],
                'visible' => true,
                'sortOrder' => 0
            ];
        $fields = [
            'customer_tax_situation' => [
                'component' => 'Magento_Ui/js/form/element/select',
                'config' => [
                    'customScope' => 'cebCustomInvoiceA',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'tooltip' => [
                        'description' => 'Seleccione si necesita Factura A',
                    ],
                ],
                'dataScope' => 'cebCustomInvoiceA' . '.' . 'customer_tax_situation',
                'label' => 'Emitir Factura A',
                'provider' => 'checkoutProvider',
                'sortOrder' => 1000,
                'validation' => [],
                'options' => $this->getOptions(),
                'visible' => true,
                'value' => ''
            ],
            'customer_cuit' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'cebCustomInvoiceA',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'tooltip' => [
                        'description' => 'Ingrese su número de CUIT (sólo números)',
                    ],
                ],
                'dataScope' => 'cebCustomInvoiceA' . '.' . 'customer_cuit',
                'label' => 'CUIT (sólo números)',
                'provider' => 'checkoutProvider',
                'sortOrder' => 1020,
                'validation' => [
                    'validate-facturaa' => true,
                    'validate-number' => 0,
                    'min_text_length' => 11,
                    'max_text_length' => 11
                ],
                'visible' => true,
                'value' => '',
                'additionalClasses' => ['validate-facturaa']
            ],
            'customer_company' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'cebCustomInvoiceA',
                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'tooltip' => [
                        'description' => 'Ingrese el nombre de su Empresa o Razón Social',
                    ],
                ],
                'dataScope' => 'cebCustomInvoiceA' . '.' . 'customer_company',
                'label' => 'Empresa',
                'provider' => 'checkoutProvider',
                'sortOrder' => 1010,
                'validation' => [
                    'validate-facturaa' => true
                ],
                'visible' => true,
                'value' => '',
                'additionalClasses' => 'validate-facturaa'
            ]

        ];

        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['afterMethods']['children']
        ['ceb-custom-checkout']['children'] = $fields;

        return $jsLayout;
    }

    private function getOptions()
    {
        return [
            [
                'value' => '',
                'label' => __('-- Please Select --')
            ],
            [
                'value' => '1',
                'label' => __('Yes')
            ],
            [
                'value' => '0',
                'label' => __('No')
            ]
        ];
    }
}

