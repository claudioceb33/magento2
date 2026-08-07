define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Ceb_InvoiceA/js/model/field-validation'
    ],
    function (Component, additionalValidators,fieldValidation) {
        'use strict';
        additionalValidators.registerValidator(fieldValidation);
        return Component.extend({});
    }
);
