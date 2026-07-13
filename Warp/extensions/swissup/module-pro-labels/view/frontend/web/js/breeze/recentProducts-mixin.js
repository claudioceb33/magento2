define([
    'jquery'
], function ($) {
    'use strict';

    $.mixin('recentProducts', {
        /** [afterGetAdditionalContent description] */
        getAdditionalContent: function (o, item, element) {
            if (item.extension_attributes && item.extension_attributes.swissup_prolabel) {
                $(element).prolabels(JSON.parse(item.extension_attributes.swissup_prolabel));
            }

            return o(item, element);
        }
    });
});
