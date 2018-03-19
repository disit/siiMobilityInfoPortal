var utils = {
    empty: function(variable) {
        empty = false;
        if(variable == '' || variable == null || variable == undefined) {
            empty = true;
        }
        return empty;
    },
    checkEmail: function(email) {
        var filter = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        if (! filter.test(email)) {
            return false;
        }
        return true;
    },
    execCall: function(method, endpoint, data, successCallback, errorCallback, spinner = true) {
        jQuery.ajax({
            type: method,
            url: endpoint,
            data: data,
            success: function(response) {
                console.log(response);
                if (response.error != null && response.error.length > 0) {
                    errorCallback(response.error);
                } else {
                    successCallback(response.data);
                }
            },
            beforeSend: function() {
                jQuery("div#user-message-box").empty();
                jQuery("div#user-message-box-success").empty();
                // Mostra il box dei messaggi del form con uno spinner ad indicare il caricamento
                if(spinner) {
                    jQuery("div#user-message-box").addClass("spinner").slideDown("fast");
                }
            },
            complete: function() {
                // Rimouve lo spinner e inserisce i risultati nel box dei messaggi del form
                jQuery("div#user-message-box").removeClass("spinner");
            }
        });
    },
    usernameChange: function() {
        var username = jQuery('#username').val();

        if (! utils.empty(username)) {
            var data = {
                action : 'check_username',
                username: username
            };

            utils.execCall(
                "POST", 
                sii_mob_user.ajaxurl, 
                data, 
                function(data) {
                    if (data.invalid) {
                        utils.addMessage('username', 'success', 'error', data.message);
                    } else {
                        utils.addMessage('username', 'error', 'success', data.message);
                    }
                }, 
                function() {},
                false
            );
        } else {
            utils.resetMessage('username');
        }
    },
    emailChange: function() {
        var email = jQuery('#email').val();
        if (! utils.empty(email)) {
            if(! utils.checkEmail(email)) {
                utils.addMessage('email', 'success', 'error', 'L\'email non è nel formato corretto');
            } else {
                var data = {
                    action : 'check_email',
                    email: email
                };

                utils.execCall(
                    "POST", 
                    sii_mob_user.ajaxurl, 
                    data, 
                    function(data) {
                        if (data.invalid) {
                            utils.addMessage('email', 'success', 'error', data.message);
                        } else {
                            utils.addMessage('email', 'error', 'success', data.message);
                        }
                    }, 
                    function() {},
                    false
                );
            }
        } else {
            utils.resetMessage('email');
        }
    },
    passwordChange: function() {
        var password = jQuery('#password').val();
        var password_confirm = jQuery('#password_confirm').val();
        jQuery('#password_confirm').removeClass('success').removeClass('error');
        jQuery('.password-error-message').remove();
        if(! utils.empty(password_confirm) && ! utils.empty(password) && password_confirm != password) {
            jQuery('#password_confirm').addClass('error');
            jQuery('<label class="password-error-message">Le password non coincidono</label>').insertAfter('#password_confirm');
        }
    },
    removeClasses: function(field, classes) {
        classes.forEach(function(sClass){
            jQuery('#' + field).removeClass(sClass);    
        });
    },
    resetMessage: function(field) {
        utils.removeClasses(field, ['success', 'error']);
        jQuery('.' + field + '-error-message').remove();
        jQuery('.' + field + '-success-message').remove();
    },

    changeMessages: function(field, sRemoveClass, sAddClass) {
        utils.resetMessage(field);
        jQuery('#' + field).addClass(sAddClass);
    },
    addMessage: function(field, sRemoveClass, sAddClass, message) {
        utils.changeMessages(field, sRemoveClass, sAddClass);
        jQuery('<label class="' + field + '-' + sAddClass + '-message">' + message + '</label><br/>').insertAfter('#' + field);
    }
};