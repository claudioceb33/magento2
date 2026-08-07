var config = {
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'Ceb_InvoiceA/js/validation-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Ceb_InvoiceA/js/action/place-order': true
            },
            'mage/validation': {
                'Ceb_InvoiceA/js/validation-facturaa': true
            }
        }
    }
}
