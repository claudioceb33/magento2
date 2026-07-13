/**
 * Button inside PageBuilder forms to open the AI generator modal
 */
define([
    'Magento_Ui/js/form/components/button',
    'Ecommerce66_AiContent/js/pagebuilder/text-generator-modal',
    'Ecommerce66_AiContent/js/pagebuilder/current-content-type',
    'mage/translate'
], function (Button, textGeneratorModal, currentContentType, $t) {
    'use strict';

    return Button.extend({
        defaults: {
            template: 'Ecommerce66_AiContent/pagebuilder/open-generator-button',
            buttonLabel: $t('Generate with AI'),
            hintText: $t('Open the AI generator. Generated HTML will replace the content above.'),
            isLoading: false,
            formElement: 'container',
            buttonClasses: 'ai-generate-button'
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();
            return this;
        },

        /**
         * Override default action (called on button click)
         */
        action: function () {
            this.openGenerator();
        },

        /**
         * Open the AI generator modal for the current text content type
         */
        openGenerator: function () {
            var contentType = currentContentType.get();

            if (!contentType || !contentType.preview) {
                alert($t('Unable to locate the text block. Close the editor and try again.'));
                return;
            }

            textGeneratorModal.open(contentType.preview);
        }
    });
});
