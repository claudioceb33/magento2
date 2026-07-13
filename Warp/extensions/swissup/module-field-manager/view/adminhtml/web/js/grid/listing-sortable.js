define([
    'ko',
    'uiLayout',
    'Magento_Ui/js/grid/listing'
], function (ko, layout, Listing) {
    'use strict';

    return Listing.extend({
        defaults: {
            sortableConfig: {
                name: '${ $.name }_sortable',
                component: 'Swissup_FieldManager/js/grid/editing/sortable',
                columnsProvider: '${ $.name }',
                dataProvider: '${ $.provider }',
                validateBeforeSave: false,
                enabled: true
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.initSortable();

            return this;
        },

        /**
         * Initializes client component.
         *
         * @returns {Editor} Chainable.
         */
        initSortable: function () {
            if (this.sortableConfig.enabled) {
                layout([this.sortableConfig]);
            }

            return this;
        }
    });
});
