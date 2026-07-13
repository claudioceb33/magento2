define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_InvoiceA/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_InvoiceA/js/model/url-builder',
    'mage/url',
    'Magento_InvoiceA/js/model/error-processor',
    'uiRegistry'
], function (
    $, 
    wrapper, 
    quote,
    customer,
    urlBuilder, 
    urlFormatter, 
    errorProcessor,
    registry
) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, redirectOnSuccess) {
            var quoteId = quote.getQuoteId();

            var url = urlFormatter.build('ecommerce66/quote/save');
            var customerTaxSituation = $('select[name="customer_tax_situation"]').val(),
                customerCuit = $('input[name="customer_cuit"]').val(),
                customerCompany = $('input[name="customer_company"]').val();
            var isCustomer = customer.isLoggedIn();
            if (customerTaxSituation=='1') {

                var payload = {
                    'cartId': quoteId,
                    'customerTaxSituation': customerTaxSituation,
                    'customerCuit': customerCuit,
                    'customerCompany': customerCompany,
                    'is_customer': isCustomer
                };

                if (!payload.customerTaxSituation) {
                    return true;
                }

                var result = true;

                $.ajax({
                    url: url,
                    data: payload,
                    dataType: 'text',
                    type: 'POST',
                }).done(
                    function (response) {
                        result = true;
                    }
                ).fail(
                    function (response) {
                        result = false;
                        errorProcessor.process(response);
                    }
                );
            }
            return originalAction(paymentData, redirectOnSuccess);
        });
    };
});
