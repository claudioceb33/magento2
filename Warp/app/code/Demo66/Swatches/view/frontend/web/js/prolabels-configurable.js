define([
    'jquery',
    'Swissup_ProLabels/js/prolabels'
], function ($, Prolabels) {
    'use strict';

    /**
     * Init prolables for configurable product.
     *
     * @param  {Object} config
     * @param  {jQuery} element
     */
    return function (config, element) {
        /**
         * Unset if inited then init with new options.
         *
         * @param  {Number} product
         */
        function reinitProlabels(product) {
            var prolabels = $(element).data('swissupProlabels');
            if (prolabels) {
                prolabels.destroy();
            }

            product = product ? product : config.superProduct;

            if (config.labels[product]) {
                Prolabels(config.labels[product], element);
            }
        }

        setTimeout(preReInitProlabels,3000,config.swatchOptions);

        function preReInitProlabels(element){
            var product = getSelectedProduct(element);
            reinitProlabels(product);
        }

        function getSelectedProduct(target){
            var _option, id, swatchRenderer, product = null;
            $.each($(target),function(option){
                if($(this).hasClass('selected')){
                    _option = $(this).data('option-id');
                    id = $(this).parent().parent().data('attribute-id');
                    swatchRenderer = $(this).parent().parent().parent().data('mage-SwatchRenderer');
                    if (swatchRenderer && _option && id) {
                        var products = swatchRenderer.optionsMap[id][_option].products;
                        product = products[0];
                    }
                }

            });

            return product;
        }
        $(document).on('click change', config.swatchOptions,function (event) {
            var swatchRenderer = $(event.currentTarget).parent().parent().parent().data('mage-SwatchRenderer');

            if (swatchRenderer) {
                var option = $(event.currentTarget).data('option-id'),
                    id = $(event.currentTarget).parent().parent().data('attribute-id');
                var products = swatchRenderer.optionsMap[id][option].products;
                reinitProlabels(products[0]);
            }

        });
    };
});
