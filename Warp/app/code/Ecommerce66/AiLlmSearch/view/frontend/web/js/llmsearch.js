/**
 * Lightweight vanilla JS widget that proxies queries to the Magento controller
 * and renders the Copilot response inside the widget container.
 */
define(['jquery', 'mage/translate'], function ($) {
    'use strict';

    function getQueryParam(name) {
        try {
            var url = new URL(window.location.href);
            return url.searchParams.get(name);
        } catch (e) {
            return null;
        }
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) {
            return '';
        }
        return String(str).replace(/[&<>"']/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[s] || s;
        });
    }

    function getFormKey() {
        if (typeof window.FORM_KEY !== 'undefined' && window.FORM_KEY) {
            return String(window.FORM_KEY);
        }
        try {
            var match = document.cookie.match(new RegExp('(^|; )form_key=([^;]*)'));
            if (match && match[2]) {
                return decodeURIComponent(match[2]);
            }
        } catch (e) {
            /* noop */
        }
        return '';
    }

    function formatPrice(value) {
        if (typeof value === 'number' && !isNaN(value)) {
            var formatted = '';
            try {
                formatted = value.toLocaleString('es-AR', {style: 'currency', currency: 'ARS'});
            } catch (e) {
                formatted = '$' + value.toFixed(2);
            }
            return escapeHtml(formatted);
        }
        if (typeof value === 'string' && value !== '') {
            return escapeHtml(value);
        }
        return '';
    }

    function renderDisabled(el, container) {
        container.innerHTML = '<div class="e66-llm-empty">AI assistant is disabled for this store view.</div>';
        var form = el.querySelector('[data-role="llm-form"]');
        if (form) {
            form.classList.add('is-disabled');
            Array.prototype.forEach.call(form.querySelectorAll('input, button, textarea, select'), function (node) {
                node.setAttribute('disabled', 'disabled');
            });
        }
    }

    function showError(container, message) {
        container.innerHTML = '<div class="e66-llm-error">' + escapeHtml(message || 'AI results unavailable. Please try again later.') + '</div>';
    }

    function buildSummary(summary) {
        if (!summary) {
            return '';
        }
        if (typeof summary === 'string') {
            return '<div class="e66-llm-summary">' + escapeHtml(summary) + '</div>';
        }
        if (typeof summary === 'object') {
            var title = summary.title ? '<strong>' + escapeHtml(summary.title) + '</strong>' : '';
            var description = summary.description || summary.text || '';
            var extra = summary.extra || '';
            var html = '<div class="e66-llm-summary">';
            if (title) {
                html += title;
            }
            if (description) {
                html += '<div class="e66-llm-summary-desc">' + escapeHtml(description) + '</div>';
            }
            if (extra) {
                html += '<div class="e66-llm-summary-extra">' + escapeHtml(extra) + '</div>';
            }
            html += '</div>';
            return html;
        }
        return '';
    }

    function buildComparison(comparison) {
        if (!comparison) {
            return '';
        }
        if (typeof comparison === 'string') {
            return '<div class="e66-llm-comparison">' + escapeHtml(comparison) + '</div>';
        }
        if (typeof comparison === 'object') {
            var pieces = [];
            if (comparison.title) {
                pieces.push('<strong>' + escapeHtml(comparison.title) + '</strong>');
            }
            if (comparison.description) {
                pieces.push('<div>' + escapeHtml(comparison.description) + '</div>');
            }
            if (comparison.items && Array.isArray(comparison.items)) {
                var list = '<ul>';
                comparison.items.forEach(function (item) {
                    list += '<li>' + escapeHtml(item) + '</li>';
                });
                list += '</ul>';
                pieces.push(list);
            }
            return pieces.length ? '<div class="e66-llm-comparison">' + pieces.join('') + '</div>' : '';
        }
        return '';
    }

    function buildProducts(products) {
        if (!Array.isArray(products) || products.length === 0) {
            return '';
        }
        var grid = '<div class="e66-llm-grid">';
        products.forEach(function (p) {
            if (!p) {
                return;
            }
            var title = escapeHtml(p.title || p.name || p.sku || 'Product');
            var url = escapeHtml(p.url || p.product_url || '#');
            var sku = p.sku || '';
            var price = formatPrice(typeof p.price === 'number' ? p.price : (typeof p.price === 'string' ? p.price : (typeof p.minimal_price === 'number' ? p.minimal_price : '')));
            var description = p.description || p.short_description || p.desc_norm || '';
            var reasoning = p.reasoning || '';
            var badge = '';
            if (Array.isArray(p.category) && p.category.length) {
                badge = p.category[0];
            } else if (p.tag) {
                badge = p.tag;
            }
            var image = p.image_url || p.image || p.image_small || '';

            grid += '<article class="e66-llm-card">';
            grid += '<div class="e66-llm-image">';
            if (image) {
                grid += '<img src="' + escapeHtml(image) + '" alt="' + title + '"/>';
            } else {
                grid += '<div class="e66-llm-noimage">No image</div>';
            }
            grid += '</div>';
            grid += '<div class="e66-llm-card-body">';
            if (badge) {
                grid += '<div class="e66-llm-badge">' + escapeHtml(badge) + '</div>';
            }
            grid += '<h3 class="e66-llm-card-title"><a href="' + url + '" target="_blank" rel="noopener noreferrer">' + title + '</a></h3>';
            if (sku) {
                grid += '<div class="e66-llm-sku">SKU: ' + escapeHtml(Array.isArray(sku) ? sku.join(', ') : sku) + '</div>';
            }
            if (price) {
                grid += '<div class="e66-llm-price">' + price + '</div>';
            }
            if (description) {
                grid += '<div class="e66-llm-desc">' + escapeHtml(description) + '</div>';
            }
            if (reasoning) {
                grid += '<div class="e66-llm-explain"><em>' + escapeHtml(reasoning) + '</em></div>';
            }
            grid += '<div class="e66-llm-card-actions">';
            if (url && url !== '#') {
                grid += '<a class="e66-llm-btn e66-llm-btn-ghost" href="' + url + '" target="_blank" rel="noopener noreferrer">View details</a>';
            }
            grid += '</div>';
            grid += '</div>';
            grid += '</article>';
        });
        grid += '</div>';
        return grid;
    }

    function buildReasoning(reasoning) {
        if (!reasoning) {
            return '';
        }
        if (Array.isArray(reasoning) && reasoning.length) {
            var list = '<ul>';
            reasoning.forEach(function (item) {
                list += '<li>' + escapeHtml(item) + '</li>';
            });
            list += '</ul>';
            return '<div class="e66-llm-reasoning">' + list + '</div>';
        }
        if (typeof reasoning === 'string') {
            return '<div class="e66-llm-reasoning">' + escapeHtml(reasoning) + '</div>';
        }
        return '';
    }

    function buildFilters(filters) {
        if (!Array.isArray(filters) || !filters.length) {
            return '';
        }
        var list = '<ul>';
        filters.forEach(function (filter) {
            list += '<li>' + escapeHtml(filter) + '</li>';
        });
        list += '</ul>';
        return '<div class="e66-llm-filters"><strong>Filters applied</strong>' + list + '</div>';
    }

    function buildSuggested(actions) {
        if (!Array.isArray(actions) || !actions.length) {
            return '';
        }
        var html = '<div class="e66-llm-actions"><ul>';
        actions.forEach(function (action) {
            if (!action) {
                return;
            }
            var label = '';
            var nextQuery = '';
            var nextAction = '';
            if (typeof action === 'string') {
                label = action;
                nextQuery = action;
            } else if (typeof action === 'object') {
                label = action.label || action.title || action.next_query || '';
                nextQuery = action.next_query || '';
                nextAction = action.action_type || action.action || '';
            }
            if (!label) {
                return;
            }
            html += '<li>';
            if (nextQuery) {
                html += '<button type="button" class="e66-llm-btn e66-llm-btn-ghost" data-role="llm-suggested" data-next-query="' + escapeHtml(nextQuery) + '"';
                if (nextAction) {
                    html += ' data-next-action="' + escapeHtml(nextAction) + '"';
                }
                html += '>' + escapeHtml(label) + '</button>';
            } else {
                html += escapeHtml(label);
            }
            html += '</li>';
        });
        html += '</ul></div>';
        return html;
    }

    function bindSuggestedActions(el, container, callback) {
        var buttons = container.querySelectorAll('[data-role="llm-suggested"]');
        Array.prototype.forEach.call(buttons, function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                var nextQuery = btn.getAttribute('data-next-query') || '';
                var nextAction = (btn.getAttribute('data-next-action') || '').toLowerCase();
                callback(nextQuery, nextAction);
            });
        });
    }

    function bootstrap(el, options) {
        if (!el) {
            return;
        }

        var opts = options || {};

        var endpoint = el.getAttribute('data-endpoint');
        var enabled = el.getAttribute('data-enabled');
        var action = (el.getAttribute('data-action') || window.e66_llm_action || 'search').toLowerCase();
        if (['search', 'compare', 'recommend'].indexOf(action) === -1) {
            action = 'search';
        }
        var sessionId = el.getAttribute('data-session') || '';
        var defaultQuery = (el.getAttribute('data-default-query') || '').trim();
        var queryInput = el.querySelector('[data-role="llm-query"]');
        var form = el.querySelector('[data-role="llm-form"]');
        var submitBtn = el.querySelector('[data-role="llm-submit"]');
        var content = el.querySelector('.e66-llm-content');
        if (!content) {
            return;
        }

        var HISTORY_STORAGE_PREFIX = 'e66_llm_history_';
        var MAX_HISTORY_ITEMS = 12;
        var historyKey = buildHistoryKey(sessionId);
        var conversationHistory = loadStoredHistory(historyKey);

        var urlQuery = getQueryParam('q') || getQueryParam('query');
        if (urlQuery) {
            defaultQuery = urlQuery.trim();
            if (queryInput) {
                queryInput.value = defaultQuery;
            }
        } else if (queryInput && !queryInput.value && defaultQuery) {
            queryInput.value = defaultQuery;
        }

        if (!endpoint) {
            showError(content, opts.errorMissingEndpoint || 'AI endpoint is not configured.');
            return;
        }

        if (!enabled || enabled === '0') {
            renderDisabled(el, content);
            return;
        }

        function setLoading(state) {
            if (submitBtn) {
                submitBtn.disabled = state;
                submitBtn.classList.toggle('is-loading', !!state);
            }
            if (queryInput) {
                queryInput.disabled = state;
            }
            el.classList.toggle('is-loading', !!state);
        }

        function buildHistoryKey(id) {
            return HISTORY_STORAGE_PREFIX + (id ? String(id) : 'anonymous');
        }

        function loadStoredHistory(key) {
            try {
                var stored = sessionStorage.getItem(key);
                if (stored) {
                    var parsed = JSON.parse(stored);
                    if (Array.isArray(parsed)) {
                        return parsed;
                    }
                }
            } catch (e) {
                /* ignore */
            }
            return [];
        }

        function persistHistory() {
            try {
                sessionStorage.setItem(historyKey, JSON.stringify(conversationHistory));
            } catch (e) {
                /* ignore */
            }
        }

        function trimHistory() {
            if (conversationHistory.length > MAX_HISTORY_ITEMS) {
                conversationHistory = conversationHistory.slice(-MAX_HISTORY_ITEMS);
            }
        }

        function getRecentHistory() {
            trimHistory();
            return conversationHistory.slice();
        }

        function buildSessionMetadata() {
            var meta = {};
            var width = window.innerWidth || document.documentElement.clientWidth || 0;
            if (width) {
                if (width <= 640) {
                    meta.device = 'mobile';
                } else if (width <= 1024) {
                    meta.device = 'tablet';
                } else {
                    meta.device = 'desktop';
                }
            }
            if (navigator.language) {
                meta.language = navigator.language;
            }
            if (navigator.userAgent) {
                meta.user_agent = navigator.userAgent;
            }
            return Object.keys(meta).length ? meta : null;
        }

        function recordUserMessage(message) {
            if (!message) {
                return;
            }
            conversationHistory.push({
                role: 'user',
                content: message,
                timestamp: new Date().toISOString(),
                action: action
            });
            trimHistory();
            persistHistory();
        }

        function assembleAssistantContent(json) {
            var segments = [];

            if (json.summary) {
                if (typeof json.summary === 'string') {
                    segments.push(json.summary);
                } else if (typeof json.summary === 'object') {
                    if (json.summary.title) {
                        segments.push(json.summary.title);
                    }
                    if (json.summary.description) {
                        segments.push(json.summary.description);
                    }
                }
            }

            if (json.llm_explanation) {
                segments.push(json.llm_explanation);
            }

            if (json.assistant_reasoning) {
                if (Array.isArray(json.assistant_reasoning)) {
                    segments = segments.concat(json.assistant_reasoning);
                } else if (typeof json.assistant_reasoning === 'string') {
                    segments.push(json.assistant_reasoning);
                }
            }

            if (!segments.length && json.comparison_summary) {
                if (typeof json.comparison_summary === 'string') {
                    segments.push(json.comparison_summary);
                } else if (json.comparison_summary.description) {
                    segments.push(json.comparison_summary.description);
                }
            }

            function mapActionLabels(list) {
                return list.map(function (item) {
                    return item && (item.label || item.title) ? (item.label || item.title) : '';
                }).join('. ');
            }

            if (!segments.length && json.suggested_actions && Array.isArray(json.suggested_actions)) {
                segments.push(mapActionLabels(json.suggested_actions));
            }

            if (!segments.length && json.actions && Array.isArray(json.actions)) {
                segments.push(mapActionLabels(json.actions));
            }

            if (!segments.length) {
                segments.push('Assistant provided product recommendations.');
            }

            return segments.filter(Boolean).join(' ');
        }

        function extractProductsShown(json) {
            var products = [];
            if (Array.isArray(json.products)) {
                json.products.forEach(function (item) {
                    if (item && item.sku) {
                        products.push(String(item.sku));
                    }
                });
            }
            if (Array.isArray(json.selected_products)) {
                json.selected_products.forEach(function (sku) {
                    if (sku) {
                        products.push(String(sku));
                    }
                });
            }
            return Array.from(new Set(products));
        }

        function recordAssistantMessage(json) {
            if (!json || typeof json !== 'object') {
                return;
            }
            var message = {
                role: 'assistant',
                content: assembleAssistantContent(json),
                timestamp: new Date().toISOString()
            };
            var shown = extractProductsShown(json);
            if (shown.length) {
                message.products_shown = shown;
            }
            conversationHistory.push(message);
            trimHistory();
            persistHistory();
        }

        function buildContextPayload() {
            var context = {};
            var history = getRecentHistory();
            if (history.length) {
                context.conversation_history = history;
            }

            var sessionMeta = buildSessionMetadata();
            if (sessionMeta) {
                context.session_metadata = sessionMeta;
            }

            var merged = {};
            try {
                if (window.e66_llm_context && typeof window.e66_llm_context === 'object') {
                    merged = $.extend(true, {}, window.e66_llm_context);
                }
            } catch (e) {
                merged = {};
            }

            if (context.conversation_history) {
                merged.conversation_history = context.conversation_history;
            }
            if (context.session_metadata) {
                merged.session_metadata = context.session_metadata;
            }

            return Object.keys(merged).length ? merged : null;
        }

        function switchHistoryKey(newSessionId) {
            var newKey = buildHistoryKey(newSessionId);
            if (newKey === historyKey) {
                return;
            }
            var currentSnapshot = conversationHistory.slice();
            historyKey = newKey;
            var stored = loadStoredHistory(historyKey);
            if (stored.length) {
                conversationHistory = stored;
            } else {
                conversationHistory = currentSnapshot;
                persistHistory();
            }
        }

        function cacheKey(query) {
            return 'e66_llm_cache_' + action + '_' + encodeURIComponent(query || '_empty');
        }

        function updateSession(newId) {
            if (!newId) {
                return;
            }
            sessionId = String(newId);
            el.setAttribute('data-session', sessionId);
            switchHistoryKey(sessionId);
        }

        function updateSelectedProducts(selected) {
            if (!selected || !Array.isArray(selected)) {
                return;
            }
            try {
                sessionStorage.setItem('e66_selected_products', JSON.stringify(selected));
            } catch (e) {
                /* ignore */
            }
        }

        function buildPayload(query) {
            var payload = {
                query: query,
                session_id: sessionId || null,
                action: action
            };

            var formKey = getFormKey();
            if (formKey) {
                payload.form_key = formKey;
            }

            var contextPayload = buildContextPayload();
            if (contextPayload) {
                payload.context = contextPayload;
            }

            try {
                if (window.e66_seller_id) {
                    var parsedSeller = parseInt(window.e66_seller_id, 10);
                    if (!isNaN(parsedSeller)) {
                        payload.seller_id = parsedSeller;
                    }
                }
            } catch (e) {
                /* noop */
            }

            try {
                var stored = sessionStorage.getItem('e66_selected_products');
                if (stored) {
                    var parsedList = JSON.parse(stored);
                    if (Array.isArray(parsedList) && parsedList.length) {
                        payload.selected_products = parsedList;
                    }
                } else if (window.e66_selected_products && Array.isArray(window.e66_selected_products)) {
                    payload.selected_products = window.e66_selected_products;
                }
            } catch (e) {
                /* noop */
            }

            return payload;
        }

        function renderResponse(json, query, options) {
            options = options || {};
            var recordHistory = options.recordHistory !== false;
            if (!json || typeof json !== 'object') {
                showError(content, 'AI results unavailable. Please try again later.');
                return;
            }

            var parts = [];
            var queryHeader = '';
            if (query && query.trim()) {
                queryHeader = '<div class="e66-llm-query">' + escapeHtml(query) + '</div>';
            }
            var summaryHtml = buildSummary(json.summary);
            if (summaryHtml) {
                parts.push(summaryHtml);
            }

            if (json.llm_explanation) {
                parts.push('<div class="e66-llm-explain"><em>' + escapeHtml(json.llm_explanation) + '</em></div>');
            }

            var comparisonHtml = buildComparison(json.comparison_summary);
            if (comparisonHtml) {
                parts.push(comparisonHtml);
            }

            var productsHtml = '';
            if (typeof json.product_html === 'string' && json.product_html.trim() !== '') {
                productsHtml = json.product_html;
            } else {
                productsHtml = buildProducts(json.products);
            }
            if (productsHtml) {
                parts.push(productsHtml);
            }

            var reasoningHtml = buildReasoning(json.assistant_reasoning);
            if (reasoningHtml) {
                parts.push(reasoningHtml);
            }

            var filtersHtml = buildFilters(json.filters_applied);
            if (filtersHtml) {
                parts.push(filtersHtml);
            }

            var actionsHtml = buildSuggested(json.suggested_actions);
            if (actionsHtml) {
                parts.push(actionsHtml);
            }

            if (!parts.length) {
                parts.push('<div class="e66-llm-empty">No AI suggestions.</div>');
            }

            var placeholder = content.querySelector('.e66-llm-placeholder');
            if (placeholder && placeholder.parentNode) {
                placeholder.parentNode.removeChild(placeholder);
            }

            var resultId = 'result-' + Date.now();
            var resultBlock = '<section class="e66-llm-result" id="' + resultId + '">' +
                queryHeader +
                parts.join('\n') +
                '</section>';

            content.insertAdjacentHTML('beforeend', resultBlock);
            var target = document.getElementById(resultId);
            if (target) {
                target.scrollIntoView({behavior: 'smooth', block: 'start'});
            }

            if (recordHistory) {
                recordAssistantMessage(json);
            }
            
            // Initialize add to cart on products - DIRECT CALL
            setTimeout(function() {
                var $target = $(target);
                var $productWrappers = $target.find('.products.wrapper');
                
                console.log('Found product wrappers:', $productWrappers.length);
                
                if ($productWrappers.length > 0) {
                    require(['Ecommerce66_AiLlmSearch/js/product-list'], function(productListWidget) {
                        console.log('Product list widget loaded');
                        console.log('Widget function type:', typeof productListWidget);
                        
                        $productWrappers.each(function(index, wrapper) {
                            console.log('Initializing widget on wrapper', index, wrapper);
                            
                            // Call the widget function with the DOM element
                            productListWidget({}, wrapper);
                        });

                        applyViewMode();
                    });
                }
            }, 200);
            
            bindSuggestedActions(el, content, function (nextQuery, nextAction) {
                if (queryInput && nextQuery) {
                    queryInput.value = nextQuery;
                }
                if (nextAction && ['search', 'compare', 'recommend'].indexOf(nextAction) !== -1) {
                    action = nextAction;
                    el.setAttribute('data-action', nextAction);
                }
                doFetch(nextQuery || (queryInput ? queryInput.value : ''), {ignoreCache: true});
            });
        }

        function doFetch(query, options) {
            options = options || {};
            var trimmed = (query || '').trim();
            if (!trimmed && defaultQuery) {
                trimmed = defaultQuery;
            }
            if (!trimmed) {
                showError(content, 'Please enter a query for the AI assistant.');
                return;
            }

            recordUserMessage(trimmed);

            var key = cacheKey(trimmed);
            if (!options.ignoreCache) {
                try {
                    var cached = sessionStorage.getItem(key);
                    if (cached) {
                        var cachedJson = JSON.parse(cached);
                        renderResponse(cachedJson, trimmed);
                        return;
                    }
                } catch (e) {
                    /* ignore cache parse errors */
                }
            }

            setLoading(true);
            var loadingEl = document.createElement('div');
            loadingEl.className = 'e66-llm-loading';
            loadingEl.textContent = opts.loadingText || $.mage.__('Loading AI results...');
            var overlay = document.createElement('div');
            overlay.className = 'e66-llm-loading-overlay';
            overlay.appendChild(loadingEl);
            content.appendChild(overlay);

            var payload = buildPayload(trimmed);
            var request = fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Form-Key': getFormKey()
                },
                body: JSON.stringify(payload)
            });

            request.then(function (resp) {
                if (!resp.ok) {
                    throw new Error('Non-200 response');
                }
                return resp.json();
            }).then(function (json) {
                updateSession(json && json.session_id);
                if (json && json.user_memory && Array.isArray(json.user_memory.selected_products)) {
                    updateSelectedProducts(json.user_memory.selected_products);
                } else if (json && Array.isArray(json.selected_products)) {
                    updateSelectedProducts(json.selected_products);
                }
                renderResponse(json, trimmed);
                try {
                    sessionStorage.setItem(key, JSON.stringify(json));
                } catch (e) {
                    /* ignore cache errors */
                }
            }).catch(function () {
                showError(overlay || content, 'AI results unavailable. Please try again later.');
            }).then(function () {
                setLoading(false);
                if (overlay && overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                doFetch(queryInput ? queryInput.value : '', {ignoreCache: true});
                if (queryInput) {
                    queryInput.value = '';
                }
            });
        }

        var ctaSelector = (opts.ctaSelector || el.getAttribute('data-cta-selector') || '#e66-llm-cta');
        try {
            var cta = el.querySelector(ctaSelector);
            if (cta) {
                cta.addEventListener('click', function () {
                    doFetch(queryInput ? queryInput.value : '', {ignoreCache: true});
                    if (queryInput) {
                        queryInput.value = '';
                    }
                });
            }
        } catch (e) {
            /* noop */
        }

        // Read view mode from config and apply to products when they render
        var viewMode = (el.getAttribute('data-view-mode') || 'grid').toLowerCase();
        
        // Function to apply view mode to product wrappers
        function applyViewMode() {
            var productWrappers = content.querySelectorAll('.products.wrapper');
            Array.prototype.forEach.call(productWrappers, function(wrapper) {
                var $wrapper = $(wrapper);
                var productList = $wrapper.data('productList');

                if (!productList || typeof productList.enableSlider !== 'function') {
                    return;
                }

                var isSliderMode = viewMode === 'slider' || viewMode === 'slide';

                if (isSliderMode) {
                    $wrapper.addClass('e66-llm-slider-mode');
                    setTimeout(function() {
                        if (productList && productList.enableSlider) {
                            productList.enableSlider();
                        }
                    }, 300);
                } else {
                    $wrapper.removeClass('e66-llm-slider-mode');
                    if (productList && productList.disableSlider) {
                        productList.disableSlider();
                    }
                }
            });
        }

        if (urlQuery) {
            doFetch(urlQuery);
        }
    }

    return function init(config, element) {
        var el;
        var options;

        if (element) {
            el = element;
            options = config || {};
        } else {
            el = config;
            options = {};
        }

        if (!el || !el.nodeType) {
            return;
        }

        bootstrap(el, options);
    };
});
