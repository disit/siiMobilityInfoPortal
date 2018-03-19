<?php

DEFINE('GOOGLE_RECAPTCHA_SITE_KEY', 'google_recaptcha_site_key');
DEFINE('GOOGLE_RECAPTCHA_SECRET_KEY', 'google_recaptcha_secret_key');
DEFINE('SIIMOB_LOGIN_PAGE', 'softec_login_page');
DEFINE('SIIMOB_REGISTRATION_PAGE', 'softec_registration_page');
DEFINE('SIIMOB_PROFILE_PAGE', 'softec_profile_page');
DEFINE('SIIMOB_PASSWORD_RECOVER_PAGE', 'softec_password_recover_page');
DEFINE('SIIMOB_PROFILE_ANCHOR', 'softec_profile_anchor');
DEFINE('SIIMOB_CAMPAIGN_PAGE' , 'softec_campaign_page');

DEFINE('SIIMOB_OPTION_GROUP', 'softec_siimob_users_options_group');

function softec_siimob_users_register_settings() 
{
    foreach (get_siimob_options() as $sName => $aOption) {
        add_option( $sName, '');    
        register_setting(SIIMOB_OPTION_GROUP, $sName, '' );
    }
}

add_action( 'admin_init', 'softec_siimob_users_register_settings' );

function softec_siimob_users_register_option_page() {
  add_options_page('Impostazioni utenti Sii-Mobility', 'Sii-Mobility', 'manage_options', 'softec-siimob-users', 'softec_siimob_users_option_page');
}
add_action('admin_menu', 'softec_siimob_users_register_option_page');

function get_siimob_options()
{
    return $aOptions = [
        GOOGLE_RECAPTCHA_SITE_KEY => [
            'label' => 'Google recaptcha site-key',
            'type' => 'text'
        ],
        GOOGLE_RECAPTCHA_SECRET_KEY => [
            'label' => 'Google recaptcha secret-key',
            'type' => 'text'
        ],
        SIIMOB_LOGIN_PAGE => [
            'label' => 'Pagina di login',
            'type' => 'select'
        ],
        SIIMOB_REGISTRATION_PAGE => [
            'label' => 'Pagina di registrazione',
            'type' => 'select'
        ],
        SIIMOB_PROFILE_PAGE => [
            'label' => 'Pagina del profilo',
            'type' => 'select'
        ],
        SIIMOB_PASSWORD_RECOVER_PAGE => [
            'label' => 'Pagina per recupero password',
            'type' => 'select'
        ],
        SIIMOB_PROFILE_ANCHOR => [
            'label' => 'Ancora profilo homepage',
            'type' => 'text'
        ],
        SIIMOB_CAMPAIGN_PAGE => [
            'label' => 'Pagina campagna Sii-Smart Sii-Mobility',
            'type' => 'select'
        ]
    ];
}
function softec_siimob_users_option_page()
{
    $aPages = get_pages();
    ?>
        <div>
            <?php screen_icon(); ?>
            <h2>Impostazioni utenti Sii-Mobility</h2>
            <form method="post" action="options.php">
            <?php settings_fields( 'softec_siimob_users_options_group' ); ?>
            <table class="form-table">
                <?php foreach(get_siimob_options() as $sName => $aOption): ?>
                    <tr>
                    <?php switch ($aOption['type']) {
                        case 'text':?>
                            <th scope="row"><label for="<?php echo $sName;?>"><?php echo $aOption['label']; ?></label></th>
                            <td><input type="text" id="<?php echo $sName;?>" name="<?php echo $sName;?>" value="<?php echo get_option(
                        $sName); ?>" /></td>        
                            <?php break;
                        case 'select': ?>
                            <th scope="row"><label for="<?php echo $sName;?>"><?php echo $aOption['label']; ?></label></th>
                            <td>
                                <select id="<?php echo $sName;?>" name="<?php echo $sName;?>">
                                    <?php foreach($aPages as $oPage): ?>
                                        <option <?php echo $oPage->ID == get_option($sName) ? 'selected' : ''; ?> value="<?php echo $oPage->ID; ?>"><?php echo $oPage->post_title; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <?php break;
                    }
                ?>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
            </form>
        </div>
    <?php
}