
define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({
            showMessageProductNotAvailableFree: function ()
            {
                var msg = checkoutConfig.quoteData.msg_not_available_item;
                if (msg !== '') msg = 'Uno de sus ítems no es elegible para envío gratis';
                $('#opc-shipping_method .product-not-available-free').text(msg);
                var enableProductNotAvailable = checkoutConfig.quoteData.not_available_item;
                if (enableProductNotAvailable == 0) return false;
                return true;
            }
        });
    };
});
