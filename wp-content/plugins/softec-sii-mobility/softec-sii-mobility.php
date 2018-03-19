<?php
/**
 * Plugin Name:   Softec Sii-Mobility
 * Description:   Sistema di utenze per sii mobility, registrazione login e profilazione con campi custom. Sistema di gestione dashboards per sii mobility. Sistema di invio e download commenti dalla piattaforma Sii-Mobility creato da Softec S.p.A.
 * Version:       0.1alpha
 * Author:        Daniele Pasqui
 */


define ( 'MODULES_DIR', 'modules' );

include( plugin_dir_path( __FILE__ ) . MODULES_DIR . '/users/users.php');
include( plugin_dir_path( __FILE__ ) . MODULES_DIR . '/comments/comments.php');
include( plugin_dir_path( __FILE__ ) . MODULES_DIR . '/dashboards/dashboards.php');