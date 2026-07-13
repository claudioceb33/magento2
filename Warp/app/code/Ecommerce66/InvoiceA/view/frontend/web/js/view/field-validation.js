define(
    [
        'uiComponent',
        'Magento_InvoiceA/js/model/payment/additional-validators',
        'Ecommerce66_InvoiceA/js/model/field-validation'
    ],
    function (Component, additionalValidators,fieldValidation) {
        'use strict';
        additionalValidators.registerValidator(fieldValidation);
        return Component.extend({});
    }
);
