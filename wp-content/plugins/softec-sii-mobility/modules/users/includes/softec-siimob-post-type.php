<?php

if ( ! function_exists( 'add_action' ) ) {
    exit;
}

// Definisce le costanti globali del plugin
define( 'SIIMOB_PROVINCE_TAXONOMY_NAME',         'province' );
define( 'SIIMOB_USER_PROVINCE_META_FIELD_NAME',  'user_province' );
define( 'SIIMOB_TYPE_TAXONOMY_NAME',             'type' );
define( 'SIIMOB_USER_TYPE_META_FIELD_NAME',      'user_type' );
define( 'SIIMOB_DASHBOARD_POST_TYPE_NAME',       'dashboard' );
define( 'SIIMOB_DASHBOARD_URL_META_FIELD_NAME',  'dashboard_url' );
define( 'SIIMOB_USER_DASHBOARD_META_FIELD_NAME', 'user_dashboard' );

/**
 * Registro la tassonomia provincia
 */
function register_province_taxonomy()
{
    $labels = array(
        'name'                => 'Province',
        'singular_name'       => 'Provincia',
        'all_items'           => 'Tutte le province',
        'view_item'           => 'Visualizza provincia',
        'add_new_item'        => 'Aggiungi provincia',
        'edit_item'           => 'Modifica provincia',
        'update_item'         => 'Aggiorna provincia',
    );    
     
    // Now register the taxonomy
     
    register_taxonomy(
        SIIMOB_PROVINCE_TAXONOMY_NAME,
        array(SIIMOB_DASHBOARD_POST_TYPE_NAME), 
        array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'meta_box_cb' => 'province_drop_cat'
        )
    );
}

function province_drop_cat()
{
    $iPostId = get_the_ID();
    $aPostTerms = get_the_terms($iPostId, SIIMOB_PROVINCE_TAXONOMY_NAME);
    ?>   
    <select name="<?php echo SIIMOB_PROVINCE_TAXONOMY_NAME; ?>">
        <option value="0">Seleziona la provincia</option>
        <?php
           $aTerms = get_terms(SIIMOB_PROVINCE_TAXONOMY_NAME, array('hide_empty' => '0'));      
           foreach ( $aTerms as $oTerm ): ?>
              <option <?php echo (! empty($aPostTerms) && in_array($oTerm, $aPostTerms)) ? 'selected' : ''; ?> value="<?php echo $oTerm->term_id; ?>"><?php echo $oTerm->name; ?></option>;
           <?php endforeach; ?>
    </select> 
    <?php
}

add_action( 'save_post', 'save_taxonomy_data' );

function save_taxonomy_data($iPostId)
{
    $oPost = get_post($iPostId);

    if ($oPost->post_type == SIIMOB_DASHBOARD_POST_TYPE_NAME && ! empty($_POST[SIIMOB_PROVINCE_TAXONOMY_NAME])) {
        $iProvincia = $_POST[SIIMOB_PROVINCE_TAXONOMY_NAME];
        wp_set_post_terms( $iPostId, array($iProvincia), SIIMOB_PROVINCE_TAXONOMY_NAME );
    }

    if ($oPost->post_type == SIIMOB_DASHBOARD_POST_TYPE_NAME && ! empty($_POST[SIIMOB_TYPE_TAXONOMY_NAME])) {
        $iType = $_POST[SIIMOB_TYPE_TAXONOMY_NAME];
        wp_set_post_terms( $iPostId, array($iType), SIIMOB_TYPE_TAXONOMY_NAME );
    }
}

/**
 * Recupero tutte le province registrate
 */
function get_provinces()
{
    return get_terms( SIIMOB_PROVINCE_TAXONOMY_NAME, array('hide_empty' => false));
}

/**
 * Registro la tassonomia tipologia utente
 */
function register_type_taxonomy()
{
    $labels = array(
        'name'                => 'Tipologie',
        'singular_name'       => 'Tipologia',
        'all_items'           => 'Tutte le tipologie',
        'view_item'           => 'Visualizza tipologia',
        'add_new_item'        => 'Aggiungi tipologia',
        'edit_item'           => 'Modifica tipologia',
        'update_item'         => 'Aggiorna tipologia',
    );
     
    register_taxonomy(
        SIIMOB_TYPE_TAXONOMY_NAME,
        array(SIIMOB_DASHBOARD_POST_TYPE_NAME), 
        array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'meta_box_cb' => 'type_drop_cat'
        )
    );
}

function type_drop_cat()
{
    $iPostId = get_the_ID();
    $aPostTerms = get_the_terms($iPostId, SIIMOB_TYPE_TAXONOMY_NAME);
    ?>   
    <select name="<?php echo SIIMOB_TYPE_TAXONOMY_NAME; ?>">
        <option value="0">Seleziona la tipologia</option>
        <?php
           $aTerms = get_terms(SIIMOB_TYPE_TAXONOMY_NAME, array('hide_empty' => '0'));      
           foreach ( $aTerms as $oTerm ): ?>
              <option <?php echo (! empty($aPostTerms) && in_array($oTerm, $aPostTerms)) ? 'selected' : ''; ?> value="<?php echo $oTerm->term_id; ?>"><?php echo $oTerm->name; ?></option>;
           <?php endforeach; ?>
    </select> 
    <?php
}

/**
 * Recupero tutte le tipologie utente registrate
 */
function get_types()
{
    return get_terms( SIIMOB_TYPE_TAXONOMY_NAME, array('hide_empty' => false));
}