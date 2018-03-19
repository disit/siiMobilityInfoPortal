<?php
/*
Plugin Name: User Verification
Plugin URI: http://pickplugins.com
Description: Verify user before access on your website.
Version: 1.0.13
Text Domain: user-verification
Domain Path: /languages
Author: PickPlugins
Author URI: http://pickplugins.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


class UserVerification{
	
	public function __construct(){
	
		$this->uv_define_constants();
		
		$this->uv_declare_classes();
		$this->uv_declare_actions();
		$this->uv_loading_script();
		
		$this->uv_loading_functions();

		add_action( 'init', array( $this, 'textdomain' ));
	}


	public function textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'user-verification' );
		load_textdomain('user-verification', WP_LANG_DIR .'/user-verification/user-verification-'. $locale .'.mo' );

		load_plugin_textdomain( 'user-verification', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}


	public function uv_loading_functions() {
		
		require_once( UV_PLUGIN_DIR . 'includes/functions.php');
	}
	

	public function uv_loading_script() {
	
		add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
		add_action( 'wp_enqueue_scripts', array( $this, 'uv_front_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'uv_admin_scripts' ) );
	}
	
	
	public function uv_declare_actions() {

		require_once( UV_PLUGIN_DIR . 'includes/actions/action-uv-registration.php');
	}
	
	public function uv_declare_classes() {
		
		require_once( UV_PLUGIN_DIR . 'includes/classes/class-emails.php');	
		require_once( UV_PLUGIN_DIR . 'includes/classes/class-settings.php');	
		require_once( UV_PLUGIN_DIR . 'includes/classes/uv-class-column-users.php');	
	}
	
	public function uv_define_constants() {

		$this->_define('UV_PLUGIN_URL', plugins_url('/', __FILE__)  );
		$this->_define('UV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		$this->_define('UV_PLUGIN_NAME', __('User Verification','user-verification') );
	}
	
	private function _define( $name, $value ) {
		if( $name && $value )
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
		
	public function uv_front_scripts(){
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		
		wp_enqueue_script('uv_front_js', plugins_url( '/assets/front/js/scripts.js' , __FILE__ ) , array( 'jquery' ));
		wp_localize_script( 'uv_front_js', 'uv_ajax', array( 'uv_ajaxurl' => admin_url( 'admin-ajax.php')));
		
		wp_enqueue_style('uv_style', UV_PLUGIN_URL.'assets/front/css/style.css');	
		
		//global
		wp_enqueue_style('font-awesome', UV_PLUGIN_URL.'assets/global/css/font-awesome.css');
	}

	public function uv_admin_scripts(){
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
		
		wp_enqueue_script('uv_admin_js', plugins_url( '/assets/admin/js/scripts.js' , __FILE__ ) , array( 'jquery' ));
		wp_localize_script( 'uv_admin_js', 'uv_ajax', array( 'uv_ajaxurl' => admin_url( 'admin-ajax.php')));
		wp_localize_script( 'uv_admin_js', 'L10n_user_verification', array(
			'confirm_text' => __( 'Are you sure?', 'user-verification' ),
			'reset_confirm_text' => __( 'Do you really want to reset?', 'user-verification' ),
			'text_approve_now' => __( 'Approve now', 'user-verification' ),
			'text_remove_approve' => __( 'Remove Approval', 'user-verification' ),
			'text_updateing' => __( 'Updating user', 'user-verification' ),
		));
							
		wp_enqueue_style('uv_admin_style', UV_PLUGIN_URL.'assets/admin/css/style.css');
		wp_enqueue_style('uv-expandable', UV_PLUGIN_URL.'assets/admin/css/uv-expandable.css');	
		
		// ParaAdmin
		wp_enqueue_script('uv_ParaAdmin', plugins_url( '/assets/global/ParaAdmin/ParaAdmin.js' , __FILE__ ) , array( 'jquery' ));		
		wp_enqueue_style('uv_paraAdmin', UV_PLUGIN_URL.'assets/global/ParaAdmin/ParaAdmin.css');
		
		// Global
		wp_enqueue_style('font-awesome', UV_PLUGIN_URL.'assets/global/css/font-awesome.css');
		wp_enqueue_style('uv_global_style', UV_PLUGIN_URL.'assets/global/css/style.css');
	
		
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'uv_color_picker', plugins_url('/assets/admin/js/color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}
} 

new UserVerification();