<?php
    $size = get_sub_field('size');
    $margin_bottom = get_sub_field('layout_spacing');

    $classes = $size;
?>

<section class="layout slider <?php echo $classes; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
    <?php if ($size != 'x-large'): ?>
        <div class="wrapper">
    <?php endif; ?>
        <?php if (have_rows('slide')): ?>
            <div class="slide-list">
            <?php while (have_rows('slide')): the_row(); ?>

                <?php
                    $image = get_sub_field('image');

                    $image_id = $image['ID'];
                    $image_url = $image['url'];
                    $image_alt = $image['alt'];
                    $image_large = $image['sizes']['large'];
                ?>

                <div class="slide<?php if (!$image && $size == 'small') : ?> no-image<?php endif; ?>">
                    <?php if ($size == 'x-large'): ?>
                        <div class="wrapper<?php if (!$image) : ?> no-image<?php endif; ?>">
                            <div class="content">
                                <h1 class="h3 title"><?php the_sub_field('title'); ?></h1>
                                <?php the_sub_field('content'); ?>
                                <?php include(locate_template('layouts/component-button.php')); ?>

                            </div>
                        </div>
                    <?php else: ?>
                        <div class="content">
                            <h1 class="h3 title"><?php the_sub_field('title'); ?></h1>
                            <?php the_sub_field('content'); ?>
                            <?php  include(locate_template('layouts/component-button.php')); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($image): ?>
                        <div class="image" style="background-image: url(<?php echo $image_url; ?>)"></div>
                        <!-- <div class="image">
                            <img <?php //acf_responsive_image($image_id, $image_large, '1600px');?>  alt="<?php echo $image_alt; ?>" />
                        </div> -->
                    <?php endif; ?>
                </div>

            <?php endwhile; ?>
            </div>
        <?php endif; ?>
    <?php if ($size != 'x-large'): ?>
        </div>
    <?php endif; ?>
</section>
