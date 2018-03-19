<?php
/**
 * Plugin Name: Frontend Reset Password
 * Description: Let your users reset their forgotten passwords from the frontend of your website.
 * Version: 1.0.3
 * Author: Square One Media
 * Author URI: https://squareonemedia.co.uk
 * Requires at least: 4.4
 * Tested up to: 4.9.4
 *
 * Text Domain: frontend-reset-password
 * Domain Path: /i18n/languages
 *
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VAYF6G99MCMHU
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'SOMFRP_FILE', __FILE__ );
define( 'SOMFRP_PATH', plugin_dir_path( __FILE__ ) );
define( 'SOMFRP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require main plugin loader
require_once( SOMFRP_PATH . 'somfrp-loader.php' );