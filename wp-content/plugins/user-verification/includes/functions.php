<?php
/*
* @Author 		pickplugins
* Copyright: 	pickplugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 



add_filter('bulk_actions-users','user_verification_bulk_approve');
function user_verification_bulk_approve($actions){
	unset( $actions['delete'] );

	$actions['uv_bulk_approve'] = __('Approve', 'user-verification');
	$actions['uv_bulk_disapprove'] = __('Disapprove', 'user-verification');

	return $actions;
}





add_filter( 'handle_bulk_actions-users', 'user_verification_bulk_approve_handler', 10, 3 );
function user_verification_bulk_approve_handler( $redirect_to, $doaction, $items ) {

	if ( $doaction == 'uv_bulk_approve' ){

		foreach ( $items as $user_id ) {
			// Perform action for each post.
			update_user_meta( $user_id, 'user_activation_status', 1 );

		}

		$redirect_to = add_query_arg( 'uv_bulk_approve', count( $items ), $redirect_to );
	}
	elseif ($doaction =='uv_bulk_disapprove'){

		foreach ( $items as $user_id ) {
			// Perform action for each post.
			update_user_meta( $user_id, 'user_activation_status', 0 );

		}

		$redirect_to = add_query_arg( 'uv_bulk_disapprove', count( $items ), $redirect_to );

	}


	return $redirect_to;

}



add_action( 'admin_notices', 'user_verification_bulk_action_admin_notice' );
function user_verification_bulk_action_admin_notice() {
	if ( isset($_REQUEST['uv_bulk_approve']) ) {

		$user_count = intval( $_REQUEST['uv_bulk_approve'] );

		echo '<div id="message" class="notice notice-success is-dismissible">';
		echo $user_count.' user account marked as approved.';
		echo '</div>';

	}
	elseif (isset($_REQUEST['uv_bulk_disapprove'])){

		$user_count = intval( $_REQUEST['uv_bulk_disapprove'] );

		echo '<div id="message" class="notice notice-success is-dismissible">';
		echo $user_count.' user account marked as disapproved.';
		echo '</div>';


	}
}



function uv_ajax_approve_user_manually(){
	
	$user_id 	= isset( $_POST['user_id'] ) ? $_POST['user_id'] : '';
	$do 		= isset( $_POST['do'] ) ? $_POST['do'] : '';
	
	if( empty( $user_id ) || empty( $do ) ) die();
	
	if( $do == 'approve' ) update_user_meta( $user_id, 'user_activation_status', 1 );
	if( $do == 'remove_approval' ) update_user_meta( $user_id, 'user_activation_status', 0 );
	
	$user_activation_status = get_user_meta( $user_id, 'user_activation_status', true );
	$user_activation_status = empty( $user_activation_status ) ? 0 : $user_activation_status;
	$uv_status 				= $user_activation_status == 1 ? __('Approved', 'user-verification') : __('Pending approval', 'user-verification');
	
	if( $user_activation_status == 1 ){
		
		$user_data 	= get_userdata( $user_id );
		uv_mail( $user_data->user_email, array(
			'action' => 'email_confirmed',
			'user_id' => $user_id,
		) );
	}
	
	echo $uv_status;
	die();
}
add_action('wp_ajax_uv_ajax_approve_user_manually', 'uv_ajax_approve_user_manually');
add_action('wp_ajax_nopriv_uv_ajax_approve_user_manually', 'uv_ajax_approve_user_manually');

function uv_woocommerce_registration_control_on_myaccount( $validation_error, $username, $password, $email ){
	
	if( 'yes' === get_option( 'woocommerce_registration_generate_username' ) ){
		$username_arr = explode( "@", $email );
		$username = isset( $username_arr[0] ) ? $username_arr[0] : "";
	}
	
	$ret = uv_check_security_permissions(  
		array( 
			'user_name' => $username,
			'user_email' => $email,
		)
	);
	
	if( isset( $ret['response'] ) && $ret['response'] != 'passed' ) {
		$validation_error->add( 'error', $ret['message'],'');
	}
		
	return $validation_error;
}
add_filter( 'woocommerce_process_registration_errors','uv_woocommerce_registration_control_on_myaccount', 10, 4 );

function uv_woocommerce_registration_control_on_checkout(){
	
	$billing_email = isset( $_POST['billing_email'] ) ? $_POST['billing_email'] : "";
	$username = isset( $_POST['account_username'] ) ? $_POST['account_username'] : "";
	if( empty( $billing_email ) ) return;
	
	if( 'yes' === get_option( 'woocommerce_registration_generate_username' ) ){
		$username_arr = explode( "@", $billing_email );
		$username = isset( $username_arr[0] ) ? $username_arr[0] : "";
	}
	
	$username_arr = explode( "@", $billing_email );
	
	$ret = uv_check_security_permissions(  
		array( 
			'user_name' => $username,
			'user_email' => $billing_email,
		)
	);
	
	if( isset( $ret['response'] ) && $ret['response'] != 'passed' ) {
		wc_add_notice( $ret['message'], 'error' );
	}
}
add_action('woocommerce_checkout_process', 'uv_woocommerce_registration_control_on_checkout');


function uv_registration_errors_functions( $errors, $sanitized_user_login, $user_email ){
	
	$ret = uv_check_security_permissions(  
		array( 
			'user_name' => $sanitized_user_login,
			'user_email' => $user_email,
		)
	);
	
	if( isset( $ret['response'] ) && $ret['response'] != 'passed' ) {
		$errors->add( $ret['response'], $ret['message'] );
	}
	
	return $errors;
}
add_filter( 'registration_errors', 'uv_registration_errors_functions', 10, 3 );

function uv_check_security_permissions( $args = array() ){
	
	$ret = array();
	
	$user_id 	= isset( $args['user_id'] ) ? $args['user_id'] : "";
	$user_name 	= isset( $args['user_name'] ) ? $args['user_name'] : "";
	$user_email = isset( $args['user_email'] ) ? $args['user_email'] : "";
	
	$user_verification_enable_block_domain 		= get_option('user_verification_enable_block_domain');
	$user_verification_enable_block_username 	= get_option('user_verification_enable_block_username');
	$uv_settings_blocked_domain 				= get_option('uv_settings_blocked_domain');
	$uv_settings_blocked_username 				= get_option('uv_settings_blocked_username');
	
	if( empty( $user_verification_enable_block_domain ) ) $user_verification_enable_block_domain = "no"; 
	if( empty( $user_verification_enable_block_username ) ) $user_verification_enable_block_username = "no"; 
	if( empty( $uv_settings_blocked_username ) ) $uv_settings_blocked_username = array();
	if( empty( $uv_settings_blocked_domain ) ) $uv_settings_blocked_domain = array();
	
	// When domain blocking is enable
	if( $user_verification_enable_block_domain == "yes" ):
	
		$email_domain_1	= !empty( $user_email ) ? explode( "@", $user_email ) : array();
		$email_domain	= !empty( $email_domain_1 ) ? explode( ".", $email_domain_1[1] ) : "";
		
		
		if( !empty( $email_domain ) && in_array( $email_domain[0], $uv_settings_blocked_domain ) ){
			
			$ret['response']	= 'blocked_domain';
			$ret['message'] 	= __( "This email domain <strong>{$email_domain[0]}</strong> is not allowed!", 'user-verification' );
		}
		
		if( empty( $ret ) )
		foreach( $uv_settings_blocked_domain as $domain ){
			
			preg_match( '/%(.*?)%/', $domain, $match_domain );

			if( isset( $match_domain[1] ) && isset( $email_domain[1] ) && strpos($email_domain[0], $match_domain[1] ) !== false ) {

				$ret['response']	= 'blocked_domain';
				$ret['message'] 	= __( "<strong>{$email_domain[0]}</strong> email domain contains <strong>{$match_domain[1]}</strong>, which is not allowed!", 'user-verification' );
				break;
			}
		}
	
	endif;
	
	// When username blocking is enable
	if( $user_verification_enable_block_username == "yes" ):

		if( !empty( $user_name ) && in_array( $user_name, $uv_settings_blocked_username ) ){
			
			$ret['response']	= 'blocked_username';
			$ret['message'] 	= __( "This username <strong>$user_name</strong> is not allowed!", 'user-verification' );
		}
		
		if( empty( $ret ) )
		foreach( $uv_settings_blocked_username as $username ){
			
			preg_match( '/%(.*?)%/', $username, $match_username );

			if( isset( $match_username[1] ) && strpos($user_name, $match_username[1] ) !== false ) {

				$ret['response']	= 'blocked_username';
				$ret['message'] 	= __( "<strong>{$user_name}</strong> username contains <strong>{$match_username[1]}</strong>, which is not allowed!", 'user-verification' );
				break;
			}
		}

	endif;
	
	return $ret;
}


function uv_filters_setting_box_uv_settings_security_function($html){
	
	$uv_settings_blocked_domain = get_option('uv_settings_blocked_domain');
	$uv_settings_blocked_username = get_option('uv_settings_blocked_username');
	
	if( empty( $uv_settings_blocked_username ) ) $uv_settings_blocked_username = array();
	if( empty( $uv_settings_blocked_domain ) ) $uv_settings_blocked_domain = array();
	
	$html .= "<div class='option-box'>";
	$html .= "<p class='option-title'>".__('Blocked Domains', 'user-verification')."</p>";
	$html .= "<p class='option-info'>".__('One domain per line. wihtout http:// or https://', 'user-verification')."</p>";
	$html .= "<ul class='uv_domain_list'>";
	$html .= "<li><div class='button uv_domain_add'><i class='fa fa-plus'></i></div></li>";
	foreach( $uv_settings_blocked_domain as $domain ):
	$html .= "<li class='uv_domain'>";
	$html .= "<input type='text' placeholder='thread.com' name='uv_settings_blocked_domain[]' value='$domain' />";
	$html .= "<div class='button uv_domain_remove'><i class='fa fa-times'></i></div>";
	$html .= "</li>";
	endforeach;
	$html .= "</ul>";
	$html .= "</div>";
	
	$html .= "<div class='option-box'>";
	$html .= "<p class='option-titl e'>".__('Blocked Usernames', 'user-verification')."</p>";
	$html .= "<p class='option-info'>".__('Press Plus button to add new option', 'user-verification')."</p>";
	$html .= "<ul class='uv_username_list'>";
	$html .= "<li><div class='button uv_username_add'><i class='fa fa-plus'></i></div></li>";
	foreach( $uv_settings_blocked_username as $username ):
	$html .= "<li class='uv_username'>";
	$html .= "<input type='text' placeholder='thread' name='uv_settings_blocked_username[]' value='$username' />";
	$html .= "<div class='button uv_username_remove'><i class='fa fa-times'></i></div>";
	$html .= "</li>";
	endforeach;
	$html .= "</ul>";
	$html .= "</div>";
	
	
	
	return $html;
}
add_filter('uv_filters_setting_box_uv_settings_security', 'uv_filters_setting_box_uv_settings_security_function' );

function uv_settings_save_function(){
	
	$blocked_domain = isset( $_POST['uv_settings_blocked_domain'] ) ? stripslashes_deep($_POST['uv_settings_blocked_domain']) : "";
	$blocked_username = isset( $_POST['uv_settings_blocked_username'] ) ? stripslashes_deep($_POST['uv_settings_blocked_username']) : "";
	
	update_option( 'uv_settings_blocked_domain', $blocked_domain );
	update_option( 'uv_settings_blocked_username', $blocked_username );
}
add_action( 'uv_settings_save', 'uv_settings_save_function' );


add_filter( 'wp_login_errors', 'user_verification_registered_message', 10, 2 );

function user_verification_registered_message( $errors, $redirect_to ) {

	$user_verification_registered_message = get_option('user_verification_registered_message');

	if( isset( $errors->errors['registered'] ) ) {
		
		$tmp = $errors->errors;

		$old = 'Registration complete. Please check your email.';
		$new = $user_verification_registered_message;

		foreach( $tmp['registered'] as $index => $msg ){
			if( $msg === $old )
			$tmp['registered'][$index] = $new;
		}
		$errors->errors = $tmp;

		unset( $tmp );
	}
	
	return $errors;
}

function user_verification_admin_notices(){

    $html= '';
    $uv_option_update = get_option('uv_option_update');
    if($uv_option_update=='done'):
    else:
        if(isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field($_GET['_wpnonce']), 'uv_option_update' ) ){
            update_option('uv_option_update','done');
        }
        $html.= '<div class="update-nag">';
        $html.= "We have update plugin, please review <a href='".wp_nonce_url(admin_url('admin.php?page=user-verification'), 'uv_option_update')."'>User Verification - Settings</a>";
        $html.= '</div>';
    endif;

    echo $html;
}
add_action('admin_notices', 'user_verification_admin_notices');

function user_verification_get_pages_list() {
	$array_pages[''] = __('None', 'user-verification');

    foreach( get_pages() as $page )
		if ( $page->post_title ) $array_pages[$page->ID] = $page->post_title;

    return $array_pages;
}


function user_verification_reset_email_templates( ) {
		
	if(current_user_can('manage_options')){
		delete_option('uv_email_templates_data');
	}
}	
add_action('wp_ajax_user_verification_reset_email_templates', 'user_verification_reset_email_templates');
add_action('wp_ajax_nopriv_user_verification_reset_email_templates', 'user_verification_reset_email_templates');
	
function uv_filter_check_activation() {
	
	
	$uv_message_invalid_key = get_option( 'uv_message_invalid_key' );
	if( empty( $uv_message_invalid_key ) ) 
	$uv_message_invalid_key = __( 'Invalid activation Key', 'user-verification' );
	
	$uv_message_key_expired = get_option( 'uv_message_key_expired' );
	if( empty( $uv_message_key_expired ) ) 
	$uv_message_key_expired = __( 'Your key is expired', 'user-verification' );
	
	$uv_message_verification_success = get_option( 'uv_message_verification_success' );
	if( empty( $uv_message_verification_success ) ) 
	$uv_message_verification_success = __( 'Your account is now verified', 'user-verification' );
	
	$uv_message_activation_sent = get_option( 'uv_message_activation_sent' );
	if( empty( $uv_message_activation_sent ) ) 
	$uv_message_activation_sent = __( 'Activation email sent, Please check latest item on email inbox', 'user-verification' );
	
	
    $html = '<div class="user-verification check">';

	if( isset( $_GET['activation_key'] ) ){
		$activation_key = sanitize_text_field($_GET['activation_key']);
		global $wpdb;
		$table = $wpdb->prefix . "usermeta";
		$meta_data	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE meta_value = %s", $activation_key ) );
		if( empty( $meta_data ) ) {
			$html.= "<div class='wrong-key'><i class='fa fa-times'></i> $uv_message_invalid_key</div>";
		}
		else{
			$user_activation_status = get_user_meta( $meta_data->user_id, 'user_activation_status', true );
			if( $user_activation_status != 0 ) {
				$html.= "<div class='expired'><i class='fa fa-calendar-times-o'></i> $uv_message_key_expired</div>";
			}
            else {
                $user_verification_redirect_verified = get_option('user_verification_redirect_verified');
				$redirect_page_url = get_permalink($user_verification_redirect_verified);
				$html.= "<div class='verified'><i class='fa fa-check-square-o'></i> $uv_message_verification_success</div>";
                update_user_meta( $meta_data->user_id, 'user_activation_status', 1 );
				
				if( "yes" == get_option( 'user_verification_login_automatically', 'yes' ) ){
					wp_set_current_user( $meta_data->user_id );
				}
				
				if(!empty($user_verification_redirect_verified)):
					$html.= "<script>jQuery(document).ready(function($){window.location.href = '$redirect_page_url';})</script>";
				else:
				endif;
			}
		}
	}

	elseif (isset( $_GET['uv_action']) && isset($_GET['id'])){

            $uv_action = sanitize_text_field($_GET['uv_action']);
            $user_id = (int) sanitize_text_field($_GET['id']);

            if($uv_action=='resend'):

                $user_activation_key = md5(uniqid('', true) );

                update_user_meta( $user_id, 'user_activation_key', $user_activation_key );

                $user_verification_verification_page = get_option('user_verification_verification_page');
                $verification_page_url = get_permalink($user_verification_verification_page);

                $user_data 	= get_userdata( $user_id );
                $link 		= $verification_page_url.'?activation_key='.$user_activation_key;
				
                uv_mail(
                    $user_data->user_email,
                    array(
                        'action' 	=> 'email_confirmed',
                        'user_id' 	=> $user_id,
                        'link'		=> $link
                    )
                );

                $html.= "<div class='resend'><i class='fa fa-paper-plane'></i> $uv_message_activation_sent</div>";
				
				
            endif;
        }
        else $html.= "<i class='fa fa-exclamation-triangle'></i> $uv_message_invalid_key";
    

		$html.= '</div>';
		return $html;
	}	

add_shortcode('user_verification_check', 'uv_filter_check_activation');	

//add_action( 'wp_footer', 'uv_filter_check_activation', 100 );

	
function uv_filter_resend_activation_link( ) {
		
		if( isset( $_GET['uv_action'] ) ) $uv_action = $_GET['uv_action'];
		else return;
		
		if( isset( $_GET['id'] ) ) $user_id = $_GET['id'];
		else return;
		
		$user_activation_key = md5(uniqid('', true) );
		
		update_user_meta( $user_id, 'user_activation_key', $user_activation_key );

        $user_verification_verification_page = get_option('user_verification_verification_page');
        $verification_page_url = get_permalink($user_verification_verification_page);

		$user_data 	= get_userdata( $user_id );
		$link 		= $verification_page_url.'?activation_key='.$user_activation_key;
		// $message 	= "<h3>Please verify your account by clicking the link below</h3>";
		// $message   .= "<a href='$link' style='padding:10px 25px; background:#16A05C; color:#fff;font-size:17px;text-decoration:none;'>Activate</a>";
		// $headers 	= array('Content-Type: text/html; charset=UTF-8');
	  
		uv_mail( 
			$user_data->user_email,
			array( 
				'action' 	=> 'email_confirmed',
				'user_id' 	=> $user_id,
				'link'		=> $link
			)
		);
			
			
		// uv_mail( $user_data->user_email, 'Verify Your Account', $message );
		
		uv_show_box_resend_email();
	}
//add_action( 'wp_footer', 'uv_filter_resend_activation_link', 101 );


// Login Check
add_action( 'authenticate', 'uv_user_authentication', 9999, 3 );
function uv_user_authentication( $errors, $username, $passwords ) { 

		if( isset( $errors->errors['incorrect_password'] ) ) return $errors;
		
		if( ! $username ) return $errors;
		$user = get_user_by( 'email', $username );
		if( empty( $user ) ) $user = get_user_by( 'login', $username );
		if( empty( $user ) ) return $errors;

		$user_activation_status = get_user_meta( $user->ID, 'user_activation_status', true ); 
		
		if( $user_activation_status == 0 && $user->ID != 1 ) {

            $user_verification_verification_page = get_option('user_verification_verification_page');
            $verification_page_url = get_permalink($user_verification_verification_page);


			$resend_link = $verification_page_url.'?uv_action=resend&id='. $user->ID;
			
			$uv_message_verify_email = get_option( 'uv_message_verify_email' );
			if( empty( $uv_message_verify_email ) ) 
			$uv_message_verify_email = __( 'Verify your email first!', 'user-verification' );
		
			$message = apply_Filters(
				'account_lock_message', 
				sprintf(
					'<strong>%s</strong> %s <a href="%s">%s</a>', 
					__('Error:', 'user-verification'),
					$uv_message_verify_email,
					$resend_link,
					__('Resend verification email','user-verification' )
				), 
				$username
			);
			
            return new \WP_Error('authentication_failed', $message);
		}		
        return $errors;
    }

	function uv_mail( $email_to_add = '', $args = array() ) {
		
		if( empty( $email_to_add ) ) return false;
		
		$action 	= isset( $args['action'] ) ? $args['action'] : '';
		$user_id 	= isset( $args['user_id'] ) ? $args['user_id'] : 1;
		$link 		= isset( $args['link'] ) ? $args['link'] : '';
		$user_info 	= get_userdata( $user_id );
		
		//update_option( 'uv_check_data', $action );

		if( empty( $action ) ) return false; 
		
		$parametar_vars = array(
			'{site_name}'			=> get_bloginfo('name'),
			'{site_description}' 	=> get_bloginfo('description'),
			'{site_url}' 			=>  get_bloginfo('url'),						
			// '{site_logo_url}'		=> $logo_url,
			'{user_name}' 			=> $user_info->user_login,						  
			'{user_avatar}' 		=> get_avatar( $user_id, 60 ),
			'{ac_activaton_url}'	=> $link
		);
		
		
		$uv_email_templates_data = get_option( 'uv_email_templates_data' );
		if(empty($uv_email_templates_data)){
				
			$class_uv_emails = new class_uv_emails();
			$templates_data = $class_uv_emails->uv_email_templates_data();
		
		} else {

			$class_uv_emails = new class_uv_emails();
			$templates_data = $class_uv_emails->uv_email_templates_data();
				
			$templates_data = array_merge($templates_data, $uv_email_templates_data);
		}
		
		
		$message_data = isset( $templates_data[$action] ) ? $templates_data[$action] : '';
		if( empty( $message_data ) ) return false; 
		
		
		$email_to 			= strtr( $message_data['email_to'], $parametar_vars );	
		$email_subject 		= strtr( $message_data['subject'], $parametar_vars );
		$email_body 		= strtr( $message_data['html'], $parametar_vars );
		$email_from 		= strtr( $message_data['email_from'], $parametar_vars );	
		$email_from_name 	= strtr( $message_data['email_from_name'], $parametar_vars );				
		$enable 			= strtr( $message_data['enable'], $parametar_vars );	
			
		// wp_update_post( array(
			// 'ID'	=> 1,
			// 'post_content' => $email_body,
		// ) );
		
		$headers = "";
		$headers .= "From: ".$email_from_name." <".$email_from."> \r\n";
		$headers .= "Bcc: ".$email_to." \r\n";		
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		$attachments = '';	
		
		$status = wp_mail( $email_to_add, $email_subject, $email_body, $headers, $attachments );
		
		return $status;
	}
		
	function uv_show_box_resend_email() {
		
		$uv_message_activation_sent = get_option( 'uv_message_activation_sent' );
		if( empty( $uv_message_activation_sent ) ) 
		$uv_message_activation_sent = __( 'Activation Email Sent', 'user-verification' );
		
		echo "<div class='uv_popup_box_container'><div class='uv_popup_box_content'>
		<span class='uv_popup_box_close'><i class='fa fa-times-circle-o'></i></span><i class='fa fa-check-square'></i>
		<h3 class='uv_popup_box_data'>$uv_message_activation_sent</h3></div></div>";
	}
	
	function uv_show_box_key_error() {
		
		$uv_message_invalid_key = get_option( 'uv_message_invalid_key' );
		if( empty( $uv_message_invalid_key ) ) 
		$uv_message_invalid_key = __( 'Invalid activation Key', 'user-verification' );
	
		echo "<div class='uv_popup_box_container'><div class='uv_popup_box_content'>
		<span class='uv_popup_box_close'><i class='fa fa-times-circle-o'></i></span>
		<i class='fa fa-exclamation-triangle'></i><h3 class='uv_popup_box_data'>$uv_message_invalid_key</h3></div></div>";
	}
	
	function uv_show_box_finished() {
		
		$uv_message_verification_success = get_option( 'uv_message_verification_success' );
		if( empty( $uv_message_verification_success ) )
		$uv_message_verification_success = __( 'Your account is now verified', 'user-verification' );
	
		echo "<div class='uv_popup_box_container'><div class='uv_popup_box_content'>
		<span class='uv_popup_box_close'><i class='fa fa-times-circle-o'></i></span>
		<i class='fa fa-check-square'></i><h3 class='uv_popup_box_data'>$uv_message_verification_success</h3></div></div>";
	}
	
	function uv_show_box_key_expired() {
		
		$uv_message_key_expired = get_option( 'uv_message_key_expired' );
		if( empty( $uv_message_key_expired ) )
		$uv_message_key_expired = __( 'Your account is now verified', 'user-verification' );
	
		echo "<div class='uv_popup_box_container'><div class='uv_popup_box_content'>
		<span class='uv_popup_box_close'><i class='fa fa-times-circle-o'></i></span>
		<i class='fa fa-exclamation-triangle'></i><h3 class='uv_popup_box_data'>$uv_message_key_expired</h3></div></div>";
	}

function woocommerce_checkout_order_processed( $order_id, $posted_data, $order ){
	
	wp_logout();
}

//add_action( 'woocommerce_checkout_order_processed', 'woocommerce_checkout_order_processed', 10, 3 );


function woocommerce_registration_auth_new_customer_function($bool){

	return false;
}
//add_filter( 'woocommerce_registration_auth_new_customer', 'woocommerce_registration_auth_new_customer_function', 10, 1 );
