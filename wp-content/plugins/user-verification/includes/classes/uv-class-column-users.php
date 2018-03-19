<?php

/*
* @Author 		pickplugins
* Copyright: 	2015 pickplugins
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


class uv_class_column_users{
	
	public function __construct(){

		add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column_function' ), 10, 3 );
		add_filter( 'manage_users_columns', array( $this, 'manage_users_columns_function' ) );
    }
	
	public function manage_users_columns_function( $columns ) {
		
		$new_columns 	= array();
		$count 			= 0;
		
		foreach( $columns as $column_key => $column_title ){ $count++;
			
			if( $count == 3 ) $new_columns[ 'uv' ] = __('Verification Status', 'user-verification');
			else $new_columns[ $column_key ] = $column_title;
		}
		
		return $new_columns;
    }
	
	public function manage_users_custom_column_function( $val, $column_name, $user_id ) {
		
		ob_start();
		
		$this_user		= get_user_by( 'id', $user_id );
	
		if( $column_name == 'uv' ) {
			
			$user_activation_status = get_user_meta( $user_id, 'user_activation_status', true );
			$user_activation_status = empty( $user_activation_status ) ? 0 : $user_activation_status;
			$uv_status 				= $user_activation_status == 1 ? __('Approved', 'user-verification') : __('Pending approval', 'user-verification');
							
			echo "<div class='uv_status'>$uv_status</div>";
			echo "<div class='row-actions'>";
			
			
			if( $user_activation_status == 0 ) {
				
				echo "<span class='uv_action uv_approve' user_id='$user_id' do='approve'>".__('Approve now', 'user-verification')."</span>";
			}
			
			if( $user_activation_status == 1 ) {
				
				echo "<span class='uv_action uv_remove_approval' user_id='$user_id' do='remove_approval'>".__('Remove Approval', 'user-verification')."</span>";
			}
			
			
			echo "</div>";
		}
		
		return ob_get_clean();
    }


} new uv_class_column_users();

