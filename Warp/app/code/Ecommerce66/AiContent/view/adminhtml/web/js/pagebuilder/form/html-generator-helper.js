define([
    'Ecommerce66_AiContent/js/pagebuilder/current-content-type',
    'Ecommerce66_AiContent/js/pagebuilder/text-generator-modal',
    'mage/translate'
], function (currentContentType, textGeneratorModal, $t) {
    'use strict';

    return {
        open: function (formComponent) {
            var contentType = currentContentType.get();

            if (!contentType || !contentType.preview) {
                alert($t('Unable to locate the HTML block. Close the editor and try again.'));
                return;
            }

            textGeneratorModal.open(contentType.preview, {
                formComponent: formComponent,
                targetField: 'html'
            });
        }
    };
});
