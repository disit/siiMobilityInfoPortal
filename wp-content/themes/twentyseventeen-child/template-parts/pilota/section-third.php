<section id="third-section">
    <div class="text-center font-weight-bold">
        <h2><?php echo get_post_meta(get_the_ID() ,'third-section-title', true); ?></h2>
        <i><?php echo get_post_meta(get_the_ID() ,'third-section-subtitle', true); ?></i>
    </div>
    <div class="text-center mt-4">
        <?php echo get_post_meta(get_the_ID() ,'join-initiative-step', true); ?>
    </div>
    <div class="text-center mt-4">
        <?php echo get_post_meta(get_the_ID() ,'third-section-app', true); ?>
    </div>
    <div class="d-flex justify-content-center mt-5">
        <button class="border border-dark text-center city-modal-button" data-toggle="modal" data-target="#prato-modal">
            <i class="fas fa-map-marker-alt fa-lg pb-2"></i>Prato
        </button>
        <button class="border border-dark text-center city-modal-button" data-toggle="modal" data-target="#pisa-modal">
            <i class="fas fa-map-marker-alt fa-lg pb-2"></i>Pisa
        </button>
        <button class="border border-dark text-center city-modal-button" data-toggle="modal" data-target="#firenze-modal">
            <i class="fas fa-map-marker-alt fa-lg pb-2"></i>Firenze
        </button>
    </div>
    <div id="prato-modal" class="modal fade" role="dialog" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg custom-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/silhouette-prato.png">
                <div class="city-name text-center">
                    <i class="fas fa-map-marker"></i> Prato
                </div>
                <label class="text-center p-4 w-100"><a target="_blank" href="http://www.capautolinee.it">CAP Autolinee Prato</a> mette a disposizione biglietti del bus e voucher per</label>
                <div class="list-container">
                    <?php echo get_post_meta(get_the_ID() ,'prato-list', true); ?>
                </div>
                <a href="#">
                    <div class="bus-icon-container">
                        <i class="fas fa-bus"></i>
                    </div>
                </a>
                <label class="w-100 text-center mt-4"><?php echo get_post_meta(get_the_ID() ,'discover-rules', true); ?></label>
            </div>
        </div>
    </div>
    <div id="pisa-modal" class="modal fade" role="dialog" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg custom-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/silhouette-pisa.png">
                <div class="city-name text-center">
                    <i class="fas fa-map-marker"></i> Pisa
                </div>
                <label class="text-center p-4 w-100"><a target="_blank" href="http://www.pisa.cttnord.it">CPT Pisa</a> mette a disposizione biglietti del bus e voucher per</label>
                <div class="list-container">
                    <?php echo get_post_meta(get_the_ID() ,'pisa-list', true); ?>
                </div>
                <a target="_blank" href="#">
                    <div class="bus-icon-container">
                        <i class="fas fa-bus"></i>
                    </div>
                </a>
                <label class="w-100 text-center mt-4"><?php echo get_post_meta(get_the_ID() ,'discover-rules', true); ?></label>
            </div>
        </div>
    </div>
    <div id="firenze-modal" class="modal fade" role="dialog" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg custom-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/pilota/silhouette-firenze.png">
                <div class="city-name text-center">
                    <i class="fas fa-map-marker"></i> Firenze
                </div>
                <label class="w-100 text-center mt-4">Segui i suggerimenti dell’app; potresti vincere numerosi premi!</label>
                <div class="row text-center mt-4">
                    <div class="col-md-6">
                        <label>Se prendi il bus invece della macchina</label><br/>
                        <label class="label-point">250 punti</label>
                    </div>
                    <div class="col-md-6">
                        <label>Rispondi al questionario</label><br/>
                        <label class="label-point">30 punti</label>
                    </div>
                </div>
                <label class="text-center p-4 w-100"><a target="_blank" href="http://www.ataf.net">Ataf</a> e <a target="_blank" href="http://www.fsbusitalia.it">BUSITALIA</a> mettono a disposizione biglietti del bus e voucher per</label>
                <a target="_blank" href="#">
                    <div class="bus-icon-container">
                        <i class="fas fa-bus"></i>
                    </div>
                </a>
                <label class="w-100 text-center mt-4"><?php echo get_post_meta(get_the_ID() ,'discover-rules', true); ?></label>
            </div>
        </div>
    </div>
</section>