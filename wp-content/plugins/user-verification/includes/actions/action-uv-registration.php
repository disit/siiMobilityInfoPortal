<?php
/*
* @Author 		PickPlugins
* Copyright: 	2015 PickPlugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


	add_action( 'user_register', 'uv_action_user_register_function', 30 );

	if ( ! function_exists( 'uv_action_user_register_function' ) ) {
		function uv_action_user_register_function( $user_id ) {

			$permalink_structure = get_option('permalink_structure');

            $user_verification_verification_page = get_option('user_verification_verification_page');
            $verification_page_url = get_permalink($user_verification_verification_page);

			$user_activation_key =  md5(uniqid('', true) );
			
			update_user_meta( $user_id, 'user_activation_key', $user_activation_key ); 
			update_user_meta( $user_id, 'user_activation_status', 0 );
			
			$user_data 	= get_userdata( $user_id );

			if(empty($permalink_structure)){
				$link 		= $verification_page_url.'&activation_key='.$user_activation_key;

			}else{

				$link 		= $verification_page_url.'?activation_key='.$user_activation_key;
			}


			
			// $message 	= "<h3>Please verify your account by clicking the link below</h3>";
			// $message   .= "<a href='$link' style='padding:10px 25px; background:#16A05C; color:#fff;font-size:17px;text-decoration:none;'>Activate</a>";
			
			
			
			
			uv_mail( 
				$user_data->user_email,
				array( 
					'action' 	=> 'user_registered',
					'user_id' 	=> $user_id,
					'link'		=> $link
				)
			);
			
			
		}
	}
