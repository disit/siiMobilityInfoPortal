<section id="first-section">
    <div class="row">
        <div class="col-md-6 d-flex flex-column align-items-center justify-content-center">
            <div id="page-title" class="font-weight-bold text-center">
                <h2><?php echo the_title(); ?></h2>
            </div>
            <div id="first-section-abstract" class="post-meta mt-5 text-center">
                <?php echo get_post_meta(get_the_ID() ,'first-section-abstract', true); ?>
            </div>
            <button class="mt-4" onclick="location.href='#second-section';"><?php echo get_post_meta(get_the_ID() ,'discover-initiative', true); ?></button>
        </div>
        <div class="col-md-6 d-flex flex-row align-items-center justify-content-center mt-4">
            <div class="position-absolute" id="download-app-store-text">
                <h4>Scarica l'app dagli store</h4>
            </div>
            <div class="store-link-container">
                <a class="store-link" target="_blank" href="<?php echo get_post_meta(get_the_ID() ,'android-download', true); ?>"><img class="android-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/google-play.png" /></a>
            </div>
            <div class="store-link-container">
                <a class="store-link" target="_blank" href="<?php echo get_post_meta(get_the_ID() ,'ios-download', true); ?>"><img class="ios-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/app-store.png" /></a>
            </div>
        </div>
    </div>
</section>