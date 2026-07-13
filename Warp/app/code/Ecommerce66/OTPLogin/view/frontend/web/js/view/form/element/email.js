/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'uiRegistry',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/action/check-email-availability',
    'Magento_Customer/js/action/login',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/validation'
], function ($, Component, ko, uiRegistry, customer, checkEmailAvailability, loginAction, quote, checkoutData, fullScreenLoader) {
    'use strict';

    var validatedEmail;

    if (!checkoutData.getValidatedEmailValue() &&
        window.checkoutConfig.validatedEmailValue
    ) {
        checkoutData.setInputFieldEmailValue(window.checkoutConfig.validatedEmailValue);
        checkoutData.setValidatedEmailValue(window.checkoutConfig.validatedEmailValue);
    }

    validatedEmail = checkoutData.getValidatedEmailValue();

    if (validatedEmail && !customer.isLoggedIn()) {
        quote.guestEmail = validatedEmail;
    }

    return Component.extend({
        defaults: {
            template: 'Ecommerce66_OTPLogin/form/element/email',
            email: checkoutData.getInputFieldEmailValue(),
            emailFocused: false,
            isLoading: false,
            isPasswordVisible: false,
            listens: {
                email: 'emailHasChanged',
                emailFocused: 'validateEmail'
            },
            ignoreTmpls: {
                email: true
            }
        },
        checkDelay: 2000,
        checkRequest: null,
        isEmailCheckComplete: null,
        isCustomerLoggedIn: customer.isLoggedIn,
        forgotPasswordUrl: window.checkoutConfig.forgotPasswordUrl,
        emailCheckTimeout: 0,
        messages: ko.observableArray([]),


        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();

            this.isPasswordVisible = this.resolveInitialPasswordVisibility();

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe(['email', 'emailFocused', 'isLoading', 'isPasswordVisible']);

            return this;
        },

        /**
         * Callback on changing email property
         */
        emailHasChanged: function () {
            var self = this;

            clearTimeout(this.emailCheckTimeout);

            if (self.validateEmail()) {
                quote.guestEmail = self.email();
                checkoutData.setValidatedEmailValue(self.email());
            }
            this.emailCheckTimeout = setTimeout(function () {
                if (self.validateEmail()) {
                    self.checkEmailAvailability();
                } else {
                    self.isPasswordVisible(false);
                }
            }, self.checkDelay);

            checkoutData.setInputFieldEmailValue(self.email());
        },

        /**
         * Check email existing.
         */
        checkEmailAvailability: function () {
            this.validateRequest();
            this.isEmailCheckComplete = $.Deferred();
            this.isLoading(true);
            this.checkRequest = checkEmailAvailability(this.isEmailCheckComplete, this.email());

            $.when(this.isEmailCheckComplete).done(function () {
                this.isPasswordVisible(false);
                checkoutData.setCheckedEmailValue('');
            }.bind(this)).fail(function () {
                this.isPasswordVisible(true);
                checkoutData.setCheckedEmailValue(this.email());
            }.bind(this)).always(function () {
                this.isLoading(false);
            }.bind(this));
        },

        /**
         * If request has been sent -> abort it.
         * ReadyStates for request aborting:
         * 1 - The request has been set up
         * 2 - The request has been sent
         * 3 - The request is in process
         */
        validateRequest: function () {
            if (this.checkRequest != null && $.inArray(this.checkRequest.readyState, [1, 2, 3])) {
                this.checkRequest.abort();
                this.checkRequest = null;
            }
        },

        /**
         * Local email validation.
         *
         * @param {Boolean} focused - input focus.
         * @returns {Boolean} - validation result.
         */
        validateEmail: function (focused) {
            var loginFormSelector = 'form[data-role=email-with-possible-login]',
                usernameSelector = loginFormSelector + ' input[name=username]',
                loginForm = $(loginFormSelector),
                validator,
                valid,
                use_otp_auth = window.checkoutConfig.use_otp_auth;

            loginForm.validation();

            if (focused === false && !!this.email()) {
                valid = !!$(usernameSelector).valid();

                if (valid) {
                    $(usernameSelector).removeAttr('aria-invalid aria-describedby');

                    if(use_otp_auth !== true){
                        this.showDefaultLogin();
                        $('#switch-method').removeClass('show').addClass('hidden');
                    }

                }

                return valid;
            }

            if (loginForm.is(':visible')) {
                validator = loginForm.validate();

                return validator.check(usernameSelector);
            }

            return true;
        },

        /**
         * Log in form submitting callback.
         *
         * @param {HTMLElement} loginForm - form element.
         */
        login: function (loginForm) {
            var loginData = {},
                formDataArray = $(loginForm).serializeArray();

            formDataArray.forEach(function (entry) {
                loginData[entry.name] = entry.value;
            });

            if (this.isPasswordVisible() && $(loginForm).validation() && $(loginForm).validation('isValid')) {
                fullScreenLoader.startLoader();
                loginAction(loginData).always(function () {
                    fullScreenLoader.stopLoader();
                });
            }
        },

        otpAuth:function (){
            return window.checkoutConfig.use_otp_auth;
        },

        showOtpInput: function() {
            if(this.otpAuth() !== true){
                this.showDefaultLogin();
            }
            var self = this;
            fullScreenLoader.startLoader();
            $('#otp-input').removeClass('hidden').addClass('show');
            $('#methods-login').removeClass('show').addClass('hidden');
            $('#switch-method').removeClass('hidden').addClass('show');
            var email = $('#customer-email').val();
            this.ajaxRequest('/otp-login/index/otplogin', 'POST', { email: email }, function (response) {
                if (response.type === 'success') {
                    self.showNotification(response.type, response.message);
                } else {
                    self.showNotification(response.type, response.message);
                }
             });
            $('#otp-login-form').removeClass('hidden').addClass('show');
            $('#otp-submit').removeClass('hidden').addClass('show');
            $('#message').removeClass('show').removeClass('success').removeClass('error').find('div').text('');
            fullScreenLoader.stopLoader();
            // Agregar binding para que al presionar Enter en el input OTP se active el botón de login OTP
            $('#customer-otp').off('keydown').on('keydown', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#otp-submit').trigger('click');
                }
            });
        },

        showDefaultLogin: function() {
            $('#customer-password-div').removeClass('hidden').addClass('show');
            $('#customer-password').removeClass('hidden').addClass('show');
            $('#default-login-form').removeClass('hidden').addClass('show');
            $('#switch-method').removeClass('hidden').addClass('show');
            $('#methods-login').removeClass('show').addClass('hidden');
            $('#message').removeClass('show').removeClass('success').removeClass('error').find('div').text('');
        },

        submitOtp: function(){
            if(this.otpAuth() !== true){
                this.showDefaultLogin();
            }
            var self = this;
            fullScreenLoader.startLoader();
            var otpcode = $('#customer-otp').val();
            this.ajaxRequest('/otp-login/index/otplogin', 'POST', { otpcode: otpcode }, function (response) {
                if (response.type === 'success') {
                    self.showNotification(response.type, response.message);
                    if(response.reload_page){
                        location.reload();
                    }
                } else {
                    self.showNotification(response.type, response.message);
                    fullScreenLoader.stopLoader();
                }
            });
        },

        switchMethod:function () {
            if(this.otpAuth() !== true){
                this.showDefaultLogin();
            }
            $('#switch-method').removeClass('show').addClass('hidden');
            $('#methods-login').removeClass('hidden').addClass('show');
            $('#otp-login-form').removeClass('show').addClass('hidden');
            $('#default-login-form').removeClass('show').addClass('hidden');
            $('#customer-password-div').removeClass('show').addClass('hidden');
            $('#otp-input').removeClass('show').addClass('hidden').val('');
            $('#customer-otp').val('');
            $('#customer-password').removeClass('show').addClass('hidden').val('');
            $('#message').removeClass('show').removeClass('success').removeClass('error').find('div').text('');
        },

        ajaxRequest: function(url, type, data, successCallback, errorCallback) {
            $.ajax({
                url: url,
                type: type,
                data: data,
                success: successCallback,
                error: errorCallback || function () {
                    console.error('Error en la solicitud AJAX');
                }
            });
        },

        showNotification: function(type, message) {
            $('#message').removeClass('hidden');
            $('#message').removeClass('notice');
            $('#message').removeClass('success');
            $('#message').removeClass('error');
            $('#message').addClass(type);
            $('#message').find('div').text(message);
        },

        /**
         * Resolves an initial state of a login form.
         *
         * @returns {Boolean} - initial visibility state.
         */
        resolveInitialPasswordVisibility: function () {
            if (checkoutData.getInputFieldEmailValue() !== '' && checkoutData.getCheckedEmailValue() !== '') {
                return true;
            }

            if (checkoutData.getInputFieldEmailValue() !== '') {
                return checkoutData.getInputFieldEmailValue() === checkoutData.getCheckedEmailValue();
            }

            return false;
        }

    });
});
