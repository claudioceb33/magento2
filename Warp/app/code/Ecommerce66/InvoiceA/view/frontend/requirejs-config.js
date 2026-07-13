var config = {
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'Ecommerce66_InvoiceA/js/validation-mixin': true
            },
            'Magento_InvoiceA/js/action/place-order': {
                'Ecommerce66_InvoiceA/js/action/place-order': true
            },
            'mage/validation': {
                'Ecommerce66_InvoiceA/js/validation-facturaa': true
            }
        }
    }
}
