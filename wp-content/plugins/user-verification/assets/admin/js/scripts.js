jQuery(document).ready(function($){
	
	
	$(document).on('click', '.row-actions .uv_action', function() {
		
		if( ! confirm( L10n_user_verification.confirm_text ) ) return;
		
		_user_id 	= $(this).attr( 'user_id' );
		_do 		= $(this).attr( 'do' );
		
		$(this).parent().prev().html( L10n_user_verification.text_updateing + ' <i class="fa fa-spin fa-cog"></i>' );
		
		$.ajax(
			{
		type: 'POST',
		context: this,
		url:uv_ajax.uv_ajaxurl,
		data: {
			"action"	: "uv_ajax_approve_user_manually", 
			"user_id"	: _user_id, 
			"do"		: _do, 
		},
		success: function(data){
			
			if( _do == 'approve' ) {
				
				$(this).text( L10n_user_verification.text_remove_approve );
				$(this).attr( 'do', 'remove_approval' );
				$(this).removeClass( 'uv_approve' );
				$(this).addClass( 'uv_remove_approval' );
			}
			
			if( _do == 'remove_approval' ) {
				
				$(this).text( L10n_user_verification.text_approve_now );
				$(this).attr( 'do', 'approve' );
				$(this).removeClass( 'uv_remove_approval' );
				$(this).addClass( 'uv_approve' );
			}
			
			if( data.length > 0 ) $(this).parent().prev().html( data );
		}
			});		
	})
		
		
	$(document).on('click', '.uv_domain_add', function() {
	
		html  = "<li class='uv_domain'>";
		html += "<input type='text' placeholder='thread.com' name='uv_settings_blocked_domain[]'/>";
		html += "<div class='button uv_domain_remove'><i class='fa fa-times'></i></div>";
		html += "</li>";
		
		$(this).parent().parent().append(html);
	})

	$(document).on('click', '.uv_domain_remove', function() {
		$(this).parent().remove();
	})
	
	$(document).on('click', '.uv_username_add', function() {
	
		html  = "<li class='uv_username'>";
		html += "<input type='text' placeholder='thread' name='uv_settings_blocked_username[]'/>";
		html += "<div class='button uv_username_remove'><i class='fa fa-times'></i></div>";
		html += "</li>";
		
		$(this).parent().parent().append(html);
	})

	$(document).on('click', '.uv_username_remove', function() {
		$(this).parent().remove();
	})
	
	

	$(document).on('click', '.uv-expandable .header .expand-collapse', function()
			{
				if($(this).parent().parent().hasClass('active'))
					{
						$(this).parent().parent().removeClass('active');
					}
				else
					{
						$(this).parent().parent().addClass('active');	
					}
				
			
			})	

		
		$(document).on('click', '.uv-emails-templates .reset-email-templates', function()
			{

				if(confirm( L10n_user_verification.reset_confirm_text )){
					
					$.ajax(
						{
					type: 'POST',
					context: this,
					url:qa_ajax.qa_ajaxurl,
					data: {"action": "user_verification_reset_email_templates", },
					success: function(data)
							{	
							
								$(this).val('Reset Done');
							
								location.reload();
							}
						});
					
					}

				})




	});	







