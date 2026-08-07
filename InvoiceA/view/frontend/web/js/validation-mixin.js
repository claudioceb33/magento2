define([
    'jquery',
    'Magento_Ui/js/lib/validation/utils'
], function ($, utils) {
    "use strict";
    var $checkout = $('.checkout-index-index');
    var section = getSection();
    var selector = ' select[name="customer_tax_situation"]';

    function getSection(){
        return '.ceb-invoicea-fields';
    }

    $checkout.on('change','select[name="customer_tax_situation"]',function (el){
        setTimeout(function(){
            section = getSection();
            //console.log(el.currentTarget);
            var selected = $(el.currentTarget).val();

            var $form = $(el.currentTarget).closest(section);
            if (selected === '0' || selected === '') {
                $('div[name="cebCustomInvoiceA.customer_cuit"]').removeClass('_required');
                $form.find('input[name="customer_cuit"]').closest('.field').fadeOut();
                $form.find('input[name="customer_cuit"]').removeClass('validate-facturaa');
                $('div[name="cebCustomInvoiceA.customer_company"]').removeClass('_required');
                $form.find('input[name="customer_company"]').closest('.field').fadeOut();
                $form.find('input[name="customer_company"]').removeClass('validate-facturaa');
            }else if(selected === '1'){
                $('div[name="cebCustomInvoiceA.customer_cuit"]').addClass('_required');
                $form.find('input[name="customer_cuit"]').closest('.field').fadeIn();
                $form.find('input[name="customer_cuit"]').addClass('validate-facturaa');
                $('div[name="cebCustomInvoiceA.customer_company"]').addClass('_required');
                $form.find('input[name="customer_company"]').closest('.field').fadeIn();
                $form.find('input[name="customer_company"]').addClass('validate-facturaa');
            }
        },700);
    });

    return function (validator) {
        /*validator.addRule(
            'validate-company',
            function (value) {
                return !(shouldBeRequired() && value === '');
            },
            $.mage.__('Si necesitás Factura A, deberás completar este campo.')
        );
        validator.addRule(
            'validate-cuit',
            function (value) {
                return !(shouldBeRequired() && value === '');
            },
            $.mage.__('Si necesitás Factura A, deberás completar este campo.')
        );*/
        return validator;
    }
});
