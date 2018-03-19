<?php
/**
 * Displays top navigation
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.2
 */

?>
<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Top Menu', 'twentyseventeen' ); ?>">
	<button class="menu-toggle" aria-controls="top-menu" aria-expanded="false">
		<?php
		echo twentyseventeen_get_svg( array( 'icon' => 'bars' ) );
		echo twentyseventeen_get_svg( array( 'icon' => 'close' ) );
		_e( 'Menu', 'twentyseventeen' );
		?>
	</button>

	<?php wp_nav_menu( array(
		'theme_location' => 'top',
		'menu_id'        => 'top-menu',
	) ); ?>

	<?php if (is_user_logged_in()) : ?>
		<?php  $oUser = wp_get_current_user(); ?>
		<?php $sProfileUrl = ! empty(get_option('softec_login_redirect')) ? get_option('softec_login_redirect') : '/profilo' ?>

		<div class="user-logged-in-profile">
			<div class="user-label-container">
				<label class="mr-2 hidden-xs"><?php echo $oUser->user_login; ?></label>
				<i class="fas fa-user"></i>
			</div>
			<div class="row profile-menu">
				<div class="w-50 mt-1">
					<i class="fas fa-user"></i>
				</div>
				<div class="w-50">
					<ul>
						<li><a href="<?php echo $sProfileUrl; ?>">Profilo</a></li>
						<li><a href="<?php echo wp_logout_url(); ?>">Esci</a></li>
					</ul>
				</div>
			</div>
		</div>

	<?php endif; ?>
	<?php/* if ( ( twentyseventeen_is_frontpage() || ( is_home() && is_front_page() ) ) && has_custom_header() ) : ?>
		<a href="#content" class="menu-scroll-down"><?php echo twentyseventeen_get_svg( array( 'icon' => 'arrow-right' ) ); ?><span class="screen-reader-text"><?php _e( 'Scroll down to content', 'twentyseventeen' ); ?></span></a>
	<?php endif;*/ ?>
</nav><!-- #site-navigation -->
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.user-label-container').on('click', function(){
			var screenWidth = $(window).width();
			if(screenWidth <= 767) {
				$('.profile-menu').toggle();
			}
		});
		$('.user-logged-in-profile').on('mouseover', function(){
			var screenWidth = $(window).width();
			if(screenWidth > 767) {
				$('.profile-menu').show();
			}
		});
		$('.user-logged-in-profile').on('mouseleave', function(){
			var screenWidth = $(window).width();
			if(screenWidth > 767) {
				$('.profile-menu').hide();
			}
		});
	});
</script>