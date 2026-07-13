define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    /**
     * Get selected swatch option for swatchesElement
     *
     * @param  {jQuery} swatchesElement
     * @param  {Object} swatchesConfig
     * @return {Object}
     */
    function getSelectedAttributes(swatchesElement, swatchesConfig) {
        var selectedAttributes = {};

        $('.swatch-attribute', swatchesElement).each(function() {
            var attrCode,
                attrId,
                filterName;

            attrCode = $(this).data('attribute-code');
            attrId = $(this).data('attribute-id');
            filterName = swatchesConfig.hasOwnProperty(attrId) ?
                swatchesConfig[attrId].inUrlLabel :
                null;

            if (!filterName) {
                return false;
            }

            $('[role="option"]', $(this)).each(function() {
                var isInUrl,
                    optionId = $(this).data('option-id'),
                    optionName = '',
                    pathName = window.location.pathname;

                if (swatchesConfig.hasOwnProperty(attrId) &&
                    swatchesConfig[attrId].hasOwnProperty(optionId)
                ) {
                    optionName = swatchesConfig[attrId][optionId].inUrlValue;
                }

                isInUrl = optionName ?
                    pathName.indexOf(filterName + '-' + optionName) :
                    -1;

                if (isInUrl !== -1) {
                    selectedAttributes[attrCode] = $(this).data('option-id');

                    return false;
                }
            });
        });

        return selectedAttributes;
    }

    return function (mageSwatchRenderer) {
        // mageSwatchRenderer == Result that Magento_Swatches/js/swatch-renderer.js returns.

        // wrap _RenderControls method to parse URL and click on selected swatch
        mageSwatchRenderer.prototype._RenderControls = wrapper.wrap(
            mageSwatchRenderer.prototype._RenderControls,
            function (originalFunction) {
                originalFunction();
                this._EmulateSelected(
                    getSelectedAttributes(
                        this.element,
                        this.options.jsonSwatchConfig
                    )
                );
            }
        );

        return mageSwatchRenderer;
    };
});
