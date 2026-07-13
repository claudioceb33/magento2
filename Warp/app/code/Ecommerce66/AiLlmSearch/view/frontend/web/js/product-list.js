/**
 * Product list handler - Manual AJAX add to cart without redirects
 */
define([
    'jquery',
    'mage/translate',
    'slick'
], function ($, $t) {
    'use strict';

    function resolveFormKey() {
        if (typeof window.FORM_KEY !== 'undefined' && window.FORM_KEY) {
            return String(window.FORM_KEY);
        }

        try {
            var match = document.cookie.match(new RegExp('(^|; )form_key=([^;]*)'));

            if (match && match[2]) {
                return decodeURIComponent(match[2]);
            }
        } catch (e) {
            console.warn('Unable to read form key from cookie', e);
        }

        return '';
    }

    return function (config, element) {
        console.log('=== PRODUCT LIST WIDGET CALLED ===');

        var domElement = element || this;
        if (!domElement) {
            return;
        }

        var options = config || {};

        var $element = $(domElement);
        if (!$element.length) {
            return;
        }

        if ($element.data('productListInitialized')) {
            return;
        }

        $element.data('productListInitialized', true);
        
        var sliderDefaults = {
            slidesToShow: 3,
            slidesToScroll: 1,
            infinite: false,
            dots: true,
            arrows: true,
            adaptiveHeight: true,
            responsive: [
                {
                    breakpoint: 1280,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 2
                    }
                },
                {
                    breakpoint: 520,
                    settings: {
                        slidesToShow: 1
                    }
                }
            ]
        };

        var sliderConfig = $.extend(true, {}, sliderDefaults, options.slider || {});
        var $productList = $element.find('ol.products');
        var sliderEnabled = false;

        function enableSlider() {
            if (!$productList.length) {
                return;
            }
            if (typeof $.fn.slick !== 'function') {
                console.warn('Slick slider not available');
                return;
            }
            if ($productList.hasClass('slick-initialized')) {
                return;
            }
            $productList.slick(sliderConfig);
            sliderEnabled = true;
        }

        function disableSlider() {
            if (!$productList.length) {
                return;
            }
            if ($productList.hasClass('slick-initialized')) {
                $productList.slick('unslick');
            }
            sliderEnabled = false;
        }

        $element.data('productList', {
            enableSlider: enableSlider,
            disableSlider: disableSlider,
            isSliderEnabled: function () {
                return sliderEnabled;
            }
        });
        
        function handleSubmit(e) {
            var $form = $(this);
            var $button = $form.find('button[type="submit"]').first();

            e.preventDefault();
            e.stopImmediatePropagation();

            console.log('Form submit intercepted');

            var formData = new FormData(this);
            var formKey = resolveFormKey();
            var actionUrl = $form.attr('action');

            if (formKey) {
                $form.find('input[name="form_key"]').val(formKey);
                formData.set('form_key', formKey);
            }

            console.log('Submitting to:', actionUrl);

            if ($button.length) {
                $button.prop('disabled', true).addClass('disabled');
                $button.find('span').text($t('Adding...'));
            }

            $.ajax({
                url: actionUrl,
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log('Add to cart response:', response);

                    if (response.minicart) {
                        $('[data-block="minicart"]').replaceWith(response.minicart);
                        $('[data-block="minicart"]').trigger('contentUpdated');
                    }

                    if (response.messages) {
                        $('[data-placeholder="messages"]').html(response.messages);
                    }

                    if ($button.length) {
                        $button.find('span').text($t('Added'));

                        setTimeout(function() {
                            $button.prop('disabled', false).removeClass('disabled');
                            $button.find('span').text($t('Add to Cart'));
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Add to cart error:', status, error);
                    console.error('Response:', xhr.responseText);

                    var errorMsg = $t('Could not add item to cart.');
                    try {
                        var errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMsg = errorResponse.message;
                        }
                    } catch(e) {
                        // noop
                    }

                    var errorHtml = '<div class="message error"><div>' + errorMsg + '</div></div>';
                    $('[data-placeholder="messages"]').html(errorHtml);

                    if ($button.length) {
                        $button.prop('disabled', false).removeClass('disabled');
                        $button.find('span').text($t('Add to Cart'));
                    }
                }
            });

            return false;
        }

        $element.off('submit.e66ProductList')
            .on('submit.e66ProductList', 'form[data-role="tocart-form"]', handleSubmit);
        
        console.log('Add to cart initialization complete');
    };
});
