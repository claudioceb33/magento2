/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

define([
    'Magento_Ui/js/grid/columns/column'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        }
    });
});
