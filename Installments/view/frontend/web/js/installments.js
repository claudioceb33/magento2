define([
    "jquery",
    "Magento_Swatches/js/swatch-renderer"
], function ($) {
    'use strict';
    $.widget('mage.pinstallments',{
        options: {},
        _create: function () {
            var $container = this.element;
            var isInstallmentNode = $container.hasClass('product-installments');
            var $scope = $container;

            if (this.options.id) {
                $container = $('#' + this.options.id);
                isInstallmentNode = $container.hasClass('product-installments');
            }

            if (isInstallmentNode) {
                $scope = $container.closest('.product-item-info, .product-info-main');
            }

            function formatAmount(amount) {
                return '$' + amount.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function getInstallments() {
                if (isInstallmentNode) {
                    return $container;
                }

                return $scope.find('.product-installments');
            }

            function resolvePrice($installment) {
                var productId = $installment.data('pid');
                var price = $('#product-price-' + productId).data('price-amount');

                if (!isNaN(price)) {
                    return parseFloat(price);
                }

                price = $installment.closest('.product-item-details').find('*[data-price-type="finalPrice"]').data('price-amount');

                return isNaN(price) ? 0 : parseFloat(price);
            }

            function updateInstallment($installment, price) {
                var qty = parseInt($installment.attr('data-installments'), 10) || 0;
                var rate = parseFloat($installment.attr('data-rate')) || 0;
                var disclaimer = $installment.attr('data-disclaimer') || '';
                var amount = qty > 0 ? (price * rate) / qty : 0;

                if (qty <= 1 || rate < 1 || !price) {
                    $installment.css('visibility', 'collapse');
                    return;
                }

                $installment.css('visibility', 'visible');
                $installment.find('.qty').text(qty);
                $installment.find('.amount').text(formatAmount(amount));
                $installment.find('.interest-free').toggleClass('hidden', rate !== 1);
                $installment.find('.disclaimer-mark').toggleClass('hidden', disclaimer.length === 0);
                $installment.find('.disclaimer').toggleClass('hidden', disclaimer.length === 0).text(disclaimer);
            }

            function refreshInstallments() {
                getInstallments().each(function () {
                    var $installment = $(this);

                    updateInstallment($installment, resolvePrice($installment));
                });
            }

            refreshInstallments();

            $(document).on('swatch.initialized', function () {
                refreshInstallments();
            });

            $scope.on('click', '.swatch-option', function () {
                var $installment = $(this).closest('.product-item-info').find('.product-installments');

                if (!$installment.length) {
                    return;
                }

                setTimeout(function () {
                    updateInstallment($installment, resolvePrice($installment));
                }, 50);
            });

            $scope.on('change', '.swatch-price', function () {
                var $installment = $(this).closest('.product-installments');

                updateInstallment($installment, parseFloat($(this).val()) || 0);
            });
        }
    });
    return $.mage.pinstallments;
});
