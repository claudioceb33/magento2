define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Ceb_ViewModelCustom/ceb-summary'
        },

        /**
         * Knockout + CustomerData:
         * Magento UI components expose observables to templates. customerData.get
         * returns a Knockout observable backed by browser storage.
         *
         * ES: Knockout + CustomerData:
         * Los UI components de Magento exponen observables a templates.
         * customerData.get devuelve un observable Knockout respaldado por storage
         * del navegador.
         */
        initialize: function () {
            this._super();
            this.cebSection = customerData.get('ceb-section');

            return this;
        }
    });
});
