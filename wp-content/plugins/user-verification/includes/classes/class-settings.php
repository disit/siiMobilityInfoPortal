<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


class class_user_verification_settings{
	
	public function __construct(){

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 12 );
    }
	
	public function admin_menu() {
		
		add_menu_page( __( 'User Verification', 'user-verification' ), __( 'User Verification', 'user-verification' ), 'manage_options', 'user-verification', array( $this, 'settings' ), 'dashicons-shield-alt');
		
		add_submenu_page( 'user-verification', __( 'Email Templates', 'user-verification' ), __( 'Email Templates', 'user-verification' ), 'manage_options', 'uv-email-template', array( $this, 'email_template' ) );

    }
	
	public function settings(){
		
		include( UV_PLUGIN_DIR. 'includes/menus/settings.php' ); 
	}	
	
	public function email_template(){
		
		include( UV_PLUGIN_DIR. 'includes/menus/email-templates.php' );
	}	
	
	
}

new class_user_verification_settings();

