
<?php
    $sBackHome = get_post_meta(get_the_ID() ,'back-home', true);
?>
<?php if (!empty($sBackHome)): ?>
    <div class="back-home">
        <a href="<?php echo $sBackHome; ?>">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/img/totem/back-home.jpg">
        </a>
    </div>
<?php endif; ?>