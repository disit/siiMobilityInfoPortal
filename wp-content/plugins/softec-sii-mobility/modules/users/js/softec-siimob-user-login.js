jQuery.noConflict()(function($) {
    "use strict";
    $(document).ready(function() {
        $('form#custom-login-form').submit(function(e){
            e.preventDefault();
            $("#user-message-box").empty();

            var username = $('#username').val();
            var password = $('#password').val();

            var data = {
                action : 'custom_user_login',
                username: username,
                password: password,
            };

            var errorCallback = function(errors) {
                var errorRow = "<ul>";

                $.each(errors, function(err) {
                   errorRow += "<li>" + errors[err] + "</li>";
                });

                errorRow += "</ul>";
                $("#user-message-box").append(errorRow).slideDown("fast");
            };

            var successCallback = function(data) {
                if(location.href == location.origin + '/') {
                    location.href = data.redirect
                    location.reload(true)
                } else {
                    location.href = location.origin + data.redirect;
                }
            };
                     
            utils.execCall("POST", sii_mob_user.ajaxurl, data, successCallback, errorCallback);
        });
    });
});