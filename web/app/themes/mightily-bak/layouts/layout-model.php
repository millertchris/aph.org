<?php
    $embed = get_sub_field('iframe');
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section class="layout model" tabindex="0" data-model="active" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
    <?php include(locate_template('layouts/component-intro.php')); ?>
    <div class="wrapper">
        <div class="row">
            <div class="col">
                <div class="video-wrapper">
                    <?php echo $embed; ?>
                </div>
            </div>
        </div>
    </div>
</section>
