/**
 * AI Content Preview Component
 */
define([
    'Magento_PageBuilder/js/content-type/preview',
    'Magento_PageBuilder/js/content-type-menu/hide-show-option',
    'jquery',
    'knockout',
    'mage/url',
    'Ecommerce66_AiContent/js/pagebuilder/content-normalizer'
], function (PreviewBase, HideShowOption, $, ko, urlBuilder, normalizeContent) {
    'use strict';

    var HTML_RESPONSE_INSTRUCTION = '\n\nFORMATO DE RESPUESTA: responde únicamente con HTML válido y sin explicaciones adicionales.';

    function resolveAdminBaseUrl() {
        var adminMatch = window.location.href.match(/(.*\/admin[^\/]*)\//);
        var base = adminMatch && adminMatch[1]
            ? adminMatch[1] + '/'
            : (window.BASE_URL || (window.location.origin + '/'));

        if (base.slice(-1) !== '/') {
            base += '/';
        }

        return base;
    }

    /**
     * AI Content preview component
     *
     * @param {ContentTypeInterface} contentType
     * @param {ContentTypeConfigInterface} config
     * @param {ObservableUpdater} observableUpdater
     * @constructor
     */
    function Preview(contentType, config, observableUpdater) {
        PreviewBase.call(this, contentType, config, observableUpdater);

        var self = this;

        this.isGenerating = ko.observable(false);
        this.generatedContent = ko.observable('');

        var persistedContent = this.contentType.dataStore.get('generated_content');
        if (persistedContent) {
            this.generatedContent(persistedContent);
        }

        this.contentType.dataStore.subscribe(function (value) {
            self.generatedContent(value || '');
        }, 'generated_content');
    }

    Preview.prototype = Object.create(PreviewBase.prototype);
    Preview.prototype.constructor = Preview;

    /**
     * Return an array of options
     *
     * @returns {OptionsInterface}
     */
    Preview.prototype.retrieveOptions = function () {
        var options = PreviewBase.prototype.retrieveOptions.call(this);

        options.hideShow = new HideShowOption({
            preview: this,
            icon: HideShowOption.showIcon,
            title: HideShowOption.showText,
            action: this.onOptionVisibilityToggle,
            classes: ["hide-show-content-type"],
            sort: 40
        });

        return options;
    };

    /**
     * Detect entity context from current URL
     */
    Preview.prototype.detectEntityContext = function () {
        var currentUrl = window.location.href;
        var context = {
            type: null,
            id: null
        };
        
        // Detect product edit page
        if (currentUrl.indexOf('/catalog/product/edit/') !== -1) {
            var productIdMatch = currentUrl.match(/\/catalog\/product\/edit\/id\/(\d+)/);
            if (productIdMatch) {
                context.type = 'product';
                context.id = productIdMatch[1];
            }
        }
        // Detect category edit page
        else if (currentUrl.indexOf('/catalog/category/edit/') !== -1) {
            var categoryIdMatch = currentUrl.match(/\/catalog\/category\/edit\/id\/(\d+)/);
            if (categoryIdMatch) {
                context.type = 'category';
                context.id = categoryIdMatch[1];
            }
        }
        // Detect CMS page edit
        else if (currentUrl.indexOf('/cms/page/edit/') !== -1) {
            var pageIdMatch = currentUrl.match(/\/cms\/page\/edit\/page_id\/(\d+)/);
            if (pageIdMatch) {
                context.type = 'cms_page';
                context.id = pageIdMatch[1];
            }
        }
        
        return context;
    };

    /**
     * Generate AI content via AJAX
     */
    Preview.prototype.generateContent = function (data, event) {
        var self = this;

        var $container = $(event.currentTarget).closest('.ai-inline-editor');
        var promptValue = $container.find('.ai-prompt-input').val();
        var contentType = $container.find('.ai-select').val();
        var contextValue = $container.find('.ai-context-input').val();

        this.contentType.dataStore.set('prompt', promptValue);
        this.contentType.dataStore.set('content_type', contentType);
        this.contentType.dataStore.set('context', contextValue);
        
        if (!promptValue || promptValue.trim() === '') {
            alert('Please enter a prompt before generating content.');
            return;
        }
        
        if (this.isGenerating()) {
            return;
        }
        
        this.isGenerating(true);

        var entityContext = this.detectEntityContext();
        if (typeof urlBuilder.setBaseUrl === 'function') {
            urlBuilder.setBaseUrl(resolveAdminBaseUrl());
        }
        var ajaxUrl = urlBuilder.build('aiecontent/pagebuilder/generate');
        
        var apiPrompt = promptValue + HTML_RESPONSE_INSTRUCTION;

        var requestData = {
            content_type: contentType || 'custom',
            prompt: apiPrompt,
            context: contextValue || '',
            form_key: window.FORM_KEY
        };
        
        if (entityContext.type && entityContext.id) {
            requestData.entity_type = entityContext.type;
            requestData.entity_id = entityContext.id;
        }
        
        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            dataType: 'json',
            data: requestData,
            timeout: 60000, // 60 seconds timeout
            success: function (response) {
                if (response.success && response.content) {
                    var normalized = normalizeContent(response.content, {forceHtml: true});
                    self.contentType.dataStore.set('generated_content', normalized);
                    self.generatedContent(normalized);
                } else {
                    alert(response.message || 'Failed to generate content');
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = 'An error occurred while generating content.';
                if (xhr.status === 404) {
                    errorMessage = 'Controller not found. Please check your routes.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Check Magento logs.';
                } else if (status === 'timeout') {
                    errorMessage = 'Request timed out. Try again.';
                }
                
                alert(errorMessage + '\nCheck browser console for details.');
            },
            complete: function () {
                self.isGenerating(false);
            }
        });
    };

    /**
     * Copy generated content to clipboard
     */
    Preview.prototype.copyContent = function () {
        var content = this.generatedContent();
        if (content) {
            var temp = $('<textarea>').val(content).appendTo('body').select();
            document.execCommand('copy');
            temp.remove();
            alert('Content copied to clipboard!');
        } else {
            alert('No content to copy');
        }
    };

    /**
     * Insert generated content as text block into PageBuilder
     */
    Preview.prototype.insertAsTextBlock = function () {
        var self = this;
        var content = this.generatedContent();
        
        if (!content) {
            alert('No content to insert');
            return;
        }
        
        // Import the text content type factory
        require([
            'Magento_PageBuilder/js/content-type-factory',
            'Magento_PageBuilder/js/config'
        ], function (createContentType, config) {
            
            // Get the parent container (should be row or column)
            var parent = self.contentType.parentContentType;
            
            if (!parent) {
                alert('Error: Cannot find parent container. Make sure the AI Content block is inside a row or column.');
                return;
            }

            // Get text content type config
            var textConfig = config.getContentTypeConfig('text');
            
            // Create text content type with the generated content
            var textData = {
                main: {
                    html: content
                }
            };

            createContentType(
                textConfig,
                parent,
                self.contentType.stageId,
                textData
            ).then(function (textContentType) {
                // Get the index of current AI content block
                var currentIndex = parent.children().indexOf(self.contentType);
                
                // Add the new text block after the AI content
                if (currentIndex >= 0) {
                    parent.addChild(textContentType, currentIndex + 1);
                } else {
                    parent.addChild(textContentType);
                }
                
                // Clear the generated content
                self.generatedContent('');
                self.contentType.dataStore.set('generated_content', '');
                alert('Content inserted as text block!');
                
            }).catch(function (error) {
                console.error('Error creating text content type:', error);
                console.error('Error stack:', error.stack);
                alert('Error inserting content: ' + error.message);
            });
        });
    };

    return Preview;
});
