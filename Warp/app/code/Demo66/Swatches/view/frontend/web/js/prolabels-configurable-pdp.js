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

        setTimeout(preReInitProlabels,1000,config.swatchOptions);

			function preReInitProlabels(element){
				var product = getSelectedProduct(element);
				reinitProlabels(product);
			}

			function getSelectedProduct(target){
				var _option, id, swatchRenderer, product = null;
				$.each($(target + ' .swatch-option'),function(option){
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

			// Listen options click when swatches enabled.
        $(config.swatchOptions).on('click', function (event) {
            var swatchRenderer = $(event.currentTarget).data('mage-SwatchRenderer');

            if (swatchRenderer) {
                reinitProlabels(swatchRenderer.getProduct());
            }

        });
    };
});
