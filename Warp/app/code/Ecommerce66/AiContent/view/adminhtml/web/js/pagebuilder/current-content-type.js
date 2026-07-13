/**
 * Tracks the content type currently being edited in Page Builder
 */
define([
    'Magento_PageBuilder/js/events'
], function (events) {
    'use strict';

    var currentContentType = null;

    events.on('contentType:editBefore', function (args) {
        currentContentType = args && args.contentType ? args.contentType : null;
    });

    events.on('contentType:removeAfter', function (args) {
        if (currentContentType && args && args.contentType === currentContentType) {
            currentContentType = null;
        }
    });

    return {
        /**
         * @returns {ContentTypeInterface|null}
         */
        get: function () {
            return currentContentType;
        },

        /**
         * @param {ContentTypeInterface|null} contentType
         */
        set: function (contentType) {
            currentContentType = contentType || null;
        }
    };
});
