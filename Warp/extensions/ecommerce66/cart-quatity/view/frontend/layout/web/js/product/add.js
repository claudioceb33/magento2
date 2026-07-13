define([
    'jquery'
], function($) {

    $.widget('ecomm66.customQty', {
        options: {
            pid: 'empty',
            searchPattern: '[data-product-pid={pid}]',
            isProductPage: false,
            miniQuoteListSelector: '[data-block=\'custom_qty\']'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            this._bind();
            if (!this.options.isProductPage) {
                this._moveButton();
            }
            this._enableButton();
            var $this = this;
            $($this.element).on( 'click', '.increaseQty, .decreaseQty', function(event) {
                event.preventDefault()
                // Get current quantity values
                var qty = $( this ).closest( '.qty-field-wrapper' ).find( '.qty' );
                var val   = parseFloat(qty.val());
                var max = parseFloat(qty.attr( 'max' ));
                var min = parseFloat(qty.attr( 'min' ));
                var step = parseFloat(qty.attr( 'step' ));

                // Change the value if plus or minus
                if ( $( this ).is( '.increaseQty' ) ) {
                    if ( max && ( max <= val ) ) {
                        qty.val( max );
                    }
                    else {
                        qty.val( val + step );
                    }
                }
                else {
                    if ( min && ( min >= val ) ) {
                        qty.val( min );
                    }
                    else if ( val > 1 ) {
                        qty.val( val - step );
                    }
                }

            });
        },

        /**
         * @private
         */
        _bind: function () {


            this._on({
                'click': '_onButtonClick'
            });
        },

        _onButtonClick: function(){

        },

        /**
         * Move button
         * @private
         */
        _moveButton: function () {
            var buttonContainer = $(this.element);
            var parent = buttonContainer.parent().parent();
            var to_cart = $(parent).find('.tocart');
            buttonContainer.insertBefore(to_cart);
        },

        /**
         * Enable button
         * @private
         */
        _enableButton: function () {
            this.element.removeAttr('disabled');
        },

        /**
         * Disable button
         * @private
         */
        _disableButton: function () {
            this.element.attr('disabled', 'disabled');
        }
    });

    return $.ecomm66.customQty;
});
