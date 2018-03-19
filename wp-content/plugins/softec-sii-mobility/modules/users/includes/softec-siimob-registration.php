<?php

if ( ! function_exists( 'add_action' ) ) {
    exit;
}

//Shortcode per la form di registrazione
add_shortcode('custom_registration_page','custom_registration_form');

/**
 * Form per la registrazione
 */
function custom_registration_form()
{
    ob_start();

    if(is_user_logged_in()) {
        ?>
            <label> Devi effettuare il logout per poterti registrare </label>
        <?php
    } else {
        ?>
            <form id="custom-registration-form" name="custom-registration-form">
                <div class="row">
                    <div class="col-md-6">
                        <label for="username" >Username* </label>
                        <input type="text" name="username" id="username" />
                    </div>
                    <div class="col-md-6">
                        <label for="email" >Email* </label>
                        <input type="email" name="email" id="email" />
                    </div>
                    <div class="col-md-6">
                        <label for="name" >Nome </label>
                        <input type="text" name="name" id="name" />
                    </div>
                    <div class="col-md-6">
                        <label for="surname" >Cognome </label>
                        <input type="text" name="surname" id="surname" />
                    </div>
                    <div class="col-md-6">
                        <label for="password" >Password* </label>
                        <input type="password" name="password" id="password" />
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirm" >Conferma Password* </label>
                        <input type="password" name="password_confirm" id="password_confirm" />
                    </div>
                </div>
                <div class="g-recaptcha" data-size="normal" data-sitekey="<?php echo get_option(GOOGLE_RECAPTCHA_SITE_KEY); ?>"></div>
                <div id="user-message-box"></div>
                <div id="user-message-box-success"></div>
                <button id="user-registration-reset">Ripristina</button>
                <button type="submit" id="user-registration-submit">Registrati!</button>
            </form>

        <?php
        wp_enqueue_style(
            'softec-siimob-user-registration-css',
            plugin_dir_url( __FILE__ ) . '../css/softec-siimob-user-registration.css'
        );
        wp_enqueue_script(
            'softec-siimob-user-registration',
            plugin_dir_url( __FILE__ ) . '../js/softec-siimob-user-registration.js',
            ['jquery','softec-siimob-utils'],
            null,
            true
        );
        wp_localize_script( 'softec-siimob-user-registration', 'sii_mob_user', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    }
    return ob_get_clean();
}

/**
 * Funzione richiamata via ajax per la registrazione
 */
function custom_user_registration() 
{   
    $oResponse = new ApiResponse();

    $oCustomRegistration = new CustomRegistration($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password_confirm'], $_POST['name'], $_POST['surname'], $_POST['captcha']);
    $oCustomRegistration->validateRegistration($oResponse);

    if (! $oResponse->hasErrors()) {
        $oCustomRegistration->completeRegistration($oResponse);
    }

    wp_send_json($oResponse->getReturn());
}

add_action("wp_ajax_nopriv_custom_user_registration", "custom_user_registration");
add_action("wp_ajax_custom_user_registration", "custom_user_registration");

class CustomRegistration
{
    private $sUsername;
    private $sEmail;
    private $sPassword;
    private $sPasswordConfirm;
    private $sName;
    private $sSurname;
    private $sCaptcha;

    public function __construct($sUsername, $sEmail, $sPassword, $sPasswordConfirm, $sName, $sSurname, $sCaptcha)
    {
        $this->sUsername = $sUsername;
        $this->sEmail = $sEmail;
        $this->sPassword = $sPassword;
        $this->sPasswordConfirm = $sPasswordConfirm;
        $this->sName = $sName;
        $this->sSurname = $sSurname;
        $this->sCaptcha = $sCaptcha;
    }

    /**
     * Validazione campi registrazione
     */
    function validateRegistration(&$oResponse)
    {
        if (empty($this->sUsername)
            || empty($this->sEmail)
            || empty($this->sPassword)
            || empty($this->sPasswordConfirm)) {

            $oResponse->setError('Compilare tutti i campi obbligatori');
        }

        $sCaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify?secret=' . get_option(GOOGLE_RECAPTCHA_SECRET_KEY) . '&response=' . $this->sCaptcha;
        $sCaptchaResult = file_get_contents($sCaptchaUrl);
        $oCaptcha = json_decode($sCaptchaResult);

        if (! $oCaptcha->success) {
            $oResponse->setError('Errore validazione captcha');
        }

        if (strlen($this->sUsername) < 4) {
            $oResponse->setError('L\'username deve essere di almeno 4 caratteri');
        }

        if (! validate_username($this->sUsername)) {
            $oResponse->setError('L\'username inserito non è valido');
        }

        if(username_exists($this->sUsername)) {
            $oResponse->setError('Username già esistente');
        }

        if (strlen($this->sPassword) < 5) {
            $oResponse->setError('La password deve essere di almeno 5 caratteri');
        }

        if ($this->sPasswordConfirm != $this->sPassword) {
            $oResponse->setError('Le password non coincidono');
        }

        if (! is_email($this->sEmail)) {
            $oResponse->setError('L\'email inserita non è nel formato corretto');
        }

        if (email_exists($this->sEmail)) {
            $oResponse->setError('Email già esistente');
        }

        return true;
    }

    /**
     * Registro l'utente
     */
    public function completeRegistration(&$oResponse)
    {
        $aUserData = [
            'user_login' => sanitize_user($this->sUsername),
            'user_email' => sanitize_email($this->sEmail),
            'user_pass' => esc_attr($this->sPassword),
            'first_name' => sanitize_text_field($this->sName),
            'last_name' => sanitize_text_field($this->sSurname)
        ];

        $iUserId = wp_insert_user($aUserData);

        if (is_wp_error($iUserId)) {
            $oResponse->setError('Errore durante la creazione dell\'utente');
        }

        if(! $oResponse->hasErrors()) {
            
            $oResponse->setData('message', 'E\' stata inviata un\'email all\'indirizzo ' . $this->sEmail . ' per confermare la registrazione');
        }

        return true;
    }
}