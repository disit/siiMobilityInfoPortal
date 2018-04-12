<div class="row campaign-row">
    <div class="col-lg-4">
        <div class="campaign-content">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/silhouette-prato.png">
            <div class="city-name text-center">
                <i class="fas fa-map-marker"></i> Prato
            </div>
            <label class="text-center p-4 w-100"><?php echo get_post_meta(get_the_ID() ,'prato-cap-text', true); ?></label>
            <div class="list-container">
                <?php echo get_post_meta(get_the_ID() ,'prato-list', true); ?>
            </div>
        </div>
        <a class="rule-link" target="_blank" href="<?php echo get_stylesheet_directory_uri(); ?>/pdf/Regolamento_della_campagna_a_premi_Prato.pdf">
            <div class="bus-icon-container">
                <i class="fas fa-bus"></i>
            </div>
            <label class="w-100 text-center mt-4"><?php echo get_post_meta(get_the_ID() ,'discover-rules', true); ?></label>
        </a>
    </div>

    <div class="col-lg-4">
        <div class="campaign-content">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/silhouette-pisa.png">
            <div class="city-name text-center">
                <i class="fas fa-map-marker"></i> Pisa
            </div>
            <label class="text-center p-4 w-100"><?php echo get_post_meta(get_the_ID() ,'pisa-cpt-text', true); ?></label>
            <div class="list-container">
                <?php echo get_post_meta(get_the_ID() ,'pisa-list', true); ?>
            </div>
        </div>
        <a class="rule-link" target="_blank" href="<?php echo get_stylesheet_directory_uri(); ?>/pdf/Regolamento_della_campagna_a_premi_Pisa.pdf">
            <div class="bus-icon-container">
                <i class="fas fa-bus"></i>
            </div>
            <label class="w-100 text-center mt-4"><?php echo get_post_meta(get_the_ID() ,'discover-rules', true); ?></label>
        </a>
    </div>

    <div class="col-lg-4">
        <div class="campaign-content">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/silhouette-firenze.png">
            <div class="city-name text-center">
                <i class="fas fa-map-marker"></i> Firenze
            </div>
            <label class="text-center p-4 w-100"><?php echo get_post_meta(get_the_ID() ,'firenze-ataf-busitalia-text', true); ?></label>
            <div class="row text-center mt-4">
                <div class="col-md-6">
                    <?php echo get_post_meta(get_the_ID() ,'firenze-bus-point-text', true); ?>
                </div>
                <div class="col-md-6">
                    <?php echo get_post_meta(get_the_ID() ,'firenze-questionario-text', true); ?>
                </div>
            </div>
            <label class="w-100 text-center mt-4"><?php echo get_post_meta(get_the_ID() ,'firenze-segui-suggerimenti', true); ?></label>
        </div>
        <a class="rule-link" target="_blank" href="<?php echo get_stylesheet_directory_uri(); ?>/pdf/Regolamento_della_campagna_a_premi_Firenze.pdf">
            <div class="bus-icon-container">
                <i class="fas fa-bus"></i>
            </div>
            <label class="w-100 text-center mt-4"><?php echo get_post_meta(get_the_ID() ,'discover-rules', true); ?></label>
        </a>
    </div>
</div>