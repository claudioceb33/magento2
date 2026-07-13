var config = {
    deps: [
        'Ecommerce66_AiContent/js/pagebuilder/current-content-type'
    ],
    config: {
        mixins: {
            'Magento_PageBuilder/js/content-type/edit': {
                'Ecommerce66_AiContent/js/content-type/edit-mixin': true
            },
            'Magento_PageBuilder/js/form/element/html-code': {
                'Ecommerce66_AiContent/js/form/element/html-code-mixin': true
            }
        }
    }
};
