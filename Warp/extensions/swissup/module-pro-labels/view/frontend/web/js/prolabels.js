define([
    'Magento_Ui/js/lib/view/utils/async',
    'Swissup_ProLabels/js/renderLabels',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($) {
    'use strict';

    $.widget('swissup.prolabels', {
        component: 'Swissup_ProLabels/js/prolabels',
        options: {
            parent: null,
            imageLabelsTarget: '',
            imageLabelsInsertion: 'appendTo',
            imageLabelsWrap: true,
            imageLabelsRenderAsync: false,
            contentLabelsTarget: '',
            contentLabelsInsertion: 'appendTo',
            labelsData: {},
            predefinedVars: {}
        },

        /**
         * [_create description]
         */
        _create: function () {
            var baseImageElement,
                contentElement,
                me = this;

            me.containers = {};
            me.renderContext = me.options.parent ?
                me.element.closest(me.options.parent) :
                me.element;

            if (me.options.imageLabelsRenderAsync) {
                $.async(
                    {
                        selector: me.options.imageLabelsTarget,
                        ctx: me.renderContext.get(0)
                    },
                    me.renderImageLabelsAsync.bind(me)
                );
            } else {
                baseImageElement = me.options.imageLabelsTarget ?
                    $(me.options.imageLabelsTarget, me.renderContext) :
                    me.renderContext;
                me.renderImageLabels(baseImageElement.get(0));
            }

            contentElement = $(me.options.contentLabelsTarget, me.renderContext);
            me.renderContentLabels(contentElement.get(0));
        },

        /**
         * Render prolabels for product image.
         *
         * @param  {HTMLElement} baseImage
         */
        renderImageLabels: function (baseImage) {
            var targetElement,
                insertionMethod,
                options,
                me = this;

            if (me.options.imageLabelsWrap &&
                !$(baseImage).hasClass('prolabels-wrapper')
            ) {
                if ($(baseImage).parent().hasClass('prolabels-wrapper')) {
                    // parent element has wrappr class
                    targetElement = $(baseImage).parent();
                } else {
                    // add prolabels-wrapper
                    targetElement = $(baseImage)
                        .wrap('<div class="prolabels-wrapper"></div>')
                        .parent();
                }
            } else {
                // do not add prolabels-wrapper
                targetElement = $(baseImage);
            }

            options = {
                labelsData: me.getImageLabels(),
                predefinedVars: me.options.predefinedVars //,
                // renderMode: 'replaceNode' -- for some unknown reason
                // there are issues with 'replaceNode' in chrome browser
                // when dev console turned off
            };

            if (targetElement.length) {
                insertionMethod = me.options.imageLabelsInsertion;
                me.containers.imageLabels = $('<div></div>');
                me.containers.imageLabels[insertionMethod](targetElement);
                me.containers.imageLabels.renderLabels(options);
            }
        },

        /**
         * Validate assigned widget ID before render image labels
         *
         * @param  {HTMLElement} baseImage
         */
        renderImageLabelsAsync: function (image) {
            // check if widget is still assigned to element before render
            if (this.uuid == this.element.data('swissupProlabels')?.uuid) {
                this.renderImageLabels(image)
            }
        },

        /**
         * Render prolabels in product info.
         *
         * @param  {String} outputElement
         */
        renderContentLabels: function (outputElement) {
            var me = this;

            me.containers.contentLabels = [];
            me.getContentLabels().forEach(function (labels) {
                var options,
                    $container,
                    $target = $(outputElement),
                    method = me.options.contentLabelsInsertion;

                if (labels.target) {
                    method = labels.target.method ? labels.target.method : method;
                    $target = labels.target.element ?
                        $(labels.target.element, me.renderContext) :
                        $target;
                }

                if ($target.length) {
                    $container = $('<div class="prolabels-content-wrapper"></div>');
                    $container[method]($target);
                    options = {
                        labelsData: [labels],
                        predefinedVars: me.options.predefinedVars
                    };
                    $container.renderLabels(options);
                    me.containers.contentLabels.push($container);
                }
            });
        },

        /**
         * @return {Object}
         */
        getImageLabels: function () {
            var data = [];

            $.each(this.options.labelsData, function () {
                if (this.position !== 'content') {
                    data.push(this);
                }
            });

            return data;
        },

        /**
         * @return {Object}
         */
        getContentLabels: function () {
            var data = [];

            $.each(this.options.labelsData, function () {
                if (this.position === 'content') {
                    data.push(this);
                }
            });

            return data;
        },

        /**
         * {@inheritdoc}
         */
        destroy: function () {
            var imageLabels = this.containers.imageLabels,
                contentLabels = this.containers.contentLabels || [];

            imageLabels && imageLabels.remove();
            contentLabels.forEach(function ($el) {
                $el.remove();
            });

            return this._super();
        }
    });

    return $.swissup.prolabels;
});
