define([
    'jquery',
    'mage/url',
    'mage/storage'
], function ($, urlBuilder, storage) {
    'use strict';

    return function (config) {
        /**
         * Get cookie by name
         * @param name
         * @returns {string|null}
         */
        function getCookie(name) {
            var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            if (match) return match[2];
            return null;
        }

        /**
         * Extract Client ID from _ga cookie
         * Format: GA1.1.XXXXXXXX.YYYYYYYY
         */
        function getClientId() {
            var gaCookie = getCookie('_ga');
            if (gaCookie) {
                var parts = gaCookie.split('.');
                if (parts.length >= 4) {
                    return parts[2] + '.' + parts[3];
                }
                return gaCookie;
            }
            return null;
        }

        /**
         * Extract Session ID from _ga_<container_id> cookie
         * Format: GS1.1.XXXXXXXX.Y.Z.AAAA
         */
        function getSessionId() {
            var measurementId = config.measurementId.replace('G-', '');
            var sessionCookie = getCookie('_ga_' + measurementId);
            if (sessionCookie) {
                var parts = sessionCookie.split('.');
                if (parts.length >= 3) {
                    // Extract numeric session_id (removes leading "s" and anything after "$")
                    return parts[2].replace(/^s/, '').split('$')[0];
                }
            }
            return null;
        }

        var clientId = getClientId();
        var sessionId = getSessionId();

        if (clientId) {
            $.ajax({
                url: urlBuilder.build('ceb_gtm/ga/capture'),
                data: {
                    client_id: clientId,
                    session_id: sessionId
                },
                type: 'POST',
                global: false,
                success: function (response) {
                    // Silent success
                }
            });
        }
    };
});
