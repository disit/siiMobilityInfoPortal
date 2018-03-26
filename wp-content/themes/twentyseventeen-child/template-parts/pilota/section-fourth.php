<section id="fourth-section">
    <div class="font-weight-bold text-center">
        <h2><?php echo get_post_meta(get_the_ID() ,'fourth-section-title', true); ?></h2>
    </div>
    <div class="step-list">
        <?php echo get_post_meta(get_the_ID() ,'fourth-section-list', true); ?>
    </div>
    <div class="samsung-huawei-config text-center p-4">
        <strong><?php echo get_post_meta(get_the_ID() ,'samsung-huawei-config', true); ?></strong>
    </div>
    <div class="row text-center">
        <div class="col-md-3">
            <img id="first-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/wizard-android-1.gif" />
        </div>
        <div class="col-md-3">
            <img id="second-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/wizard-android-2.gif" />
        </div>
        <div class="col-md-3">
            <img id="third-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/wizard-android-3.gif" />
        </div>
        <div class="col-md-3">
            <img id="fourth-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/wizard-android-4.gif" />
        </div>
    </div>
</section>

<!-- Immagine modale -->
<div id="modal" class="modal">

  <span class="close">&times;</span>

  <img class="modal-content" id="img">

  <div id="caption"></div>
</div>