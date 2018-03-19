<?php 

// Interrompe l'esecuzione se il file è richiamato direttamente
if ( ! function_exists( 'add_action' ) ) {
    exit;
}

include( 'includes/softec-siimob-post-type.php');
include( 'includes/softec-siimob-api-utils.php');
include( 'includes/softec-siimob-registration.php');
include( 'includes/softec-siimob-login.php');
include( 'includes/softec-siimob-profile.php');
include( 'includes/softec-siimob-users-settings-page.php');

add_action('init', 'siimob_users_init');

function siimob_users_init()
{
    register_province_taxonomy();
    register_type_taxonomy();
}

add_action( 'show_user_profile', 'show_extra_field' );
add_action( 'edit_user_profile', 'show_extra_field' );

function show_extra_field($oUser)
{
    extra_field_form($oUser);
    user_selected_dasboards($oUser);
}

add_action( 'personal_options_update', 'save_user_extra_fields' );
add_action( 'edit_user_profile_update', 'save_user_extra_fields' );

function save_user_extra_fields( $iUserId ) {
    if ( ! empty( $_POST[SIIMOB_USER_PROVINCE_META_FIELD_NAME] )) {
        update_user_meta($iUserId, SIIMOB_USER_PROVINCE_META_FIELD_NAME, $_POST[SIIMOB_USER_PROVINCE_META_FIELD_NAME]);
    }
    if ( ! empty( $_POST[SIIMOB_USER_TYPE_META_FIELD_NAME] )) {
        update_user_meta($iUserId, SIIMOB_USER_TYPE_META_FIELD_NAME, $_POST[SIIMOB_USER_TYPE_META_FIELD_NAME]);
    }
    update_user_meta($iUserId, SIIMOB_USER_DASHBOARD_META_FIELD_NAME, $_POST[SIIMOB_USER_DASHBOARD_META_FIELD_NAME]);
}

function extra_field_form($oUser)
{
    if (! is_null($oUser) && in_array( 'subscriber', (array) $oUser->roles )) { 
        $aProvinces = get_provinces();
        $iCurrentProvince = $oUser != null ? get_user_meta($oUser->ID, SIIMOB_USER_PROVINCE_META_FIELD_NAME, true) : null;
        $aTypes = get_types();
        $iCurrentType = $oUser != null ? get_user_meta($oUser->ID, SIIMOB_USER_TYPE_META_FIELD_NAME, true) : null;
    ?>
        <h3> Informazioni personali </h3>
        <table class="form-table">
            <tbody class="row">
                <tr class="col-md-6"> 
                    <th class="w-50 float-left">
                        <label for="province">Provincia</label>
                    </th>
                    <td class="w-50 float-left">
                        <select name=<?php echo SIIMOB_USER_PROVINCE_META_FIELD_NAME ?> id=<?php echo SIIMOB_USER_PROVINCE_META_FIELD_NAME ?>>
                            <option value="0">Seleziona la provincia</option>
                            <?php foreach ($aProvinces as $oProvince): ?>
                            <?php $sSelected = (! empty($iCurrentProvince) && $iCurrentProvince == $oProvince->term_id) ? 'selected' : ''; ?>
                                <option <?php echo $sSelected; ?> value="<?php echo $oProvince->term_id; ?>"><?php echo $oProvince->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr class="col-md-6">
                    <th class="w-50 float-left">
                        <label for="type">Tipologia utente</label>
                    </th>
                    <td class="w-50 float-left">
                        <select name=<?php echo SIIMOB_USER_TYPE_META_FIELD_NAME ?> id=<?php echo SIIMOB_USER_TYPE_META_FIELD_NAME ?>>
                            <option value="0">Seleziona la tipologia</option>
                            <?php foreach ($aTypes as $oType): ?>
                            <?php $sSelected = (! empty($iCurrentType) && $iCurrentType == $oType->term_id) ? 'selected' : ''; ?>
                                <option <?php echo $sSelected; ?> value="<?php echo $oType->term_id; ?>"><?php echo $oType->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    <?php
    }
}

function user_selected_dasboards($oUser)
{
    $aDashboards = get_user_dashboards($oUser->ID); 
    $sName = SIIMOB_USER_DASHBOARD_META_FIELD_NAME . '[]'; ?>
    <?php if (! empty($aDashboards)) : ?>
        <h3> Dashboard </h3>
        <table class="form-table">
            <?php foreach ($aDashboards as $iDashboard) : ?>
                <?php $sId = 'dashboard_' . $iDashboard; ?>
                <tr id="<?php echo $iDashboard; ?>">
                    <th>
                        <label><?php echo get_the_title($iDashboard); ?></label>
                    </th>
                    <td>
                        <button class="button button-primary" onclick="removeElement(this)" id="<?php echo $iDashboard;?>" class="trash-icon fas fa-trash-alt">Rimuovi</button>
                        <input type="hidden" name="<?php echo $sName; ?>" id="<?php echo $sId; ?>" value="<?php echo $iDashboard; ?>"/>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <script>
            function removeElement(element)
            {
                var id = jQuery(element).attr('id');
                jQuery('tr#' + id).remove();
            }
        </script>
    <?php endif; ?>
    <?php
}

add_action( 'wp_enqueue_scripts', 'siimob_users_enqueue' );

function siimob_users_enqueue()
{
    wp_enqueue_script(
        'softec-siimob-utils',
        plugin_dir_url( __FILE__ ) . 'js/softec-siimob-user-utils.js',
        'jquery',
        null,
        true
    );
    wp_localize_script( 'softec-siimob-utils', 'sii_mob_user', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

function check_username()
{
    $oResponse = new ApiResponse();
    $sUsername = $_POST['username'];
    $oUser = wp_get_current_user();
    if(strlen($sUsername) < 4) {
        $oResponse->setData('invalid', true);
        $oResponse->setData('message', 'L\'username deve essere di almeno 4 caratteri');
    } else if(username_exists($sUsername) && (empty($oUser) || $oUser->user_login != $sUsername)) {
        $oResponse->setData('invalid', true);
        $oResponse->setData('message', 'E\' già presente un utente con questo username');
    } else {
        $oResponse->setData('invalid', false);
        $oResponse->setData('message', 'Username valido');
    }

    wp_send_json($oResponse->getReturn());
}

add_action("wp_ajax_nopriv_check_username", "check_username");
add_action("wp_ajax_check_username", "check_username");

function check_email()
{
    $oResponse = new ApiResponse();
    $sEmail = $_POST['email'];
    $oUser = wp_get_current_user();
    if(email_exists($sEmail) && (empty($oUser) || $oUser->user_email != $sEmail)) {
        $oResponse->setData('invalid', true);
        $oResponse->setData('message', 'E\' già presente un utente con questa email');
    } else {
        $oResponse->setData('invalid', false);
        $oResponse->setData('message', 'Email valida');
    }

    wp_send_json($oResponse->getReturn());
}

add_action("wp_ajax_nopriv_check_email", "check_email");
add_action("wp_ajax_check_email", "check_email");

function add_recaptcha() 
{
    echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
}

add_action( 'wp_head', 'add_recaptcha' );

function unlog(){
    wp_redirect( site_url() );
    exit();
}

add_action('wp_logout','unlog');