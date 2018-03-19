<?php
/**
 * Frontend Reset Password - Reset Form
 * 
 * @version	1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="password-lost-form-wrap">

	<?php if ( ! empty( $errors ) ) : ?>

		<?php foreach ( $errors as $error ) : ?>
			<p class="som-password-sent-message som-password-error-message">
				<span><?php echo $error; ?></span>
			</p>
		<?php endforeach; ?>

	<?php endif; ?>

	<form id="resetpasswordform" method="post" class="account-page-form som-pass-strength-form">
		<fieldset>
			<legend><?php echo $form_title; ?></legend>
			<p class="extra-space">
				<?php echo $reset_text; ?>
			</p>

			<div>

			<p>

				<label for="som_new_user_pass"><?php _e( 'New Password', 'frontend-reset-password' ); ?></label>

				<?php if ( ! empty( $min_length ) ) { ?>
					<input name="som_new_user_pass" id="som_new_user_pass" class="disblock som-password-input som-pass-strength-input" type="password" pattern=".{<?php echo $min_length; ?>,}" required>
				<?php } else { ?>
					<input name="som_new_user_pass" id="som_new_user_pass" class="disblock som-password-input som-pass-strength-input" type="password" required>
				<?php } ?>

				<?php do_action( 'som_after_change_new_pass_input' ); ?>

			</p>
			<p>

				<label for="som_new_user_pass_again"><?php _e( 'Re-enter Password', 'frontend-reset-password' ); ?></label>

				<?php if ( ! empty( $min_length ) ) { ?>
					<input name="som_new_user_pass_again" id="som_new_user_pass_again" class="disblock som-password-input" type="password" pattern=".{<?php echo $min_length; ?>,}" required>
				<?php } else { ?>
					<input name="som_new_user_pass_again" id="som_new_user_pass_again" class="disblock som-password-input" type="password" required>
				<?php } ?>

			</p>

			</div>

			<div class="lostpassword-submit">

				<?php wp_nonce_field( 'somfrp_reset_pass', 'somfrp_nonce' ); ?>
		
				<input type="hidden" name="submitted" id="submitted" value="true">
				<input type="hidden" name="action" id="somfrp_post_action" value="somfrp_reset_pass">
				<button type="submit" id="reset-pass-submit" name="reset-pass-submit" class="button big-btn"><?php echo $button_text; ?></button>
			</div>

		</fieldset>
	</form>

</div>