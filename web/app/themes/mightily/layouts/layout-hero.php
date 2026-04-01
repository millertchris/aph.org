<?php
    $image = get_sub_field('image');
    $content = get_sub_field('content');
    $title = get_sub_field('title');
    $margin_bottom = get_sub_field('layout_spacing');

    if ($image) {
        $image = '<div class="image"><img src="'.$image['url'].'" alt="'.$image['alt'].'"/></div>';
    }

    $hero_size = get_sub_field('hero_size');
    $align_image = get_sub_field('align_image');
    $display_image = get_sub_field('display_image');

    if($hero_size == "xxlarge") {
        $align_image = '';
    }

    $classes =  $hero_size . ' ' . $align_image . ' ' . $display_image;
?>

<section class="layout hero <?php echo $classes; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
    <div class="wrapper">
        <div class="row">
            <div class="col">
                <?php if ($display_image == 'has_image' || $display_image == null): ?>
                    <?php echo $content; ?>
                <?php else: ?>
                    <?php if($layout_counter == 1) : ?>
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
                    <?php else : ?>
                    <h2 data-aos="fade-up" class="h1 title animated">
                        <?php if ($title['line_one']): ?>
                            <span class="line-one break manual-kern"><?php echo $title['line_one']; ?></span>
                        <?php endif; ?>
                        <?php if ($title['line_two']): ?>
                            <span class="line-two magical-underline manual-kern"><?php echo $title['line_two']; ?></span>
                        <?php endif; ?>
                        <?php if ($title['line_three']): ?>
                            <span class="line-three break manual-kern"><?php echo $title['line_three']; ?></span>
                        <?php endif; ?>
                    </h2>                        
                    <?php endif; ?>
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