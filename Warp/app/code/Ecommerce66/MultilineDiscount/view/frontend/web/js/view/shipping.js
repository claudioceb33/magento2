define([
    'uiComponent',
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function (Component, ko, $, quote, customerData) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            quote.shippingMethod.subscribe(this.onShippingMethodChange, this);
        },

        onShippingMethodChange: function (method) {
            customerData.invalidate(['cart']);
            customerData.reload(['cart'], true);
        }
    });
});
