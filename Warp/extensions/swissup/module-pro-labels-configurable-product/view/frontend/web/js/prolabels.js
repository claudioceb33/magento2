define([
    'jquery',
    'Swissup_ProLabels/js/prolabels'
], function ($) {
    'use strict';

    /**
     * Init prolables for configurable product.
     *
     * @param  {Object} config
     * @param  {jQuery} element
     */
    const prolabelsConfigurable = (options, element) => {

        /**
         * Unset if inited then init with new options.
         *
         * @param  {Number} product
         */
        const reinit = (product) => {
            const $el = $(element);

            try {
                $el?.prolabels?.('destroy');
            } catch (error) {
                // console.log('You try to destroy ProLabels but they are not initialed yet.');
            }
            product = product || options.superProduct;
            if (options.labels[product])
                $el.prolabels(options.labels[product]);
        }

        reinit(options.superProduct);

        // Listen options change when swatches disabled.
        $(options.configurableOptions).on('change.prolabelsConfigurable', (event) => {
            const $productForm = $('#product_addtocart_form');
            const configurable = $productForm.data('mageConfigurable');

            configurable && reinit(configurable.simpleProduct);
        });

        // Listen options change when swatches enabled.
        $(options.swatchOptions).on('change.prolabelsConfigurable', (event) => {
            const productId = $(event.currentTarget)?.SwatchRenderer?.('getProduct');

            reinit(typeof productId === 'string' ? productId : false);
        });
    };

    prolabelsConfigurable.component = 'Swissup_ProLabelsConfigurableProduct/js/prolabels';

    return prolabelsConfigurable;
});
