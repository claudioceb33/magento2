define([
    'jquery',
    'mage/translate',
    'mage/validation'
], function ($, $t) {
        'use strict';
        return {
            validate: function (hideError) {
                
                var isValid = true, validatorConfig = {
                    errorElement: 'div',
                    hideError: false
                };
                if (!$.validator.validateSingleElement($('.ecommerce66-invoicea-fields input[name="customer_company"]'),validatorConfig) || !$.validator.validateSingleElement($('.ecommerce66-invoicea-fields input[name="customer_cuit"]'),validatorConfig)) {
                    isValid = false;
                }

                return isValid;
            }
        }
    }
);
