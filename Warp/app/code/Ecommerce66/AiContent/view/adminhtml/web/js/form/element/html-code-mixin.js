define([
    'Ecommerce66_AiContent/js/pagebuilder/form/html-generator-helper'
], function (htmlGeneratorHelper) {
    'use strict';

    return function (HtmlCode) {
        return HtmlCode.extend({
            defaults: {
                elementTmpl: 'Ecommerce66_AiContent/form/element/html-code'
            },

            /**
             * Trigger AI generator modal
             */
            clickGenerateAi: function () {
                htmlGeneratorHelper.open(this);
            }
        });
    };
});
