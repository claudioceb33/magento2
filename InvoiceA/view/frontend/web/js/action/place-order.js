define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'mage/url',
    'Magento_Checkout/js/model/error-processor',
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
            var url = urlFormatter.build('ceb/quote/save');
            var customerTaxSituation = $('select[name="customer_tax_situation"]').val(),
                customerCuit = $('input[name="customer_cuit"]').val(),
                customerCompany = $('input[name="customer_company"]').val();
            var isCustomer = customer.isLoggedIn();

            if (customerTaxSituation !== '1') {
                return originalAction(paymentData, redirectOnSuccess);
            }

            var payload = {
                'cartId': quoteId,
                'customerTaxSituation': customerTaxSituation,
                'customerCuit': customerCuit,
                'customerCompany': customerCompany,
                'is_customer': isCustomer
            };

            return $.ajax({
                url: url,
                data: payload,
                dataType: 'text',
                type: 'POST'
            }).then(
                function () {
                    return originalAction(paymentData, redirectOnSuccess);
                },
                function (response) {
                    errorProcessor.process(response);
                    return $.Deferred().reject(response);
                }
            );
        });
    };
});
