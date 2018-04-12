<?php 
/*
 *
 * ===== POST TYPE =====
 * 1. Registrazione post_type di tipo siimob_comment
 *
 *
 * ===== INVIO COMMENTI =====
 * 1. I commenti verranno inviati come post di Wordpress, con post_type di tipo siimob_comment
 * 2. Il post avrà i seguenti post_meta:
 *      2.1 siimob_comment_service_name:  nome del servizio o POI da Service Map
 *      2.2 siimob_comment_service_uri:   URI del servizio o POI da Service Map
 *      2.3 siimob_comment_photo_thumb:   URL dell'immagine anteprima da Service Map
 *      2.4 siimob_comment_civic:         numero civico del servizio o POI da Service Map
 *      2.5 siimob_comment_type_label:    tipo di servizio o POI da Service Map
 *      2.6 siimob_comment_stars:         valutazione da 1 a 5 del servizio o POI
 *      2.7 siimob_comment_timestamp:     data e ora del commento, reperite da Sii-Mobility o da WP
 *      2.8 siimob_comment_uid:           user id dell'utente su Sii-Mobility
 *      2.9 siimob_comment_uploaded:      flag booleano indicante se il commento è caricato su Sii-Mobility (default: 0)
 * 3. Il form di invio commenti presenta le seguenti funzionalità:
 *      3.1 Campo con ricerca POI o Servizio mediante chiamata AJAX a Service Map.
 *          La chiamata restituirà i seguenti valori:
 *          3.1.1 URI con ID del serviio o POI da salvare nel database
 *          3.1.2 Nome del servizio o POI da visualizzare all'utente
 *      3.2 Campo valutazione servizio o POI da 1 a 5 stelle
 *      3.3 Textarea per commenti testuale (solo testo, niente editor WYSIWYG)
 *      3.5 Pulsante di invio commento
 *      3.6 Pulsante di reset del form
 * 4. I commenti verranno inviati a Wordpress tramite AJAX. Una volta inviati, il form verrà svuotato
 *    dei valori inseriti e un messaggio ringrazierà l'utente del suo commento e verrà aggiornata la
 *    lista dei commenti, includendo il commento appena inviato
 *
 *
 * ===== USERMETA =====
 * 1. Creazione user_meta con valore l'uid di Sii-Mobility
 * 2. Hardcoding dell'uid di Sii-Mobility a user_meta fino a che non viene completato lo sviluppo 
 *    della User information API e integrato il single sign on con Facebook
 *
 *
 * ===== LISTING COMMENTI =====
 * 1. Chiamata AJAX per richiedere gli ultimi 5 commenti da Wordpress
 * 2. Visualizzazione commenti con le seguenti caratteristiche:
 *      2.1 Commenti ordinati cronologicamente dal più recente al meno recente
 *      2.2 Visualizzazione dei seguenti campi:
 *          2.2.1 Nome del servizio o POI con numero civico e tipo di servizio o POI
 *          2.2.2 Date e ora invio commento
 *          2.2.2 Valutazione da 1 a 5
 *          2.2.3 Commento testuale
 *          2.2.4 Foto miniatura di anteprima (se assente verrà visualizzata un'immagine di default)
 * 3. Se viene effettuato uno scroll sulla lista di commenti, una nuova chiamata AJAX visualizzerà altri 5
 *    commenti, sempre visualizzati dal più recente al meno recente
 *
 *
 * ===== CRON WORDPRESS =====
 * 1. Impostazione del cron di Wordpress per upload e download dei commenti da e per Sii-Mobility ogni 5 minuti
 * 2. Wordpress scaricherà tutti i commenti con timestamp superiore all'ultimo commento con
 *    flag siimob_comment_uploaded impostato a true e gli imposterà i seguenti post_meta:
 *      2.1 siimob_comment_service_name:  nome del servizio o POI da Service Map
 *      2.2 siimob_comment_service_uri:   URI del servizio o POI da Service Map
 *      2.3 siimob_comment_photo_thumb:   URL dell'immagine anteprima reperita con chiamata alla Service Info API
 *      2.4 siimob_comment_civic:         numero civico del servizio o POI da Service Map
 *      2.5 siimob_comment_type_label:    tipo di servizio o POI da Service Map
 *      2.6 siimob_comment_stars:         valutazione da 1 a 5 del servizio o POI
 *      2.7 siimob_comment_timestamp:     data e ora del commento reperito da Sii-Mobility
 *      2.8 siimob_comment_uid:           nessun valore
 *      2.9 siimob_comment_uploaded:      valore true
 * 3. Successivamente invierà tramite API a Sii-Mobility i commenti non ancora caricati, identificati dal
 *    flag siimob_comment_uploaded impostato a false.
 * 4. A caricamento completato, imposterà a true il flag siimob_comment_uploaded nel post_meta dei commenti appena caricati
 *
 *
 * @TODO:
 * - Integrare sistema per prendere l'uid di Sii-Mobility ed associarlo al login utente su
 *   Wordpress (Facebook Single Sign On e/o servizi affini)
 * - Configurare reCAPTCHA per il dominio del sito su cui verrà caricato il plugin
 * - Scrivere nel task cron di Wordpress le istruzioni pr il download e l'upload dei commenti da e per Sii-Mobility
 *
 */


// Interrompe l'esecuzione se il file è richiamato direttamente
if ( ! function_exists( 'add_action' ) ) {
    exit;
}


// Definisce le costanti globali del plugin
DEFINE( 'SIIMOB_COMMENT__POST_TYPE_NAME',   'siimob_comment' );
DEFINE( 'SIIMOB_COMMENT__USERMETA_UID',     'siimob_comment_uid' );

DEFINE( 'SIIMOB_COMMENT__POSTMETA_SERVICE_NAME',    'siimob_comment_service_name' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_SERVICE_URI',     'siimob_comment_service_uri' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_PHOTO_THUMB',     'siimob_comment_photo_thumb' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_CIVIC',           'siimob_comment_civic' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_TYPE_LABEL',      'siimob_comment_type_label' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_RATING',          'siimob_comment_rating' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_TIMESTAMP',       'siimob_comment_timestamp' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_UID',             'siimob_comment_uid' );
DEFINE( 'SIIMOB_COMMENT__POSTMETA_UPLOADED',        'siimob_comment_uploaded' );

DEFINE('GOOGLE_RECAPTCHA_SITE_KEY',   'google_recaptcha_site_key');
DEFINE('GOOGLE_RECAPTCHA_SECRET_KEY', 'google_recaptcha_secret_key');


// Registra gli hook per l'attivazione e la disattivazione del plugin
register_activation_hook( __FILE__, 'siimob_comments_activation' );
register_deactivation_hook( __FILE__, 'siimob_comments_deactivation' );

// Aggiunge l'action per il Cron
add_action( 'siimob_comments_refresh', 'siimob_comments_download_upload' );


/*
 * Inizializzazione plugin
 *  - Creazione post type di tipo siimob_comment per identificare i commenti di Sii-Mobility nei post di Wordpress
 *  - Creazione action per il cron di Wordpress per il donwload e l'upload dei commenti da Sii-Mobility
 */
function siimob_comment_init() {
    // Registro post type
    register_post_type(
        SIIMOB_COMMENT__POST_TYPE_NAME,
        array(
            'label'                 => 'Commenti Sii Mobility',
            'public'                => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => true,
            'show_ui'               => true,
            'show_in_nav_menus'     => true,
            'show_in_menu'          => true,
            'show_in_admin_bar'     => true,
            'query_var'             => true,
            'has_archive'           => false,
            'hierarchical'          => false,
        )
    );
}


/*
 * Creazione user_meta con chiave siimob_uid per associare l'account Wordpress con l'id utente su Sii-Mobility
 * Harcoda gli uid di Sii-Mobility in attesa dell'API per prelevare le info degli utenti e del single
 * sign on di Facebook e relativa documentazione
 */
function siimob_comment_users_meta( $action ) {
    // Prende tutti gli utenti di Wordpress
    $users = get_users();

    if ( ! empty( $users ) ) {
        if ( $action == 'add' ) {
            // Itera sugli utenti hardcodandogli l'uid di Sii-Mobility con un hash in SHA256 in uno user_meta
            foreach ( $users as $user )
            {
                $uid_hash = hash( 'sha256', $user->id );
                add_user_meta( $user->id, SIIMOB_COMMENT__USERMETA_UID, $uid_hash, true );
            }
        } elseif ( $action == 'remove' ) {
            // Itera sugli utenti rimuovendogli l'uid di Sii-Mobility hardcodato
            foreach ( $users as $user )
            {
                delete_user_meta( $user->id, SIIMOB_COMMENT__USERMETA_UID );
            }
        } else {
            exit;
        }
    }
}


/*
 * Callback per aggiungere l'intervallo di 5 minuti agli schedule di WP Cron
 */
function add_cron_recurrence_interval( $schedules ) {
    $schedules['every_five_minutes'] = array(
        'interval'  => 5, // Ogni 5 minuti (300 secondi, impostato a 5 secondi per debug)
        'display'   => __('Ogni 5 minuti'),
    );

    return $schedules;
}


/*
 * Funzione di attivazione plugin.
 * - Crea gli user_meta con valore l'uid di Sii-Mobility
 */
function siimob_comments_activation() {
    // Aggiunge gli user meta agli utenti esistenti
    // siimob_comment_users_meta( 'add' );

    // Schedula ogni 5 minuti l'upload ed il download dei commenti da Sii-Mobility
    wp_schedule_event( time(), 'every_five_minutes', 'siimob_comments_refresh' );
}


/*
 * Funzione di disattivazione plugin.
 * - Cancella tutti i commenti caricati su Wordpress con post_type == siimob_comment
 * - Deregistra siimob_comment come post_type
 * - Cancella tutti gli user_meta che hanno come valore l'uid utente di Sii-Mobility
 *
 * Questa funzione è pensata per ripristinare il plugin allo stato iniziale
 */
function siimob_comments_deactivation() {
    // Seleziona tutti i post con post_type == siimob_comment
    $query = new WP_Query( array(
        'post_type'         => SIIMOB_COMMENT__POST_TYPE_NAME,
        'post_status'       => 'publish',
        'posts_per_page'    => -1,
    ));

    // Itera sui post per cancellarli completamente
    while( $query->have_posts() ) {
        $query->the_post();
        wp_delete_post( get_the_ID(), true );
    }

    wp_reset_postdata();

    // Deregistra il post_type siimob_comment
    unregister_post_type( SIIMOB_COMMENT__POST_TYPE_NAME );

    // Rimuove gli user_meta con l'uid di Sii-Mobility hardcodati
    // siimob_comment_users_meta( 'remove' );

    // Rimuove dal Cron lo schedule di download e upload commenti da Sii-Mobility
    wp_clear_scheduled_hook( 'siimob_comments_refresh' );
}


/*
 * Callback eseguita ogni 5 minuti dal cron di Wordpress per ricevere ed inviare i commenti a Sii-Mobility
 */
function siimob_comments_download_upload() {
    /*
     * Funzioni per il download e upload commenti da e per Sii-Mobility
     */
}


/*
 * Funzione invocata a seguito di chiamata POST mediante AJAX dal frontend
 * Crea un post con post_type == siimob_comment con i dati inviati tramite AJAX
 */
function siimob_comment_post() {
    // Eliminato il campo utente, i commenti saranno aperti a tutti gli utenti che accedono al portale
    // $current_user   = wp_get_current_user();
    $error_counter  = 0;
    $json_response  = array();

    // Prende i valori inviati tramite AJAX
    $service_name = sanitize_text_field( $_REQUEST['service_name'] );
    $service_uri  = sanitize_text_field( $_REQUEST['service_uri'] );
    $photo_thumb  = sanitize_text_field( $_REQUEST['photo_thumb'] );
    $civic        = sanitize_text_field( $_REQUEST['civic'] );
    $type_label   = sanitize_text_field( $_REQUEST['type_label'] );
    $stars        = (int) $_REQUEST['stars'];
    $comment      = $_REQUEST['comment'];
    $captcha      = $_REQUEST['captcha'];

    // Validazione reCAPTCHA
    $captcha_secret = get_option(GOOGLE_RECAPTCHA_SECRET_KEY);
    $captcha_url    = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $captcha_secret . '&response=' . $captcha;
    $captcha_json   = file_get_contents( $captcha_url );
    $captcha_result = json_decode( $captcha_json );

    // Verifica se i valori inviati tramite AJAX sono corretti
    ( strlen( $service_name ) > 2 ) ?: $error_counter++;
    ( strlen( $service_uri ) > 2 ) ?: $error_counter++;
    ( $stars > 0 && $stars < 6 ) ?: $error_counter++;
    ( strlen( $comment ) > 2 ) ?: $error_counter++;
    ( $captcha_result->success ) ?: $error_counter++;

    $timestamp    = current_time( 'mysql' );
    // $uid          = get_user_meta( $current_user->ID, SIIMOB_COMMENT__USERMETA_UID, true );

    if ( $error_counter == 0 ) {
        // Se tutti i valori sono corretti, salva il post
        wp_insert_post( array(
            'post_date'      => $timestamp,
            'post_content'   => $comment,
            'post_title'     => $service_name,
            'post_status'    => 'publish',
            'post_type'      => SIIMOB_COMMENT__POST_TYPE_NAME,
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
            'meta_input'     => array(
                SIIMOB_COMMENT__POSTMETA_SERVICE_NAME   => $service_name,
                SIIMOB_COMMENT__POSTMETA_SERVICE_URI    => $service_uri,
                SIIMOB_COMMENT__POSTMETA_PHOTO_THUMB    => $photo_thumb,
                SIIMOB_COMMENT__POSTMETA_CIVIC          => $civic,
                SIIMOB_COMMENT__POSTMETA_TYPE_LABEL     => $type_label,
                SIIMOB_COMMENT__POSTMETA_RATING         => $stars,
                SIIMOB_COMMENT__POSTMETA_TIMESTAMP      => $timestamp,
                // SIIMOB_COMMENT__POSTMETA_UID            => $uid,
                SIIMOB_COMMENT__POSTMETA_UPLOADED       => 0,
            ),
        ));

        // E invia una risposta positiva al client
        $json_response = array(
            'saved'  => true,
        );
    } else {
        // Altrimenti invia una risposta negativa al client
        $json_response = array(
            'saved'  => false,
        );
    }

    wp_send_json( $json_response );
}


/*
 * Funzione per la visualizzazione dei post con post_type == siimob_comment
 * Dopo avere richiamato i post, li serializza in un json e li invia al frontend
 * Se nella chiamata è presente un valore offset, lo inserisce nella query, altrimenti
 * mostra solo gli ultimi 5 post
 */
function siimob_comments_get() {
    $query = new WP_Query( array(
        'post_type'         => SIIMOB_COMMENT__POST_TYPE_NAME,
        'post_status'       => 'publish',
        'posts_per_page'    => 5,
        'offset'            => ( isset( $_REQUEST['offset'] ) ) ? ( (int) $_REQUEST['offset'] ) : 0,
    ));

    $json_response = array();

    if ( $query->have_posts() ) {
        $datetime_format  = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

        while ( $query->have_posts() ) {
            $query->the_post();

            $postmeta_datetime = get_post_meta( get_the_ID(), SIIMOB_COMMENT__POSTMETA_TIMESTAMP, true );

            $json_response[] = array(
                'service_name'  => get_post_meta( get_the_ID(), SIIMOB_COMMENT__POSTMETA_SERVICE_NAME, true ),
                'service_uri'   => get_post_meta( get_the_ID(), SIIMOB_COMMENT__POSTMETA_SERVICE_URI, true ),
                'photo_thumb'   => get_post_meta( get_the_ID(), SIIMOB_COMMENT__POSTMETA_PHOTO_THUMB, true ),
                'civic'         => get_post_meta( get_the_ID(), SIIMOB_COMMENT__POSTMETA_CIVIC, true ),
                'type_label'    => get_post_meta( get_the_ID(), SIIMOB_COMMENT__POSTMETA_TYPE_LABEL, true ),
                'stars'         => get_post_meta( get_the_ID(), SIIMOB_COMMENT__POSTMETA_RATING, true ),
                'comment'       => wpautop( get_the_content() ),
                'timestamp'     => date_i18n( $datetime_format, strtotime( $postmeta_datetime ) ),
            );
        }
        wp_reset_postdata();
    }

    wp_send_json( $json_response );
}


/*
 * Visualizza il frontend richiamato dal shortcode [siimob-comments]
 */
function siimob_comments_show_frontend() {
    echo '<h2>Invia commento</h2>
    <form name="ratings-form" id="ratings-form">

    <label for="service-name">Punto di Interesse</label>
    <input name="service-name" id="service-name" type="text" placeholder="Nome o indirizzo" />
    <input name="service-data" id="service-data" type="hidden" />
    <div id="result-box"></div>

    <label for="star-5">Valutazione</label>
        <div class="stars">
            <input type="radio" name="star" class="star-1" id="star-1" value="1" />
            <label class="star-1" for="star-1">1</label>
            <input type="radio" name="star" class="star-2" id="star-2" value="2" />
            <label class="star-2" for="star-2">2</label>
            <input type="radio" name="star" class="star-3" id="star-3" value="3" />
            <label class="star-3" for="star-3">3</label>
            <input type="radio" name="star" class="star-4" id="star-4" value="4" />
            <label class="star-4" for="star-4">4</label>
            <input type="radio" name="star" class="star-5" id="star-5" value="5" />
            <label class="star-5" for="star-5">5</label>
            <span></span>
        </div>

    <label for="comment">Commento</label>
    <textarea name="comment" id="comment" placeholder="Commento"></textarea>

    <div class="g-recaptcha" data-size="normal" data-sitekey="' . get_option(GOOGLE_RECAPTCHA_SITE_KEY) . '"></div>

    <div id="message-box"></div>

    <button type="submit" id="ratings-form-submit">Invia</button>
    <button type="reset" id="ratings-form-reset">Ripristina</button>

    </form>';

    echo '<h2>Ultimi commenti inseriti</h2>
    <div id="last-comments"></div>';
    wp_enqueue_style(
        'siimob-comments-css',
        plugin_dir_url( __FILE__ ) . 'css/siimob-comments.css'
    );

    wp_enqueue_script(
        'siimob-comments-js',
        plugin_dir_url( __FILE__ ) . 'js/siimob-comments-get.js',
        'jquery',
        null,
        true
    );

    /*if ( is_user_logged_in() ) {
        wp_enqueue_script(
            'siimob-comments-post',
            plugin_dir_url( __FILE__ ) . 'js/siimob-comments-post.js',
            'jquery',
            null,
            true
        );
    }*/
    wp_enqueue_script(
        'siimob-comments-post',
        plugin_dir_url( __FILE__ ) . 'js/siimob-comments-post.js',
        'jquery',
        null,
        true
    );

    wp_localize_script(
        'siimob-comments-js',
        'siimobComments',
        array(
            'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
            'pluginUrl'         => plugin_dir_url( __FILE__ ),
            'getLastComments'   => '',
        )
    );
}

/*
 * Inserisce lo snippet javascript di reCAPTCHA prima della chiusura del tag <head>
 */
function add_recaptcha_js() {
    if ( is_page( 'commenti' ) ) {
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
    }
}


// Aggiunge l'intervallo di 5 minuti agli schedule di WP Cron
add_filter( 'cron_schedules', 'add_cron_recurrence_interval' );

add_action( 'init', 'siimob_comment_init' );
add_action( 'wp_head', 'add_recaptcha_js' );
//add_action( 'wp_enqueue_scripts', 'siimob_comments_enqueue' );

add_action( 'wp_ajax_siimob_comment_post', 'siimob_comment_post' );
add_action( 'wp_ajax_nopriv_siimob_comment_post', 'siimob_comment_post' );

add_action( 'wp_ajax_siimob_comments_get', 'siimob_comments_get' );
add_action( 'wp_ajax_nopriv_siimob_comments_get', 'siimob_comments_get' );

add_shortcode( 'siimob-comments', 'siimob_comments_show_frontend' );

?>
