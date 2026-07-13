define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals',
        'Magento_Catalog/js/price-utils'
    ],
    function ($,Component,quote,totals,priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Ecommerce66_MultilineDiscount/checkout/summary/discount'
            },
            totals: quote.getTotals(),
            isDisplayedDiscountTotal : function () {
                return true;
            },
            getSegments : function () {
                if (totals.getSegment('custom_discount') == null) return [{}];
                var segments = totals.getSegment('custom_discount').value;
                if (segments.length > 0) {
                    return $.map(segments, function (value, key){
                        var val = typeof value === 'object'?value:JSON.parse(value);
                        return {code: val.code, title:val.title, value:priceUtils.formatPriceLocale(val.value, quote.getPriceFormat())};
                    });
                }
                return [{}];
            }
        });
    }
);
