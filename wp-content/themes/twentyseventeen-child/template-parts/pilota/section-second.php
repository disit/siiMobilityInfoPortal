<section id="second-section">
    <div class="info-table row">
        <div class="w-75"><?php echo get_post_meta(get_the_ID() ,'city-text-table', true); ?></div>
        <div class="w-25 custom-icon"><i class="far fa-check-circle"></i></div>
        <div class="w-75"><?php echo get_post_meta(get_the_ID() ,'private-vehicle-text', true); ?></div>
        <div class="w-25 custom-icon"><i class="far fa-check-circle"></i></div>
        <div class="w-75 orange"><?php echo get_post_meta(get_the_ID() ,'public-vehicle-text', true); ?></div>
        <div class="w-25 custom-icon orange"><i class="far fa-meh orange-color"></i></div>
    </div>
    <div class="second-section-info">
        <div class="text-center">
            <label>
                <?php echo get_post_meta(get_the_ID() ,'join-campaign', true); ?>
            </label>
        </div>
        <div class="text-center">
            <label>
                <?php echo get_post_meta(get_the_ID() ,'download-app-text', true); ?>
            </label>
        </div>
        <div class="col-md-5 text-center m-auto">
            <button class="mt-4" onclick="location.href='#third-section';"><?php echo get_post_meta(get_the_ID() ,'discover-more', true); ?></button>
        </div>
    </div>
</section>