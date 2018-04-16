<section id="fourth-section">
    <div class="text-center text-lg-left font-weight-bold">
        <h2><?php echo get_post_meta(get_the_ID() ,'configurazione-telefono-title', true); ?></h2>
        <label><?php echo get_post_meta(get_the_ID() ,'configurazione-telefono-sub-title', true); ?></label>
    </div>
    <div class="text-center text-lg-left step-list font-weight-bold">
        <?php echo get_post_meta(get_the_ID() ,'configurazione-telefono-list', true); ?>
    </div>
    <div class="mt-5">
        <b>Configurazione per smartphone Samsung:</b>
    </div>
    <br/>
    <div class="row text-center mt-5">
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
    <div class="mt-5 pt-5">
        <b>Configurazione per smartphone Huawei:</b>
    </div>
    <br/>
    <div class="text-center">
            <img id="huawei-img" src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/huawei-info.png" />
    </div>
    <p class="text-center text-lg-right mt-5 termini-policy"><a class="mr-4" target="_blank" href="<?php echo get_stylesheet_directory_uri(); ?>/pdf/Termini_uso.pdf"><b>Termini e condizioni d'uso del servizio</b></a><a target="_blank" href="<?php echo get_stylesheet_directory_uri(); ?>/pdf/Privacy_Policy.pdf"><b>Privacy policy</b></a></p>
</section>

<!-- Immagine modale -->
<div id="modal" class="modal">

  <span class="close">&times;</span>

  <img class="modal-content" id="img">

  <div id="caption"></div>
</div>