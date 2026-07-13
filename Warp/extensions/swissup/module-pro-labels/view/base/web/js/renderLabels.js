define([
    'jquery',
    'Swissup_ProLabels/js/viewModel/labels',
    'ko',
    'Magento_Ui/js/lib/knockout/bootstrap', // required for KO to load remote tempplates
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, LabelsViewModel, ko) {
    'use strict';

    $.widget('swissup.renderLabels', {
        component: 'Swissup_ProLabels/js/renderLabels',
        options: {
            template: 'Swissup_ProLabels/labels',
            labelsData: {},
            predefinedVars: {},
            target: '',
            renderMode: 'replaceChildren' // other 'replaceNode'
        },

        viewModel: null,

        /**
         * Add ko template bind and apply ko binding to element
         */
        _create: function () {
            const me = this;
            const remoteTemplate = me.options.template;
            const inlineTemplate = `${me.options.template}.html`;

            me.viewModel = new LabelsViewModel(
                me.options.labelsData,
                me.options.predefinedVars
            );
            ko.renderTemplate(
                document.getElementById(inlineTemplate) ? inlineTemplate : remoteTemplate,
                me.viewModel,
                {},
                $(me.options.target || me.element, me.element).get(0),
                me.options.renderMode
            );
        },

        /**
         * @return {LabelsViewModel}
         */
        getViewModel: function () {
            return this.viewModel;
        }
    });

    return $.swissup.renderLabels;
});
