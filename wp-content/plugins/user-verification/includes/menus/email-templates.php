<?php	


/*
* @Author 		PickPlugins
* Copyright: 	2015 PickPlugins.com


*/

if ( ! defined('ABSPATH')) exit;  // if direct access 



class class_uv_emails_templates  {
	
	
    public function __construct(){
		
		echo $this->uv_templates_settings_display();
		
    }
	
	
	
	public function uv_templates_editor(){
		
			
			$uv_email_templates_data = get_option( 'uv_email_templates_data' );
			
			if(empty($uv_email_templates_data)){
				
				$class_uv_emails = new class_uv_emails();
				$templates_data = $class_uv_emails->uv_email_templates_data();
			
			} else {

				$class_uv_emails = new class_uv_emails();
				$templates_data = $class_uv_emails->uv_email_templates_data();
				
				$templates_data =array_merge($templates_data, $uv_email_templates_data);
				
			}
			//echo '<pre>'; print_r( $templates_data ); echo '</pre>';
			
			
		
			$html = '';
			
			//$templates_data = $this->uv_email_templates_data();
		
			$html.= '<div class="templates_editor uv-expandable">';		
			foreach($templates_data as $key=>$templates){
				
				if(!empty($templates['email_to'])){
					$email_to = $templates['email_to'];
					}
				else{
					$email_to = '';
					}
				
				if(!empty($templates['email_from'])){
					$email_from = $templates['email_from'];
					}
				else{
					$email_from = '';
					}				
				
				
				if(!empty($templates['email_from_name'])){
					$email_from_name = $templates['email_from_name'];
					}
				else{
					
					//$site_name = get_bloginfo('name');
					$email_from_name = '';
					}					
				
				
				if(!empty($templates['enable'])){
					$enable = $templates['enable'];
					}
				else{
					$enable = '';
					}				
				
				
				
				if(!empty($templates['description'])){
					$description = $templates['description'];
					}
				else{
					$description = '';
					}				
				
				
				
				
				$html.= '<div class="items template '.$key.'">';
				$html.= '<div class="header"><span class="expand-collapse"><i class="fa fa-expand"></i><i class="fa fa-compress"></i></span>'.$templates['name'].'</div>';
							
				
				$html.= '<input type="hidden" name="uv_email_templates_data['.$key.'][name]" value="'.$templates['name'].'" />';				
									
				$html.= '<div class="options">';
				
				$html.= '<div class="description">'.$description.'</div><br/><br/>';				
				
				
				$html.= '<label>'.__('Enable ?', 'user-verification').'<br/>';	// .options			
				$html.= '<select name="uv_email_templates_data['.$key.'][enable]" >';
				
				if($enable=='yes'){
					
					$html.= '<option selected  value="yes" >'.__('Yes', 'user-verification').'</option>';
					}
				else{
					$html.= '<option value="yes" >'.__('Yes', 'user-verification').'</option>';
					}
					
				if($enable=='no'){
					
					$html.= '<option selected value="no" >'.__('No', 'user-verification').'</option>';
					}
				else{
					$html.= '<option value="no" >'.__('No', 'user-verification').'</option>';
					}					
				$html.= '</select>';
				$html.= '</label><br /><br />';	
				
				
					
				$html.= '<label>'.__('Email To: (Copy)', 'user-verification').'<br/>';	// .options				
				$html.= '<input placeholder="hello_1@hello.com,hello_2@hello.com" type="text" name="uv_email_templates_data['.$key.'][email_to]" value="'.$email_to.'" />';	// .options	
				$html.= '</label><br /><br />';		

		
				$html.= '<label>'.__('Email from name:', 'user-verification').'<br/>';	// .options				
				$html.= '<input placeholder="hello_1@hello.com" type="text" name="uv_email_templates_data['.$key.'][email_from_name]" value="'.$email_from_name.'" />';	// .options	
				$html.= '</label><br /><br />';			
		
				$html.= '<label>'.__('Email from:', 'user-verification').'<br/>';	// .options				
				$html.= '<input placeholder="hello_1@hello.com" type="text" name="uv_email_templates_data['.$key.'][email_from]" value="'.$email_from.'" />';	// .options	
				$html.= '</label><br /><br />';			
		
		
		
		
				
				
				$html.= '<label>'.__('Email Subject:','user-verification').'<br/>';	// .options				
				$html.= '<input type="text" name="uv_email_templates_data['.$key.'][subject]" value="'.$templates['subject'].'" />';	// .options	
				$html.= '</label>';					
						
						
				ob_start();
				wp_editor( $templates['html'], $key, $settings = array('textarea_name'=>'uv_email_templates_data['.$key.'][html]','media_buttons'=>false,'wpautop'=>true,'teeny'=>true,'editor_height'=>'400px', ) );				
				$editor_contents = ob_get_clean();
			
				$html.= '<br/><label>'.__('Email Body:','user-verification').'<br/>';	// .options				
				$html.= $editor_contents;
				$html.= '</label>';		

				$html.= '</div>';	// .options			
				$html.= '</div>'; //.items

				
				}
		
		$html.= '</div>';	
		
		return $html;
		}
		
		
	
	
	
	public function uv_templates_settings_display(){
		
		$html = '';
		
		if(empty($_POST['uv_hidden'])) {
			$uv_email_templates_data = get_option( 'uv_email_templates_data' );							
		}
		else {
			if($_POST['uv_hidden'] == 'Y'){
				
				$uv_email_templates_data = stripslashes_deep($_POST['uv_email_templates_data']);
				update_option('uv_email_templates_data', $uv_email_templates_data);				
		
				$html.= '<div class="updated"><p><strong>'.__('Changes Saved.', 'user-verification' ).'</strong></p></div>';	
			}
		}
		
		
		$html.= '<div class="wrap">';
		$html.= '<div id="icon-tools" class="icon32"><br></div><h2>'.__(UV_PLUGIN_NAME.' - Emails Templates', 'user-verification').'</h2>';	
		$html.= '<form  method="post" action="'.str_replace( '%7E', '~', $_SERVER['REQUEST_URI']).'">';		
		$html.= '<input type="hidden" name="uv_hidden" value="Y">';			
		$html.= '<div class="para-settings uv-emails-templates">';
		$html.= $this->uv_templates_editor();
		$html.= '<p class="submit">
                    <input class="button button-primary" type="submit" name="Submit" value="'.__('Save Changes','user-verification' ).'" />
                    <input class="reset-email-templates button" type="button" value="'.__('Reset','user-verification' ).'" />
					</p>';
		$html.= '</form>';

		$class_uv_emails = new class_uv_emails();
		$parameters = $class_uv_emails->uv_email_templates_parameters();

		$html.= '<div class="parameters"><ul>';			
		
		foreach($parameters as $key=>$parameter){
			
			$html.='<li><br /><b>'.$parameter['title'].'</b>';
			
			foreach($parameter['parameters'] as $parameter_name){
				$html.='<li>'.$parameter_name;			
				$html.='</li>';
			}
			
			$html.='</li>';
		}
		$html.= '</ul>';		
		$html.= '</div></div></div>';			
			
		return $html;	
	}

} new class_uv_emails_templates();


