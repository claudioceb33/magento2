// jscs:disable jsDoc
define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'form',
    'validation',
    'mage/translate'
], function ($, mageTemplate, rg) {
    'use strict';

    return function (config, element) {
        var optionPanel = $(element),
            optionsValues = [],
            editForm = $('#edit_form'),
            attributeOption = {
                itemCount: 0,
                totalItems: 0,
                rendered: 0,
                template: mageTemplate(config.attributeTemplate),
                elements: '',
                add: function (data, render) {
                    var newElement;

                    if (!data.intype) {
                        data.intype = this.getOptionInputType();
                    }

                    newElement = this.template({
                        data: data
                    });

                    this.itemCount++;
                    this.totalItems++;
                    this.elements += newElement;

                    if (render) {
                        this.render();
                        this.updateItemsCountField();
                    }
                },
                updateItemsCountField: function () {
                    $('#seourls-option-count-check').val(this.totalItems > 0 ? '1' : '');
                },
                render: function () {
                    $('[data-role=options-container]', optionPanel).append(this.elements);
                    this.elements = '';
                },
                renderWithDelay: function (data, from, step, delay) {
                    var arrayLength = data.length,
                        len;

                    for (len = from + step; from < len && from < arrayLength; from++) {
                        this.add(data[from]);
                    }
                    this.render();

                    if (from === arrayLength) {
                        this.updateItemsCountField();
                        this.rendered = 1;
                        $('body').trigger('processStop');

                        return true;
                    }
                    setTimeout(this.renderWithDelay.bind(this, data, from, step, delay), delay);
                },
                ignoreValidate: function () {
                    var ignore = '.ignore-validate input, ' +
                        '.ignore-validate select, ' +
                        '.ignore-validate textarea';

                    $('#edit_form').data('validator').settings.forceIgnore = ignore;
                },
                getOptionInputType: function () {
                    var optionDefaultInputType = 'radio';

                    if ($('#frontend_input').val() === 'multiselect') {
                        optionDefaultInputType = 'checkbox';
                    }

                    return optionDefaultInputType;
                }
            };

        optionPanel.on('render', function () {
            attributeOption.ignoreValidate();

            if (attributeOption.rendered) {
                return false;
            }
            $('body').trigger('processStart');
            attributeOption.renderWithDelay(config.attributesData, 0, 100, 300);
        });

        editForm.on('submit', function () {
            optionPanel.find('input[name]')
                .each(function () {
                    if (this.disabled) {
                        return;
                    }

                    if (this.type === 'checkbox' || this.type === 'radio') {
                        if (this.checked) {
                            optionsValues.push(this.name + '=' + $(this).val());
                        }
                    } else {
                        optionsValues.push(this.name + '=' + $(this).val());
                    }
                });
            $('<input>')
                .attr({
                    type: 'hidden',
                    name: 'swissup[values_serialized]'
                })
                .val(JSON.stringify(optionsValues))
                .prependTo(editForm);
            optionPanel.find('table')
                .replaceWith($('<div>').text(jQuery.mage.__('Sending attribute values as package.')));
        });

        rg.set('seourls-manage-options-panel', attributeOption);

        optionPanel.trigger('render');
    };
});
