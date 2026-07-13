var config = {
    map: {
        '*': {
            'slick': 'Magento_PageBuilder/js/resource/slick/slick.min'
        }
    },
    shim: {
        'Magento_PageBuilder/js/resource/slick/slick.min': {
            deps: ['jquery']
        }
    }
};
