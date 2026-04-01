<?php
    $display_intro = get_sub_field('display_intro');
    $title = get_sub_field('title');
    $subtitle = get_sub_field('subtitle');
    $margin_bottom = get_sub_field('layout_spacing');

    $images = get_sub_field('gallery');
    $size = 'thumbnail'; // (thumbnail, medium, large, full or custom size)
?>

<section class="layout gallery" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
    <div class="wrapper">
        <div class="content">

            <?php include(locate_template('layouts/component-intro.php')); ?>

            <?php if ($images): ?>
                <ul class="gallery-list">
                    <?php foreach ($images as $image): ?>
                        <li class="gallery-item" data-id="<?php echo $image['ID']; ?>" data-url="<?php echo $image['url']; ?>">
                        	<?php echo wp_get_attachment_image($image['ID'], $size); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div>
    </div>
</section>
