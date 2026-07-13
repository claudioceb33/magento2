/**
 * Utility to normalize AI responses into clean HTML strings
 */
define([], function () {
    'use strict';

    var DEFAULT_FIELDS = ['content', 'description', 'text', 'html', 'message', 'body'];

    function hasHtmlMarkup(value) {
        return /<\/?[a-z][\s\S]*>/i.test(value);
    }

    function extractFromObject(payload, fields) {
        var candidates = fields || DEFAULT_FIELDS;
        for (var i = 0; i < candidates.length; i++) {
            if (payload[candidates[i]]) {
                return payload[candidates[i]];
            }
        }
        return '';
    }

    function escapeHtml(str) {
        return str.replace(/[&<>]/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;'}[c];
        });
    }

    function convertInlineMarkdown(text) {
        return text
            .replace(/(\*\*|__)(.+?)\1/g, '<strong>$2</strong>')
            .replace(/(\*|_)(.+?)\1/g, '<em>$2</em>')
            .replace(/`([^`]+)`/g, '<code>$1</code>');
    }

    function convertHeading(line) {
        var match = line.match(/^(#{1,6})\s+(.*)$/);
        if (!match) {
            return null;
        }
        var level = match[1].length;
        return '<h' + level + '>' + convertInlineMarkdown(match[2].trim()) + '</h' + level + '>';
    }

    function convertList(block) {
        var lines = block.split('\n');
        var isUnordered = lines.every(function (line) {
            return /^\s*[-*+]\s+/.test(line);
        });
        if (isUnordered) {
            return '<ul>' + lines.map(function (line) {
                return '<li>' + convertInlineMarkdown(line.replace(/^\s*[-*+]\s+/, '').trim()) + '</li>';
            }).join('') + '</ul>';
        }

        var isOrdered = lines.every(function (line) {
            return /^\s*\d+[.)]\s+/.test(line);
        });
        if (isOrdered) {
            return '<ol>' + lines.map(function (line) {
                return '<li>' + convertInlineMarkdown(line.replace(/^\s*\d+[.)]\s+/, '').trim()) + '</li>';
            }).join('') + '</ol>';
        }

        return null;
    }

    function normalizePlainText(content) {
        var sanitized = content.replace(/\r\n/g, '\n').trim();
        if (!sanitized) {
            return '';
        }

        sanitized = sanitized.replace(/```([\s\S]*?)```/g, function (match, inner) {
            return '<pre><code>' + escapeHtml(inner.trim()) + '</code></pre>';
        });

        var blocks = sanitized.split(/\n\s*\n/).map(function (block) {
            block = block.trim();
            if (!block) {
                return '';
            }

            var heading = convertHeading(block);
            if (heading) {
                return heading;
            }

            var listMarkup = convertList(block);
            if (listMarkup) {
                return listMarkup;
            }

            return '<p>' + convertInlineMarkdown(block).replace(/\n+/g, '<br>') + '</p>';
        }).filter(function (block) {
            return block !== '';
        });

        return blocks.length ? blocks.join('') : '';
    }

    /**
     * Normalize arbitrary AI responses (objects, JSON strings, plain text) into HTML
     *
     * @param {any} raw
     * @param {Object} [options]
     * @param {boolean} [options.forceHtml=false]
     * @param {string[]} [options.fields]
     * @returns {string}
     */
    return function normalizeContent(raw, options) {
        options = options || {};

        if (raw === null || raw === undefined) {
            return '';
        }

        var content = raw;

        if (typeof content === 'object') {
            content = extractFromObject(content, options.fields);
        }

        if (typeof content !== 'string') {
            try {
                content = JSON.stringify(content);
            } catch (e) {
                content = '';
            }
        }

        content = content ? String(content).trim() : '';

        if (!content) {
            return '';
        }

        if (content.charAt(0) === '{') {
            try {
                var parsed = JSON.parse(content);
                if (parsed && typeof parsed === 'object') {
                    content = extractFromObject(parsed, options.fields) || content;
                }
            } catch (ignored) {
                // leave content as-is
            }
        }

        content = content.trim();

        if (!content) {
            return '';
        }

        if (options.forceHtml || hasHtmlMarkup(content)) {
            return hasHtmlMarkup(content) ? content : normalizePlainText(content);
        }

        return content;
    };
});
