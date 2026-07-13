define(['jquery'], function ($) {

    var $fastLoginMode = $('#fast-login-mode');
    var $defaultLoginMode = $('#default-login-mode');
    var $return = $('#return');
    var $otpEmailSubmit = $('#otp-email-submit');
    var $otpEmailDiv = $('#otp-email-div');
    var $otpCodeDiv = $('#otp-code-div');
    var $customOtpFieldset = $('#custom-otp-fieldset');
    var $defaultModeFieldset = $('#default-mode-fieldset');
    var $emailInput = $('#otp-email');
    var $otpCodeInput = $('#otp-code');

    function hideLoginButtons() {
        $fastLoginMode.removeClass('primary');
        $defaultLoginMode.removeClass('primary');
        $fastLoginMode.addClass('secondary');
        $defaultLoginMode.addClass('secondary');
        //$return.removeClass('hidden');
    }

    function showLoginButtonDefault() {
        $defaultLoginMode.addClass('primary');
        $defaultLoginMode.removeClass('secondary');
    }

    function showLoginButtonOtp() {
        $fastLoginMode.addClass('primary');
        $fastLoginMode.removeClass('secondary');
    }

    function showLoginButtons() {
        //$fastLoginMode.removeClass('hidden');
        //$defaultLoginMode.removeClass('hidden');
    }

    function clearInputs() {
        $emailInput.val('');
        $otpCodeInput.val('');
    }

    function hideFieldSets() {
        $customOtpFieldset.addClass('hidden');
        $defaultModeFieldset.addClass('hidden');
    }

    $('#email').on('input', function() {
        $('#otp-email').val($(this).val());
    });

    // Cuando se modifique #otp-email, copia su contenido a #email
    $('#otp-email').on('input', function() {
        $('#email').val($(this).val());
    });

    function ajaxRequest(url, type, data, successCallback, errorCallback) {
        $.ajax({
            url: url,
            type: type,
            data: data,
            success: successCallback,
            error: errorCallback || function () {
                console.error('Error en la solicitud AJAX');
            }
        });
    }

    $fastLoginMode.click(function () {
        hideFieldSets();
        hideLoginButtons();
        //$return.removeClass('hidden');
        showLoginButtonDefault();
        $otpEmailSubmit.removeClass('hidden');
        $otpEmailDiv.removeClass('hidden');
        $customOtpFieldset.removeClass('hidden');
        $otpCodeDiv.addClass('hidden');
        $('#otp-login-submit').addClass('hidden');
        $('#message').addClass('hidden');
        $('#message').find('div').text('');
    });

    $return.click(function () {
        $fastLoginMode.removeClass('hidden');
        $defaultLoginMode.removeClass('hidden');
        $return.addClass('hidden');
        clearInputs();
        hideFieldSets();
    });

    $otpEmailSubmit.on('click', function (e) {
        e.preventDefault();
        $(this).addClass('hidden');
        //$otpEmailDiv.addClass('hidden');
        $otpCodeDiv.removeClass('hidden');
        $('#otp-login-submit').removeClass('hidden');

        var email = $emailInput.val();
        ajaxRequest('/otp-login/index/otplogin', 'POST', { email: email }, function (response) {
            if (response.type === 'success') {
                showNotification(response.type, response.message);
            } else {
                showNotification(response.type, response.message);
            }
        });
    });

    $('#otp-login-submit').on('click', function (e) {
        e.preventDefault();
        var otpCode = $otpCodeInput.val();
        ajaxRequest('/otp-login/index/otplogin', 'POST', { otpcode: otpCode }, function (response) {
            if (response.type === 'success') {
                showNotification(response.type, response.message);
                if(response.reload_page){
                    location.reload();
                }
            } else {
                showNotification(response.type, response.message);
            }
        });
    });

    $('#otp-email').off('keydown').on('keydown', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            $('#otp-email-submit').trigger('click');
        }
    });

    $('#otp-code').off('keydown').on('keydown', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            $('#otp-login-submit').trigger('click');
        }
    });

    /** INICIO default **/

    $defaultLoginMode.click(function () {
        hideFieldSets();
        hideLoginButtons();
        showLoginButtonOtp();
        $defaultModeFieldset.removeClass('hidden');
    });

     function showNotification(type, message) {
         $('#message').removeClass('hidden');
         $('#message').removeClass('notice');
         $('#message').removeClass('success');
         $('#message').removeClass('error');
         $('#message').addClass(type);
         $('#message').find('div').text(message);
    }

});
