/**
 * Copyright © Ecommerce66. All rights reserved.
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    $.widget('ecommerce66.aiAssistant', {
        options: {
            aiQueryUrl: '',
            productListUrl: '',
            recommendationsUrl: '',
            showRecommendations: true,
            widgetTitle: '',
            placeholderText: '',
            relatedExamples: []
        },

        _create: function () {
            this.history = [];
            this.historyLimit = 8;
            this.showRecommendations = this.options.showRecommendations !== false;
            this.historyWrapper = this.element.find('[data-role="history-wrapper"]');
            this.historyList = this.element.find('[data-role="history-list"]');
            this.historyClear = this.element.find('[data-role="history-clear"]');
            this.examplesWrapper = this.element.find('[data-role="examples-wrapper"]');
            this.examplesText = this.element.find('[data-role="examples-text"]');
            this.examplesTimers = [];
            this.resultsArea = this.element.find('.ai-results-area');
            this.recommendationsWrapper = this.element.find('[data-role="recommendations-wrapper"]');
            this.recommendationsContainer = this.element.find('[data-role="recommendations-container"]');
            this.recommendationsTitle = this.element.find('[data-role="recommendations-title"]');
            this.recommendationsMeta = this.element.find('[data-role="recommendations-meta"]');

            this._bind();
            this._initHistory();
            this._initExamples();
        },

        _bind: function () {
            var self = this;
            
            this.element.find('.ai-search-form').on('submit.aiAssistant', function (e) {
                e.preventDefault();
                self._submitQuery();
            });

            this.element.find('.ai-clear').on('click.aiAssistant', function () {
                self._clearResults();
            });
        },

        _initHistory: function () {
            var self = this;

            if (this.historyClear.length) {
                this.historyClear.on('click.aiAssistant', function () {
                    self.history = [];
                    self._renderHistory();
                });
            }

            if (this.historyList.length) {
                this.historyList.on('click.aiAssistant', '.history-chip', function (event) {
                    event.preventDefault();
                    var query = $(this).data('query');
                    if (!query) {
                        return;
                    }

                    self.element.find('.ai-prompt').val(query);
                    self._submitQuery();
                });
            }
        },

        _submitQuery: function () {
            var self = this;
            var prompt = this.element.find('.ai-prompt').val().trim();

            if (!prompt) {
                this._showMessage($t('Please enter a search query.'), true);
                return;
            }

            this.lastPrompt = prompt;
            this._registerHistoryEntry(prompt);
            this._clearResults();
            this._showLoading(true);
            this._setSubmitLoading(true);

            var payload = {
                prompt: prompt
            };

            if (this.options.customEndpoint) {
                payload.custom_endpoint = this.options.customEndpoint;
            }

            $.ajax({
                url: this.options.aiQueryUrl,
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(payload),
                success: function (response) {
                    self._showLoading(false);

                    if (response.success) {
                        if (response.skus && response.skus.length > 0) {
                            self._fetchProducts(response.skus);
                            return;
                        }

                        self._setSubmitLoading(false);

                        if (response.message) {
                            self._showMessage(response.message, false);
                        } else {
                            self._showMessage($t('No products found for your query.'), false);
                        }

                        return;
                    }

                    self._setSubmitLoading(false);
                    self._showMessage(response.message || $t('Unable to process your request.'), true);
                },
                error: function () {
                    self._showLoading(false);
                    self._setSubmitLoading(false);
                    self._showMessage($t('An error occurred. Please try again.'), true);
                }
            });
        },

        _fetchProducts: function (skus) {
            var self = this;

            if (!skus || skus.length === 0) {
                this._setSubmitLoading(false);
                this._showMessage($t('No products to display.'), false);
                return;
            }

            this._showLoading(true);
            this._setSubmitLoading(true);

            $.ajax({
                url: this.options.productListUrl,
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    skus: skus,
                    prompt: this.lastPrompt || ''
                }),
                success: function (response) {
                    self._showLoading(false);
                    if (response.success && response.html) {
                        self._displayProductsHtml(response.html, response.count || 0);

                        if (self.showRecommendations) {
                            self._showRecommendationsLoading(true);
                            self._loadRecommendations(
                                response.primary_sku || (skus[0] || ''),
                                response.ordered_skus || skus
                            );
                        }
                    } else {
                        self._showMessage(response.message || $t('No products found.'), false);
                        self._resetRecommendations();
                    }

                    self._setSubmitLoading(false);
                },
                error: function () {
                    self._showLoading(false);
                    self._setSubmitLoading(false);
                    self._showMessage($t('An error occurred while loading products.'), true);
                    self._resetRecommendations();
                }
            });
        },

        _loadRecommendations: function (primarySku, originalSkus) {
            if (!this.showRecommendations) {
                return;
            }

            var self = this;
            var url = this.options.recommendationsUrl || '';

            if (!url || !primarySku) {
                this._showRecommendationsLoading(false);
                this._resetRecommendations();
                return;
            }

            $.ajax({
                url: url,
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    primary_sku: primarySku,
                    skus: originalSkus || []
                }),
                success: function (response) {
                    self._showRecommendationsLoading(false);

                    if (response.success && response.html) {
                        self._displayRecommendations(response);
                        return;
                    }

                    self._resetRecommendations();
                },
                error: function () {
                    self._showRecommendationsLoading(false);
                    self._resetRecommendations();
                }
            });
        },

        _showRecommendationsLoading: function (show) {
            if (!this.showRecommendations) {
                return;
            }

            var wrapper = this.recommendationsWrapper;
            var container = this.recommendationsContainer;
            var meta = this.recommendationsMeta;

            if (!wrapper || !wrapper.length) {
                return;
            }

            wrapper.toggleClass('is-loading', !!show);

            if (show) {
                wrapper.show().addClass('is-visible');

                if (container && container.length) {
                    container.empty().append(
                        $('<div>', {
                            'class': 'recommendation-loading',
                            text: $t('Fetching recommendations...')
                        })
                    );
                }

                if (meta && meta.length) {
                    meta.empty().attr('aria-hidden', 'true');
                }
            } else if (container && container.length) {
                container.find('.recommendation-loading').remove();
            }
        },

        _displayProductsHtml: function (html, count) {
            var resultsContainer = this.element.find('[data-role="results-container"]');
            var resultsMeta = this.element.find('[data-role="results-meta"]');
            var productResults = this.element.find('.product-results');

            resultsContainer.html(html);

            count = parseInt(count, 10);
            if (isNaN(count)) {
                count = 0;
            }

            if (count > 0) {
                var label = count === 1 ? $t('1 product found') : $t('%1 products found').replace('%1', count);
                resultsMeta.text(label).removeAttr('aria-hidden');
            } else {
                resultsMeta.empty().attr('aria-hidden', 'true');
            }

            productResults.show().addClass('is-visible');
            this.element.find('.ai-message').hide();
            this.element.find('.ai-clear').show();

            resultsContainer.trigger('contentUpdated');
            this._initializeCatalogForms(resultsContainer);
        },

        _displayRecommendations: function (data) {
            if (!this.showRecommendations) {
                return;
            }

            this._showRecommendationsLoading(false);

            var wrapper = this.recommendationsWrapper;
            var container = this.recommendationsContainer;
            var title = this.recommendationsTitle;
            var meta = this.recommendationsMeta;

            if (!wrapper || !wrapper.length || !container || !container.length) {
                return;
            }

            if (!data || !data.html) {
                this._resetRecommendations();
                return;
            }

            container.html(data.html);

            if (title && title.length) {
                title.text(data.title || $t('You might also like'));
            }

            if (meta && meta.length) {
                var count = parseInt(data.count, 10);

                if (!isNaN(count) && count > 0) {
                    var label = count === 1
                        ? $t('1 related product')
                        : $t('%1 related products').replace('%1', count);

                    meta.text(label).removeAttr('aria-hidden');
                } else {
                    meta.empty().attr('aria-hidden', 'true');
                }
            }

            wrapper.show().addClass('is-visible').removeClass('is-loading');
            container.trigger('contentUpdated');
            this._initializeCatalogForms(container);
            this._setSidebarLayout(true);
        },

        _initializeCatalogForms: function (scope) {
            if (!scope || !scope.length) {
                return;
            }

            var scopeElement = scope;

            require(['jquery', 'Magento_Catalog/js/catalog-add-to-cart'], function ($) {
                var forms = scopeElement.find('form[data-role="tocart-form"]');

                if (window.FORM_KEY) {
                    forms.each(function () {
                        var $form = $(this);
                        var formKeyField = $form.find('input[name="form_key"]');

                        if (!formKeyField.length) {
                            $('<input>', {
                                type: 'hidden',
                                name: 'form_key',
                                value: window.FORM_KEY
                            }).appendTo($form);
                        } else {
                            formKeyField.val(window.FORM_KEY);
                        }
                    });
                }

                forms.each(function () {
                    var $form = $(this);
                    if (!$form.attr('data-mage-init')) {
                        $form.attr('data-mage-init', '{"catalogAddToCart": {}}');
                    }
                });

                forms.catalogAddToCart();
                forms.find('.action.tocart').prop('disabled', false);
            });
        },

        _resetRecommendations: function () {
            if (!this.showRecommendations) {
                return;
            }

            var wrapper = this.recommendationsWrapper;
            var container = this.recommendationsContainer;
            var meta = this.recommendationsMeta;

            this._showRecommendationsLoading(false);

            if (container && container.length) {
                container.empty();
            }

            if (meta && meta.length) {
                meta.empty().attr('aria-hidden', 'true');
            }

            if (wrapper && wrapper.length) {
                wrapper.hide().removeClass('is-visible is-loading');
            }

            this._setSidebarLayout(false);
        },

        _setSidebarLayout: function (enable) {
            if (!this.showRecommendations) {
                return;
            }

            if (!this.resultsArea || !this.resultsArea.length) {
                return;
            }

            this.resultsArea.toggleClass('has-sidebar', !!enable);
        },

        _showMessage: function (message, isError) {
            var messageBox = this.element.find('.ai-message');
            messageBox
                .text(message)
                .removeClass('message-info message-error')
                .addClass(isError ? 'message-error' : 'message-info')
                .show();
            
            this.element.find('.ai-clear').show();
            this.element.find('.product-results').hide().removeClass('is-visible');
            this._resetRecommendations();
            this._setSubmitLoading(false);
        },

        _showLoading: function (show) {
            this.element.find('.loading-indicator').toggle(show);
        },

        _clearResults: function () {
            this.element.find('.ai-prompt').val('');
            this.element.find('[data-role="results-container"]').empty();
            this.element.find('[data-role="results-meta"]').empty().attr('aria-hidden', 'true');
            this.element.find('.product-results').hide().removeClass('is-visible');
            this.element.find('.ai-message').hide();
            this.element.find('.ai-clear').hide();
            this.element.find('.loading-indicator').hide();
            this._resetRecommendations();
            this._setSubmitLoading(false);
        },

        _registerHistoryEntry: function (query) {
            if (!query) {
                return;
            }

            if (!this.history) {
                this.history = [];
            }

            this.history = this.history.filter(function (entry) {
                return entry.query !== query;
            });

            this.history.unshift({
                query: query,
                timestamp: Date.now()
            });

            if (this.history.length > this.historyLimit) {
                this.history = this.history.slice(0, this.historyLimit);
            }

            this._renderHistory();
        },

        _renderHistory: function () {
            if (!this.historyList.length || !this.historyWrapper.length) {
                return;
            }

            if (!this.history || !this.history.length) {
                this.historyList.empty();
                this.historyWrapper.css('display', 'none').attr('aria-hidden', 'true');
                return;
            }

            var chips = this.history.map(function (entry) {
                return $('<button>', {
                    type: 'button',
                    'class': 'history-chip',
                    'data-query': entry.query,
                    text: entry.query
                });
            });

            this.historyList.empty().append(chips);
            this.historyWrapper.css('display', 'flex').attr('aria-hidden', 'false');
        },

        _setSubmitLoading: function (isLoading) {
            var button = this.element.find('.prompt-submit');

            if (!button.length) {
                return;
            }

            button.toggleClass('is-loading', !!isLoading).prop('disabled', !!isLoading);
        },

        _initExamples: function () {
            var examples = this.options.relatedExamples || [];

            if (typeof examples === 'string' && examples.length) {
                examples = [examples];
            }

            if (!$.isArray(examples)) {
                examples = [];
            }

            examples = $.map(examples, function (item) {
                return $.trim(item);
            });
            examples = $.grep(examples, function (item) {
                return item.length > 0;
            });

            this.examplesData = examples;
            this._clearExampleTimers();

            if (!this.examplesWrapper.length || !this.examplesText.length || !examples.length) {
                if (this.examplesWrapper.length) {
                    this.examplesWrapper.css('display', 'none').attr('aria-hidden', 'true');
                }
                return;
            }

            this.examplesWrapper.css('display', 'flex').attr('aria-hidden', 'false');
            this._startExamplesTicker(0);
        },

        _startExamplesTicker: function (index) {
            if (!this.examplesData || !this.examplesData.length || !this.examplesText.length) {
                return;
            }

            this._clearExampleTimers();

            var self = this;
            var examples = this.examplesData;
            var currentIndex = index || 0;

            if (currentIndex >= examples.length) {
                currentIndex = 0;
            }

            this.examplesIndex = currentIndex;
            var currentText = examples[currentIndex];
            var position = 0;

            this.examplesText.text('');

            var typeChar = function () {
                if (!self.examplesText.length) {
                    return;
                }

                if (position <= currentText.length) {
                    self.examplesText.text(currentText.substring(0, position));
                    position += 1;
                    var typingTimer = setTimeout(typeChar, 80);
                    self.examplesTimers.push(typingTimer);
                } else {
                    var pauseTimer = setTimeout(function () {
                        var nextIndex = (currentIndex + 1) % examples.length;
                        self._startExamplesTicker(nextIndex);
                    }, 2000);
                    self.examplesTimers.push(pauseTimer);
                }
            };

            var initialTimer = setTimeout(typeChar, 200);
            this.examplesTimers.push(initialTimer);
        },

        _clearExampleTimers: function () {
            if (!this.examplesTimers) {
                this.examplesTimers = [];
                return;
            }

            this.examplesTimers.forEach(function (timerId) {
                clearTimeout(timerId);
            });
            this.examplesTimers = [];
        },

        _destroy: function () {
            this.element.find('.ai-search-form').off('.aiAssistant');
            this.element.find('.ai-clear').off('.aiAssistant');

            if (this.historyClear && this.historyClear.length) {
                this.historyClear.off('.aiAssistant');
            }

            if (this.historyList && this.historyList.length) {
                this.historyList.off('.aiAssistant');
            }

            if (this.examplesWrapper && this.examplesWrapper.length) {
                this.examplesWrapper.css('display', 'none').attr('aria-hidden', 'true');
            }

            this._clearExampleTimers();
            this._resetRecommendations();
        }
    });

    return $.ecommerce66.aiAssistant;
});
