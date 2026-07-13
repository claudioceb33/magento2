define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'uiRegistry',
    'domReady!'
], function ($, alert, $t, registry) {
    'use strict';

    return function (config, element) {
        var buttonId = 'ai-seo-generate-btn-cms';
        var buttonClass = 'ai-generate-button';
        
        setTimeout(function() {
            if ($('#' + buttonId).length === 0) {
                var fieldset = $('[data-index="search_engine_optimisation"]').find('.admin__fieldset-wrapper-content').first();
                if (fieldset.length) {
                    var buttonHtml = '<div id="' + buttonId + '-container" style="margin-bottom: 15px;">' +
                        '<button type="button" id="' + buttonId + '" class="' + buttonClass + '">' +
                        '<span>' + $t('Generate SEO with AI') + '</span>' +
                        '</button>' +
                        '</div>';
                    fieldset.prepend(buttonHtml);
                    
                    $('#' + buttonId).on('click', function() {
                        handleGenerateSeo(config, buttonId);
                    });
                }
            }
        }, 1000);
    };
    
    function handleGenerateSeo(config, buttonId) {
        var entityId = getEntityId(config);
        
        if (!entityId) {
            window.alert('Please save the CMS page first before generating SEO content.');
            return;
        }

        showLoading(buttonId, true);

        var formKey = window.FORM_KEY || $('input[name="form_key"]').val();
        var params = new URLSearchParams();
        params.append('form_key', formKey);
        params.append('entity_type', 'cms_page');
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
                    populateFields(response.meta_title, response.meta_description, config);
                    
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
        var source = registry.get(config.provider || 'cms_page_form.cms_page_form_data_source');
        if (source && source.data) {
            return source.data.page_id || source.data.id;
        }
        return null;
    }

    function populateFields(metaTitle, metaDescription, config) {
        if (metaTitle) {
            var titleField = registry.get('cms_page_form.cms_page_form.search_engine_optimisation.meta_title');
            if (titleField && titleField.value) {
                titleField.value(metaTitle);
            }
        }

        if (metaDescription) {
            var descField = registry.get('cms_page_form.cms_page_form.search_engine_optimisation.meta_description');
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
