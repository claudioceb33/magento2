define([
    'jquery',
    'knockout',
    'Magento_Ui/js/form/form'
], function ($, ko, Form) {
    'use strict';

    var logVM;

    var ViewModel = function() {
        this.rows = ko.observableArray();
        this.showLoader = ko.observable(true);
        this.clearOutput = function () {
            this.rows.removeAll();
        }
    };

    /**
     * Process ajax response
     *
     * @param  {Object} response [description]
     * @return void
     */
    function processResponse(response) {
        if (!logVM) {
            logVM = new ViewModel();
            ko.applyBindings(logVM, prepareLog().get(0));
        }

        // response.log is array of rows
        $.each(response.log, function (index, row) {
            var indexOf = logVM.rows()
                .map(function (e) {return e.lineId})
                .indexOf(row.lineId);
            if (indexOf != -1) {
                logVM.rows.replace(logVM.rows()[indexOf], row);
            } else {
                logVM.rows.push(row);
            }
        });

        if (response.url) {
            $.ajax({
                url: response.url,
                data: {
                    form_key: window.FORM_KEY,
                    page_size: response.page_size,
                    cur_page: response.next_page,
                    entity_type: response.entity_type
                }
            })
            .done(processResponse);
        } else {
            logVM.showLoader(false);
        }
    };

    /**
     * Init data-bind for generate log
     *
     * @return {Object} jQuery object
     */
    function prepareLog() {
        return $('.seotemplates-generate-log').attr({
                'data-bind': "template: { name: 'generate-log-template' }"
            });
    }

    return Form.extend({

        /** @inheritdoc */
        initialize: function () {
            this._super();

            // add custom listener for responseData property of form componenet
            this.responseData.subscribe(function (response) {
                if (logVM) {
                    logVM.clearOutput();
                    logVM.showLoader(true);
                }

                processResponse(response);
            });

            return this;
        }
    });
});
