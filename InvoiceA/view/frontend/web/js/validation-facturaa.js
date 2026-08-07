define([
    'jquery'
], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod.apply($.validator,[
            'validate-facturaa',
            function (value) {
                var selected = $('.ceb-invoicea-fields select[name="customer_tax_situation"]').val();
                return !(!(selected === '0' || selected === '') && value === '');
            },
            $.mage.__('Si necesitás Factura A, debes completar este campo.')
        ]);
    }
});
