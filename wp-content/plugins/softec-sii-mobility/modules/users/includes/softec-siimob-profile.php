<?php

if ( ! function_exists( 'add_action' ) ) {
    exit;
}

// Shortcode per la pagina del profilo
add_shortcode('custom_profile_page','custom_profile_form');

/**
 * Form del profilo utente
 */
function custom_profile_form()
{
    ob_start();
    
    if(! is_user_logged_in()) {
        ?>
            <div class="left-side"><?php echo do_shortcode( '[custom_login_page]' ); ?></div>
            <div class="center-side"></div>
            <hr class="divisor">
            <div class="right-side d-flex align-items-center flex-column">
                <label class="profile-text-content">Registrati per configurare le dashboard con gli strumenti che preferisci.</label>
                <div class="profile-image-content"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/profilo.png'; ?>"></div>
            </div>
        <?php
    } else {
        $oUser = wp_get_current_user();
        ?>
            <div id="profile" style="margin-bottom: 100px;"></div>
            <form id="custom-profile-form" name="custom-profile-form">
                <div id="first-step">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="username" >Username </label>
                            <input type="text" name="username" id="username" value="<?php echo $oUser->user_login; ?>" disabled="disabled"/>
                        </div>
                        <div class="col-md-6">
                            <label for="email" >Email* </label>
                            <input type="email" name="email" id="email" value="<?php echo $oUser->user_email; ?>"/>
                        </div>
                        <div class="col-md-6">
                            <label for="name" >Nome </label>
                            <input type="text" name="name" id="name" value="<?php echo $oUser->first_name; ?>"/>
                        </div>
                        <div class="col-md-6">
                            <label for="surname" >Cognome </label>
                            <input type="text" name="surname" id="surname" value="<?php echo $oUser->last_name; ?>"/>
                        </div>
                    </div>
                    <?php extra_field_form($oUser); ?>
                    <div class="row">
                        <div class="col-md-8 row ml-auto mr-auto mt-4 mb-4">
                            <?php print_dashboard_select(); ?>
                        </div>
                    </div>
                    <?php print_user_dashboards($oUser); ?>
                    <div id="user-profile-submit-container" class="w-25 ml-auto float-right">
                        <button type="submit" id="user-profile-submit">Salva</button>
                    </div>
                    <div id="user-message-box"></div>
                    <div id="user-message-box-success"></div>
                </div>
            </form>
        <?php
    }
    wp_enqueue_style(
        'softec-siimob-user-profile-css',
        plugin_dir_url( __FILE__ ) . '../css/softec-siimob-user-profile.css'
    );
    wp_enqueue_script(
        'softec-siimob-user-profile',
        plugin_dir_url( __FILE__ ) . '../js/softec-siimob-user-profile.js',
        ['jquery','softec-siimob-utils'],
        null,
        true
    );
    wp_localize_script( 'softec-siimob-user-profile', 'sii_mob_user', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    return ob_get_clean();   
}

function get_user_dashboards($iUserId)
{
    return get_user_meta($iUserId, SIIMOB_USER_DASHBOARD_META_FIELD_NAME, true);
}

function print_user_dashboards($oUser)
{
    $aDashboards = get_user_dashboards($oUser->ID);
    $sName = SIIMOB_USER_DASHBOARD_META_FIELD_NAME . '[]'; 
    $sDisplay = ! empty($aDashboards) ? 'inline-block' : 'none' ; ?>

    <div id="user-dashboard">
        <label id="user-dashboard-text" style="display: <?php echo $sDisplay; ?>">Le tue dashboard</label>
        <div id="dashboard-row" class="dashboard-row row">
            <?php if (! empty($aDashboards)): ?>
                <?php foreach ($aDashboards as $iDashboard) : 
                    $oDashboard = get_post($iDashboard);
                    $sId = 'dashboard_' . $iDashboard;
                    $sUrl = get_post_meta($iDashboard, SIIMOB_DASHBOARD_URL_META_FIELD_NAME, true);
                    $sUrl = ! empty($sUrl) ? $sUrl : 'javascript:void(0)';
                    $sPermalink = get_permalink($iDashboard);
                ?>
                    <div id="<?php echo $sId;?>" class="col-md-4">
                        <div class="dashboard-circle">
                            <input type="hidden" id="<?php echo $sId;?>" name="<?php echo $sName;?>" value="<?php echo $iDashboard;?>">
                            <label class="dashboard-title"><?php echo $oDashboard->post_title?></label><br/>
                            <a class="dashboard-view-link" target="_blank" href="<?php echo $sPermalink;?>">Visualizza</a><br/>
                            <i onclick="removeElement(this)" id="<?php echo $sId;?>" class="trash-icon fas fa-trash-alt"></i>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php
}

function print_dashboard_select()
{
    $aDashboards = get_dashboards(); ?>
    <div class="col-md-12 text-center">
        <label id="add-dashboard-text" class="mb-3" for="dashboards">Aggiungi una dashboard</label>
    </div>
    <div class="col-md-7 d-flex align-items-center justify-content-center mb-4">
        <select name="dashboards" id="dashboards" class="w-100">

            <?php foreach ($aDashboards as $oDashboard) : ?>
                <?php
                    $sPermalink = get_permalink($oDashboard->ID);
                    $iImageId = get_post_thumbnail_id($oDashboard->ID);
                    $sImageUrl = wp_get_attachment_image_src($iImageId, 'full');
                ?> 
                <option value="<?php echo $oDashboard->ID ?>" data-link="<?php echo $sPermalink; ?>" data-image="<?php echo $sImageUrl[0]; ?>"><?php echo $oDashboard->post_title; ?></option>
            <?php endforeach; ?>

        </select>
    </div>
    <div class="col-md-5 mb-4">
        <button class="w-100" type="button" id="add-dashboard">Aggiungi</button>
    </div>
    <?php
}
/**
 * Funzione per salvataggio del profilo utente richiamabile via ajax
 */

function custom_user_profile()
{
    $oResponse = new ApiResponse();

    $oCustomProfile = new CustomProfile($_POST['email'], $_POST['name'], $_POST['surname'], $_POST[SIIMOB_USER_TYPE_META_FIELD_NAME], $_POST[SIIMOB_USER_PROVINCE_META_FIELD_NAME], $_POST[SIIMOB_USER_DASHBOARD_META_FIELD_NAME]);

    $oCustomProfile->validateProfile($oResponse);

    if (! $oResponse->hasErrors()) {
        $oCustomProfile->saveProfile($oResponse);

        if(! $oResponse->hasErrors()) {
            $oCustomProfile->updateMeta($oResponse);
        }
    }

    wp_send_json($oResponse->getReturn());
}

add_action("wp_ajax_nopriv_custom_user_profile", "custom_user_profile");
add_action("wp_ajax_custom_user_profile", "custom_user_profile");

class CustomProfile
{
    private $sEmail;
    private $sName;
    private $sSurname;
    private $iType;
    private $iProvince;
    private $oUser;
    private $aDashboards;

    public function __construct($sEmail, $sName, $sSurname, $iType, $iProvince, $aDashboards)
    {
        $this->sEmail = $sEmail;
        $this->sName = $sName;
        $this->sSurname = $sSurname;
        $this->iType = $iType;
        $this->iProvince = $iProvince;
        $this->aDashboards = $aDashboards;
        $this->oUser = wp_get_current_user();
    }

    /**
     * Validazione campi profilo utente
     */
    function validateProfile(&$oResponse)
    {
        if (empty($this->sEmail)) {
            $oResponse->setError('Compilare tutti i campi obbligatori');
        }

        if (! is_email($this->sEmail)) {
            $oResponse->setError('L\'email inserita non è nel formato corretto');
        }

        if(email_exists($this->sEmail) && (empty($this->oUser) || $this->oUser->user_email != $this->sEmail)) {
            $oResponse->setError('Email già esistente');
        }

        $this->aDashboards = array_unique($this->aDashboards);
    }

    /**
     * Salvataggio profilo utente
     */
    function saveProfile(&$oResponse)
    {
        $aUserData = [
            'ID' => $this->oUser->ID,
            'user_email' => $this->sEmail,
            'first_name' => $this->sName,
            'last_name' => $this->sSurname
        ];

        $iUserId = wp_update_user($aUserData);

        if (is_wp_error($iUserId)) {
            $oResponse->setError('Errore durante il salvataggio');
        }
    }

    /**
     * Salvataggio meta dati (Provincia e tipologia utente)
     */
    function updateMeta(&$oResponse)
    {
        update_user_meta($this->oUser->ID, SIIMOB_USER_TYPE_META_FIELD_NAME, $this->iType);
        update_user_meta($this->oUser->ID, SIIMOB_USER_PROVINCE_META_FIELD_NAME, $this->iProvince);
        update_user_meta($this->oUser->ID, SIIMOB_USER_DASHBOARD_META_FIELD_NAME, $this->aDashboards);
        $oResponse->setData('message', 'Utente aggiornato con successo');
    }
}