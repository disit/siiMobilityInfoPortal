<?php	
/*
* @Author 		PickPlugins
* Copyright: 	2015 PickPlugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 



class class_uv_settings_page {
	
	
    public function __construct(){
		
    }
	
	public function uv_settings_options($options = array()){
		
		// $class_uv_functions = new class_uv_functions();
		
		$section_options = array(
			

             'user_verification_verification_page'=>array(
                    'css_class'=>'user_verification_verification_page',
                    'title'=>__('verification page?','user-verification'),
                    'option_details'=>__('Verification checker page where you place the shortcode <code>[user_verification_check]</code>, please create a page and use this shortcode uder post content.','user-verification'),
                    'input_type'=>'select',
                    'input_values'=>'',
					'input_args'=> user_verification_get_pages_list(),
			),

            'user_verification_redirect_verified'=>array(
                'css_class'=>'user_verification_redirect_verified',
                'title'=>__('Redirect after verification?','user-verification'),
                'option_details'=>__('Redirect page after successfully verified account.','user-verification'),
                'input_type'=>'select',
                'input_values'=>'',
                'input_args'=> user_verification_get_pages_list(),
            ),
			

			
			'user_verification_login_automatically'=>array(
                'css_class'=>'user_verification_login_automatically',
                'title'=>__('Automatically login after verification?','user-verification'),
                'option_details'=>__('Yes means, users click on the Account activation link from email and they login automaticcaly to your website, No means they don\'t','user-verification'),
                'input_type'=>'select',
				'input_args'=> array(
					'yes'=>__('Yes', 'user-verification'),
					'no'=>__('No', 'user-verification'),
				)
            ),
			
		);	
		$options['uv_settings_options']['title'] = '<i class="fa fa-cogs"></i> '.__('Options','user-verification');
		$options['uv_settings_options']['fields'] = apply_filters( 'uv_settings_options', $section_options );
		
		$section_options = array(
		
            'user_verification_enable_block_domain'=>array(
				'css_class'=>'user_verification_enable_block_domain',
                'title'=>__('Enable domain block','user-verification'),
                'option_details'=>__('Enable domain blocking.','user-verification'),
                'input_type'=>'select',
				'input_args'=> array(
					'no'=>__('No', 'user-verification'),
					'yes'=>__('Yes', 'user-verification'),
				)
			),
			
			'user_verification_enable_block_username'=>array(
				'css_class'=>'user_verification_enable_block_username',
                'title'=>__('Enable username block','user-verification'),
                'option_details'=>__('Enable username blocking.','user-verification'),
                'input_type'=>'select',
				'input_args'=> array(
					'no'=>__('No', 'user-verification'),
					'yes'=>__('Yes', 'user-verification'),
				)
			),
			
		);	
		$options['uv_settings_security']['title'] = '<i class="fa fa-shield"></i> '.__('Security','user-verification');
		$options['uv_settings_security']['fields'] = apply_filters( 'uv_settings_security', $section_options );
		
		
		
		$section_options = array(
		
            'uv_message_invalid_key'=>array(
				'title'=>__('Invalid activation key','user-verification'),
                'option_details'=>__('Show custom message when user activation key is invalid or wrong','user-verification'),
                'input_type'=>'textarea',
			),
			'uv_message_activation_sent'=>array(
				'title'=>__('Activation key send','user-verification'),
                'option_details'=>__('Show custom message when activation key is sent on user email','user-verification'),
                'input_type'=>'textarea',
			),
			'uv_message_verify_email'=>array(
				'title'=>__('Verify email address','user-verification'),
                'option_details'=>__('Show custom message when user an user try to login without verifying his/her email with proper activation key','user-verification'),
                'input_type'=>'textarea',
			),

            'user_verification_registered_message'=>array(
	            'css_class'=>'user_verification_registered_message',
	            'title'=>__('Registration success message','user-verification'),
	            'option_details'=>__('User will get this message as soon as registered on your website','user-verification'),
	            'input_type'=>'textarea',
            ),

			'uv_message_verification_success'=>array(
				'title'=>__('Verification successfull','user-verification'),
                'option_details'=>__('Show custom message when user successfully verified','user-verification'),
                'input_type'=>'textarea',
			),
			'uv_message_key_expired'=>array(
				'title'=>__('Activation key Expired','user-verification'),
                'option_details'=>__('Show custom message when user activation key is expired','user-verification'),
                'input_type'=>'textarea',
			),
			
		);	
		$options['uv_settings_message']['title'] = '<i class="fa fa-commenting"></i> '.__('Messages','user-verification');
		$options['uv_settings_message']['fields'] = apply_filters( 'uv_settings_message', $section_options );
		
		
		
		$options = apply_filters( 'uv_filter_settings_options', $options );

		return $options;
	}
	
	
	public function uv_settings_options_form(){
		
			global $post;
			
			$uv_settings_options = $this->uv_settings_options();
			
			// echo "<pre>"; print_r( $uv_settings_options ); echo "</pre>";
			
			$html = '';

			$html.= '<div class="para-settings post-grid-settings">';			

			$html_nav = '';
			$html_box = '';
					
			$i=1;
			foreach($uv_settings_options as $key=>$options){
			
			if( $i == 1 ) $html_nav.= '<li nav="'.$i.'" class="nav'.$i.' active">'.$options['title'].'</li>';				
			else $html_nav.= '<li nav="'.$i.'" class="nav'.$i.'">'.$options['title'].'</li>';
				
			if( $i == 1 ) $html_box.= '<li style="display: block;" class="box'.$i.' tab-box active">';				
			else $html_box.= '<li style="display: none;" class="box'.$i.' tab-box">';
			
			
			$single_html_box = '';
			
			foreach( $options['fields'] as $option_key => $option_info ){
				
				$option_value =  get_option( "$option_key", '' );				
				if( empty( $option_value ) )
				$option_value = isset( $option_info['input_values'] ) ? $option_info['input_values'] : '';
				
				$placeholder = isset( $option_info['placeholder'] ) ? $option_info['placeholder'] : ''; 
				$css_class = isset( $option_info['css_class'] ) ? $option_info['css_class'] : ''; 
				
				$single_html_box.= "<div class='option-box $css_class'>";
				$single_html_box.= '<p class="option-title">'.$option_info['title'].'</p>';
				$single_html_box.= '<p class="option-info">'.$option_info['option_details'].'</p>';
				
				if($option_info['input_type'] == 'text')
				$single_html_box.= '<input type="text" id="'.$option_key.'" placeholder="'.$placeholder.'" name="'.$option_key.'" value="'.$option_value.'" /> ';					
	
				elseif( $option_info['input_type'] == 'text-multi' ) {
					
					$input_args = $option_info['input_args'];
					foreach( $input_args as $input_args_key => $input_args_values ) {
						if(empty($option_value[$input_args_key]))
						$option_value[$input_args_key] = $input_args[$input_args_key];
							
						$single_html_box.= '<label>'.ucfirst($input_args_key).'<br/><input class="job-bm-color" type="text" placeholder="'.$placeholder.'" name="'.$option_key.'['.$input_args_key.']" value="'.$option_value[$input_args_key].'" /></label><br/>';	
					}					
				}
					
				elseif($option_info['input_type'] == 'textarea')
				$single_html_box.= '<textarea placeholder="'.$placeholder.'" name="'.$option_key.'" >'.$option_value.'</textarea> ';
					
				elseif( $option_info['input_type'] == 'radio' ) {
					
					$input_args = $option_info['input_args'];
					foreach( $input_args as $input_args_key => $input_args_values ) {
						
						$checked = ( $input_args_key == $option_value ) ? $checked = 'checked' : '';
							
						$html_box.= '<label><input class="'.$option_key.'" type="radio" '.$checked.' value="'.$input_args_key.'" name="'.$option_key.'"   >'.$input_args_values.'</label><br/>';
					}
				}
					
				elseif( $option_info['input_type'] == 'select' ) {
					
					$input_args = $option_info['input_args'];
					$single_html_box 	.= '<select name="'.$option_key.'" >';
					
					foreach( $input_args as $input_args_key => $input_args_values ) {
						$selected = ( $input_args_key == $option_value ) ? 'selected' : '';
						$single_html_box.= '<option '.$selected.' value="'.$input_args_key.'">'.$input_args_values.'</option>';
					}
					
					$single_html_box.= '</select>';
				}					
				
				elseif( $option_info['input_type'] == 'selectmultiple' ) {
					
					$input_args = $option_info['input_args'];
					$single_html_box.= '<select multiple="multiple" size="6" name="'.$option_key.'[]" >';

					foreach($input_args as $input_args_key=>$input_args_values){
						
						$selected = in_array( $input_args_key, $option_value ) ? 'selected' : '';
						$single_html_box.= '<option '.$selected.' value="'.$input_args_key.'">'.$input_args_values.'</option>';
					}
					$single_html_box.= '</select>';
				}				

				elseif( $option_info['input_type'] == 'checkbox' ) {
					foreach($option_info['input_args'] as $input_args_key=>$input_args_values){

						$checked = in_array( $input_args_key, $option_value ) ? 'checked' : '';
						$single_html_box.= '<label><input '.$checked.' value="'.$input_args_key.'" name="'.$option_key.'['.$input_args_key.']"  type="checkbox" >'.$input_args_values.'</label><br/>';
					}
				}
					
				elseif( $option_info['input_type'] == 'file' ){
					
					$single_html_box.= '<input type="text" id="file_'.$option_key.'" name="'.$option_key.'" value="'.$option_value.'" /><br />';
					$single_html_box.= '<input id="upload_button_'.$option_key.'" class="upload_button_'.$option_key.' button" type="button" value="Upload File" />';					
					$single_html_box.= '<br /><br /><div style="overflow:hidden;max-height:150px;max-width:150px;" class="logo-preview"><img style=" width:100%;" src="'.$option_value.'" /></div>';
					$single_html_box.= '
					<script>jQuery(document).ready(function($){
					var custom_uploader; 
					jQuery("#upload_button_'.$option_key.'").click(function(e) {
						e.preventDefault();
						if (custom_uploader) {
							custom_uploader.open();
							return;
						}
						custom_uploader = wp.media.frames.file_frame = wp.media({
							title: "Choose File",
							button: { text: "Choose File" },
							multiple: false
						});
						custom_uploader.on("select", function() {
							attachment = custom_uploader.state().get("selection").first().toJSON();
							jQuery("#file_'.$option_key.'").val(attachment.url);
							jQuery(".logo-preview img").attr("src",attachment.url);											
						});
						custom_uploader.open();
					});
					})
					</script>';					
				}
				$single_html_box.= '</div>';
			}
			
			$html_box .= apply_filters( "uv_filters_setting_box_$key" , $single_html_box );
			
			$html_box.= '</li>';
			
			$i++;
			}
			
			
			$html.= '<ul class="tab-nav">';
			$html.= $html_nav;			
			$html.= '</ul>';
			$html.= '<ul class="box">';
			$html.= $html_box;
			$html.= '</ul>';		
			
			
			
			$html.= '</div>';			
			return $html;
		}

}

new class_uv_settings_page();


 

$class_uv_settings_page = new class_uv_settings_page();
$uv_settings_options = $class_uv_settings_page->uv_settings_options();

if( ! empty( $_POST['uv_hidden'] ) && $_POST['uv_hidden'] == 'Y') {

	foreach($uv_settings_options as $options_tab=>$options){
		foreach($options['fields'] as $option_key=>$option_data){
			if(!empty($_POST[$option_key])){
				${$option_key} = stripslashes_deep($_POST[$option_key]);
				update_option($option_key, ${$option_key});
			}
			// else{
				// ${$option_key} = array();
				// update_option($option_key, ${$option_key});
			// }
		}
	}
	do_action( 'uv_settings_save' );
	
	?><div class="updated"><p><strong><?php _e('Changes Saved.', 'user-verification' ); ?></strong></p></div><?php
}	

foreach($uv_settings_options as $options_tab=>$options){
	foreach($options['fields'] as $option_key=>$option_data){
		${$option_key} = get_option( $option_key );
	}
}

?>

<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div><?php echo "<h2>".__(UV_PLUGIN_NAME.' Settings', 'user-verification')."</h2>";?>
	<form  method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		
		<input type="hidden" name="uv_hidden" value="Y">
        <?php 
			settings_fields( 'uv_plugin_options' );
			do_settings_sections( 'uv_plugin_options' );
			echo $class_uv_settings_page->uv_settings_options_form(); 
		?>
		<p class="submit"><input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save Changes','user-verification' ); ?>" /></p>
	
	</form>
</div>
