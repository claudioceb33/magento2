define([], function () {
    'use strict';

    return function (target) {
        return {
            complete: function (response) {
                // Ejecutar la original
                target.complete.apply(this, arguments);

                try {
                    const form = this.form;
                    const sku = form.querySelector('[data-product-sku]')?.dataset.productSku;
                    const qty = form.querySelector('input[name="qty"]')?.value || 1;

                    const itemId = sku || this.productSku || null;

                    if (itemId) {
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            event: 'add_to_cart',
                            ecommerce: {
                                items: [{
                                    item_id: itemId,
                                    quantity: parseInt(qty, 10)
                                }]
                            }
                        });
                    }
                } catch (e) {
                    console.warn('GTM mixin add_to_cart error', e);
                }
            }
        };
    };
});
