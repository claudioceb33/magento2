define([
    'ko',
    'Magento_Ui/js/lib/view/utils/async',
    'uiLayout',
    'uiCollection'
], function (ko, $, layout, Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            clientConfig: {
                name: '${ $.name }_client',
                component: 'Magento_Ui/js/grid/editing/client',
                validateBeforeSave: false
            },
            modules: {
                source: '${ $.dataProvider }',
                client: '${ $.clientConfig.name }',
                columns: '${ $.columnsProvider }'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initClient()
                .initSortable();

            return this;
        },

        /**
         * Initializes client component.
         *
         * @returns {Editor} Chainable.
         */
        initClient: function () {
            layout([this.clientConfig]);

            return this;
        },

        /**
         * Init sortable rows
         */
        initSortable: function () {
            var self = this;
            $.async('.admin__data-grid-wrap tbody', function (el) {
                $(el).sortable({
                    axis: 'y',
                    handle: '.draggable-handle',
                    placeholder: 'afm-placeholder data-row',
                    forcePlaceholderSize: true,

                    /**
                     * @param {Event} event
                     * @param {Object} ui
                     */
                    start: function (event, ui) {
                        ui.placeholder.append(ui.helper.html());
                    },

                    /**
                     * @param {Event} event
                     * @param {Object} ui
                     */
                    update: function (event, ui) {
                        var prevItem = ui.item.prev('.data-row').children().get(0),
                            nextItem = ui.item.next('.data-row').children().get(0),
                            contextItem = ko.contextFor(ui.item.children().get(0)),
                            contextPrev = prevItem ? ko.contextFor(prevItem) : false,
                            contextNext = nextItem ? ko.contextFor(nextItem) : false,
                            data = {
                                item: contextItem.$row()[self.indexField]
                            };

                        if (contextNext) {
                            data.before = contextNext.$row()[self.indexField];
                        } else if (contextPrev) {
                            data.after = contextPrev.$row()[self.indexField];
                        }

                        self.save(data);

                        // move the item back as it conflicts with grid rendering
                        $(this).sortable('cancel');
                    }
                });
            });
        },

        /**
         * Save moved item
         */
        save: function (data) {
            this.columns('showLoader');

            this.client()
                .save(data)
                .done(this.onDataSaved.bind(this))
                .fail(this.onSaveError.bind(this));

            return this;
        },

        /**
         * Handles successful save request.
         */
        onDataSaved: function () {
            this.source('reload', {
                refresh: true
            });
        },

        /**
         * Handles failed save request.
         *
         * @param {(Array|Object)} errors - List of errors or a single error object.
         */
        onSaveError: function (errors) {
            this.columns('hideLoader');
        }
    });
});
