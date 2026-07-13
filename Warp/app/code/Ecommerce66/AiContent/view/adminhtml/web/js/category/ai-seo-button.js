/**
 * AI SEO Button for Category Edit
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'uiRegistry'
], function($, alert, $t, registry) {
    'use strict';
    
    return function(config, element) {
        var buttonId = 'ai-seo-generate-btn-category';
        
        setTimeout(function() {
            initButton(config, buttonId);
        }, 2000);
    };
    
    function initButton(config, buttonId) {
        if ($('#' + buttonId).length > 0) {
            return;
        }
        
        var fieldset = $('[data-index="search_engine_optimization"]').find('.admin__fieldset-wrapper-content').first();
        if (fieldset.length) {
            var buttonHtml = '<div id="' + buttonId + '-container" style="margin-bottom: 15px;">' +
                '<button type="button" id="' + buttonId + '" class="ai-generate-button">' +
                '<span>' + $t('Generate SEO with AI') + '</span>' +
                '</button>' +
                '</div>';
            fieldset.prepend(buttonHtml);
            
            $('#' + buttonId).on('click', function() {
                handleGenerateSeo(config, buttonId);
            });
        }
    }
    
    function handleGenerateSeo(config, buttonId) {
        var entityId = getEntityId(config);
        
        if (!entityId) {
            alert({
                title: $t('Warning'),
                content: $t('Please save the category first before generating SEO content.')
            });
            return;
        }

        showLoading(buttonId, true);

        var formKey = window.FORM_KEY || $('input[name="form_key"]').val();
        var params = new URLSearchParams();
        params.append('form_key', formKey);
        params.append('entity_type', 'category');
        params.append('entity_id', entityId);
        params.append('content_type', 'seo');

        $.ajax({
            url: config.generate_url,
            type: 'POST',
            dataType: 'json',
            data: params.toString(),
            contentType: 'application/x-www-form-urlencoded',
            success: function (response) {
                showLoading(buttonId, false);
                
                if (response.success) {
                    populateFields(response.meta_title, response.meta_description);
                    
                    $('#' + buttonId).addClass('ai-generate-success');
                    setTimeout(function() {
                        $('#' + buttonId).removeClass('ai-generate-success');
                    }, 600);
                    
                    alert({
                        title: $t('Success'),
                        content: $t('SEO content generated successfully!')
                    });
                } else {
                    alert({
                        title: $t('Error'),
                        content: response.message || $t('Failed to generate content.')
                    });
                }
            },
            error: function (xhr, status, error) {
                showLoading(buttonId, false);
                alert({
                    title: $t('Error'),
                    content: $t('An error occurred: ') + error
                });
            }
        });
    }

    function getEntityId(config) {
        var source = registry.get('category_form.category_form_data_source');
        if (source && source.data) {
            return source.data.entity_id || source.data.id;
        }
        return null;
    }

    function populateFields(metaTitle, metaDescription) {
        if (metaTitle) {
            var titleField = registry.get('category_form.category_form.search_engine_optimization.meta_title');
            if (titleField && titleField.value) {
                titleField.value(metaTitle);
            }
        }

        if (metaDescription) {
            var descField = registry.get('category_form.category_form.search_engine_optimization.meta_description');
            if (descField && descField.value) {
                descField.value(metaDescription);
            }
        }
    }

    function showLoading(buttonId, show) {
        var button = $('#' + buttonId);
        if (show) {
            button.prop('disabled', true).addClass('loading');
            button.find('span').text($t('Generating...'));
        } else {
            button.prop('disabled', false).removeClass('loading');
            button.find('span').text($t('Generate SEO with AI'));
        }
    }
});
