<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 

class class_uv_emails{
	
	public function __construct(){

		//add_action('add_meta_boxes', array($this, 'meta_boxes_job'));
		//add_action('save_post', array($this, 'meta_boxes_job_save'));

		}
		
		
		
	public function uv_send_email($email_data){
		
		//$to_email='', $email_subject='', $email_body='', $attachments=''
		
		
		$email_to = $email_data['email_to'];	
		$email_from = $email_data['email_from'];			
		$email_from_name = $email_data['email_from_name'];
		$subject = $email_data['subject'];
		$email_body = $email_data['html'];		
		$email_subject = $email_data['subject'];			
		$enable = $email_data['enable'];
		$attachments = $email_data['attachments'];		
					
		
		
		//$uv_from_email = get_option('uv_from_email');
		//$site_name = get_bloginfo('name');

		$headers = "";
		$headers .= "From: ".$email_from_name." <".$email_from."> \r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		$status = wp_mail($email_to, $subject, $email_body, $headers, $attachments);
		
		return $status;
	}	
		
		
		
	public function uv_email_templates_data(){
		
		$templates_data_html = array();
		
		include UV_PLUGIN_DIR . 'templates/emails/user_registered.php';	
		include UV_PLUGIN_DIR . 'templates/emails/email_confirmed.php';	
					
		
		$templates_data = array(
							
			'user_registered'=>array(	'name'=>__('New User Registered','user-verification'),
				'description'=>__('Notification email for admin when a new user is registered.','user-verification'),
				'subject'=>__('New User Submitted - {site_url}','user-verification'),
				'html'=>$templates_data_html['user_registered'],
				'email_to'=>get_option('admin_email'),
				'email_from'=>get_option('admin_email'),
				'email_from_name'=> get_bloginfo('name'),																		
				'enable'=> 'yes',										
			),
			'email_confirmed'=>array(	'name'=>__('New User Confirmed','user-verification'),
				'description'=>__('Notification email for confirming a new User.','user-verification'),
				'subject'=>__('New User Confirmed - {site_url}','user-verification'),
				'html'=>$templates_data_html['email_confirmed'],
				'email_to'=>get_option('admin_email'),
				'email_from'=>get_option('admin_email'),
				'email_from_name'=> get_bloginfo('name'),										
				'enable'=> 'yes',
			),									
			
						
			

		);
		
		$templates_data = apply_filters('uv_filters_email_templates_data', $templates_data);
		
		return $templates_data;

		}
		


	public function uv_email_templates_parameters(){
		
		
			$parameters['site_parameter'] = array(
												'title'=>__('Site Parameters','user-verification'),
												'parameters'=>array('{site_name}','{site_description}','{site_url}','{site_logo_url}'),										
												);
												
			$parameters['user_parameter'] = array(
												'title'=>__('Users Parameters','user-verification'),
												'parameters'=>array('{user_name}','{user_avatar}','{user_email}'),										
												);	
												
								
		
												
			$parameters = apply_filters('uv_emails_templates_parameters',$parameters);
		
		
			return $parameters;
		
		}
	
		
		
		
		
		

	}
	
new class_uv_emails();