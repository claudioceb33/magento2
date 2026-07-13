/**
 * Simple AI Generator Modal for Direct Insert
 * Generates content and inserts directly without preview
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'knockout'
], function ($, modal, $t, ko) {
    'use strict';

    var modalInstance = null;
    var $modalElement = null;
    var viewModel = null;
    var insertCallback = null;

    /**
     * Build modal template with preview
     */
    function getModalTemplate() {
        return '<div class="ai-simple-generator-modal">' +
            // Input section
            '<div class="ai-input-section" data-bind="visible: !hasGeneratedContent()">' +
            '<div class="admin__fieldset">' +
            '<div class="admin__field admin__field-inline">' +
            '<label class="admin__field-label"><span>' + $t('Content Type') + '</span></label>' +
            '<div class="admin__field-control">' +
            '<select class="admin__control-select" data-bind="value: contentType">' +
            '<option value="product_description">' + $t('Product Description') + '</option>' +
            '<option value="category_description">' + $t('Category Description') + '</option>' +
            '<option value="marketing_copy">' + $t('Marketing Copy') + '</option>' +
            '<option value="blog_post">' + $t('Blog Post') + '</option>' +
            '<option value="custom">' + $t('Custom') + '</option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="admin__field admin__field-inline">' +
            '<label class="admin__field-label"><span>' + $t('Context') + '</span></label>' +
            '<div class="admin__field-control">' +
            '<input type="text" class="admin__control-text" ' +
            'data-bind="textInput: context, valueUpdate: \'afterkeydown\'" ' +
            'placeholder="' + $t('Optional context') + '">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="admin__field admin__field-wide">' +
            '<label class="admin__field-label"><span>' + $t('What would you like to generate?') + '</span></label>' +
            '<div class="admin__field-control">' +
            '<textarea class="admin__control-textarea" ' +
            'data-bind="textInput: prompt, valueUpdate: \'afterkeydown\'" ' +
            'placeholder="' + $t('e.g., Write a compelling short description for this product') + '" rows="3"></textarea>' +
            '</div>' +
            '</div>' +
            '<div class="admin__field admin__field-wide">' +
            '<div class="admin__field-control">' +
            '<button type="button" class="action-primary action-generate-ai" ' +
            'data-bind="click: generateContent, disable: isGenerating()">' +
            '<span data-bind="visible: !isGenerating()">⚡ ' + $t('Generate') + '</span>' +
            '<span data-bind="visible: isGenerating()">⏳ ' + $t('Generating...') + '</span>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            // Preview section
            '<div class="ai-preview-section" data-bind="visible: hasGeneratedContent()">' +
            '<div class="admin__field admin__field-wide">' +
            '<label class="admin__field-label"><span>' + $t('Generated Content') + '</span></label>' +
            '<div class="admin__field-control">' +
            '<div class="ai-content-preview" data-bind="html: generatedContent"></div>' +
            '</div>' +
            '</div>' +
            '<div class="admin__field admin__field-wide">' +
            '<div class="admin__field-control ai-action-buttons">' +
            '<button type="button" class="action-primary action-insert-ai" ' +
            'data-bind="click: insertContent">' +
            '✓ ' + $t('Insert Content') +
            '</button>' +
            '<button type="button" class="action-secondary action-regenerate-ai" ' +
            'data-bind="click: regenerateContent, disable: isGenerating()">' +
            '<span data-bind="visible: !isGenerating()">↻ ' + $t('Regenerate') + '</span>' +
            '<span data-bind="visible: isGenerating()">⏳ ' + $t('Regenerating...') + '</span>' +
            '</button>' +
            '<button type="button" class="action-secondary action-back-ai" ' +
            'data-bind="click: backToEdit">' +
            '← ' + $t('Back') +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    }
    
    /**
     * Clean AI response content
     */
    function cleanAiResponse(content) {
        if (!content) return '';
        
        // If it's a JSON object, extract the message field
        if (typeof content === 'object' && content.message) {
            content = content.message;
        }
        
        // If it's a JSON string, parse it
        if (typeof content === 'string' && content.trim().startsWith('{')) {
            try {
                var parsed = JSON.parse(content);
                if (parsed.message) {
                    content = parsed.message;
                }
            } catch (e) {
                // Not valid JSON, continue with string cleaning
            }
        }
        
        // Remove markdown code blocks
        content = content.replace(/```html\n?/g, '');
        content = content.replace(/```\n?/g, '');
        
        // Trim whitespace and newlines
        content = content.trim();
        
        return content;
    }

    /**
     * Detect entity context from URL
     */
    function detectEntityContext() {
        var url = window.location.href;
        var context = { type: null, id: null };

        if (url.indexOf('/catalog/product/edit/') !== -1) {
            var productMatch = url.match(/\/id\/(\d+)/);
            if (productMatch) {
                context.type = 'product';
                context.id = productMatch[1];
            }
        } else if (url.indexOf('/catalog/category/edit/') !== -1) {
            var categoryMatch = url.match(/\/id\/(\d+)/);
            if (categoryMatch) {
                context.type = 'category';
                context.id = categoryMatch[1];
            }
        } else if (url.indexOf('/cms/page/edit/') !== -1) {
            var pageMatch = url.match(/\/page_id\/(\d+)/);
            if (pageMatch) {
                context.type = 'cms_page';
                context.id = pageMatch[1];
            }
        }

        return context;
    }

    /**
     * Get default content type based on context
     */
    function getDefaultContentType() {
        var context = detectEntityContext();
        if (!context || !context.type) {
            return 'custom';
        }

        var contentTypeMap = {
            'product': 'product_description',
            'category': 'category_description',
            'cms_page': 'marketing_copy'
        };

        return contentTypeMap[context.type] || 'custom';
    }

    /**
     * Get admin URL
     */
    function getAdminUrl() {
        var currentUrl = window.location.href;
        var adminMatch = currentUrl.match(/^(https?:\/\/[^\/]+\/[^\/]+)/);
        if (adminMatch && adminMatch[1]) {
            return adminMatch[1];
        }
        return window.BASE_URL || '';
    }

    return {
        /**
         * Open the simple AI generator modal
         * @param {Function} callback - Callback function to insert content
         * @param {Object} options - Additional options
         */
        open: function (callback, options) {
            options = options || {};
            insertCallback = callback;
            var entityContext = detectEntityContext();
            var defaultContentType = options.contentType || getDefaultContentType();

            // Create view model
            viewModel = {
                prompt: ko.observable(''),
                contentType: ko.observable(defaultContentType),
                context: ko.observable(''),
                isGenerating: ko.observable(false),
                hasGeneratedContent: ko.observable(false),
                generatedContent: ko.observable(''),

                generateContent: function () {
                    if (!viewModel.prompt().trim()) {
                        alert($t('Please enter a prompt'));
                        return;
                    }

                    viewModel.isGenerating(true);

                    var requestData = {
                        content_type: viewModel.contentType(),
                        prompt: viewModel.prompt(),
                        context: viewModel.context(),
                        form_key: window.FORM_KEY
                    };

                    if (entityContext.type && entityContext.id) {
                        requestData.entity_type = entityContext.type;
                        requestData.entity_id = entityContext.id;
                    }

                    var ajaxUrl = getAdminUrl() + '/aiecontent/pagebuilder/generate';

                    $.ajax({
                        url: ajaxUrl,
                        method: 'POST',
                        dataType: 'json',
                        data: requestData,
                        timeout: 60000,
                        success: function (response) {
                            if (response.success && response.content) {
                                var cleanedContent = cleanAiResponse(response.content);
                                viewModel.generatedContent(cleanedContent);
                                viewModel.hasGeneratedContent(true);
                            } else {
                                alert($t('Error: ') + (response.message || 'Unknown error'));
                            }
                        },
                        error: function (xhr) {
                            var msg = $t('Error generating content');
                            if (xhr.status === 404) {
                                msg = $t('Generator endpoint not found');
                            }
                            alert(msg);
                        },
                        complete: function () {
                            viewModel.isGenerating(false);
                        }
                    });
                },
                
                insertContent: function () {
                    var content = viewModel.generatedContent();
                    if (content && typeof insertCallback === 'function') {
                        insertCallback(content);
                    }
                    modalInstance.closeModal();
                    
                    // Show success message
                    var $message = $('<div class="message message-success success">' +
                        '<div>' + $t('Content inserted successfully!') + '</div>' +
                        '</div>');
                    $('.page-main-actions').after($message);
                    setTimeout(function() {
                        $message.fadeOut(function() { $(this).remove(); });
                    }, 3000);
                },
                
                regenerateContent: function () {
                    viewModel.hasGeneratedContent(false);
                    viewModel.generatedContent('');
                    // Keep the prompt and generate again
                    setTimeout(function() {
                        viewModel.generateContent();
                    }, 100);
                },
                
                backToEdit: function () {
                    viewModel.hasGeneratedContent(false);
                    viewModel.generatedContent('');
                }
            };

            // Create modal element if not exists
            if (!$modalElement) {
                $modalElement = $(getModalTemplate());
                $('body').append($modalElement);

                var modalOptions = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: $t('AI Content Generator'),
                    modalClass: 'ai-simple-generator-modal-container',
                    buttons: [],
                    closed: function() {
                        // Reset state when modal is closed
                        if (viewModel) {
                            viewModel.hasGeneratedContent(false);
                            viewModel.generatedContent('');
                            viewModel.prompt('');
                            viewModel.context('');
                            viewModel.isGenerating(false);
                        }
                    }
                };

                modalInstance = modal(modalOptions, $modalElement);
            }

            // Reset state before opening
            viewModel.hasGeneratedContent(false);
            viewModel.generatedContent('');
            viewModel.isGenerating(false);

            // Apply bindings
            ko.cleanNode($modalElement[0]);
            ko.applyBindings(viewModel, $modalElement[0]);

            // Open the modal
            modalInstance.openModal();
        }
    };
});
