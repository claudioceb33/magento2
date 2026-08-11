define(['jquery'], function ($) {
    'use strict';

    function normalizePrice(value) {
        const amount = parseFloat(value);

        return Number.isFinite(amount) ? amount : null;
    }

    function pushToDataLayer(data) {
        window.dataLayer = window.dataLayer || [];
        if (data && data.event) {
            window.dataLayer.push(data);
        }
    }

    function getPriceFromPriceBox(root) {
        const priceBox = $(root).find('[data-role=priceBox]').data('mage-priceBox');

        if (!priceBox || !priceBox.options || !priceBox.options.prices) {
            return null;
        }

        return normalizePrice(priceBox.options.prices.finalPrice?.amount);
    }

    function getPriceFromDataAttributes(root) {
        const priceWrapper = root.querySelector(
            '.price-final_price .price-wrapper[data-price-amount], ' +
            '[data-price-type="finalPrice"] .price-wrapper[data-price-amount], ' +
            '.special-price .price-wrapper[data-price-amount], ' +
            '.price-wrapper[data-price-amount]'
        );

        if (!priceWrapper) {
            return null;
        }

        return normalizePrice(priceWrapper.getAttribute('data-price-amount'));
    }

    function getPriceFromText(root) {
        const priceElement = root.querySelector(
            '.price-final_price .price, ' +
            '[data-price-type="finalPrice"] .price, ' +
            '.special-price .price, ' +
            '.price'
        );

        if (!priceElement) {
            return null;
        }

        const rawPrice = priceElement.textContent.replace(/[^0-9,.-]/g, '');
        const normalizedPrice = rawPrice.indexOf(',') > -1 && rawPrice.indexOf('.') > -1
            ? rawPrice.replace(/\./g, '').replace(',', '.')
            : rawPrice.replace(',', '.');

        return normalizePrice(normalizedPrice);
    }

    function getEventItemPrice(data) {
        const itemPrice = data?.ecommerce?.items?.[0]?.price ?? data?.productInfo?.[0]?.price;

        return normalizePrice(itemPrice);
    }

    function getMagentoPrice(root) {
        try {
            const priceFromPriceBox = getPriceFromPriceBox(root);

            if (priceFromPriceBox !== null) {
                return priceFromPriceBox;
            }
        } catch (e) {
            void e;
        }

        const priceFromDataAttributes = getPriceFromDataAttributes(root);

        if (priceFromDataAttributes !== null) {
            return priceFromDataAttributes;
        }

        return getPriceFromText(root);
    }

    function getProductRoot(form) {
        return form.closest('li.item.product')
            || form.closest('.product-item-info')
            || form.closest('.product-info-main')
            || document;
    }

    function getSwatchContainer(root) {
        const $root = $(root);

        return $root.find('[data-role=swatch-options], [data-role^="swatch-option-"], .swatch-opt, .swatch-opt-wrapper').first();
    }

    function getSwatchRenderer(root) {
        const $swatchContainer = getSwatchContainer(root);

        if (!$swatchContainer.length) {
            return null;
        }

        return $swatchContainer.data('mage-SwatchRenderer')
            || $swatchContainer.data('mageSwatchRenderer')
            || null;
    }

    function getSwatchJsonConfig(root) {
        const swatchRenderer = getSwatchRenderer(root);

        if (swatchRenderer && swatchRenderer.options && swatchRenderer.options.jsonConfig) {
            return swatchRenderer.options.jsonConfig;
        }

        const $swatchContainer = getSwatchContainer(root);
        const swatchOptions = $swatchContainer.attr('data-swopt');

        if (!swatchOptions) {
            return null;
        }

        try {
            return JSON.parse(swatchOptions).jsonConfig || null;
        } catch (e) {
            return null;
        }
    }

    function normalizeOptionValues(optionValues) {
        if (!Array.isArray(optionValues)) {
            return [];
        }

        return optionValues
            .map(function (value) {
                return String(value);
            })
            .sort();
    }

    function resolveSimpleProductIdFromProductInfo(productInfo, jsonConfig) {
        const selectedProductInfo = Array.isArray(productInfo) ? productInfo[0] : null;
        const optionValues = normalizeOptionValues(selectedProductInfo?.optionValues);
        const productIndex = jsonConfig?.index || {};

        if (!optionValues.length) {
            return null;
        }

        return Object.keys(productIndex).find(function (productId) {
            const indexedValues = normalizeOptionValues(Object.values(productIndex[productId] || {}));

            return indexedValues.length === optionValues.length
                && indexedValues.every(function (value, index) {
                    return value === optionValues[index];
                });
        }) || null;
    }

    function getSelectedSimpleProductIdFromDom(root) {
        const childId = root.querySelector('.price-final_price span[data-child-id]')?.getAttribute('data-child-id');

        if (childId) {
            return childId;
        }

        return root.querySelector('.product-item-name a[data-child-pid]')?.getAttribute('data-child-pid') || null;
    }

    function getSelectedSimpleProductId(form, root, data) {
        const selectedSimpleProduct = form.querySelector('[name="selected_configurable_option"]')?.value;

        if (selectedSimpleProduct) {
            return selectedSimpleProduct;
        }

        const jsonConfig = getSwatchJsonConfig(root);
        const resolvedFromProductInfo = resolveSimpleProductIdFromProductInfo(data?.productInfo, jsonConfig);

        if (resolvedFromProductInfo) {
            return resolvedFromProductInfo;
        }

        const swatchRenderer = getSwatchRenderer(root);

        if (swatchRenderer && typeof swatchRenderer.getProduct === 'function') {
            const simpleProductId = swatchRenderer.getProduct();

            if (simpleProductId) {
                return simpleProductId;
            }
        }

        return getSelectedSimpleProductIdFromDom(root);
    }

    function getSelectedSimpleData(root, simpleProductId) {
        const jsonConfig = getSwatchJsonConfig(root);
        const dynamicConfig = jsonConfig?.dynamic || {};
        const optionPrices = jsonConfig?.optionPrices || {};
        const fallbackPrice = getMagentoPrice(root);

        if (!simpleProductId) {
            return {
                sku: null,
                name: null,
                price: fallbackPrice
            };
        }

        return {
            sku: dynamicConfig?.sku?.[simpleProductId]?.value || null,
            name: dynamicConfig?.name?.[simpleProductId]?.value || null,
            price: normalizePrice(optionPrices?.[simpleProductId]?.finalPrice?.amount) ?? fallbackPrice
        };
    }

    /**
     * Extracts product data from PDP or product listing cards.
     */
    function getProductData(form, data) {
        const root = getProductRoot(form);
        const simpleProductId = getSelectedSimpleProductId(form, root, data);
        const selectedSimpleData = getSelectedSimpleData(root, simpleProductId);
        const qty = form.querySelector('input[name="qty"]')?.value || 1;
        const fallbackName = root.querySelector('.product-item-name a, .product-item-name .product-item-link, .page-title .base')?.innerText.trim() || null;
        const brand = root.querySelector('.product-item-brand a, .product.attribute.brand a')?.innerText.trim() || null;
        
        // Try to get category from breadcrumbs or page title
        const breadcrumbCategory = $('.breadcrumbs .item:not(.product):last a, .breadcrumbs .item:not(.product):last strong').text().trim();
        const pageTitle = $('.page-title .base').text().trim();
        const category = root.querySelector('.product-item-category a')?.innerText.trim() || breadcrumbCategory || pageTitle || 'category';

        return {
            item_id: selectedSimpleData.sku || data.sku || null,
            item_name: selectedSimpleData.name || fallbackName,
            item_brand: brand,
            item_category: category,
            currency: 'ARS',
            price: selectedSimpleData.price ?? getEventItemPrice(data),
            quantity: parseFloat(qty) || 1
        };
    }

    function handleAddToCart() {
        $(document).on('ajax:addToCart', function (event, data) {
            try {
                console.log('GTM add to cart mixin');
                const form = data.form[0] || data.form;
                const productData = getProductData(form, data);

                if (productData.item_id) {
                    pushToDataLayer({ ecommerce: null });
                    pushToDataLayer({
                        event: 'add_to_cart',
                        ecommerce: {
                            currency: productData.currency,
                            value: productData.price !== null ? productData.price * productData.quantity : undefined,
                            items: [productData]
                        }
                    });
                }
            } catch (e) {
                console.warn('[GTM] Error procesando ajax:addToCart:', e);
            }
        });
    }

    function trackShippingAndPayment() {
        let shippingPushed = false;
        let paymentPushed = false;

        $(document).on('click', '#shipping-method-buttons-container [data-role="opc-continue"]', function () {
            if (shippingPushed) return;
            shippingPushed = true;

            const selected = $('input[type="radio"][id^="s_method_"]:checked').val();

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'add_shipping_info',
                ecommerce: {
                    shipping_tier: selected || 'unknown',
                    items: window.CebGtmCartItems || []
                }
            });

            console.log('[GTM] add_shipping_info', selected);
        });

        $(document).on('change', 'input[name="payment[method]"]', function () {
            if (paymentPushed) return;
            paymentPushed = true;

            const selected = $('input[name="payment[method]"]:checked').val();

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'add_payment_info',
                ecommerce: {
                    payment_type: selected || 'unknown',
                    items: window.CebGtmCartItems || []
                }
            });
            console.log('[GTM] add_payment_info', selected);
        });
    }

    return function (config) {
        if (config && config.event) {
            pushToDataLayer(config);
        }

        handleAddToCart();
        trackShippingAndPayment();
    };
});
