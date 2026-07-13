/*
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    return function (dataUrl) {
        $.validator.addMethod(
            "ip",
            function (value, element) {
                var ip = $('#blacklist_ip').val();
                var email = $('#blacklist_email').val();
                if (ip == '' && email == '') {
                    return false;
                } else {
                    return true;
                }
            },
            $.mage.__("Missing email or ip. You should input one of them"),
        );
        $.validator.addMethod(
            "email",
            function (value, element) {
                var ip = $('#blacklist_ip').val();
                var email = $('#blacklist_email').val();
                if (ip == '' && email == '') {
                    return false;
                } else {
                    return true;
                }
            },
            $.mage.__("Missing email or ip. You should input one of them"),
        );
    }
});
