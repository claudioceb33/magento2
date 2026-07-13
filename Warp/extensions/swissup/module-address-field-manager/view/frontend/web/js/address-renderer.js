/*eslint max-depth: ["error", 4]*/

define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore'
], function ($, _) {
    'use strict';

    var observer;

    /**
     * Check if node is a <br>
     *
     * @param  {Node}  el
     * @return {Boolean}
     */
    function isBr(el) {
        if (!el) {
            return true;
        }

        return el.nodeType === 1 && el.tagName === 'BR';
    }

    /**
     * Check if node is a comma
     *
     * @param  {Node}  el
     * @return {Boolean}
     */
    function isComma(el) {
        if (!el) {
            return false;
        }

        return el.nodeType === 3 && el.nodeValue === ', ';
    }
    
    /**
     * Check if Yes/No Field
     *
     * @param  {Node}  el
     * @return {Boolean}
     */
    function isYesNoField(el) {
        if (!el) {
            return false;
        }

        return el.nodeType === 3 && (el.nodeValue === 'Yes' || el.nodeValue === 'No' || el.nodeValue === 'Disabled');
    }
    
    /**
     * Check if node is visible
     *
     * @param  {Node}  el
     * @return {Boolean}
     */
    function isVisible(el) {
        if (!el) {
            return false;
        }

        switch (el.nodeType) {
            case 1:
                return isBr(el) || $(el).text().trim() && $(el).is(':visible');

            case 3:
                return el.nodeValue.trim();

            case 8:
                return false;
        }
    }

    /**
     * Get first previous or next visible sibling
     *
     * @param  {Node} el
     * @param  {String} siblingType - [previousSibling|nextSibling]
     * @return {Node}
     */
    function getSibling(el, siblingType) {
        var result = null,
            tmp = el,
            found = false;

        siblingType = siblingType || 'previousSibling';

        do {
            tmp = tmp[siblingType];

            if (!tmp) {
                break;
            }

            if (isVisible(tmp)) {
                result = tmp;
                found = true;
            }
        } while (!found);

        return result;
    }

    /**
     * Cleanup trailing commas caused by hidden or optional field status
     *
     * @param  {Node} el
     */
    function cleanupAddressSelect(el) {
        $(el).find('option').each(function () {
            var address = $(this).text(),
                cleanAddress = address.replace(/(,\s*){2,}/, ', ');

            $(this).text(cleanAddress);
        });
    }

    /**
     * Cleanup trailing commas and repeated <br>'s caused by hidden or
     * optional field status
     *
     * @param  {Node} el
     */
    function cleanupAddressText(el) {
        var children = el.childNodes,
            current, prev, next, i;

        $(el).find('span.afm-br br').unwrap();
        $(el).find('span.afm-comma').replaceWith(', ');

        for (i = 0; i < children.length; i++) {
            current = children[i];

            if (current.nodeType === 8) {
                continue;
            }
            
            /* hide Yes / No field value */
            if (isYesNoField(current)) {
                $(current).wrap('<span class="afm-hidden" style="display:none"></span>');
            }
            
            prev = getSibling(current, 'previousSibling');
            next = getSibling(current, 'nextSibling');

            if (isComma(current)) {
                if (isComma(prev) || isBr(prev) || isBr(next)) {
                    $(current).wrap('<span class="afm-comma afm-hidden" style="display:none"></span>');
                }
            } else if (isBr(current)) {
                if (isBr(prev)) {
                    $(current).wrap('<span class="afm-br afm-hidden" style="display:none"></span>');
                }
            }
        }
    }

    observer = new MutationObserver(function (mutations) {
        var mutation = mutations[0],
            target;

        switch (mutation.type) {
            case 'childList':
                target = mutation.target;
                break;

            case 'characterData':
                target = mutation.target.parentNode;
                break;

            default:
                return;
        }

        _.debounce(function () {
            cleanupAddressText(target);
        }, 200)();
    });

    return function (config) {
        $.async({
            selector: config.selectors.join(',')
        }, function (el) {
            if (el.tagName === 'SELECT') {
                cleanupAddressSelect(el);

                return;
            }

            cleanupAddressText(el);

            if (config.mutable) {
                observer.observe(el, {
                    childList: true,
                    characterData: true,
                    subtree: true
                });
            }
        });
    };
});
