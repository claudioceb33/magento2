/*
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

define([
    'jquery',
    'jquery/ui',
    'Lof_Formbuilder/js/underscore',
    'Lof_Formbuilder/js/rivets',
    'Lof_Formbuilder/js/backbone',
    'Lof_Formbuilder/js/vesbrowser',
    'Lof_Formbuilder/js/deep-model',
    'Lof_Formbuilder/js/jquery.minicolors.min',
    'Lof_Formbuilder/js/minicolor',
    '//maps.googleapis.com/maps/api/js?sensor=false&libraries=places&key=AIzaSyDRVUZdOrZ1HuJFaFkDtmby0E93eJLykIk',
    'Lof_Formbuilder/js/google_map',
    "wysiwygAdapter",
    "Magento_Ui/js/modal/prompt",
    "Magento_Ui/js/modal/confirm",
    "Magento_Ui/js/modal/alert",
    "Magento_Ui/js/modal/modal",
    "jquery/jstree/jquery.jstree",
    "mage/mage"
], function ($, ui, Underscore, rivets, Backbone, tinyMCEm, prompt, confirm, alert, modal) {

    var currentModel = '';
    jQuery.scrollWindowTo = function (pos, duration, cb) {
        if (duration == null) {
            duration = 0;
        }
        if (pos === $(window).scrollTop()) {
            $(window).trigger('scroll');
            if (typeof cb === "function") {
                cb();
            }
            return;
        }
        return $('html, body').animate({
            scrollTop: pos - 150
        }, 600);
    };

    (function () {
        rivets.binders.input = {
            publishes: true,
            routine: rivets.binders.value.routine,
            bind: function (el) {
                return jQuery(el).bind('input.rivets', this.publish);
            },
            unbind: function (el) {
                return jQuery(el).unbind('input.rivets');
            }
        };

        rivets.configure({
            prefix: "rv",
            adapter: {
                subscribe: function (obj, keypath, callback) {
                    callback.wrapped = function (m, v) {
                        return callback(v);
                    };
                    return obj.on('change:' + keypath, callback.wrapped);
                },
                unsubscribe: function (obj, keypath, callback) {
                    return obj.off('change:' + keypath, callback.wrapped);
                },
                read: function (obj, keypath) {
                    if (keypath === "cid") {
                        return obj.cid;
                    }
                    return obj.get(keypath);
                },
                publish: function (obj, keypath, value) {
                    if (obj.cid) {
                        return obj.set(keypath, value);
                    } else {
                        return obj[keypath] = value;
                    }
                }
            }
        });

    }).call(this);

    (function () {
        var BuilderView, EditFieldView, Formbuilder, FormbuilderCollection, FormbuilderModel, ViewFieldView, _ref, _ref1, _ref2, _ref3, _ref4,
            __hasProp = {}.hasOwnProperty,
            __extends = function (child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

        FormbuilderModel = (function (_super) {
            __extends(FormbuilderModel, _super);

            function FormbuilderModel() {
                _ref = FormbuilderModel.__super__.constructor.apply(this, arguments);
                return _ref;
            }

            FormbuilderModel.prototype.sync = function () { };

            FormbuilderModel.prototype.indexInDOM = function () {
                var $wrapper,
                    _this = this;
                $wrapper = jQuery(".fb-field-wrapper").filter((function (_, el) {
                    return jQuery(el).data('cid') === _this.cid;
                }));
                return jQuery(".fb-field-wrapper").index($wrapper);
            };

            FormbuilderModel.prototype.is_input = function () {
                return Formbuilder.inputFields[this.get(Formbuilder.options.mappings.FIELD_TYPE)] != null;
            };

            return FormbuilderModel;

        })(Backbone.DeepModel);

        FormbuilderCollection = (function (_super) {
            __extends(FormbuilderCollection, _super);

            function FormbuilderCollection() {
                _ref1 = FormbuilderCollection.__super__.constructor.apply(this, arguments);
                return _ref1;
            }

            FormbuilderCollection.prototype.initialize = function () {
                return this.on('add', this.copyCidToModel);
            };

            FormbuilderCollection.prototype.model = FormbuilderModel;

            FormbuilderCollection.prototype.comparator = function (model) {
                return model.indexInDOM();
            };

            FormbuilderCollection.prototype.copyCidToModel = function (model) {
                return model.attributes.cid = model.cid;
            };

            return FormbuilderCollection;

        })(Backbone.Collection);

        ViewFieldView = (function (_super) {
            __extends(ViewFieldView, _super);

            function ViewFieldView() {
                _ref2 = ViewFieldView.__super__.constructor.apply(this, arguments);
                return _ref2;
            }

            ViewFieldView.prototype.className = "fb-field-wrapper";

            ViewFieldView.prototype.events = {
                'click .subtemplate-wrapper': 'focusEditView',
                'click .js-duplicate': 'duplicate',
                'click .js-clear': 'clear'
            };

            ViewFieldView.prototype.initialize = function (options) {
                this.parentView = options.parentView;
                this.listenTo(this.model, "change", this.render);
                return this.listenTo(this.model, "destroy", this.remove);
            };
            ViewFieldView.prototype.render = function () {
                var regEx = /^col-sm/;
                var elm = $("div");

                var classes = this.$el.attr('class').split(/\s+/); //it will return  foo1, foo2, foo3, foo4
                for (var i = 0; i < classes.length; i++) {
                    var className = classes[i];
                    if (className.match(regEx)) {
                        this.$el.removeClass(className);
                    }
                }

                this.$el.addClass('response-field-' + this.model.get(Formbuilder.options.mappings.FIELD_TYPE) + ' col-sm-' + this.model.get(Formbuilder.options.mappings.WRAPPERCOL)).data('cid', this.model.cid).html(Formbuilder.templates["view/base" + (!this.model.is_input() ? '_non_input' : '')]({
                    rf: this.model
                }));
                return this;
            };

            ViewFieldView.prototype.focusEditView = function () {
                return this.parentView.createAndShowEditView(this.model);
            };

            ViewFieldView.prototype.clear = function (e) {
                var cb, x,
                    _this = this;
                e.preventDefault();
                e.stopPropagation();
                cb = function () {
                    _this.parentView.handleFormUpdate();
                    return _this.model.destroy();
                };
                x = Formbuilder.options.CLEAR_FIELD_CONFIRM;
                switch (typeof x) {
                    case 'string':
                        if (confirm(x)) {
                            return cb();
                        }
                        break;
                    case 'function':
                        return x(cb);
                    default:
                        return cb();
                }
            };

            ViewFieldView.prototype.duplicate = function () {
                var attrs;
                attrs = _.clone(this.model.attributes);
                delete attrs['id'];
                attrs['label'] += ' Copy';
                return this.parentView.createField(attrs, {
                    position: this.model.indexInDOM() + 1
                });
            };

            return ViewFieldView;

        })(Backbone.View);

        EditFieldView = (function (_super) {
            __extends(EditFieldView, _super);

            function EditFieldView() {
                _ref3 = EditFieldView.__super__.constructor.apply(this, arguments);
                return _ref3;
            }

            EditFieldView.prototype.className = "edit-response-field";

            EditFieldView.prototype.events = {
                'click .js-add-option': 'addOption',
                'click .js-add-many-option': 'addManyOption',
                'click .js-remove-option': 'removeOption',
                'click .js-add-cate-option': 'addCategoryOption',
                'click .js-remove-cate-option': 'removeCategoryOption',
                'click .js-default-updated': 'defaultUpdated',
                'input .option-label-input': 'forceRender'
            };

            EditFieldView.prototype.initialize = function (options) {
                this.parentView = options.parentView;
                return this.listenTo(this.model, "destroy", this.remove);
            };

            EditFieldView.prototype.render = function () {
                this.$el.html(Formbuilder.templates["edit/base" + (!this.model.is_input() ? '_non_input' : '')]({
                    rf: this.model
                }));
                rivets.bind(this.$el, {
                    model: this.model
                });
                return this;
            };

            EditFieldView.prototype.remove = function () {
                this.parentView.editView = void 0;
                this.parentView.$el.find("[data-target=\"#addField\"]").click();
                return EditFieldView.__super__.remove.apply(this, arguments);
            };

            EditFieldView.prototype.addOption = function (e) {
                var $el, i, newOption, options;
                $el = jQuery(e.currentTarget);
                i = this.$el.find('.option').index($el.closest('.option'));
                options = this.model.get(Formbuilder.options.mappings.OPTIONS) || [];
                newOption = {
                    label: "",
                    checked: false
                };
                if (i > -1) {
                    options.splice(i + 1, 0, newOption);
                } else {
                    options.push(newOption);
                }
                this.model.set(Formbuilder.options.mappings.OPTIONS, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.OPTIONS);
                return this.forceRender();
            };

            EditFieldView.prototype.addManyOption = function (e) {
                $elemeents = prompt("Options", 'element 1,element 2');
                if ($elemeents) {
                    $elemeents = $elemeents.split(',');
                    var $el, i, newOption, options;
                    var main = this;
                    $elemeents.forEach(function (element) {
                        $el = jQuery(e.currentTarget);
                        i = main.$el.find('.option').index($el.closest('.option'));
                        options = main.model.get(Formbuilder.options.mappings.OPTIONS) || [];
                        newOption = {
                            label: element,
                            checked: false
                        };
                        if (i > -1) {
                            options.splice(i + 1, 0, newOption);
                        } else {
                            options.push(newOption);
                        }
                        main.model.set(Formbuilder.options.mappings.OPTIONS, options);
                        main.model.trigger("change:" + Formbuilder.options.mappings.OPTIONS);


                    });
                    return this.forceRender();
                }
            };


            EditFieldView.prototype.removeOption = function (e) {
                var $el, index, options;
                $el = jQuery(e.currentTarget);
                index = this.$el.find(".js-remove-option").index($el);
                options = this.model.get(Formbuilder.options.mappings.OPTIONS);
                options.splice(index, 1);
                this.model.set(Formbuilder.options.mappings.OPTIONS, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.OPTIONS);
                return this.forceRender();
            };

            EditFieldView.prototype.addCategoryOption = function (e) {
                var $el, i, newOption, options;
                $el = jQuery(e.currentTarget);
                i = this.$el.find('.option').index($el.closest('.option'));
                options = this.model.get(Formbuilder.options.mappings.CATE_ID) || [];
                newOption = {
                    value: "",
                    label: "",
                    checked: false
                };
                if (i > -1) {
                    options.splice(i + 1, 0, newOption);
                } else {
                    options.push(newOption);
                }
                this.model.set(Formbuilder.options.mappings.CATE_ID, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.CATE_ID);
                return this.forceRender();
            };

            EditFieldView.prototype.removeCategoryOption = function (e) {
                var $el, index, options;
                $el = jQuery(e.currentTarget);
                index = this.$el.find(".js-remove-option").index($el);
                options = this.model.get(Formbuilder.options.mappings.CATE_ID);
                options.splice(index, 1);
                this.model.set(Formbuilder.options.mappings.CATE_ID, options);
                this.model.trigger("change:" + Formbuilder.options.mappings.CATE_ID);
                return this.forceRender();
            };

            EditFieldView.prototype.defaultUpdated = function (e) {
                var $el;
                $el = jQuery(e.currentTarget);
                if (this.model.get(Formbuilder.options.mappings.FIELD_TYPE) !== 'checkboxes') {
                    this.$el.find(".js-default-updated").not($el).attr('checked', false).trigger('change');
                }
                return this.forceRender();
            };

            EditFieldView.prototype.forceRender = function () {
                return this.model.trigger('change');
            };

            return EditFieldView;

        })(Backbone.View);

        BuilderView = (function (_super) {
            __extends(BuilderView, _super);

            function BuilderView() {
                _ref4 = BuilderView.__super__.constructor.apply(this, arguments);
                return _ref4;
            }

            BuilderView.prototype.SUBVIEWS = [];

            BuilderView.prototype.events = {
                'click .js-save-form': 'saveForm',
                'click .fb-tabs a': 'showTab',
                'click .fb-add-field-types a': 'addField',
                'mouseover .fb-add-field-types': 'lockLeftWrapper',
                'mouseout .fb-add-field-types': 'unlockLeftWrapper'
            };

            BuilderView.prototype.initialize = function (options) {
                var selector;
                selector = options.selector, this.formBuilder = options.formBuilder, this.bootstrapData = options.bootstrapData;
                if (selector != null) {
                    this.setElement(jQuery(selector));
                }
                this.collection = new FormbuilderCollection;
                this.collection.bind('add', this.addOne, this);
                this.collection.bind('reset', this.reset, this);
                this.collection.bind('change', this.handleFormUpdate, this);
                this.collection.bind('destroy add reset', this.hideShowNoResponseFields, this);
                this.collection.bind('destroy', this.ensureEditViewScrolled, this);
                this.render();
                this.collection.reset(this.bootstrapData);

                /** Load Thankyou Field */
                var models = this.collection.models;
                var thankyoufield = $('#form_thankyou_field').data('value');
                $('#form_thankyou_field .loffields').remove();
                if ($('#form_sender_email_field').length > 0) {
                    var senderemailfield = $('#form_sender_email_field').data('value');
                    var sendernamefield = $('#form_sender_name_field').data('value');
                    $('#form_sender_email_field .loffields').remove();
                    $('#form_sender_name_field .loffields').remove();
                }
                for (var i = 0; i < models.length; i++) {
                    var field = models[i];
                    if (field['attributes']['field_type'] == "text" || field['attributes']['field_type'] == "email") {
                        var attr = '';
                        var field_cid = field['attributes']['cid'];
                        var field_id = typeof (field['attributes']['field_id']) !== "undefined" ? field['attributes']['field_id'] : '';

                        if (field_cid == thankyoufield || (field_id && field_id == thankyoufield)) {
                            attr = 'selected="selected"';
                        }
                        if (field_id) {
                            var fieldHtml = '<option class="loffields" ' + attr + ' value="' + field_id + '">' + field['attributes']['label'] + '</option>';
                        } else {
                            var fieldHtml = '<option class="loffields" ' + attr + ' value="' + field_cid + '">' + field['attributes']['label'] + '</option>';
                        }


                        $('#form_thankyou_field').append(fieldHtml);

                        /*Load sender email field*/
                        if ($('#form_sender_email_field').length > 0) {
                            var attr2 = '';
                            if (field_cid == senderemailfield || (field_id && field_id == senderemailfield)) {
                                attr2 = 'selected="selected"';
                            }
                            if (field_id) {
                                var fieldHtml2 = '<option class="loffields" ' + attr2 + ' value="' + field_id + '">' + field['attributes']['label'] + '</option>';
                            } else {
                                var fieldHtml2 = '<option class="loffields" ' + attr2 + ' value="' + field_cid + '">' + field['attributes']['label'] + '</option>';
                            }
                            $('#form_sender_email_field').append(fieldHtml2);
                        }

                        /*Load sender name field*/
                        if ($('#form_sender_name_field').length > 0) {
                            var attr3 = '';
                            if (field_cid == sendernamefield || (field_id && field_id == sendernamefield)) {
                                attr3 = 'selected="selected"';
                            }
                            if (field_id) {
                                var fieldHtml3 = '<option class="loffields" ' + attr3 + ' value="' + field_id + '">' + field['attributes']['label'] + '</option>';
                            } else {
                                var fieldHtml3 = '<option class="loffields" ' + attr3 + ' value="' + field_cid + '">' + field['attributes']['label'] + '</option>';
                            }
                            $('#form_sender_name_field').append(fieldHtml3);
                        }
                    }
                }
                /** Load Thankyou Field */
                return this.bindSaveEvent();
            };

            BuilderView.prototype.bindSaveEvent = function () {
                var _this = this;
                this.formSaved = true;
                this.saveFormButton = this.$el.find(".js-save-form");
                this.saveFormButton.attr('disabled', true).text(Formbuilder.options.dict.ALL_CHANGES_SAVED);
                if (!!Formbuilder.options.AUTOSAVE) {
                    setInterval(function () {
                        return _this.saveForm.call(_this);
                    }, 500);
                }
                return jQuery(window).bind('beforeunload', function () {
                    if (_this.formSaved) {
                        return void 0;
                    } else {
                        return Formbuilder.options.dict.UNSAVED_CHANGES;
                    }
                });
            };

            BuilderView.prototype.reset = function () {
                this.$responseFields.html('');
                return this.addAll();
            };

            BuilderView.prototype.render = function () {
                var subview, _i, _len, _ref5;
                this.$el.html(Formbuilder.templates['page']());
                this.$fbLeft = this.$el.find('.fb-left');
                this.$responseFields = this.$el.find('.fb-response-fields');
                this.bindWindowScrollEvent();
                this.hideShowNoResponseFields();
                _ref5 = this.SUBVIEWS;
                for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
                    subview = _ref5[_i];
                    new subview({
                        parentView: this
                    }).render();
                }
                return this;
            };

            BuilderView.prototype.bindWindowScrollEvent = function () { };

            BuilderView.prototype.showTab = function (e) {
                var $el, first_model, target;
                $el = jQuery(e.currentTarget);
                target = $el.data('target');
                $el.closest('li').addClass('active').siblings('li').removeClass('active');
                jQuery(target).addClass('active').siblings('.fb-tab-pane').removeClass('active');
                if (target !== '#editField') {
                    this.unlockLeftWrapper();
                }
                if (target === '#editField' && !this.editView && (first_model = this.collection.models[0])) {
                    currentModel = first_model;
                    return this.createAndShowEditView(first_model);
                }
            };

            BuilderView.prototype.addOne = function (responseField, _, options) {
                var $replacePosition, view;
                view = new ViewFieldView({
                    model: responseField,
                    parentView: this
                });
                if (options.$replaceEl != null) {
                    return options.$replaceEl.replaceWith(view.render().el);
                } else if ((options.position == null) || options.position === -1) {
                    return this.$responseFields.append(view.render().el);
                } else if (options.position === 0) {
                    return this.$responseFields.prepend(view.render().el);
                } else if (($replacePosition = this.$responseFields.find(".fb-field-wrapper").eq(options.position))[0]) {
                    return $replacePosition.before(view.render().el);
                } else {
                    return this.$responseFields.append(view.render().el);
                }
            };

            BuilderView.prototype.setSortable = function () {
                var _this = this;
                if (this.$responseFields.hasClass('ui-sortable')) {
                    this.$responseFields.sortable('destroy');
                }
                this.$responseFields.sortable({
                    forcePlaceholderSize: true,
                    placeholder: 'sortable-placeholder',
                    stop: function (e, ui) {
                        var rf;
                        if (ui.item.data('field-type')) {
                            rf = _this.collection.create(Formbuilder.helpers.defaultFieldAttrs(ui.item.data('field-type')), {
                                $replaceEl: ui.item
                            });
                            _this.createAndShowEditView(rf);
                        }
                        _this.handleFormUpdate();
                        return true;
                    },
                    update: function (e, ui) {
                        if (!ui.item.data('field-type')) {
                            return _this.ensureEditViewScrolled();
                        }
                    }
                });
                return this.setDraggable();
            };

            BuilderView.prototype.setDraggable = function () {
                var $addFieldButtons,
                    _this = this;
                $addFieldButtons = this.$el.find("[data-field-type]");
                return $addFieldButtons.draggable({
                    connectToSortable: this.$responseFields,
                    helper: function () {
                        var $helper;
                        $helper = jQuery("<div data-field-type='" + $(this).data('field-type') + "' class='response-field-draggable-helper' />");
                        $helper.css({
                            width: _this.$responseFields.width(),
                            height: '80px'
                        });
                        return $helper;
                    }
                });
            };

            BuilderView.prototype.addAll = function () {
                this.collection.each(this.addOne, this);
                return this.setSortable();
            };

            BuilderView.prototype.hideShowNoResponseFields = function () {
                return this.$el.find(".fb-no-response-fields")[this.collection.length > 0 ? 'hide' : 'show']();
            };

            BuilderView.prototype.addField = function (e) {
                var field_type;
                field_type = jQuery(e.currentTarget).data('field-type');
                return this.createField(Formbuilder.helpers.defaultFieldAttrs(field_type));
            };

            BuilderView.prototype.createField = function (attrs, options) {
                var rf;
                rf = this.collection.create(attrs, options);
                this.createAndShowEditView(rf);
                return this.handleFormUpdate();
            };

            BuilderView.prototype.createAndShowEditView = function (model) {
                var $newEditEl, $responseFieldEl;
                $responseFieldEl = this.$el.find(".fb-field-wrapper").filter(function () {
                    return jQuery(this).data('cid') === model.cid;
                });
                $responseFieldEl.addClass('editing').siblings('.fb-field-wrapper').removeClass('editing');
                if (this.editView) {
                    currentModel = model;
                    if (this.editView.model.cid === model.cid) {
                        this.$el.find(".fb-tabs a[data-target=\"#editField\"]").click();
                        this.scrollLeftWrapper($responseFieldEl);
                        return;
                    }
                    this.editView.remove();
                }
                this.editView = new EditFieldView({
                    model: model,
                    parentView: this
                });
                $newEditEl = this.editView.render().$el;
                this.$el.find(".fb-edit-field-wrapper").html($newEditEl);
                this.$el.find(".fb-tabs a[data-target=\"#editField\"]").click();
                this.scrollLeftWrapper($responseFieldEl);
                return this;
            };

            BuilderView.prototype.ensureEditViewScrolled = function () {
                if (!this.editView) {
                    return;
                }
                return this.scrollLeftWrapper(jQuery(".fb-field-wrapper.editing"));
            };

            BuilderView.prototype.scrollLeftWrapper = function ($responseFieldEl) {
                var _this = this;
                this.unlockLeftWrapper();
                if (!$responseFieldEl[0]) {
                    return;
                }
                return jQuery.scrollWindowTo((this.$el.offset().top + $responseFieldEl.offset().top) - this.$responseFields.offset().top, 200, function () {
                    return _this.lockLeftWrapper();
                });
            };

            BuilderView.prototype.lockLeftWrapper = function () {
                return this.$fbLeft.data('locked', true);
            };

            BuilderView.prototype.unlockLeftWrapper = function () {
                return this.$fbLeft.data('locked', false);
            };

            var thankyoufield = $('#form_thankyou_field').data("value");
            $('#form_thankyou_field').change(function (event) {
                thankyoufield = $(this).val();
            });

            if ($('#form_sender_email_field').length > 0) {
                var senderemailfield = $('#form_sender_email_field').data("value");
                $('#form_sender_email_field').change(function (event) {
                    senderemailfield = $(this).val();
                });
            }

            if ($('#form_sender_name_field').length > 0) {
                var sendernamefield = $('#form_sender_name_field').data("value");
                $('#form_sender_name_field').change(function (event) {
                    sendernamefield = $(this).val();
                });
            }

            BuilderView.prototype.handleFormUpdate = function () {
                /** Load Thankyou Field */
                var models = this.collection.models;
                $('#form_thankyou_field .loffields').remove();
                $('#form_sender_email_field .loffields').remove();
                $('#form_sender_name_field .loffields').remove();
                for (var i = 0; i < models.length; i++) {
                    var field = models[i];
                    if (field['attributes']['field_type'] == "text" || field['attributes']['field_type'] == "email") {
                        var attr = '';
                        var field_cid = field['attributes']['cid'];
                        var field_id = typeof (field['attributes']['field_id']) !== "undefined" ? field['attributes']['field_id'] : '';
                        if (field_cid == thankyoufield || (field_id && field_id == thankyoufield)) {
                            attr = 'selected="selected"';
                        }
                        if (field_id) {
                            var fieldHtml = '<option class="loffields" ' + attr + ' value="' + field_id + '">' + field['attributes']['label'] + '</option>';
                        } else {
                            var fieldHtml = '<option class="loffields" ' + attr + ' value="' + field_cid + '">' + field['attributes']['label'] + '</option>';
                        }


                        $('#form_thankyou_field').append(fieldHtml);

                        /*load sender email field*/
                        if ($('#form_sender_email_field').length > 0) {
                            var attr2 = '';
                            if (field_cid == senderemailfield || (field_id && field_id == senderemailfield)) {
                                attr2 = 'selected="selected"';
                            }
                            if (field_id) {
                                var fieldHtml2 = '<option class="loffields" ' + attr2 + ' value="' + field_id + '">' + field['attributes']['label'] + '</option>';
                            } else {
                                var fieldHtml2 = '<option class="loffields" ' + attr2 + ' value="' + field_cid + '">' + field['attributes']['label'] + '</option>';
                            }


                            $('#form_sender_email_field').append(fieldHtml2);
                        }

                        /*load sender name field*/
                        if ($('#form_sender_name_field').length > 0) {
                            var attr3 = '';
                            if (field_cid == sendernamefield || (field_id && field_id == sendernamefield)) {
                                attr3 = 'selected="selected"';
                            }
                            if (field_id) {
                                var fieldHtml3 = '<option class="loffields" ' + attr3 + ' value="' + field_id + '">' + field['attributes']['label'] + '</option>';
                            } else {
                                var fieldHtml3 = '<option class="loffields" ' + attr3 + ' value="' + field_cid + '">' + field['attributes']['label'] + '</option>';
                            }


                            $('#form_sender_name_field').append(fieldHtml3);
                        }
                    }
                }
                /** Load Thankyou Field */

                if (this.updatingBatch) {
                    return;
                }
                this.formSaved = false;
                return this.saveFormButton.removeAttr('disabled').text(Formbuilder.options.dict.SAVE_FORM);
            };

            BuilderView.prototype.saveForm = function (e) {
                var payload;
                if (this.formSaved) {
                    return;
                }
                this.formSaved = true;
                this.saveFormButton.attr('disabled', true).text(Formbuilder.options.dict.ALL_CHANGES_SAVED);
                this.collection.sort();
                payload = JSON.stringify({
                    fields: this.collection.toJSON()
                });
                if (Formbuilder.options.HTTP_ENDPOINT) {
                    this.doAjaxSave(payload);
                }
                return this.formBuilder.trigger('save', payload);
            };

            BuilderView.prototype.doAjaxSave = function (payload) {
                var _this = this;
                return jQuery.ajax({
                    url: Formbuilder.options.HTTP_ENDPOINT,
                    type: Formbuilder.options.HTTP_METHOD,
                    data: payload,
                    contentType: "application/json",
                    success: function (data) {
                        var datum, _i, _len, _ref5;
                        _this.updatingBatch = true;
                        for (_i = 0, _len = data.length; _i < _len; _i++) {
                            datum = data[_i];
                            if ((_ref5 = _this.collection.get(datum.cid)) != null) {
                                _ref5.set({
                                    id: datum.id
                                });
                            }
                            _this.collection.trigger('sync');
                        }
                        return _this.updatingBatch = void 0;
                    }
                });
            };

            return BuilderView;

        })(Backbone.View);

        Formbuilder = (function () {
            Formbuilder.helpers = {
                defaultFieldAttrs: function (field_type) {
                    var attrs, _base;
                    attrs = {};
                    attrs[Formbuilder.options.mappings.LABEL] = 'Untitled';
                    attrs[Formbuilder.options.mappings.FIELD_TYPE] = field_type;
                    attrs[Formbuilder.options.mappings.REQUIRED] = true;
                    attrs[Formbuilder.options.mappings.SHOW_IN_EMAIL] = true;
                    attrs['field_options'] = {};
                    return (typeof (_base = Formbuilder.fields[field_type]).defaultAttributes === "function" ? _base.defaultAttributes(attrs) : void 0) || attrs;
                },
                simple_format: function (x) {
                    return x != null ? x.replace(/\n/g, '<br />') : void 0;
                }
            };

            Formbuilder.options = {
                BUTTON_CLASS: 'fb-button',
                HTTP_ENDPOINT: '',
                HTTP_METHOD: 'POST',
                AUTOSAVE: true,
                CLEAR_FIELD_CONFIRM: false,
                mappings: {
                    FIELDCOL: 'fieldcol',
                    WRAPPERCOL: 'wrappercol',
                    FIELD_HEIGHT: 'fieldheight',
                    SIZE: 'field_options.size',
                    UNITS: 'field_options.units',
                    SHOW_BREAKLINE: 'show_breakline',
                    LABEL: 'label',
                    CSS_CLASS: 'css_class',
                    PLACEHOLDER: 'placeholder',
                    DEFAULT_VALUE: 'default_value',
                    IMAGE_URL: 'image_url',
                    INLINE_CSS: 'inline_css',
                    IS_HIDDEN_FIELD: 'is_hidden_field',
                    GRID_COLUMN: 'grid_column',
                    IS_READONLY: 'is_readonly',
                    CUSTOM_TEMPLATE: 'custom_template',
                    DATE_VALIDATION_YEAR: 'date_validation_year',
                    DATE_VALIDATION_TO: 'date_validation_to',
                    FIELD_TYPE: 'field_type',
                    CATE_ID: 'field_options.category_id',
                    SHOW_POS: 'show_position',
                    MAX_LEVEL: 'max_level',
                    SHOW_HEADING: 'show_heading',
                    SHOW_MAP_LOCATION: 'show_location',
                    SHOW_MAP_RADIUS: 'show_radius',
                    SHOW_CITY: 'show_city',
                    SHOW_STATE: 'show_state',
                    SHOW_ZIPCODE: 'show_zipcode',
                    SHOW_ADDRESS: 'show_address',
                    SHOW_COUNTRY: 'show_country',
                    ZIPCODE_PLACEHOLDER: 'zipcode_placeholder',
                    ADDRESS_PLACEHOLDER: 'address_placeholder',
                    CITY_PLACEHOLDER: 'city_placeholder',
                    STATE_PLACEHOLDER: 'state_placeholder',
                    COUNTRY_PLACEHOLDER: 'country_placeholder',
                    SHOW_FILE_LIST: 'show_file_list',
                    LIMIT: 'limit',
                    DEFAULT: 'default',
                    WIDTH: 'width',
                    HEIGHT: 'height',
                    RADIUS: 'radius',
                    CODE_HTML: 'code_html',
                    DEFAULT_ADDRESS: 'address',
                    DEFAULT_LAT: 'default_lat',
                    DEFAULT_LONG: 'default_long',
                    REQUIRED: 'required',
                    SHOW_IN_EMAIL: 'show_in_email',
                    ADMIN_ONLY: 'admin_only',
                    DRAGDROPTEXT: 'drag_drop_text',
                    OPENBROWSERTEXT: 'open_browser_text',
                    OPTIONS: 'field_options.options',
                    DESCRIPTION: 'field_options.description',
                    INCLUDE_OTHER: 'field_options.include_other_option',
                    INCLUDE_BLANK: 'field_options.include_blank_option',
                    INTEGER_ONLY: 'field_options.integer_only',
                    MIN: 'field_options.min',
                    MAX: 'field_options.max',
                    MINLENGTH: 'field_options.minlength',
                    MAXLENGTH: 'field_options.maxlength',
                    LENGTH_UNITS: 'field_options.min_max_length_units',
                    VALIDATION: 'field_options.validation',
                    IMAGE_TYPE: 'image_type',
                    IMAGE_MAXIMUM_SIZE: 'image_maximum_size',
                    IS_CHECKED: 'field_options.is_checked',
                    COLOR_LABEL: 'field_options.color_label',
                    COLOR_TEXT: 'field_options.color_text',
                    COLOR_DESCRIPTION: 'field_options.color_description',
                    BACKGROUND_COLOR: 'field_options.background_color',
                    FIELD_ID: 'field_id',
                    BORDER_RADIUS: 'field_options.border_radius',
                    BORDER_STYLE: 'field_options.border_style',
                    BORDER_WIDTH: 'field_options.border_width',
                    BORDER_COLOR: 'field_options.border_color',

                    FONT_STYLE: 'field_options.font_style',
                    FONT_SIZE: 'field_options.font_size',
                    FONT_WEIGHT: 'field_options.font_weight',
                    FILE_NUMBER_LIMIT: 'file_number_limit',
                    ICON: 'field_options.icon',
                    ICON_COLOR: 'field_options.icon_color',

                    PRODUCT_SKU: 'product_sku',
                    SHOW_MAP: 'show_map',
                    LOCATION_LABEL: 'location_label',
                    ESIGNATURE_SHOW_CONTROL: 'esignature_show_control'
                },
                dict: {
                    ALL_CHANGES_SAVED: 'All changes saved',
                    SAVE_FORM: 'Save form',
                    UNSAVED_CHANGES: 'You have unsaved changes. If you leave this page, you will lose those changes!'
                }
            };

            Formbuilder.fields = {};

            Formbuilder.inputFields = {};

            Formbuilder.nonInputFields = {};

            Formbuilder.registerField = function (name, opts) {
                var x, _i, _len, _ref5;
                _ref5 = ['view', 'edit'];
                for (_i = 0, _len = _ref5.length; _i < _len; _i++) {
                    x = _ref5[_i];
                    opts[x] = _.template(opts[x]);
                }
                opts.field_type = name;
                Formbuilder.fields[name] = opts;
                if (opts.type === 'non_input') {
                    return Formbuilder.nonInputFields[name] = opts;
                } else {
                    return Formbuilder.inputFields[name] = opts;
                }
            };

            function Formbuilder(opts) {
                var args;
                if (opts == null) {
                    opts = {};
                }
                _.extend(this, Backbone.Events);
                args = _.extend(opts, {
                    formBuilder: this
                });
                this.mainView = new BuilderView(args);
            }

            return Formbuilder;

        })();

        window.Formbuilder = Formbuilder;

        if (typeof module !== "undefined" && module !== null) {
            module.exports = Formbuilder;
        } else {
            window.Formbuilder = Formbuilder;
        }

    }).call(this);


    (function () {
        Formbuilder.registerField('address', {
            order: 50,
            view: "<div class='input-line'>\n  <span class='street'>\n    <input type='text' />\n    <label>Address</label>\n  </span>\n</div>\n\n<div class='input-line'>\n  <span class='city'>\n    <input type='text' />\n    <label>City</label>\n  </span>\n\n  <span class='state'>\n    <input type='text' />\n    <label>State / Province / Region</label>\n  </span>\n</div>\n\n<div class='input-line'>\n  <span class='zip'>\n    <input type='text' />\n    <label>Zipcode</label>\n  </span>\n\n  <span class='country'>\n    <select><option>United States</option></select>\n    <label>Country</label>\n  </span>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/address']({ includeOther: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-home\"></span></span> Address",
            defaultAttributes: function (attrs) {
                attrs.show_address = 1;
                attrs.show_city = 1;
                attrs.show_state = 1;
                attrs.show_zipcode = 1;
                attrs.show_country = 1;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                attrs.address_placeholder = '';
                attrs.city_placeholder = '';
                attrs.state_placeholder = '';
                attrs.zipcode_placeholder = '';
                attrs.country_placeholder = '';
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('model_dropdown', {
            order: 105,
            view: "<div class='inline-group'>\n<table class='workflow_table'>\n<tbody>\n<tr><th>Pos</th><th>Model 1</th><th>Model 2</th></tr>\n<tr><td style='width:25px'><b>1.</b></td><td>\n<select style='margin-right: 15px;'>\n<option value=''>Select Model 1</option>\n</select>\n</td><td>\n<select>\n<option value=''>\nSelect Model 2\n</option>\n</select>\n</td>\n</tbody>\n</table>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/model_dropdown']({ includeBlank: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-caret-down'></span></span> Model Dropdowns",
            defaultAttributes: function (attrs) {
                attrs.max_level = 2;
                attrs.show_position = 1;

                attrs.field_options.category_id = [
                    {
                        value: "",
                        label: "",
                        checked: false
                    }, {
                        value: "",
                        label: "",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });
    }).call(this);


    (function () {
        Formbuilder.registerField('checkboxes', {
            order: 10,
            view: "<% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n <% if(typeof (rf.get(Formbuilder.options.mappings.OPTIONS)[i].label) != 'undefined') { %>\n<div>\n    <label class='fb-option' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;'>\n      <input type='checkbox' <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'checked' %> onclick=\"javascript: return false;\" />\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </label>\n  </div>\n<% } %>\n<% } %>\n\n<% if (rf.get(Formbuilder.options.mappings.INCLUDE_OTHER)) { %>\n  <div class='other-option'>\n    <label class='fb-option' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;'>\n      <input type='checkbox' />\n      Other\n    </label>\n\n    <input type='text' />\n  </div>\n<% } %>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/color']() %>\n\n<%= Formbuilder.templates['edit/options']({ includeOther: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-square-o\"></span></span> Checkboxes",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "",
                        checked: false
                    }, {
                        label: "",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('product_field', {
            order: 10,
            type: "non_input",
            view: "",
            edit: "<div class='lof-fieldset'><div class='fb-edit-section-header'>Label</div> <div class='fb-edit-section-content'><input style='width: 100%' type='text' data-rv-input='model.<%= Formbuilder.options.mappings.LABEL %>' /></div><div class='edit-row'><label>Field ID</label><input type='text' data-rv-input='model.<%= Formbuilder.options.mappings.FIELD_ID %>' placeholder='<%= rf.cid %>'/><span class='help'>input custom field Id which will use on field name of the form on frontend. The field name format = prefix + field id + form id. Empty to get default id=<strong><%= rf.cid %></strong></span></div><%= Formbuilder.templates['edit/label_color_font']() %></div>\n<div class='fb-common-checkboxes'><label><input type='checkbox' data-rv-checked='model.<%= Formbuilder.options.mappings.IS_CHECKED %>' value='1'>Is Checked?</label></div><%= Formbuilder.templates['edit/productsku']() %><%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-square-o\"></span></span> Product Checkbox",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [];
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                attrs.required = true;
                attrs.show_in_email = true;
                return attrs;
            }
        });

    }).call(this);


    (function () {
        Formbuilder.registerField('subscription', {
            order: 60,
            view: "<% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n <% if(typeof (rf.get(Formbuilder.options.mappings.OPTIONS)[i].label) != 'undefined') { %>\n<div style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>;'>\n    <label class='fb-option' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;'>\n      <input type='checkbox' <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'checked' %> onclick=\"javascript: return false;\" />\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </label>\n  </div>\n<% } %>\n<% } %>\n\n<% if (rf.get(Formbuilder.options.mappings.INCLUDE_OTHER)) { %>\n  <div class='other-option'>\n    <label class='fb-option'>\n      <input type='checkbox' />\n      Other\n    </label>\n\n    <input type='text' />\n  </div>\n<% } %>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/color']() %><%= Formbuilder.templates['edit/subscription']({ includeOther: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-square-o\"></span></span> Subscription",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "Sign Up for Newsletter",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('rating', {
            order: 55,
            view: "<div class='inline-group'>\n<div class='rating small' data-role='rating' data-size='small' data-value='<%= rf.get(Formbuilder.options.mappings.DEFAULT) %>'>\n<% for (i=1; i<= (rf.get(Formbuilder.options.mappings.LIMIT) || 5); i++) { %>\n<span class='star <% if(i <= (rf.get(Formbuilder.options.mappings.DEFAULT) || 0)) { %>on<% } %>'></span><% } %>\n<span class='score'>Rating: <%= rf.get(Formbuilder.options.mappings.DEFAULT) %> stars</span>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/rating']({ includeBlank: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-star'></span></span> Rating",
            defaultAttributes: function (attrs) {
                attrs.limit = 5;
                attrs.default = 0;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });
    }).call(this);

    (function () {
        Formbuilder.registerField('google_map', {
            order: 70,
            view: "<div class='inline-group'>\n<div class='img-google-map'>&nbsp;</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/google_map']({ includeBlank: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-map-marker'></span></span> Google Map",
            defaultAttributes: function (attrs) {
                attrs.width = 550;
                attrs.height = 400;
                attrs.radius = 300;
                attrs.address = "";
                attrs.default_lat = 21.0199438;
                attrs.default_long = 105.81731119999995;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                attrs.show_location = 1;
                attrs.show_radius = 1;
                attrs.show_map = 1;
                attrs.location_label = "Location:";
                return attrs;
            }
        });
    }).call(this);

    (function () {
        Formbuilder.registerField('date', {
            order: 20,
            view: "<div class='input-line'>\n<input type='text' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; ' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' /><i class='fa <%= rf.get(Formbuilder.options.mappings.ICON) %>' style='color:<%= rf.get(Formbuilder.options.mappings.ICON_COLOR) %>;'></i></div>",
            edit: "<%= Formbuilder.templates['edit/dateValidation']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/icon']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-calendar\"></span></span> Date",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('dropdown', {
            order: 24,
            view: "<select style='border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; ' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>'>\n  <% if (rf.get(Formbuilder.options.mappings.INCLUDE_BLANK)) { %>\n    <option value=''></option>\n  <% } %>\n\n  <% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n    <option <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'selected' %>>\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </option>\n  <% } %>\n</select>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/options']({ includeBlank: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-caret-down\"></span></span> Dropdown",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "",
                        checked: false
                    }, {
                        label: "",
                        checked: false
                    }
                ];
                attrs.field_options.include_blank_option = false;
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('email', {
            order: 40,
            view: "<div class='input-text'><input type='text' placeholder='<%= rf.get(Formbuilder.options.mappings.PLACEHOLDER) %>' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; ' /><i class='fa <%= rf.get(Formbuilder.options.mappings.ICON) %>' style='color:<%= rf.get(Formbuilder.options.mappings.ICON_COLOR) %>;'></i></div>",
            edit: "<%= Formbuilder.templates['edit/placeholder']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/icon']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/extraoptions']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-envelope-o\"></span></span> Email",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {


    }).call(this);

    (function () {
        Formbuilder.registerField('number', {
            order: 30,
            view: "<div class='input-text'><input type='text' placeholder='<%= rf.get(Formbuilder.options.mappings.PLACEHOLDER) %>' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; ' />\n<i class='fa <%= rf.get(Formbuilder.options.mappings.ICON) %>' style='color:<%= rf.get(Formbuilder.options.mappings.ICON_COLOR) %>;'></i>\n<% if (units = rf.get(Formbuilder.options.mappings.UNITS)) { %>\n  <%= units %>\n<% } %></div>",
            edit: "<%= Formbuilder.templates['edit/placeholder']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/icon']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/min_max']() %>\n<%= Formbuilder.templates['edit/units']() %>\n<%= Formbuilder.templates['edit/integer_only']() %>\n<%= Formbuilder.templates['edit/extraoptions']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-number\">123</span></span> Number",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('paragraph', {
            order: 5,
            view: "<textarea placeholder='<%= rf.get(Formbuilder.options.mappings.PLACEHOLDER) %>' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; '></textarea>",
            edit: "<%= Formbuilder.templates['edit/placeholder']() %>\n<%= Formbuilder.templates['edit/bootstrapcoltextarea']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/min_max_length']() %>\n<%= Formbuilder.templates['edit/validation']() %>\n<%= Formbuilder.templates['edit/extraoptions']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\">&#182;</span> Paragraph",
            defaultAttributes: function (attrs) {
                attrs.field_options.size = 'small';
                attrs.fieldcol = 12;
                attrs.fieldheight = '200px';
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('price', {
            order: 45,
            view: "<div class='input-line'>\n  <span class='above-line' style='width: 5%'>$</span>\n  <span class='dolars'>\n    <input type='text' placeholder='<%= rf.get(Formbuilder.options.mappings.PLACEHOLDER) %>' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; '/><i class='fa <%= rf.get(Formbuilder.options.mappings.ICON) %>' style='color:<%= rf.get(Formbuilder.options.mappings.ICON_COLOR) %>;'></i></span></div>",
            edit: "<%= Formbuilder.templates['edit/placeholder']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/icon']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/extraoptions']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-usd\"></span></span> Price",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('radio', {
            order: 15,
            view: "<% for (i in (rf.get(Formbuilder.options.mappings.OPTIONS) || [])) { %>\n<% if(typeof(rf.get(Formbuilder.options.mappings.OPTIONS)[i].label) != 'undefined') { %>\n <div>\n    <label class='fb-option' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;' >\n      <input type='radio' <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].checked && 'checked' %> onclick=\"javascript: return false;\" />\n      <%= rf.get(Formbuilder.options.mappings.OPTIONS)[i].label %>\n    </label>\n  </div>\n<% } %>\n<% } %>\n\n<% if (rf.get(Formbuilder.options.mappings.INCLUDE_OTHER)) { %>\n  <div class='other-option'>\n    <label class='fb-option' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;'>\n      <input type='radio' />\n      Other\n    </label>\n\n    <input type='text' />\n  </div>\n<% } %>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/options']({ includeOther: true }) %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-circle-o\"></span></span> Multiple Choice",
            defaultAttributes: function (attrs) {
                attrs.field_options.options = [
                    {
                        label: "",
                        checked: false
                    }, {
                        label: "",
                        checked: false
                    }
                ];
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('section_break', {
            order: 0,
            type: 'non_input',
            view: "",
            edit: "<div class='lof-fieldset'><div class='fb-edit-section-header'>Label</div> <div class='fb-edit-section-content'><input style='width: 100%' type='text' data-rv-input='model.<%= Formbuilder.options.mappings.LABEL %>' /><%= Formbuilder.templates['edit/showbreakline']() %></div></div><%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-minus'></span></span> Section Break",
            defaultAttributes: function (attrs) {
                attrs.show_breakline = 1;
                attrs.wrappercol = 12;
                return attrs;
            }
        });

        (function () {
            Formbuilder.registerField('html', {
                order: 0,
                type: 'non_input',
                view: "",
                edit: "<div class='lof-fieldset'><div class='fb-edit-section-header'>Label</div> <div class='fb-edit-section-content'><input type='text' data-rv-input='model.<%= Formbuilder.options.mappings.LABEL %>' />\n <label>Description:</label><textarea style='width: 100%' data-rv-input='model.<%= Formbuilder.options.mappings.DESCRIPTION %>' /></div></div><%= Formbuilder.templates['edit/color']() %>\n<%=  Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
                addButton: "<span class='symbol'><span class='fa fa-html5'></span></span> Html",
                defaultAttributes: function (attrs) {
                    attrs.field_options.size = 'small';
                    attrs.fieldcol = 12;
                    attrs.wrappercol = 12;
                    return attrs;
                },

            });
        }).call(this);

        (function () {

            Formbuilder.registerField('image', {
                order: 0,
                type: 'non_input',
                view: "<img src='<%= rf.get(Formbuilder.options.mappings.IMAGE_URL) %>' alt='Preview Image' class='field-img-preview'/>",
                edit: "<div hidden class='edit-row'><label>Field ID</label><input type='text' class='custom-field-image-id' data-rv-input='model.<%= Formbuilder.options.mappings.FIELD_ID %>' id='<%= rf.cid %>' placeholder='<%= rf.cid %>'/><span class='help'> id=<strong><%= rf.cid %></strong></span></div>\n<%= Formbuilder.templates['edit/image']() %>\n</div></div><%= Formbuilder.templates['edit/color']() %>\n<%=  Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
                addButton: "<span class='symbol'><span class='fa fa-image'></span></span> Preview Image",
                defaultAttributes: function (attrs) {
                    attrs.field_options.size = 'small';
                    attrs.fieldcol = 12;
                    attrs.wrappercol = 12;
                    attrs.label = "";
                    return attrs;
                },

            });
        }).call(this);

    }).call(this);

    (function () {
        Formbuilder.registerField('file_upload', {
            order: 40,
            type: 'non_input',
            view: "<input type='file' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;' />",
            edit: "<%= Formbuilder.templates['edit/file_upload']() %>\n<%=  Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-upload'></span></span> File Upload",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.image_type = 'jpg,png,jpeg,gif';
                attrs.image_maximum_size = 1;
                return attrs;
            }
        });
    }).call(this);

    (function () {
        Formbuilder.registerField('multifile_upload', {
            order: 42,
            type: 'non_input',
            view: "<div class='fbd-files-uploader dm-uploader p-5' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>'><h3 class='mb-5 mt-5 text-muted' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;'><%= rf.get(Formbuilder.options.mappings.DRAGDROPTEXT) %></h3><div class='btn btn-primary btn-block mb-5'><span><%= rf.get(Formbuilder.options.mappings.OPENBROWSERTEXT) %></span></div></div>",
            edit: "<%= Formbuilder.templates['edit/multifile_upload']() %>\n<%=  Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-upload'></span></span> Multi Files Upload",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.image_type = 'jpg,png,jpeg,gif';
                attrs.drag_drop_text = 'Drag &amp; drop files here';
                attrs.image_maximum_size = 1;
                attrs.open_browser_text = 'Open the file Browser';
                attrs.file_number_limit = 1;
                return attrs;
            }
        });
    }).call(this);

    (function () {
        Formbuilder.registerField('text', {
            order: 0,
            view: "<div class='input-text'><input type='text' placeholder='<%= rf.get(Formbuilder.options.mappings.PLACEHOLDER) %>'  class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; ' /><i class='fa <%= rf.get(Formbuilder.options.mappings.ICON) %>' style='color:<%= rf.get(Formbuilder.options.mappings.ICON_COLOR) %>;'></i></div>",
            edit: "<%= Formbuilder.templates['edit/placeholder']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/icon']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/min_max_length']() %>\n<%= Formbuilder.templates['edit/validation']() %>\n<%= Formbuilder.templates['edit/extraoptions']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-font'></span></span> Text",
            defaultAttributes: function (attrs) {
                attrs.field_options.size = 'small';
                attrs.fieldcol = 12;
                attrs.wrappercol = 12;
                return attrs;
            },

        });

    }).call(this);

    (function () {
        Formbuilder.registerField('phone', {
            order: 0,
            view: "<div class='input-text input-phone'><input type='tel' placeholder='<%= rf.get(Formbuilder.options.mappings.PLACEHOLDER) %>'  class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; ' /><i class='fa <%= rf.get(Formbuilder.options.mappings.ICON) %>' style='color:<%= rf.get(Formbuilder.options.mappings.ICON_COLOR) %>;'></i></div>",
            edit: "<%= Formbuilder.templates['edit/placeholder']() %>\n<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/icon']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/min_max_length']() %>\n<%= Formbuilder.templates['edit/validation']() %>\n<%= Formbuilder.templates['edit/extraoptions']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class='symbol'><span class='fa fa-phone'></span></span> Phone",
            defaultAttributes: function (attrs) {
                attrs.field_options.size = 'small';
                attrs.fieldcol = 12;
                attrs.wrappercol = 12;
                return attrs;
            },

        });

    }).call(this);



    (function () {
        Formbuilder.registerField('time', {
            order: 25,
            view: "<div class='input-line' style='color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>;'>\n  <span class='hours'>\n    <input type=\"text\" />\n    <label>HH</label>\n  </span>\n\n  <span class='above-line'>:</span>\n\n  <span class='minutes'>\n    <input type=\"text\" />\n    <label>MM</label>\n  </span>\n\n  <span class='above-line'>:</span>\n\n  <span class='seconds'>\n    <input type=\"text\" />\n    <label>SS</label>\n  </span>\n\n  <span class='am_pm'>\n    <select>\n      <option>AM</option>\n      <option>PM</option>\n    </select>\n  </span>\n</div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapwrappercol']() %>\n<%= Formbuilder.templates['edit/color']() %>\n<%= Formbuilder.templates['edit/extraoptions1']() %>\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-clock-o\"></span></span> Time",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('website', {
            order: 35,
            view: "<div class='input-text'><input type='text' placeholder='http://' class='col-sm-<%= rf.get(Formbuilder.options.mappings.FIELDCOL) %>' style='background-color:<%= rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR) %>; border-width:<%= rf.get(Formbuilder.options.mappings.BORDER_WIDTH) %>px; border-style:<%= rf.get(Formbuilder.options.mappings.BORDER_STYLE) %>; border-radius:<%= rf.get(Formbuilder.options.mappings.BORDER_RADIUS) %>px; border-color:<%= rf.get(Formbuilder.options.mappings.BORDER_COLOR) %>; color:<%= rf.get(Formbuilder.options.mappings.COLOR_TEXT) %>; '  /><i class='fa <%= rf.get(Formbuilder.options.mappings.ICON) %>' style='color:<%= rf.get(Formbuilder.options.mappings.ICON_COLOR) %>;'></i></div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/icon']() %>\n<%= Formbuilder.templates['edit/border']() %>\n<%= Formbuilder.templates['edit/color']() %>\n\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-link\"></span></span> Website",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                return attrs;
            }
        });

    }).call(this);

    (function () {
        Formbuilder.registerField('digital_signature', {
            order: 40,
            view: "<div class='input-text'><div class='img-digital-signature'>&nbsp;</div></div>",
            edit: "<%= Formbuilder.templates['edit/bootstrapcol']() %>\n<%= Formbuilder.templates['edit/esignature']() %>\n\n<%= Formbuilder.templates['edit/customtemplate']() %>",
            addButton: "<span class=\"symbol\"><span class=\"fa fa-check\"></span></span> Digital Signature",
            defaultAttributes: function (attrs) {
                attrs.wrappercol = 12;
                attrs.fieldcol = 12;
                attrs.color = "#000";
                attrs.esignature_show_control = 0;
                return attrs;
            }
        });

    }).call(this);

    this["Formbuilder"] = this["Formbuilder"] || {};
    this["Formbuilder"]["templates"] = this["Formbuilder"]["templates"] || {};

    this["Formbuilder"]["templates"]["edit/base"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p +=
                ((__t = (Formbuilder.templates['edit/base_header']())) == null ? '' : __t) +
                '\n' +
                ((__t = (Formbuilder.templates['edit/common'](obj))) == null ? '' : __t) +
                '\n' +
                ((__t = (Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].edit({ rf: rf }))) == null ? '' : __t) +
                '\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/base_header"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-field-label\'>\n  <span data-rv-text="model.' +
                ((__t = (Formbuilder.options.mappings.LABEL)) == null ? '' : __t) +
                '"></span>\n  <code class=\'field-type\' data-rv-text=\'model.' +
                ((__t = (Formbuilder.options.mappings.FIELD_TYPE)) == null ? '' : __t) +
                '\'></code>\n  <span class=\'fa fa-arrow-right pull-right\'></span>\n'
            //__p += 'Id: <span id="currentid"> ' + currentModel.cid + ' </span>';
            __p += '</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/base_non_input"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p +=
                ((__t = (Formbuilder.templates['edit/base_header']())) == null ? '' : __t) +
                '\n' +
                ((__t = (Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].edit({ rf: rf }))) == null ? '' : __t) +
                '\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/checkboxes"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.REQUIRED)) == null ? '' : __t) +
                '\' />\n  Required\n</label>\n<!-- label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.ADMIN_ONLY)) == null ? '' : __t) +
                '\' />\n  Admin only\n</label -->';
            __p += '<p><label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_IN_EMAIL)) == null ? '' : __t) +
                '\' />\n  Show In Email\n</label></p>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/fieldheight"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="afield" style="margin-top: 20px;"><span>Field Height</span><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.FIELD_HEIGHT)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/showbreakline"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="afield" style="margin-top: 20px;"><span>Show Breakline</span><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_BREAKLINE)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';
        }
        return __p
    };





    this["Formbuilder"]["templates"]["edit/bootstrapcoltextarea"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'><div class="row"><div class="col-sm-12 fb-edit-section-header">Bootstrap Column</div></div></div>';
            __p += '<div class="fb-edit-section-content" data-role="content">';
            __p += '<div class="row">';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Wrapper Width</div>\n<select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.WRAPPERCOL)) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Field Width</div>\n<select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.FIELDCOL)) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Field Height</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.FIELD_HEIGHT)) == null ? '' : __t) +
                '" style="width: 300px;" />Config css height for field. Example: 100px';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/bootstrapcol"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Bootstrap Column</div>';
            __p += '<div class="fb-edit-section-content row" data-role="content">';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Wrapper Width</div>\n<select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.WRAPPERCOL)) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Field Width</div>\n<select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.FIELDCOL)) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Field Height</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.FIELD_HEIGHT)) == null ? '' : __t) +
                '" style="width: 300px;" />Config css height for field. Example: 100px';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }
        return __p;
    };

    this["Formbuilder"]["templates"]["edit/bootstrapwrappercol"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Bootstrap Column</div>'
            __p += '<div class="fb-edit-section-content row" data-role="content">';
            __p += '<div class="col-sm-6">';
            __p += '<div class=\'fb-edit-section-header\' style="font-size: 13px;border-bottom: 0;margin: 12px 0;font-weight: normal;margin-bottom: 0;">Wrapper Width</div>\n<select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.WRAPPERCOL)) == null ? '' : __t) +
                '">\n  <option value="1">1</option>\n  <option value="2">2</option>\n  <option value="3">3</option>\n <option value="4">4</option>\n<option value="5">5</option>\n<option value="6">6</option>\n<option value="7">7</option>\n<option value="8">8</option>\n<option value="9">9</option>\n<option value="10">10</option>\n<option value="11">11</option>\n<option value="12">12</option>\n</select>\n';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };


    this["Formbuilder"]["templates"]["edit/common"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Label</div>';
            __p += '<div class="fb-edit-section-content">';
            __p += '<div class=\'fb-common-wrapper\'>\n  <div class=\'fb-label-description\'>\n    ' +
                ((__t = (Formbuilder.templates['edit/label_description'](obj))) == null ? '' : __t);
            __p += '\n  </div>\n  <div class=\'fb-common-checkboxes\'>\n    ' +
                ((__t = (Formbuilder.templates['edit/checkboxes']())) == null ? '' : __t) +
                '\n  </div>\n  <div class=\'fb-clear\'></div>\n</div>\n';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/integer_only"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Integer only</div>';
            __p += '<div class="fb-edit-section-content">';
            __p += '<input style="width: 15px" type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.INTEGER_ONLY)) == null ? '' : __t) +
                '\' />\n  Only accept integers\n</label>\n';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/label_description"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<input type=\'text\' data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.LABEL)) == null ? '' : __t) +
                '\' placeholder=\'placeholder\' />';
            __p += '<div class="edit-row"><label>Field ID</label><input type=\'text\' data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.FIELD_ID)) == null ? '' : __t) +
                '\' placeholder=\'' + obj.rf.cid + '\' /><span class="help">input custom field Id which will use on field name of the form on frontend. The field name format = prefix + field id + form id. Empty to get default id=<strong>' + obj.rf.cid + '</strong> </span></div>';
            __p += '<div class="edit-row"><label>Color</label><input class="minicolors color-text" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.COLOR_LABEL)) == null ? '' : __t) +
                '" /></div>';
            __p += ((__t = (Formbuilder.templates['edit/font']())) == null ? '' : __t);
            __p += '<textarea data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.DESCRIPTION)) == null ? '' : __t) +
                '\'\n  placeholder=\'Add a longer description to this field\'></textarea><p>Support Magento 2 variables: <br/><b>Current Customer</b>: {{var customer.name}}, {{var customer.email}}<br/><b>Current Product: </b>{{var product.name}}, {{var product.price}}<br/><b>Current Category: </b>{{var category.name}}<br/><b>Current Store: </b> {{var store.getFrontendName()}}<br/></p>';

        }
        return __p
    };
    this["Formbuilder"]["templates"]["edit/label_color_font"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<input type=\'text\' data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.LABEL)) == null ? '' : __t) +
                '\' placeholder=\'placeholder\' />';
            __p += '<div class="edit-row"><label>Color</label><input class="minicolors color-text" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.COLOR_LABEL)) == null ? '' : __t) +
                '" /></div>';
            __p += ((__t = (Formbuilder.templates['edit/font']())) == null ? '' : __t);

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/min_max"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Minimum / Maximum</div>';
            __p += '<div class="fb-edit-section-content" data-role="content">';
            __p += 'Above\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.MIN)) == null ? '' : __t) +
                '" style="width: 50px" />\n\n&nbsp;&nbsp;\n\nBelow\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.MAX)) == null ? '' : __t) +
                '" style="width: 50px" />\n';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/min_max_length"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Length Limit</div>';
            __p += '<div class="fb-edit-section-content row">';
            __p += '<div class="col-sm-6"><div class="edit-row"><label>Min</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.MINLENGTH)) == null ? '' : __t) +
                '"/></div></div>';
            __p += '<div class="col-sm-6"><div class="edit-row"><label>Max</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.MAXLENGTH)) == null ? '' : __t) +
                '" /></div></div>';
            __p += '<div class="col-sm-12"><div class="edit-row"><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.LENGTH_UNITS)) == null ? '' : __t) +
                '">\n  <option value="characters">characters</option>\n  <option value="words">words</option>\n</select></div></div>';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/color"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Color</div>'
            __p += '<div class="fb-edit-section-content">';

            __p += '<div class="edit-row"><label>Text</label><input class="minicolors color-label" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.COLOR_TEXT)) == null ? '' : __t) +
                '" /></div>';

            __p += '<div class="edit-row"><label>Background</label><input class="minicolors color-label" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.BACKGROUND_COLOR)) == null ? '' : __t) +
                '" /></div>';

            __p + '<div class="edit-row"><label>Description</label><input class="minicolors color-text" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.COLOR_DESCRIPTION)) == null ? '' : __t) +
                '"/></div>';

            __p += '</div>';
            __p += '</div>';

            __p += '<script>' +
                'jQuery(document).ready(function($) {' +
                'jQuery(".fb-edit-field-wrapper .minicolors").each(function() {' +
                'jQuery(this).minicolors({' +
                'control: jQuery(this).attr("data-control") || "hue",' +
                'defaultValue: jQuery(this).attr("data-defaultValue") || "",' +
                'format: jQuery(this).attr("data-format") || "hex",' +
                'keywords: jQuery(this).attr("data-keywords") || "",' +
                'inline: jQuery(this).attr("data-inline") === "true",' +
                'letterCase: jQuery(this).attr("data-letterCase") || "lowercase",' +
                'opacity: jQuery(this).attr("data-opacity"),' +
                'position: jQuery(this).attr("data-position") || "bottom left",' +
                'swatches: jQuery(this).attr("data-swatches") ? jQuery(this).attr("data-swatches").split("|") : [],' +
                'change: function(value, opacity) {' +
                'if( !value ) return;' +
                'if( opacity ) value += ", " + opacity;' +
                'if( typeof console === "object" ) {' +
                'console.log(value);' +
                '}' +
                ' },' +
                'theme: "bootstrap"' +
                '});' +
                '});' +
                '});' +

                '</script>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/image"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        const pathurl = _FORMBUILDER_SELECT_IMAGE_AJAX_URL
        let editorId = ''
        let urlAjax = ''
        var data = jQuery.ajax(
            {
                type: "POST", url: pathurl, dataType: "json", data: { cms: 1 }, async: false
            }).responseJSON;
        editorId = data.target_element_id
        urlAjax = data.url
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Label</div>'
            __p += '<div class="fb-edit-section-content">';

            __p += '<div class="edit-row"><label>Label</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.LABEL)) == null ? '' : __t) +
                '" /></div>';
            __p += '<div class="edit-row"><label>Image Url:</label><input id="' + editorId + '" class="form-image-preview input-field-cms-image" style="width: 100%" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.IMAGE_URL)) == null ? '' : __t) +
                '" /></div>';
            __p += '<div><button title="Insert Image" type="button" class="action-default scalable action-wysiwyg button-open-image-browse"><span>Insert Image</span></button></div>';


            __p += '</div>';
            __p += '</div>';

            __p += '<script>' +
                'jQuery(document).ready(function($) {' +
                'var elements = jQuery(".button-open-image-browse");' +
                'elements.on("click", function () {' +
                `VesMediabrowserUtility.openDialog('${urlAjax}', null, null,"", "")` +
                '});' +
                '});' +
                '</script>';
        }
        return __p
    };



    this["Formbuilder"]["templates"]["edit/placeholder"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Place Holder</div>';
            __p += '<div class="fb-edit-section-content">'
            __p += '<input placeholder="placeholder" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.PLACEHOLDER)) == null ? '' : __t) +
                '" />\n\n';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/extraoptions"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Extra Options</div>';
            __p += '<div class="fb-edit-section-content">'

            __p += '<div class="edit-row"><label>Default Value</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DEFAULT_VALUE)) == null ? '' : __t) +
                '"/><p>Support Magento 2 variables: <br/><b>Current Customer</b>: {{var customer.name}}, {{var customer.email}}<br/><b>Current Product: </b>{{var product.name}}, {{var product.price}}<br/><b>Current Category: </b>{{var category.name}}<br/><b>Current Store: </b> {{var store.getFrontendName()}}<br/></p></div>';

            __p += '<div class="edit-row"><label>Is Hidden Field</label><input type="checkbox" data-rv-checked="model.' +
                ((__t = (Formbuilder.options.mappings.IS_HIDDEN_FIELD)) == null ? '' : __t) +
                '"/></div>';

            __p += '<div class="edit-row"><label>Is Readonly</label><input type="checkbox" data-rv-checked="model.' +
                ((__t = (Formbuilder.options.mappings.IS_READONLY)) == null ? '' : __t) +
                '"/></div>';

            __p += '<div class="edit-row"><label>Custom CSS Classes</label><input type="text" style="width: 100%" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.CSS_CLASS)) == null ? '' : __t) +
                '"></textarea>Add custom css classes for form field tag. Example: custom-field-classes</div>';

            __p += '<div class="edit-row"><label>Inline Css</label><textarea style="width: 100%" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.INLINE_CSS)) == null ? '' : __t) +
                '"></textarea>Add css code. Example: margin-right: 10px; padding-left: 5px</div>';

            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/extraoptions1"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Extra Options</div>';
            __p += '<div class="fb-edit-section-content">'

            __p += '<div class="edit-row"><label>Custom CSS Classes</label><input type="text" style="width: 100%" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.CSS_CLASS)) == null ? '' : __t) +
                '"></textarea>Add custom css classes for form field tag. Example: custom-field-classes</div>';

            __p += '<div class="edit-row"><label>Inline Css</label><textarea style="width: 100%" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.INLINE_CSS)) == null ? '' : __t) +
                '"></textarea>Add css code. Example: margin-right: 10px; padding-left: 5px</div>';

            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/dateValidation"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Date Validation</div>';
            __p += '<div class="fb-edit-section-content">'

            __p += '<div class="edit-row"><label>Restrict by number Year (equal or less than year)</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DATE_VALIDATION_YEAR)) == null ? '' : __t) +
                '"/>Input number of year. Example: 18 - it mean 18 years ago or older.</div>';

            __p += '<div class="edit-row"><label>Or Restrict Date To (equal or less than date)</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DATE_VALIDATION_TO)) == null ? '' : __t) +
                '"/>Add restrict date with format: m/d/Y or Y</div>';

            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/customtemplate"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Custom Template</div>';
            __p += '<div class="fb-edit-section-content">'

            __p += '<div class="edit-row"><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.CUSTOM_TEMPLATE)) == null ? '' : __t) +
                '"/></div><br/>';
            __p += 'Enter custom template file path. Examples: <b>fields/text.phtml</b><br/><b>fields/website.phtml</b><br/><b>fields/radio.phtml</b>';

            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/productsku"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Product SKU</div>';
            __p += '<div class="fb-edit-section-content">'

            __p += '<div class="edit-row"><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.PRODUCT_SKU)) == null ? '' : __t) +
                '"/></div><br/>';
            __p += 'Enter product SKU as value for the field.';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/font"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="edit-row"><label>Font Size(px)</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.FONT_SIZE)) == null ? '' : __t) +
                '"/></div>';
            __p += '<div class="edit-row"><label>Font Style</label>\n<select  data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.FONT_STYLE)) == null ? '' : __t) +
                '">\n\n' +
                '<option value="normal">Normal</option>' +
                '<option value="italic">Italic</option>' +
                '<option value="oblique">Oblique</option>' +
                '</select></div>';
            __p += '<div class="edit-row"><label>Font Weight</label><input  type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.FONT_WEIGHT)) == null ? '' : __t) +
                '"/></div>';

        }
        return __p
    };
    this["Formbuilder"]["templates"]["edit/icon"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';

            __p += '<div class=\'fb-edit-section-header\'">Icon</div>\n';
            __p += '<div class="fb-edit-section-content row">';

            __p += '<div class="col-sm-6"><div class="edit-row"><label>Icon</label><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.ICON)) == null ? '' : __t) +
                '">\n' +
                '<option value="fa-heart" class="fa fa-heart">\nHeart</option>\n' +
                '<option value="fa-calendar" class="fa fa-calendar">\nCalendar</option>\n' +
                '<option value="fa-envelope-o" class="fa fa-envelope-o">\nEmail</option>\n' +
                '<option value="fa-paperclip" class="fa fa-paperclip">\nAttach</option>\n' +
                '<option value="fa-picture-o" class="fa fa-picture-o">\nPicture</option>\n' +
                '<option value="fa-phone" class="fa fa-phone">\nPhone</option>\n' +
                '<option value="fa-star" class="fa fa-star">\nStar</option>\n' +
                '<option value="fa-question" class="fa fa-question">\nQuestion</option>\n' +
                '<option value="fa-code" class="fa fa-code">\nCode</option>\n' +
                '<option value="fa-money" class="fa fa-money">\nMoney</option>\n' +

                '<option value="">----------------------------------</option>\n' +

                '<option value="fa-check" class="fa fa-check">\nCheck</option><br>\n' +
                '<option value="fa-circle" class="fa fa-circle">\nCircle</option>\n' +
                '<option value="fa-clock-o" class="fa fa-clock-o">\nClock</option>\n' +
                '<option value="fa-scissors" class="fa fa-scissors">\nScissors</option>\n' +
                '<option value="fa-pencil" class="fa fa-pencil" >\nPencil</option>\n' +
                '<option value="fa-square-o" class="fa fa-square-o">\nCheckboxes</option>\n' +
                '<option value="fa-file-text-o" class="fa fa-file-text-o">\nFile text</option>\n' +
                '<option value="fa-cog" class="fa fa-cog">\nCog</option>\n' +
                '<option value="fa-arrows" class="fa fa-arrows">\nMove</option>\n' +
                '<option value="fa-ban" class="fa fa-ban">\nCancel</option>\n' +

                '<option value="">----------------------------------</option>\n' +

                '<option value="fa-thumbs-o-up" class="fa fa-thumbs-o-up ">\nLike</option>\n' +
                '<option value="fa-thumbs-o-down" class="fa fa-thumbs-o-down">\nDislike</option>\n' +
                '<option value="fa-facebook-square" class="fa fa-facebook-square">\nFacebook</option>\n' +
                '<option value="fa-twitter" class="fa fa-twitter">\nTwitter</option>\n' +
                '<option value="fa-youtube-play" class="fa fa-youtube-play">\nYoutube Play</option>\n' +
                '<option value="fa-dropbox" class="fa fa-dropbox">\nDropbox</option>\n' +
                '<option value="fa-github" class="fa fa-github">\nGithug</option>\n' +
                '<option value="fa-linkedin-square" class="fa fa-linkedin-square">\nLinkedin</option>\n' +
                '<option value="fa-pinterest-p" class="fa fa-pinterest-p">\nPrinterest</option>\n' +
                '<option value="fa-wordpress" class="fa fa-wordpress">\nWordpress</option>\n' +

                '<option value="">----------------------------------</option>\n' +

                '<option value="fa-user" class="fa fa-user">\nUser</option>\n' +
                '<option value="fa-bookmark-o" class="fa fa-bookmark-o">\nBookmark</option>\n' +
                '<option value="fa-home" class="fa fa-home">\nHome</option>\n' +
                '<option value="fa-link" class="fa fa-link">\nLink</option>\n' +
                '<option value="fa-map-marker" class="fa fa-map-marker">\nMap Maker</option>\n' +
                '<option value="fa-credit-card" class="fa fa-credit-card">\nCredit Card</option>\n' +
                '<option value="fa-car" class="fa fa-car">\nCar</option>\n' +
                '<option value="fa-shopping-cart" class="fa fa-shopping-cart">\nShopping Cart</option>\n' +
                '<option value="fa-paypal" class="fa fa-paypal">\nPaypal</option>\n' +
                '<option value="fa-spinner" class="fa fa-spinner">\nSpinner</option>\n' +

                '<option value="">----------------------------------</option>\n' +

                '<option value="fa-road" class="fa fa-road">\nRoad</option>\n' +
                '<option value="fa-shopping-bag" class="fa fa-shopping-bag">\nShopping Bag</option>\n' +
                '<option value="fa-sort" class="fa fa-sort">\nSort</option>\n' +
                '<option value="fa-map-o" class="fa fa-map-o">\nMap</option>\n' +
                '<option value="fa-usd" class="fa fa-usd">\nPrice</option>\n' +
                '<option value="fa-globe" class="fa fa-globe">\nGlobe</option>\n' +
                '<option value="fa-briefcase" class="fa fa-briefcase">\nBriefcase</option>\n' +
                '<option value="fa-building" class="fa fa-building">\nBuilding</option>\n' +
                '<option value="">None</option>\n' +
                '</select></div></div>';

            __p += '<div class="col-sm-6"><div class="edit-row"><label>Color</label><input class="minicolors color-label" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.ICON_COLOR)) == null ? '' : __t) +
                '" /></div></div>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };
    this["Formbuilder"]["templates"]["edit/border"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';

            __p += '<div class=\'fb-edit-section-header\'>Border</div>';

            __p += '<div class="fb-edit-section-content">'
            __p += '<div class="edit-row"><label>Width(px)</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.BORDER_WIDTH)) == null ? '' : __t) +
                '" /></div>';

            __p += '<div class="edit-row"><label>Style</label><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.BORDER_STYLE)) == null ? '' : __t) +
                '">\n' +
                '<option value="none">Default value. Specifies no border</option>\n' +
                '<option value="hidden">The same as "none", except in border conflict resolution for table elements</option>\n' +
                '<option value="dotted">Specifies a dotted border</option>\n' +
                '<option value="dashed">Specifies a dashed border</option>\n' +
                '<option value="solid">Specifies a solid border</option>\n' +
                '<option value="double">Specifies a double border</option>\n' +
                '<option value="groove">Specifies a 3D grooved border. The effect depends on the border-color value</option>\n' +
                '<option value="ridge">Specifies a 3D ridged border. The effect depends on the border-color value</option>\n' +
                '<option value="inset">Specifies a 3D inset border. The effect depends on the border-color value</option>\n' +
                '<option value="outset">Specifies a 3D outset border. The effect depends on the border-color value</option>\n' +
                '<option value="initial">Sets this property to its default value</option>\n' +
                '<option value="inherit">Inherits this property from its parent element</option>\n' +
                '</select></div>';

            __p += '<div class="edit-row"><label>Radius(px)</label><input  type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.BORDER_RADIUS)) == null ? '' : __t) +
                '" /></div>';

            __p += '<div class="edit-row"><label>Color</label><input class="minicolors color-label" type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.BORDER_COLOR)) == null ? '' : __t) +
                '"/></div>';
            __p += '</div>';
            __p += '</div>';

        }
        return __p
    };
    this["Formbuilder"]["templates"]["edit/validation"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';

            __p += '<div class=\'fb-edit-section-header\'>Validation</div>';

            __p += '<div class="fb-edit-section-content">';
            __p += '<select  data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.VALIDATION)) == null ? '' : __t) +
                '">\n <option value=""> </option>\n <option value="validate-no-html-tags">HTML tags are not allowed</option>\n  <option value="validate-select">Please select an option</option>\n' +
                '<option value="required-entry">This is a required field</option>\n  <option value="validate-number">Please enter a valid number in this field</option>\n' +
                '<option value="validate-number-range">The value is not within the specified range</option>\n' +
                '<option value="validate-digits">Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas</option>\n  <option value="validate-digits-range">The value is not within the specified range</option>\n' +
                '<option value="validate-alpha">Please use letters only (a-z or A-Z) in this field</option>\n  <option value="validate-code">Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter</option>\n' +
                '<option value="validate-alphanum">Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed</option>\n  <option value="validate-alphanum-with-spaces">Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field</option>\n' +
                '<option value="validate-street">Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field</option>\n' +
                '<option value="validate-phoneStrict">Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890</option>\n' +
                '<option value="validate-phoneLax">Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890</option>\n' +
                '<option value="validate-fax">Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890</option>\n' +
                '<option value="validate-date">Please enter a valid date</option>\n' +
                '<option value="validate-date-range">The From Date value should be less than or equal to the To Date value</option>\n' +
                '<option value="validate-email">Please enter a valid email address. For example johndoe@domain.com</option>\n' +
                '<option value="validate-emailSender">Please use only visible characters and spaces</option>\n' +
                '<option value="validate-password">Please enter 6 or more characters. Leading or trailing spaces will be ignorede</option>\n' +
                '<option value="validate-admin-password">Please enter 7 or more characters. Password should contain both numeric and alphabetic characters</option>\n' +
                '<option value="validate-both-passwords">Please make sure your passwords match</option>\n' +
                '<option value="validate-url">Please enter a valid URL. Protocol is required (http://, https:// or ftp://)</option>\n' +
                '<option value="validate-clean-url">Please enter a valid URL. For example http://www.example.com or www.example.com</option>\n' +
                '<option value="validate-identifier">Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page"</option>\n' +
                '<option value="validate-xml-identifier">Please enter a valid XML-identifier. For example something_1, block5, id-4</option>\n' +
                '<option value="validate-zip">Please enter a valid zip code. For example 90602 or 90602-1234</option>\n' +
                '<option value="validate-zip-international">Please enter a valid zip code</option>\n' +
                '<option value="validate-date-au">Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006</option>\n' +
                '<option value="validate-currency-dollar">Please enter a valid $ amount. For example $100.00</option>\n' +
                '<option value="validate-one-required">Please select one of the above options</option>\n' +
                '<option value="validate-one-required-by-name">Please select one of the options</option>\n' +
                '<option value="validate-not-negative-number">Please enter a number 0 or greater in this field</option>\n' +
                '<option value="validate-zero-or-greater">Please enter a number 0 or greater in this field</option>\n' +
                '<option value="validate-greater-than-zero">Please enter a number greater than 0 in this field</option>\n' +
                '<option value="validate-state">Please select State/Province</option>\n' +
                '<option value="validate-new-password">Please enter 6 or more characters. Leading or trailing spaces will be ignored</option>\n' +
                '<option value="validate-cc-number">Please enter a valid credit card number</option>\n' +
                '<option value="validate-cc-type">Credit card number does not match credit card type</option>\n' +
                '<option value="validate-cc-type-select">Card type does not match credit card number</option>\n' +
                '<option value="validate-cc-exp">Incorrect credit card expiration date</option>\n' +
                '<option value="validate-cc-cvn">Please enter a valid credit card verification number</option>\n' +
                '<option value="validate-ajax"></option>\n' +
                '<option value="validate-data">Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter</option>\n' +
                '<option value="validate-css-length">Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%</option>\n' +
                '<option value="validate-length">Text length does not satisfy specified text range</option>\n' +
                '<option value="validate-percents">Please enter a number lower than 100</option>\n' +
                '<option value="validate-cc-ukss">Please enter issue number or start date for switch/solo card type</option>\n' +
                '</select>\n';

            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/options"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {

            __p += '<div class="lof-fieldset">';

            __p += '<div class=\'fb-edit-section-header\'>Options</div>';
            __p += '<div class="fb-edit-section-content">';
            if (typeof includeBlank !== 'undefined') {
                ;
                __p += '\n  <label>\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = (Formbuilder.options.mappings.INCLUDE_BLANK)) == null ? '' : __t) +
                    '\' />\n    Include blank\n  </label>\n';
            };
            __p += '\n\n<div class=\'option\' data-rv-each-option=\'model.' +
                ((__t = (Formbuilder.options.mappings.OPTIONS)) == null ? '' : __t) +
                '\'>\n  <input type="checkbox" class=\'js-default-updated\' data-rv-checked="option:checked" />\n  <input type="text" data-rv-input="option:label" class=\'option-label-input\' />\n  <a class="js-add-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Add Option"><i class=\'fa fa-plus-circle\'></i></a>\n  <a class="js-remove-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Remove Option"><i class=\'fa fa-minus-circle\'></i></a>\n</div>\n\n';
            if (typeof includeOther !== 'undefined') {
                ;
                __p += '\n  <label>\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = (Formbuilder.options.mappings.INCLUDE_OTHER)) == null ? '' : __t) +
                    '\' />\n    Include "other"\n  </label>\n';
            };
            __p += '\n\n<div class=\'fb-bottom-add\'>\n  <a class="js-add-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '">Add option</a>\n</div>\n';

            __p += '\n\n<div class=\'fb-bottom-add\'>\n  <a class="js-add-many-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '">Add many options</a>\n</div>\n';

            __p += '<div class="edit-row" style="margin-top: 10px;"><label>Grid Column</label><input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.GRID_COLUMN)) == null ? '' : __t) +
                '" /></div>';

            __p += '<br/><div class=\'fb-edit-section-helper\'>Add images to select fields from media folder with the code for option name: \n<br/> \nOption <i>{{media url="myfolder/myfile.png"}}</i> <br/> myfolder/ is folder path under <strong>media/</strong> folder<br/><br/>For example:<br/> sony{{media url="brand/sony.png"}}</div>\n';

            __p += '</div>';
            __p += '</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/subscription"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {

            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Subscription Text</div>';
            __p += '<div class="fb-edit-section-content">';

            if (typeof includeBlank !== 'undefined') {
                ;
                __p += '\n  <label>\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = (Formbuilder.options.mappings.INCLUDE_BLANK)) == null ? '' : __t) +
                    '\' />\n    Include blank\n  </label>\n';
            };
            __p += '\n\n<div class=\'option\' data-rv-each-option=\'model.' +
                ((__t = (Formbuilder.options.mappings.OPTIONS)) == null ? '' : __t) +
                '\'>\n  <input type="checkbox" style="width: 5%;" class=\'js-default-updated\' data-rv-checked="option:checked" />\n  <input type="text" style="width: 92%; float: right;" data-rv-input="option:label" class=\'option-label-input\' />\n  <a style="display:none;" class="js-add-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Add Option"><i class=\'fa fa-plus-circle\'></i></a>\n  <a style="display:none;" class="js-remove-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Remove Option"><i class=\'fa fa-minus-circle\'></i></a>\n</div>\n\n';
            if (typeof includeOther !== 'undefined') {
                ;
                __p += '\n  <label style="display:none;">\n    <input type=\'checkbox\' data-rv-checked=\'model.' +
                    ((__t = (Formbuilder.options.mappings.INCLUDE_OTHER)) == null ? '' : __t) +
                    '\' />\n    Include "other"\n  </label>\n';
            };
            __p += '\n\n<div  style="display:none;" class=\'fb-bottom-add\'>\n  <a class="js-add-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '">Add option</a>\n</div>\n';


            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/size"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Size1</div>';
            __p += '<div class="fb-edit-section-content">'
            __p += '<select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SIZE)) == null ? '' : __t) +
                '">\n  <option value="small">Small</option>\n  <option value="medium">Medium</option>\n  <option value="large">Large</option>\n</select>\n';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/rating"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Number Of Stars</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.LIMIT)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Default Score</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DEFAULT)) == null ? '' : __t) +
                '" style="width: 60px" />\n';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["edit/model_dropdown"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {
            __p += '<div class=\'fb-edit-section-header\'>Show Position</div>\n<select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_POS)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';

            __p += '<div class=\'fb-edit-section-header\'>Max Model Level</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.MAX_LEVEL)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Data Model Categories</div>\n\n';
            __p += '\n\n<div class=\'option\' data-rv-each-option=\'model.' +
                ((__t = (Formbuilder.options.mappings.CATE_ID)) == null ? '' : __t) +
                '\'>\n  <input type="checkbox" class=\'js-default-updated\' data-rv-checked="option:checked" />\n  <select class=\'option-label-input\' data-rv-value="option:value">';
            if (typeof (data_model_categories) && data_model_categories.length) {
                __p += '\n<option value="0">-- Select Model Category -- </option>';
                for (i = 0; i < data_model_categories.length; i++) {
                    __p += '\n<option value="' + data_model_categories[i]['value'] + '">' + data_model_categories[i]['label'] + '</option>';
                }
            }
            __p += '\n</select>\n  <a class="js-add-cate-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Add Option"><i class=\'fa fa-plus-circle\'></i></a>\n  <a class="js-remove-cate-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Remove Option"><i class=\'fa fa-minus-circle\'></i></a>\n</div>\n\n';

            __p += '\n\n<div class=\'fb-bottom-add\'>\n  <a class="js-add-cate-option ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '">Add option</a>\n</div>\n';

        }

        return __p
    };

    this["Formbuilder"]["templates"]["edit/address"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        __p += '<div class="address-settings">';
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">View Settings</div>';

            __p += '<div class="fb-edit-section-content" data-role="content">';
            __p += '<div class="afield"><span>Show Address:</span> <select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_ADDRESS)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Address Placeholder:</span> <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.ADDRESS_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" /> - <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.ADDRESS_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class="afield"><span>Show City:</span> <select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_CITY)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>City Placeholder:</span> <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.CITY_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" /> - <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.CITY_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class="afield"><span>Show State:</span><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_STATE)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>State Placeholder:</span> <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.STATE_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" /> - <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.STATE_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class="afield"><span>Show Zipcode:</span><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_ZIPCODE)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Zipcode Placeholder:</span> <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.ZIPCODE_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" /> - <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.ZIPCODE_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class="afield"><span>Show Country:</span><select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_COUNTRY)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="2">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Country Placeholder:</span> <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.COUNTRY_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" /> - <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.COUNTRY_PLACEHOLDER)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

        }
        __p += '</div>';

        __p += '</div>';
        return __p
    };

    this["Formbuilder"]["templates"]["edit/html"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        __p += '<div class="html-settings">';
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Code Html</div>';
            __p += '<div class="fb-edit-section-content" data-role="content">';
            __p += '\n<textarea width="100%" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.LABEL)) == null ? '' : __t) +
                '"  />\n';
            __p += '</div>';
            __p += '</div>';
        }
        __p += '</div>';

        __p += '</div>';

        return __p
    };
    this["Formbuilder"]["templates"]["edit/esignature"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        __p += '<div class="lof-fieldset">';
        __p += '<div class=\'fb-edit-section-header\' data-role="title">E-Signature</div>';
        __p += '<div class="fb-edit-section-content" data-role="content">';
        with (obj) {
            __p += '<div class="afield"><span>Show Control</span> <select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.ESIGNATURE_SHOW_CONTROL)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="0">Hide</option>\n</select>\n';
            __p += '</div>';
        }
        __p += '</div>';
        __p += '</div>';

        return __p
    };

    this["Formbuilder"]["templates"]["edit/google_map"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Google Map</div>';

            __p += '<div class="fb-edit-section-content" data-role="content">';
            __p += '<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.WIDTH)) == null ? '' : __t) +
                '" style="width: 60px" /> - <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.HEIGHT)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class="afield"><span>Show Radius</span> <select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_MAP_RADIUS)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="0">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Show Map</span> <select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_MAP)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="0">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class="afield"><span>Location Label</span> <input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.LOCATION_LABEL)) == null ? '' : __t) + '" style="width: 100%" />\n';
            __p += '</div>';

            __p += '<div class=\'fb-edit-section-header\'>Radius</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.RADIUS)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Width</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.WIDTH)) == null ? '' : __t) +
                '" style="width: 60px" />\n';

            __p += '<div class=\'fb-edit-section-header\'>Height</div>\n<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.HEIGHT)) == null ? '' : __t) +
                '" style="width: 60px" />\n';




            __p += '<div class="afield"><span>Show City:</span> <select data-rv-value="model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_MAP_LOCATION)) == null ? '' : __t) +
                '">\n  <option value="1">Show</option>\n  <option value="0">Hide</option>\n</select>\n';
            __p += '</div>';

            __p += '<div class=\'fb-edit-section-header\'>Location map</div>\n<div class="field-map" ><input type="text" value="" name="formbuilder_map[addess]" id="formbuilder_map" class="large-text formbuilder-map-search" placeholder="Enter a location" autocomplete="off">' +
                '<div class="formbuilder-map" style="height:200px;"></div>' +
                '<input type="text" id="formbuilder_map" name="formbuilder_map[address]" class="formbuilder-map-address" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DEFAULT_ADDRESS)) == null ? '' : __t) +
                '">' +
                '<input type="text" id="formbuilder_map" name="formbuilder_map[latitude]" class="formbuilder-map-latitude" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DEFAULT_LAT)) == null ? '' : __t) +
                '"style="width:48%; float: left">' +
                '<input type="text" id="formbuilder_map" name="formbuilder_map[longitude]" class="formbuilder-map-longitude" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DEFAULT_LONG)) == null ? '' : __t) +
                '"style="width:48%; float: right"></div>\n' +
                '<script>' +
                'jQuery(document).ready(function() {' +
                '(function( $ ) {' +
                '"use strict";' +

                'var maps = [];' +

                '$( ".field-map" ).each( function() {' +

                'initializeMap( $( this ) );' +
                '});' +

                'function initializeMap( mapInstance ) {' +
                'var searchInput = mapInstance.find( ".formbuilder-map-search" );' +
                'var mapCanvas = mapInstance.find( ".formbuilder-map" );' +
                'var latitude = mapInstance.find( ".formbuilder-map-latitude" );' +
                'var longitude = mapInstance.find( ".formbuilder-map-longitude" );' +
                'var address = mapInstance.find( ".formbuilder-map-address" );' +
                'var latLng = new google.maps.LatLng( 54.800685, -4.130859 );' +
                'var zoom = 20;' +

                // If we have saved values, let"s set the position and zoom level
                'if ( latitude.val().length > 0 && longitude.val().length > 0 ) {' +
                'latLng = new google.maps.LatLng( latitude.val(), longitude.val() );' +
                'zoom = 20;' +
                '}' +

                // Map
                'var mapOptions = {' +
                'center: latLng,' +
                'zoom: zoom' +
                '};' +
                'var map = new google.maps.Map( mapCanvas[0], mapOptions );' +

                'latitude.on("change", function() {' +
                'map.setCenter( new google.maps.LatLng( latitude.val(), longitude.val() ) );' +
                '});' +

                'longitude.on("change", function() {' +
                'map.setCenter( new google.maps.LatLng( latitude.val(), longitude.val() ) );' +
                '});' +

                // Marker
                'var markerOptions = {' +
                'map: map,' +
                'draggable: true,' +
                'title: "Drag to set the exact location"' +
                '};' +
                'var marker = new google.maps.Marker( markerOptions );' +

                'if ( latitude.val().length > 0 && longitude.val().length > 0 ) {' +
                'marker.setPosition( latLng );' +
                '}' +

                // Search
                'var autocomplete = new google.maps.places.Autocomplete( searchInput[0] );' +
                'autocomplete.bindTo( "bounds", map ); ' +

                'google.maps.event.addListener( autocomplete, "place_changed", function() {' +
                'var place = autocomplete.getPlace();' +
                'if ( ! place.geometry ) {' +
                'return;' +
                '}' +

                'if ( place.geometry.viewport ) {' +
                'map.fitBounds( place.geometry.viewport );' +
                '} else {' +
                'map.setCenter( place.geometry.location );' +
                'map.setZoom( 17 );' +
                '}' +

                'marker.setPosition( place.geometry.location );' +

                'latitude.val( place.geometry.location.lat() );' +
                'longitude.val( place.geometry.location.lng() ); address.val(place.formatted_address);' +
                '});' +

                '$( searchInput ).keypress( function( event ) {' +
                'if ( 13 === event.keyCode ) {' +
                'event.preventDefault();' +
                '}' +
                '});' +
                // Allow marker to be repositioned
                'google.maps.event.addListener( marker, "drag", function() {' +
                'latitude.val( marker.getPosition().lat() );' +
                'longitude.val( marker.getPosition().lng() );' +
                '});' +

                'maps.push( map );' +
                '}' +

                // Resize map when meta box is opened
                'if ( typeof postboxes !== "undefined" ) {' +
                'postboxes.pbshow = function () {' +
                'var arrayLength = maps.length;' +
                'for (var i = 0; i < arrayLength; i++) {' +
                'var mapCenter = maps[i].getCenter();' +
                'google.maps.event.trigger( maps[i], "resize" );' +
                'maps[i].setCenter( mapCenter );' +
                '}' +
                '};' +
                '}' +

                // When a new row is added, reinitialize Google Maps
                '$( ".cmb-repeatable-group" ).on( "cmb2_add_row", function( event, newRow ) {' +
                'var groupWrap = $( newRow ).closest( ".cmb-repeatable-group" );' +
                'groupWrap.find( ".field-map" ).each( function() {' +
                'initializeMap( $( this ) );' +
                '});' +
                '});' +

                '})( jQuery );' +


                ' });' +
                '</script>';
        }
        __p += '</div>';
        __p += '</div>';

        return __p
    };

    this["Formbuilder"]["templates"]["edit/file_upload"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;

        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class="fb-edit-section-header">Label</div>';
            __p += '<div class="fb-edit-section-content">';
            __p += '<div class="fb-common-wrapper">';
            __p += '<div class="fb-label-description">';
            __p += '<input type=\'text\' data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.LABEL)) == null ? '' : __t) +
                '\' />\n<textarea data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.DESCRIPTION)) == null ? '' : __t) +
                '\'\n  placeholder=\'Add a longer description to this field\'></textarea><p>Support Magento 2 variables: <br/><b>Current Customer</b>: {{var customer.name}}, {{var customer.email}}<br/><b>Current Product: </b>{{var product.name}}, {{var product.price}}<br/><b>Current Category: </b>{{var category.name}}<br/><b>Current Store: </b> {{var store.getFrontendName()}}<br/></p>';
            __p += '</div>';
            __p += '<label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.REQUIRED)) == null ? '' : __t) +
                '\' />\n  Required\n</label>\n<!-- label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.ADMIN_ONLY)) == null ? '' : __t) +
                '\' />\n  Admin only\n</label -->';
            __p += '<p><label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_IN_EMAIL)) == null ? '' : __t) +
                '\' />\n  Show In Email\n</label>\n';

            __p += '</div>';
            __p += '</div>';
            __p += '</div>';

            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Options</div>\n\n';
            __p += '<div class="fb-edit-section-content">';
            __p += '<div class="fb-common-wrapper">';
            __p += '<div class="fb-label-description">';
            __p += '<div class="field-row">';
            __p += '<p>Field type</p>';
            __p += '<input name="image_type" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.IMAGE_TYPE)) == null ? '' : __t) +
                '" placeholder="png,jpg,gif,jpeg,zip,doc,pdf"/>';
            __p += '<div class="text-small">Comma-separated.</div>';
            __p += '</div>';
            __p += '<div class="field-row">';
            __p += '<p>Maximum Size(MB)</p>';
            __p += '<input name="image_maximum_size" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.IMAGE_MAXIMUM_SIZE)) == null ? '' : __t) +
                '"/>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }

        return __p
    };

    this["Formbuilder"]["templates"]["edit/multifile_upload"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;

        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class="fb-edit-section-header">Label</div>';
            __p += '<div class="fb-edit-section-content">';
            __p += '<div class="fb-common-wrapper">';
            __p += '<div class="fb-label-description">';
            __p += '<input type=\'text\' data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.LABEL)) == null ? '' : __t) +
                '\' />\n<textarea data-rv-input=\'model.' +
                ((__t = (Formbuilder.options.mappings.DESCRIPTION)) == null ? '' : __t) +
                '\'\n  placeholder=\'Add a longer description to this field\'></textarea><p>Support Magento 2 variables: <br/><b>Current Customer</b>: {{var customer.name}}, {{var customer.email}}<br/><b>Current Product: </b>{{var product.name}}, {{var product.price}}<br/><b>Current Category: </b>{{var category.name}}<br/><b>Current Store: </b> {{var store.getFrontendName()}}<br/></p>';
            __p += '</div>';
            __p += '<label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.REQUIRED)) == null ? '' : __t) +
                '\' />\n  Required\n</label>\n<!-- label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.ADMIN_ONLY)) == null ? '' : __t) +
                '\' />\n  Admin only\n</label -->';
            __p += '<p><label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_IN_EMAIL)) == null ? '' : __t) +
                '\' />\n  Show In Email\n</label>\n';
            __p += '<p><label>\n  <input type=\'checkbox\' data-rv-checked=\'model.' +
                ((__t = (Formbuilder.options.mappings.SHOW_FILE_LIST)) == null ? '' : __t) +
                '\' />\n  Show File List \n</label>\n';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';

            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\'>Options</div>\n\n';
            __p += '<div class="fb-edit-section-content">';
            __p += '<div class="fb-common-wrapper">';
            __p += '<div class="fb-label-description">';
            __p += '<div class="field-row">';
            __p += '<p>Field type</p>';
            __p += '<input name="image_type" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.IMAGE_TYPE)) == null ? '' : __t) +
                '" placeholder="png,jpg,gif,jpeg,zip,doc,pdf"/>';
            __p += '<div class="text-small">Comma-separated.</div>';
            __p += '</div>';
            __p += '<div class="field-row">';
            __p += '<p>Drag n Drop Text</p>';
            __p += '<input name="image_maximum_size" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.DRAGDROPTEXT)) == null ? 'Drag &amp; drop files here' : __t) +
                '"/>';
            __p += '</div>';
            __p += '<div class="field-row">';
            __p += '<p>Open the file browser Text</p>';
            __p += '<input name="image_maximum_size" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.OPENBROWSERTEXT)) == null ? 'Open the file Browser' : __t) +
                '"/>';
            __p += '</div>';
            __p += '<div class="field-row">';
            __p += '<p>Maximum Size(MB)</p>';
            __p += '<input name="image_maximum_size" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.IMAGE_MAXIMUM_SIZE)) == null ? '10' : __t) +
                '"/>';
            __p += '</div>';
            __p += '<div class="field-row">';
            __p += '<p>Max upload files (number files can upload)</p>';
            __p += '<input name="file_number_limit" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.FILE_NUMBER_LIMIT)) == null ? '5' : __t) +
                '"/>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }

        return __p
    };

    this["Formbuilder"]["templates"]["edit/units"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class="lof-fieldset">';
            __p += '<div class=\'fb-edit-section-header\' data-role="title">Units</div>';
            __p += '<div class="fb-edit-section-content row" data-role="content">';
            __p += '<div class="col-sm-12">';
            __p += '<input type="text" data-rv-input="model.' +
                ((__t = (Formbuilder.options.mappings.UNITS)) == null ? '' : __t) +
                '" />\n';
            __p += '</div>';
            __p += '</div>';
            __p += '</div>';
        }
        return __p
    };

    this["Formbuilder"]["templates"]["page"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p +=
                //((__t = ( Formbuilder.templates['partials/save_button']() )) == null ? '' : __t) +
                '\n' +
                ((__t = (Formbuilder.templates['partials/left_side']())) == null ? '' : __t) +
                '\n' +
                ((__t = (Formbuilder.templates['partials/right_side']())) == null ? '' : __t) +
                '\n<div class=\'fb-clear\'></div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/add_field"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {
            __p += '<div class=\'fb-tab-pane active\' id=\'addField\'>\n  <div class=\'fb-add-field-types\'>\n    <div class=\'section\'>\n      ';
            _.each(_.sortBy(Formbuilder.inputFields, 'order'), function (f) {
                ;
                __p += '\n        <a data-field-type="' +
                    ((__t = (f.field_type)) == null ? '' : __t) +
                    '" class="' +
                    ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                    '">\n          ' +
                    ((__t = (f.addButton)) == null ? '' : __t) +
                    '\n        </a>\n      ';
            });;
            __p += '\n    </div>\n\n    <div class=\'section\'>\n      ';
            _.each(_.sortBy(Formbuilder.nonInputFields, 'order'), function (f) {
                ;
                __p += '\n        <a data-field-type="' +
                    ((__t = (f.field_type)) == null ? '' : __t) +
                    '" class="' +
                    ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                    '">\n          ' +
                    ((__t = (f.addButton)) == null ? '' : __t) +
                    '\n        </a>\n      ';
            });;
            __p += '\n    </div>\n  </div>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/edit_field"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-tab-pane\' id=\'editField\'>\n  <div class=\'fb-edit-field-wrapper\'></div>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/left_side"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-left\'>\n<div class="wrapper-left"><ul class=\'fb-tabs\'>\n    <li class=\'active\'><a data-target=\'#addField\'>Add new field</a></li>\n    <li><a data-target=\'#editField\'>Edit field</a></li>\n  </ul>\n\n  <div class=\'fb-tab-content\'>\n    ' +
                ((__t = (Formbuilder.templates['partials/add_field']())) == null ? '' : __t) +
                '\n    ' +
                ((__t = (Formbuilder.templates['partials/edit_field']())) == null ? '' : __t) +
                '\n  </div></div>\n</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/right_side"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-right\'>\n  <div class=\'fb-no-response-fields\'>No response fields</div>\n  <div class=\'fb-response-fields\'></div>\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["partials/save_button"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'fb-save-wrapper\'>\n  <button class=\'js-save-form ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '\'></button>\n</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/base"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'subtemplate-wrapper\'>\n  <div class=\'cover\'></div>\n  ' +
                ((__t = (Formbuilder.templates['view/label']({ rf: rf }))) == null ? '' : __t) +
                '\n\n  ' +
                ((__t = (Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].view({ rf: rf }))) == null ? '' : __t) +
                '\n\n  ' +
                ((__t = (Formbuilder.templates['view/description']({ rf: rf }))) == null ? '' : __t) +
                '\n  ' +
                ((__t = (Formbuilder.templates['view/duplicate_remove']({ rf: rf }))) == null ? '' : __t) +
                '\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/base_non_input"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        var field_type = obj.rf.attributes.field_type;

        with (obj) {
            __p += '<div class=\'subtemplate-wrapper\'>\n  <div class=\'cover\'></div>\n  ';

            if (field_type == 'section_break') {
                ((__p += (Formbuilder.templates['view/section_break_label']({ rf: rf }))) == null ? '' : __t);
            }
            if (field_type == 'product_field') {
                ((__p += (Formbuilder.templates['view/product_field']({ rf: rf }))) == null ? '' : __t);
            }
            if (field_type == 'html') {
                ((__p += (Formbuilder.templates['view/section_break_label']({ rf: rf }))) == null ? '' : __t);
            }
            __p += '\n\n  ';

            if (field_type == 'file_upload') {
                ((__p += (Formbuilder.templates['view/label']({ rf: rf }))) == null ? '' : __t);
            }

            if (field_type == 'multifile_upload') {
                ((__p += (Formbuilder.templates['view/label']({ rf: rf }))) == null ? '' : __t);
            }

            ((__p += (Formbuilder.fields[rf.get(Formbuilder.options.mappings.FIELD_TYPE)].view({ rf: rf }))) == null ? '' : __t);

            ((__p += (Formbuilder.templates['view/description']({ rf: rf }))) == null ? '' : __t);

            __p += '\n\n  ' + '\n\n  ' +
                ((__t = (Formbuilder.templates['view/duplicate_remove']({ rf: rf }))) == null ? '' : __t) +
                '\n</div>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/description"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<span class=\'help-block\' style="color:' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.COLOR_DESCRIPTION)))) == null ? '' : __t) +

                ';">\n  ' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.DESCRIPTION)))) == null ? '' : __t) +
                '\n</span>\n';

        }
        return __p
    };



    this["Formbuilder"]["templates"]["view/duplicate_remove"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape;
        with (obj) {
            __p += '<div class=\'actions-wrapper\'>\n  <a class="js-duplicate ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Duplicate Field"><i class=\'fa fa-plus-circle\'></i></a>\n  <a class="js-clear ' +
                ((__t = (Formbuilder.options.BUTTON_CLASS)) == null ? '' : __t) +
                '" title="Remove Field"><i class=\'fa fa-minus-circle\'></i></a>\n</div>';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/label"] = function (obj) {
        obj || (obj = {});
        var __t, __fs, __fw, __f, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {
            __p += '<label style="' +
                ((__fs = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_SIZE)))) == null ? '' : 'font-size:' + __fs + 'px;') +
                ((__f = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_STYLE)))) == null ? '' : 'font-style:' + __f + ';') +
                ((__fw = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_WEIGHT)))) == null ? '' : 'font-weight:' + __fw + ';') +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.COLOR_LABEL)))) == null ? '' : 'color:' + __t + ';') +
                '">\n  <span>' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.LABEL)))) == null ? '' : __t) +
                '\n  ';
            if (rf.get(Formbuilder.options.mappings.REQUIRED)) {
                ;
                __p += '\n    <abbr title=\'required\'>*</abbr>\n  ';
            };
            __p += '\n</label>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/product_field"] = function (obj) {
        obj || (obj = {});
        var __t, __fs, __fw, __f, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {
            __p += '<label class="fb-option product-field-view" style="' +
                ((__fs = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_SIZE)))) == null ? '' : 'font-size:' + __fs + 'px;') +
                ((__f = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_STYLE)))) == null ? '' : 'font-style:' + __f + ';') +
                ((__fw = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_WEIGHT)))) == null ? '' : 'font-weight:' + __fw + ';') +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.COLOR_LABEL)))) == null ? '' : 'color:' + __t + ';') +
                '">\n <input type="checkbox"/> <span>' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.LABEL)))) == null ? '' : __t) +
                '\n  ';
            __p += '\n</label>\n';

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/input"] = function (obj) {
        obj || (obj = {});
        var __t, __fs, __fw, __f, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {

        }
        return __p
    };

    this["Formbuilder"]["templates"]["view/section_break_label"] = function (obj) {
        obj || (obj = {});
        var __t, __p = '', __e = _.escape, __j = Array.prototype.join;
        function print() { __p += __j.call(arguments, '') }
        with (obj) {
            __p += '<div class="field-section-break" style="' +
                'background-color:' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.BACKGROUND_COLOR)))) == null ? '' : __t) + ';">\n' +
                '<span style="color:' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.COLOR_TEXT)))) == null ? '' : __t) + '; font-size:' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_SIZE)))) == null ? '' : __t) + 'px;  font-style:' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_STYLE)))) == null ? '' : __t) + '; font-weight:' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.FONT_WEIGHT)))) == null ? '' : __t) + ';">' +
                ((__t = (Formbuilder.helpers.simple_format(rf.get(Formbuilder.options.mappings.LABEL)))) == null ? '' : __t) +
                '</span> ';
            __p += '\n</div>\n';

        }
        return __p
    };

});

