/**
 * Mixin to fix breakpoints issue in edit.js for custom content types
 */
define([], function () {
    'use strict';

    return function (Edit) {
        return Edit.extend({
            /**
             * Override getFormNamespace to handle missing breakpoints
             */
            getFormNamespace: function (contentTypeData) {
                var viewport = this.instance.preview.viewport();
                var currentAppearance = this.dataStore.get("appearance");
                var appearance = this.instance.config.appearances[currentAppearance];
                
                // Fix: Initialize breakpoints if not present
                if (!appearance.breakpoints) {
                    appearance.breakpoints = {
                        desktop: {
                            form: this.instance.config.form
                        },
                        mobile: {
                            form: this.instance.config.form
                        }
                    };
                }
                
                var breakpoints = appearance.breakpoints;
                var formNamespace = this.getDefaultNamespaceForm();

                if (breakpoints && breakpoints[viewport()] && breakpoints[viewport()].form) {
                    formNamespace = breakpoints[viewport()].form;
                }

                return formNamespace;
            }
        });
    };
});
