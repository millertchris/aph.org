<?php
    // $image = get_sub_field('image');
    // $margin_bottom = get_sub_field('layout_spacing');
    //
    // $image_id = $image['ID'];
    // $image_url = $image['url'];
    // $image_alt = $image['alt'];
    // $image_large = $image['sizes']['large'];
?>

<section class="layout parallax">
    <?php include(locate_template('layouts/component-intro.php')); ?>
    <div class="wrapper">
        <div class="row">
            <div class="object background">
                <div class="rellax item" data-rellax-speed="1">
                    <img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/orbit-back.png" alt="">
                </div>
            </div>
            <div class="object foreground">
                <div class="rellax item" data-rellax-speed="4">
                    <img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/orbit-front.png" alt="">
                </div>
            </div>
            <?php include(locate_template('layouts/component-button.php')); ?>
        </div>
    </div>
</section>
