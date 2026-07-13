/**
 * AI Text Generator Modal for PageBuilder Forms
 * Opens a modal to generate content and insert into text/html blocks
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'knockout',
    'text!Ecommerce66_AiContent/template/pagebuilder/text-generator-modal.html',
    'Ecommerce66_AiContent/js/pagebuilder/content-normalizer'
], function ($, modal, $t, ko, modalTemplate, normalizeContent) {
    'use strict';

    var modalInstance = null;
    var $modalElement = null;
    var viewModel = null;

    return {
        /**
         * Open the AI generator modal
         * @param {Object} preview - The content type preview instance
         * @param {Object} options - Additional options (formComponent, targetField, defaultContentType)
         */
        open: function (preview, options) {
            options = options || {};
            var self = this;

            if (!preview || !preview.contentType) {
                alert($t('Unable to access content type. Please try again.'));
                return;
            }

            // Detect entity context
            var entityContext = this.detectEntityContext();
            
            // Determine default content type based on context
            var defaultContentType = options.defaultContentType || this.getDefaultContentType(entityContext);

            // Create view model
            viewModel = {
                prompt: ko.observable(''),
                promptPlaceholder: $t('Enter your prompt... (e.g., "Write a professional product description")'),
                contentType: ko.observable(defaultContentType),
                context: ko.observable(''),
                contextPlaceholder: $t('Optional context (product name, tone, etc.)'),
                isGenerating: ko.observable(false),
                generatedContent: ko.observable(''),
                entityContext: entityContext,

                generateContent: function () {
                    if (!viewModel.prompt().trim()) {
                        alert($t('Please enter a prompt'));
                        return;
                    }

                    viewModel.isGenerating(true);
                    viewModel.generatedContent('');

                    var requestData = {
                        content_type: viewModel.contentType(),
                        prompt: viewModel.prompt(),
                        context: viewModel.context(),
                        form_key: window.FORM_KEY
                    };

                    if (viewModel.entityContext.type && viewModel.entityContext.id) {
                        requestData.entity_type = viewModel.entityContext.type;
                        requestData.entity_id = viewModel.entityContext.id;
                    }

                    var ajaxUrl = self.getAdminUrl() + '/aiecontent/pagebuilder/generate';

                    $.ajax({
                        url: ajaxUrl,
                        method: 'POST',
                        dataType: 'json',
                        data: requestData,
                        timeout: 60000,
                        success: function (response) {
                            if (response.success && response.content) {
                                var normalized = normalizeContent(response.content, {forceHtml: true});
                                if (!normalized) {
                                    alert($t('The AI service did not return HTML content. Try adjusting your prompt.'));
                                    viewModel.generatedContent('');
                                } else {
                                    viewModel.generatedContent(normalized);
                                }
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
                    if (!content) {
                        alert($t('No content to insert'));
                        return;
                    }

                    if (options.formComponent && options.targetField) {
                        options.formComponent.source.set('data.' + options.targetField, content);
                    }

                    if (preview && preview.contentType && preview.contentType.dataStore) {
                        preview.contentType.dataStore.set('content', content);
                        preview.contentType.dataStore.set('html', content);
                    }

                    if (typeof window.tinyMCE !== 'undefined') {
                        var editor = window.tinyMCE.activeEditor;
                        if (editor && typeof editor.getContent === 'function') {
                            editor.setContent(content);
                            editor.fire('change');
                        }
                    }

                    $modalElement.modal('closeModal');
                },

                copyContent: function () {
                    var content = viewModel.generatedContent();
                    if (!content) {
                        return;
                    }

                    var $temp = $('<textarea>');
                    $('body').append($temp);
                    $temp.val(content).select();
                    document.execCommand('copy');
                    $temp.remove();

                    alert($t('Content copied to clipboard!'));
                }
            };

            // Create modal element if not exists
            if (!$modalElement) {
                $modalElement = $('<div class="ai-generator-modal-root"></div>').appendTo(document.body);
                $modalElement.html(modalTemplate);

                var modalOptions = {
                    type: 'slide',
                    title: $t('AI Content Generator'),
                    buttons: [],
                    modalClass: 'ai-generator-modal-container',
                    focus: '.ai-prompt-input'
                };

                modal(modalOptions, $modalElement);
            }

            // Apply bindings on inner content class
            var targetNode = $modalElement.find('.ai-text-generator-modal-content')[0] || $modalElement[0];
            ko.cleanNode(targetNode);
            ko.applyBindings(viewModel, targetNode);

            // Open the modal
            $modalElement.modal('openModal');
        },

        /**
         * Get admin URL by detecting it from current page
         */
        getAdminUrl: function () {
            var currentUrl = window.location.href;
            var adminMatch = currentUrl.match(/^(https?:\/\/[^\/]+\/[^\/]+)/);
            if (adminMatch && adminMatch[1]) {
                return adminMatch[1];
            }
            return window.BASE_URL || '';
        },

        /**
         * Detect entity context from URL
         */
        detectEntityContext: function () {
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
        },

        /**
         * Get default content type based on entity context
         */
        getDefaultContentType: function (entityContext) {
            if (!entityContext || !entityContext.type) {
                return 'custom';
            }

            var contentTypeMap = {
                'product': 'product_description',
                'category': 'category_description',
                'cms_page': 'marketing_copy'
            };

            return contentTypeMap[entityContext.type] || 'custom';
        }
    };
});
