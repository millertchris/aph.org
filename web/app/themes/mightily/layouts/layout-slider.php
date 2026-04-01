<?php
    $size = get_sub_field('size');
    $margin_bottom = get_sub_field('layout_spacing');
    $style = get_sub_field('style');

    if(!$style) {
        $style = 'style-1';
    }

    $title = get_sub_field('title');
    $subtitle = get_sub_field('subtitle');
    $link = get_sub_field('link');
    
    $enable_alternate_arrow_position = get_sub_field('enable_alternate_arrow_position');
    
    if($style == 'style-2'){
        $size = 'x-large';
        $enable_alternate_arrow_position = true;
    }

    $classes = $size . ' ' . $style;

    if( $enable_alternate_arrow_position  && $size == 'x-large') {
        $classes .= ' alternate-arrow-position';
    }
?>

<section class="layout slider <?php echo $classes; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
    <?php if($style=='style-2'): ?>
        <?php if($title): ?>
            <?php if($layout_counter == 1) : ?>
                <h1 class="section-title"><?php echo $title ?></h1>
            <?php else : ?>
                <h2 class="section-title"><?php echo $title ?></h2>
            <?php endif; ?>
        <?php endif; ?>
        <?php if($subtitle || $link): ?><p class='section-subtitle'><span><?php echo $subtitle ?></span><a class="btn" href="<?php echo $link['url']?>"><?php echo $link['title']; ?></a></p><?php endif; ?>
    <?php endif; ?>
    <?php if ($size != 'x-large'): ?>
        <div class="wrapper">
    <?php endif; ?>
        <?php if (have_rows('slide')): ?>
            <div class="slide-list">
            <?php while (have_rows('slide')): the_row(); ?>

                <?php
                    $image = get_sub_field('image');
                    $image_id = '';
                    $image_url = '';
                    $image_alt = '';
                    $image_large = '';                    
                    if($image){
                        $image_id = $image['ID'];
                        $image_url = $image['url'];
                        $image_alt = $image['alt'];
                        $image_large = $image['sizes']['large'];
                    }

                    $mobile_image = get_sub_field('mobile_image');
                    $mobile_image_id = '';
                    $mobile_image_url = '';
                    $mobile_image_alt = '';
                    $mobile_image_large = '';                    
                    if($mobile_image){
                        $mobile_image_id = $mobile_image['ID'];
                        $mobile_image_url = $mobile_image['url'];
                        $mobile_image_alt = $mobile_image['alt'];
                        $mobile_image_large = $mobile_image['sizes']['large'];
                    } 
                ?>

                <div class="slide<?php if (!$image && $size == 'small') : ?> no-image<?php endif; ?>">
                    <?php if ($size == 'x-large' || $style == 'style-2'): ?>
                        <div class="wrapper<?php if (!$image) : ?> no-image<?php endif; ?>">
                            <div class="content">
                                <?php if($layout_counter == 1) : ?>
                                    <?php if($style=='style-2'): ?>
                                        <h2 class="h3 title"><?php the_sub_field('title'); ?></h2>
                                    <?php else : ?>
                                        <h1 class="h3 title"><?php the_sub_field('title'); ?></h1>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <?php if($style=='style-2'): ?>
                                        <h3 class="h3 title"><?php the_sub_field('title'); ?></h3>
                                    <?php else : ?>
                                        <h2 class="h3 title"><?php the_sub_field('title'); ?></h2>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php 
                                    $content = get_sub_field('content');
                                    $has_buttons = get_sub_field('enable_button');
                                    // if($enable_alternate_arrow_position) {
                                    //     $content = substr(strip_tags($content), 0, $has_buttons ? 180 : 250);
                                    //     $content = substr($content, 0, strrpos($content, ' ')) . " ...";
                                    // }
                                    echo $content;
                                ?>
                                <?php include(locate_template('layouts/component-single-button.php')); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="content">
                            <?php if($layout_counter == 1) : ?>
                                <h1 class="h3 title"><?php the_sub_field('title'); ?></h1>
                            <?php else : ?>
                                <h2 class="h3 title"><?php the_sub_field('title'); ?></h2>
                            <?php endif; ?>
                            <?php 
                                $content = get_sub_field('content');
                                $has_buttons = get_sub_field('enable_button');
                                // if($enable_alternate_arrow_position) {
                                //     $content = substr($content, 0, $has_buttons ? 180 : 250);
                                //     $content = substr($content, 0, strrpos($content, ' ')) . " ...";
                                // }
                                echo $content;
                            ?>
                            <?php include(locate_template('layouts/component-button.php')); ?>
                            <?php include(locate_template('layouts/component-single-button.php')); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($image) : ?>
                        <div class="image <?php echo($image && $mobile_image) ? 'desktop-only' : '' ; ?>">
                            <img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>">
                        </div>                            
                    <?php endif; ?>

                    <?php if ($mobile_image) : ?>
                        <div class="image <?php echo($image && $mobile_image) ? 'mobile-only' : '' ; ?>">
                            <img src="<?php echo $mobile_image_url; ?>" alt="<?php echo $mobile_image_alt; ?>">
                        </div>                            
                    <?php endif; ?>
                </div>

            <?php endwhile; ?>
            </div>
        <?php endif; ?>
    <?php if ($size != 'x-large'): ?>
        </div>
    <?php endif; ?>
</section>