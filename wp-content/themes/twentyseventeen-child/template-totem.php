<?php
	/* Template Name: Pagine Template Totem */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
	<?php get_template_part( 'template-parts/totem/head' ); ?>

	<body class="totem-page">

		<?php get_template_part( 'template-parts/totem/header' ); ?>
		<div class="totem-main-content">
			<div class="totem-content">
				<?php if (have_posts()) : ?>
					<?php while (have_posts()) : the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; ?>
				<?php endif; ?>
			</div>
			<?php get_template_part( 'template-parts/totem/back-home' ); ?>
		</div>
		<?php get_template_part( 'template-parts/totem/footer' ); ?>

	</body>
</html>
