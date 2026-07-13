define([
    'mage/url',
    'mage/utils/wrapper'
], function (url, wrapper) {
    'use strict';

    return function (target) {
        target.process = wrapper.wrap(
            target.process,
            function (originalMethod, response, messageContainer) {
                var error,
                    matches;

                if (response.status != 400) { //eslint-disable-line eqeqeq
                    return originalMethod(response, messageContainer);
                }

                try {
                    error = JSON.parse(response.responseText);
                } catch (e) {
                    return originalMethod(response, messageContainer);
                }

                if (!error.parameters ||
                    !error.parameters.message ||
                    error.parameters.message.indexOf('afm|') === -1
                ) {
                    return originalMethod(response, messageContainer);
                }

                matches = error.parameters.message.match(/afm\|(\d+)\|/);

                if (!matches) {
                    return originalMethod(response, messageContainer);
                }

                window.location.replace(url.build('address-field-manager/address/edit/id/' + matches[1]));
            }
        );

        return target;
    };
});
