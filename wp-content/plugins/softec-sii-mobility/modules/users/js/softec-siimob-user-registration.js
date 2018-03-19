jQuery.noConflict()(function($) {
    "use strict";
    $(document).ready(function() {
        $('form#custom-registration-form #user-registration-reset').click(function(e){
            e.preventDefault();
            var fields = ['username', 'email', 'name', 'surname', 'password', 'password_confirm'];
            fields.forEach(function(field){
                $('#' + field).val("");    
            });
            $("#user-message-box").empty();
            $("#user-message-box-success").empty();
            utils.resetMessage('username');
            utils.resetMessage('email');
            grecaptcha.reset();
        });

        $('form#custom-registration-form').submit(function(e){
            e.preventDefault();

            $("#user-message-box").empty();
            var errorsArray = [];

            var username = $('#username').val();
            var email = $('#email').val();
            var name = $('#name').val();
            var surname = $('#surname').val();
            var password = $('#password').val();
            var password_confirm = $('#password_confirm').val();

            if(utils.empty(username) || utils.empty(email) || utils.empty(password) || utils.empty(password_confirm)) {
                errorsArray.push('Compilare tutti i campi obbligatori');                
            }
            if(errorsArray.length === 0 && ! utils.checkEmail(email)) {
                errorsArray.push('Inserire un\'email valida');
            }
            if(errorsArray.length === 0 && password != password_confirm) {
                errorsArray.push('Le password non coincidono');
            }
            if (!(grecaptcha && grecaptcha.getResponse().length > 0)) {
                errorsArray.push("Spunta la casella <strong>Non sono un robot</strong>");
            }
            if(errorsArray.length !== 0) {
                var errorRow = "<ul>";

                $.each(errorsArray, function(err) {
                  errorRow += "<li>" + errorsArray[err] + "</li>";
                });

                errorRow += "</ul>";
                $("#user-message-box").append(errorRow).slideDown("fast");
            } else {
                var data = {
                    action : 'custom_user_registration',
                    username: username,
                    email: email,
                    name: name,
                    surname: surname,
                    password: password,
                    password_confirm: password_confirm,
                    captcha: grecaptcha.getResponse()
                };

                var errorCallback = function(errors) {
                    var errorRow = "<ul>";
                    $.each(errors, function(err) {
                       errorRow += "<li>" + errors[err] + "</li>";
                    });

                    errorRow += "</ul>";
                    $("#user-message-box").append(errorRow).slideDown("fast");
                    grecaptcha.reset();
                };

                var successCallback = function(data) {
                    var successData = '<ul><li>' + data.message + '</li></ul>';
                    $("#user-message-box-success").append(successData).slideDown("fast");
                    grecaptcha.reset();
                };
                         
                utils.execCall("POST", sii_mob_user.ajaxurl, data, successCallback, errorCallback);
            }
        });
        $('form#custom-registration-form #username').change(function(e){
            utils.usernameChange();
        });

        $('form#custom-registration-form #email').change(function(e){
            utils.emailChange();
        });
        $('form#custom-registration-form #password_confirm').change(function(e){
            utils.passwordChange();
        });
        $('form#custom-registration-form #password').change(function(e){
            utils.passwordChange();
        });
    });
});