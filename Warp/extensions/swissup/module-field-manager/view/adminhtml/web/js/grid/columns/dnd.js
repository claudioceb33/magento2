define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            label: '',
            filterable: false,
            sortOrder: 0,
            fieldClass: {
                'data-grid-draggable-row-cell': true
            },
            bodyTmpl: 'ui/dynamic-rows/cells/dnd'
        },

        /**
         * Dummy method that is called from ui/dynamic-rows/cells/dnd template
         *
         * @param {HTMLElement} elem
         */
        initListeners: function (elem) {
            //
        }
    });
});
