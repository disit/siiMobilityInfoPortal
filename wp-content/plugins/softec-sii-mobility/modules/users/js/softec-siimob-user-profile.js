jQuery.noConflict()(function($) {
    "use strict";
    $(document).ready(function() {
        $('form#custom-profile-form #email').change(function(e){
            utils.emailChange();
        });
        $('form#custom-profile-form').submit(function(e){
            e.preventDefault();

            $("#user-message-box").empty();
            var errorsArray = [];

            var email = $('#email').val();
            var name = $('#name').val();
            var surname = $('#surname').val();
            var province = $('#user_province').val();
            var type = $('#user_type').val();

            var dashboards = [];

            $('input[id^="dashboard_"]').each(function() {
                dashboards.push($(this).val());
            });

            if(utils.empty(email)) {
                errorsArray.push('Compilare tutti i campi obbligatori');
            }
            if(errorsArray.length === 0 && ! utils.checkEmail(email)) {
                errorsArray.push('Inserire un\'email valida');
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
                    action : 'custom_user_profile',
                    email: email,
                    name: name,
                    surname: surname,
                    user_province: province,
                    user_type: type,
                    user_dashboard: dashboards
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
                    var successData = '<ul><li>' + data.message + '</li></ul>';
                    $("#user-message-box-success").append(successData).slideDown("fast");
                };
                         
                utils.execCall("POST", sii_mob_user.ajaxurl, data, successCallback, errorCallback);
            }
        });

        $('#add-dashboard').click(function(){
            $("#user-message-box").empty();
            $("#user-message-box-success").empty();
            
            var dashboardId = $('#dashboards').val();

            if ($('input[type=hidden]#dashboard_' + dashboardId).length > 0) {
                var errorRow = '<ul>';
                errorRow += '<li>Hai già aggiunto la dashboard selezionata</li>';
                errorRow += '</ul>';
                $("#user-message-box").append(errorRow).slideDown("fast");
            } else {
                var dashboardName = $( "#dashboards option:selected" ).text();
                var dashBoardLink = $( "#dashboards option:selected" ).data("link");
                var dashboard = '<div id="dashboard_' + dashboardId + '" class="col-md-4">';
                dashboard += '<div class="dashboard-circle">';
                dashboard += '<input type="hidden" id="dashboard_' + dashboardId + '" name="dashboard_url[]" value="' + dashboardId + '">';
                dashboard += '<label class="dashboard-title">' + dashboardName + '</label><br/>';
                dashboard += '<a class="dashboard-view-link" target="_blank" href="' + dashBoardLink + '">Visualizza</a><br/>';
                dashboard += '<i onclick="removeElement(this)" id="dashboard_' + dashboardId + '" class="trash-icon fas fa-trash-alt"></i>';
                dashboard += '</div>';
                dashboard += '</div>';

                $('#dashboard-row').append(dashboard).hide().fadeIn("slow");
                $('#user-dashboard-text').show();
            }
        });
    });
});

function removeElement(element)
{
    var id = jQuery(element).attr("id");
    jQuery("div#" + id).fadeOut("normal", function() {
        jQuery(this).remove();
        if( jQuery('input[type=hidden][id^="dashboard_"]').length <= 0) {
            jQuery('#user-dashboard-text').hide();
        }
    });
}