<?php

if ( ! function_exists( 'add_action' ) ) {
    exit;
}

//Shortcode per la pagina di login
add_shortcode('custom_login_page','custom_login_form');

/**
 * Form di login
 */
function custom_login_form()
{
    ob_start();

    if(is_user_logged_in()) {
        ?>
            <label> Hai già effettuato l'accesso </label>
        <?php
    } else {
        ?>
            <form id="custom-login-form" name="custom-login-form">
                <label for="username" >Username </label>
                <input type="text" name="username" id="username" />
                <label for="password" >Password </label>
                <input type="password" name="password" id="password" />
                <div id="user-message-box"></div>
                <div class="password-registration"><a href="<?php echo get_permalink(get_option(SIIMOB_PASSWORD_RECOVER_PAGE)); ?>">Password dimenticata?</a>   |   <a href="<?php echo get_permalink(get_option(SIIMOB_REGISTRATION_PAGE)); ?>">Registrati</a></div>
                <div class="login-button-container">
                    <button type="submit" id="user-login-submit">Accedi</button>
                </div>
            </form>
        <?php
        wp_enqueue_style(
            'softec-siimob-user-login-css',
            plugin_dir_url( __FILE__ ) . '../css/softec-siimob-user-login.css'
        );



        wp_enqueue_script(
            'softec-siimob-user-login',
            plugin_dir_url( __FILE__ ) . '../js/softec-siimob-user-login.js',
            ['jquery','softec-siimob-utils'],
            null,
            true
        );
        wp_localize_script( 'softec-siimob-user-login', 'sii_mob_user', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    }

    return ob_get_clean();
}

add_action("wp_ajax_nopriv_custom_user_login", "custom_user_login");
add_action("wp_ajax_custom_user_login", "custom_user_login");

/**
 * Funzione per login utente richiamabile via ajax 
 */
function custom_user_login()
{
    $oResponse = new ApiResponse();
    
    $sUsername = $_POST['username'];
    $sPassword = $_POST['password'];

    $oCustomLogin = new CustomLogin($_POST['username'], $_POST['password']);

    $oCustomLogin->validateLogin($oResponse);

    if(! $oResponse->hasErrors()) {
        $oCustomLogin->execLogin($oResponse);
    }

    wp_send_json($oResponse->getReturn());
}

class CustomLogin
{
    private $sUsername;
    private $sPassword;
    private $sRedirect;

    public function __construct($sUsername, $sPassword)
    {
        $this->sUsername = $sUsername;
        $this->sPassword = $sPassword;
        $sProfilePagePermalink = ! empty(get_option(SIIMOB_PROFILE_ANCHOR)) ? get_option(SIIMOB_PROFILE_ANCHOR) : get_permalink(get_option(SIIMOB_PROFILE_PAGE));
        $this->sRedirect = ! empty($sProfilePagePermalink) ? $sProfilePagePermalink : '/profilo';
    }

    public function validateLogin(&$oResponse)
    {
        if (empty($this->sUsername) || empty($this->sPassword)) {
            $oResponse->setError('Compilare tutti i campi');
        }
    }

    public function execLogin(&$oResponse)
    {
        $aCredentials = array(
            'user_login'    => $this->sUsername,
            'user_password' => $this->sPassword,
            'remember'      => true
        );

        $oUser = wp_signon($aCredentials);

        if (is_wp_error($oUser)) {
            if(! empty($oUser->errors['authentication_failed'])) {
                $oResponse->setError($oUser->errors['authentication_failed']);
            } else {
                $oResponse->setError('Username o password errati');
            }
        } else {
            $oResponse->setData('redirect', $this->sRedirect);
        }
    }
}