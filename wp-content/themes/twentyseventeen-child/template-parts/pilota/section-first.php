<section id="first-section">
    <div id="page-title" class="font-weight-bold text-center">
        <h1><?php echo the_title(); ?></h1>
    </div>
    <div class="row">
        <div class="col-lg-6 d-flex flex-row align-items-center justify-content-center mt-4">
            <img id="img-campagna" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/campagna.jpg" />
        </div>
        <div class="col-lg-6 d-flex flex-column align-items-center justify-content-center">
            <div id="first-section-abstract" class="post-meta mt-5 text-center text-lg-left">
                <?php echo get_post_meta(get_the_ID() ,'campagna-primo-testo', true); ?>
            </div>
            <div id="first-section-abstract" class="post-meta mt-5 text-center text-lg-left">
                <?php echo get_post_meta(get_the_ID() ,'campagna-secondo-testo', true); ?>
            </div>
            <div id="first-section-abstract" class="post-meta mt-5 text-center text-lg-left">
                <?php echo get_post_meta(get_the_ID() ,'campagna-terzo-testo', true); ?>
            </div>
            <h4 class="mt-5 text-center">
                <?php echo get_post_meta(get_the_ID() ,'testo-scarica-app', true); ?>
            </h4>
            <a class="store-link" target="_blank" href="<?php echo get_post_meta(get_the_ID() ,'android-download-link', true); ?>"><img class="android-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/google-play.png" /></a>
        </div>
    </div>
</section>
