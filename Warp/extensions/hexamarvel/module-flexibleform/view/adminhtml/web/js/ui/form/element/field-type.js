/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';

    return select.extend({

        initialize: function () {
            this._super();
            this.onUpdate(this.value());
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var optionArray = ["select", "multiselect", "checkbox", "radio"];

            var field1 = uiRegistry.get('index = option_values');
            if (optionArray.indexOf(value) !== -1) {
                field1.show();
            } else {
                field1.hide();
            }

            var textArray = ["text", "email", "number", "textarea", "date", "time", "datetime"];

            var field2 = uiRegistry.get('index = placeholder');
            if (textArray.indexOf(value) !== -1) {
                field2.show();
            } else {
                field2.hide();
            }

            var termsArray = ["terms"];

            var field3 = uiRegistry.get('index = terms_options');
            if (termsArray.indexOf(value) !== -1) {
                field3.show();
            } else {
                field3.hide();
            }

            return this._super();
        },
    });
});
