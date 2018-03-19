<?php

if ( ! function_exists( 'add_action' ) ) {
    exit;
}

// Definisce le costanti globali del plugin
define( 'SIIMOB_DASHBOARD_POST_TYPE_NAME',          'dashboard' );
define( 'SIIMOB_DASHBOARD_URL_META_FIELD_NAME',     'dashboard_url' );
define( 'SIIMOB_DASHBOARD_COLUMN_META_FIELD_NAME',  'dashboard_column' );
define( 'SIIMOB_DASHBOARD_HEIGHT_META_FIELD_NAME',  'dashboard_height' );
define( 'SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME', 'dashboard_general' );

/**
 * Registro il tipo di contenuto dashboard
 */
function register_dashboard_post_type()
{
    $labels = array(
        'name'                => 'Dashboard',
        'singular_name'       => 'Dashboard',
        'all_items'           => 'Tutte le dashboard',
        'view_item'           => 'Visualizza dashboard',
        'add_new_item'        => 'Aggiungi dashboard',
        'edit_item'           => 'Modifica dashboard',
        'update_item'         => 'Aggiorna dashboard',
    );
    register_post_type(
        SIIMOB_DASHBOARD_POST_TYPE_NAME,
        array(
            'labels'                 => $labels,
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
            'supports'              => array('title', 'editor')
        )
    );
}

function get_dashboards()
{
    return get_posts(array(
        'post_type' => SIIMOB_DASHBOARD_POST_TYPE_NAME,
        'numberposts' => -1
    ));
}

function add_dashboard_meta_box($oPost){

    add_meta_box( 'url_meta_box', 'Url', 'build_url_meta_box', SIIMOB_DASHBOARD_POST_TYPE_NAME, 'side', 'low' );
    $screen = get_current_screen();
    if ( 'add' != $screen->action ) {
        add_meta_box( 'dashboard_iframe_meta_box', 'Preview', 'build_dashboard_iframe_meta_box', SIIMOB_DASHBOARD_POST_TYPE_NAME, 'normal', 'low' );
    }
    add_meta_box( 'dashboard_column_meta_box', 'Larghezza dashboard', 'build_dashboard_column_meta_box', SIIMOB_DASHBOARD_POST_TYPE_NAME, 'side', 'low' );
    add_meta_box( 'dashboard_height_meta_box', 'Altezza dashboard', 'build_dashboard_height_meta_box', SIIMOB_DASHBOARD_POST_TYPE_NAME, 'side', 'low' );
    add_meta_box( 'dashboard_general_meta_box', 'Dashboard generale', 'build_dashboard_general_meta_box', SIIMOB_DASHBOARD_POST_TYPE_NAME, 'side', 'low' );
}

add_action( 'add_meta_boxes_' . SIIMOB_DASHBOARD_POST_TYPE_NAME, 'add_dashboard_meta_box' );

function build_url_meta_box($oPost)
{
    $sUrl = get_post_meta( $oPost->ID, SIIMOB_DASHBOARD_URL_META_FIELD_NAME, true );
    ?>
        <div class='inside'>
            <p>
                <input type="text" name="<?php echo SIIMOB_DASHBOARD_URL_META_FIELD_NAME; ?>" value="<?php echo $sUrl; ?>" /> 
            </p>
        </div>
    <?php
}

function build_dashboard_column_meta_box($oPost)
{
    $sColumn = get_post_meta( $oPost->ID, SIIMOB_DASHBOARD_COLUMN_META_FIELD_NAME, true );
    ?>
        <div class='inside'>
            <p>
                <input type="text" name="<?php echo SIIMOB_DASHBOARD_COLUMN_META_FIELD_NAME; ?>" value="<?php echo $sColumn; ?>" /> 
            </p>
        </div>
    <?php
}

function build_dashboard_height_meta_box($oPost)
{
    $iHeight = get_post_meta( $oPost->ID, SIIMOB_DASHBOARD_HEIGHT_META_FIELD_NAME, true );
    ?>
        <div class='inside'>
            <p>
                <input type="text" name="<?php echo SIIMOB_DASHBOARD_HEIGHT_META_FIELD_NAME; ?>" value="<?php echo $iHeight; ?>" /> 
            </p>
        </div>
    <?php
}

function build_dashboard_general_meta_box($oPost)
{
    $sCheckbox = get_post_meta( $oPost->ID, SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME, true );
    ?>
        <div class='inside'>
            <p>
                <input type="checkbox" name="<?php echo SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME; ?>" value="yes" <?php echo ! empty($sCheckbox) ? 'checked' : ''?>/ > 
            </p>
        </div>
    <?php
}

add_action( 'send_headers', 'send_frame_options_header', 10, 0 );
function build_dashboard_iframe_meta_box($oPost)
{
    $sUrl = get_post_meta( $oPost->ID, SIIMOB_DASHBOARD_URL_META_FIELD_NAME, true );
    ?>
        <iframe style="width: 100%; height: 500px;" src="<?php echo $sUrl; ?>"></iframe>
    <?php
}

function _save_meta_boxes_data($iPostId){
    update_post_meta($iPostId, SIIMOB_DASHBOARD_URL_META_FIELD_NAME, sanitize_text_field($_POST[SIIMOB_DASHBOARD_URL_META_FIELD_NAME]));
    update_post_meta($iPostId, SIIMOB_DASHBOARD_COLUMN_META_FIELD_NAME, sanitize_text_field($_POST[SIIMOB_DASHBOARD_COLUMN_META_FIELD_NAME]));
    update_post_meta($iPostId, SIIMOB_DASHBOARD_HEIGHT_META_FIELD_NAME, sanitize_text_field($_POST[SIIMOB_DASHBOARD_HEIGHT_META_FIELD_NAME]));
    update_post_meta($iPostId, SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME, sanitize_text_field($_POST[SIIMOB_DASHBOARD_GENERAL_META_FIELD_NAME]));
}

add_action( 'save_post_' . SIIMOB_DASHBOARD_POST_TYPE_NAME, '_save_meta_boxes_data', 10, 2 );

