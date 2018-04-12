<?php /* Template Name: Progetto Pilota */ ?>

<?php get_header(); ?>

    <div class="site-content-contain">
        <div id="content" class="site-main" role="main">
            <article id="post-<?php echo the_ID(); ?>" class="twentyseventeen-panel  post-<?php echo the_ID(); ?> page type-page status-publish hentry">
                <div class="panel-content">
                    <div class="wrap no-padding-top">
                        <div class="entry-content">
                            <?php get_template_part( 'template-parts/pilota/section', 'first'  ); ?>
                            <hr/>
                            <?php get_template_part( 'template-parts/pilota/section', 'second'  ); ?>
                            <?php get_template_part( 'template-parts/pilota/section', 'campaign' ); ?>
                            <hr/>
                            <?php get_template_part( 'template-parts/pilota/section', 'fourth' ); ?>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
    <script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/modal.js"></script>
<?php get_footer(); ?>
