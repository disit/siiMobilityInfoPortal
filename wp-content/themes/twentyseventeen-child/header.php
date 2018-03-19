<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'twentyseventeen' ); ?></a>

	<header id="masthead" class="site-header" role="banner">

		<?php get_template_part( 'template-parts/header/header', 'image' ); ?>

		<?php if ( has_nav_menu( 'top' ) ) : ?>
			<div class="navigation-top">
				<div class="wrap">
					<?php get_template_part( 'template-parts/navigation/navigation', 'top' ); ?>
				</div><!-- .wrap -->
			</div><!-- .navigation-top -->
		<?php endif; ?>

	</header><!-- #masthead -->

	<?php

	/*
	 * If a regular post or page, and not the front page, show the featured image.
	 * Using get_queried_object_id() here since the $post global may not be set before a call to the_post().
	 */
	if ( ( is_single() || ( is_page() && ! twentyseventeen_is_frontpage() ) ) && has_post_thumbnail( get_queried_object_id() ) ) :
		echo '<div class="single-featured-image-header">';
		echo get_the_post_thumbnail( get_queried_object_id(), 'twentyseventeen-featured-image' );
		echo '</div><!-- .single-featured-image-header -->';
	endif;
	?>

	<div class="site-content-contain">
		<div class="row container-loghi">
			<div class="bar-pilota w-50 d-flex align-items-center">
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/logo-siimobility.png" />
                <a href="<?php echo get_permalink(get_option(SIIMOB_CAMPAIGN_PAGE)); ?>"><b>Sii smart. Sii-Mobility!</b></a>
	        </div>
			<div id="loghi" class="w-50 ml-auto text-right d-flex align-items-center justify-content-end">
				<img id="logo-miur" src="<?php echo get_stylesheet_directory_uri(); ?>/img/logo-miur.png">
				<a id="logo-disit" target="_blank" href="http://www.disit.org"><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/logo-disit.png"></a>
			</div>
		</div>
		<div id="content" class="site-content">

<script>
    jQuery(document).ready(function($){

        var distance = $('.container-loghi').offset().top;

        $(window).scroll(function() {
            if ( $(this).scrollTop() > distance ) {
                $('.container-loghi').addClass('fixed');
            } else {
                $('.container-loghi').removeClass('fixed');
            }
        });
    });

</script>