<?php

// Interrompe l'esecuzione se il file è richiamato direttamente
if ( ! function_exists( 'add_action' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'siimob_dashboard_enqueue' );

function siimob_dashboard_enqueue()
{
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'bootstrap',
        'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css'
    );
    wp_enqueue_style(
        'fontawesome',
        'https://use.fontawesome.com/releases/v5.0.7/css/all.css'
    );

    wp_enqueue_script(
        'bootstrap',
        'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js',
        'jquery',
        null,
        true
    );

    wp_enqueue_script(
        'custom',
        'js/custom.js',
        'jquery',
        null,
        true
    );
}

add_action('after_setup_theme', 'remove_admin_bar');
 
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}