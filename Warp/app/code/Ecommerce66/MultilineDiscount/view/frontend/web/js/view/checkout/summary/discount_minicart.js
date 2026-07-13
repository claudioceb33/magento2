define(
    [
        'jquery',
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'Magento_Catalog/js/price-utils'
    ],
    function ($,Component,customerData,priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Gratia_Checkout/checkout/summary/discount'
            },
            isDisplayedDiscountTotal : function () {
                return true;
            },
            getSegments : function () {
                var cart = customerData.get('cart');
                if (cart().custom_discounts == null) return [{}];
                var segments = cart().custom_discounts;
                if (Object.keys(segments).length > 0) {
                    return $.map(segments, function (value, key){
                        var val = typeof value === 'object'?value:JSON.parse(value);
                        return {title:val.label, value:priceUtils.formatPriceLocale(val.amount, {})};
                    });
                }
                return [{}];
            }
        });
    }
);
