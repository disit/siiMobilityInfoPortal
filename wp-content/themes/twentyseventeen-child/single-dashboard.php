<?php 
    $sUrl = get_post_meta(get_the_ID(), 'dashboard_url', true);
    $iHeight = get_post_meta(get_the_ID(), 'dashboard_height', true);
    $sHeight = ! empty($iHeight) ? $iHeight . 'px' : '500px';
?>

<?php get_header(); ?>

<div class="text-center mb-5">
    <h1> <?php the_title(); ?> </h1>
</div>
<div id="dashboard-<?php echo the_ID();?>">
    <div class="col-md-12">
        <iframe style="width: 100%; height: <?php echo $sHeight; ?>;" src="<?php echo $sUrl;?>"></iframe>
    </div>
</div>


<?php get_footer(); ?>