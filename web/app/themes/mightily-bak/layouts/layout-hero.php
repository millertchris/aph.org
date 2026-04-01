<?php
    $image = get_sub_field('image');
    $content = get_sub_field('content');
    $title = get_sub_field('title');
    $margin_bottom = get_sub_field('layout_spacing');

    if ($image) {
        $image = '<div class="image" style="background-image: url(' . $image['url'] . ');"></div>';
    }

    $hero_size = get_sub_field('hero_size');
    $align_image = get_sub_field('align_image');
    $display_image = get_sub_field('display_image');

    $classes =  $hero_size . ' ' . $align_image . ' ' . $display_image;
?>

<section class="layout hero <?php echo $classes; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
    <div class="wrapper">
        <div class="row">
            <div class="col">
                <?php if ($display_image == 'has_image' || $display_image == null): ?>
                    <?php echo $content; ?>
                <?php else: ?>
                    <h1 data-aos="fade-up" class="title animated">
                        <?php if ($title['line_one']): ?>
                            <span class="line-one break manual-kern"><?php echo $title['line_one']; ?></span>
                        <?php endif; ?>
                        <?php if ($title['line_two']): ?>
                            <span class="line-two magical-underline manual-kern"><?php echo $title['line_two']; ?></span>
                        <?php endif; ?>
                        <?php if ($title['line_three']): ?>
                            <span class="line-three break manual-kern"><?php echo $title['line_three']; ?></span>
                        <?php endif; ?>
                    </h1>
                <?php endif; ?>
                <?php include(locate_template('layouts/component-button.php')); ?>
            </div>
            <?php if ($hero_size == 'small'): ?>
                <div class="col">
                    <?php echo $image; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($hero_size !== 'small'): ?>
        <?php echo $image; ?>
    <?php endif; ?>
</section>
<?php if (is_front_page() && $layout_counter == 1): ?>
    <div class="accent-lines" data-aos="box" data-aos-anchor-placement="bottom-bottom">
        <span class="vertical pink" data-aos="line-animation" data-aos-anchor=".accent-lines" data-aos-anchor-placement="bottom-bottom" data-aos-delay="400"></span>
        <span class="vertical green" data-aos="line-animation" data-aos-anchor=".accent-lines" data-aos-anchor-placement="bottom-bottom" data-aos-delay="400"></span>
        <span class="vertical yellow" data-aos="line-animation" data-aos-anchor=".accent-lines" data-aos-anchor-placement="bottom-bottom" data-aos-delay="400"></span>
    </div>
<?php endif; ?>
