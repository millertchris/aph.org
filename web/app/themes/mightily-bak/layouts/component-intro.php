<?php
    $intro = get_sub_field('intro');
    $display_intro = $intro['display_intro'];
    $title = $intro['title'];
    $subtitle = $intro['subtitle'];

    $style = $intro['styles'];

    if ($style == 'style-1') {
        $heading_size = 'h2';
    } else {
        $heading_size = 'h4';
    }

    $classes = $style;
?>
<?php if ($display_intro): ?>
    <div class="intro <?php echo $classes; ?>">
        <div class="wrapper">
            <h1 class="<?php echo $heading_size; ?> title"><?php echo $title; ?></h1>
            <?php if ($subtitle): ?>
                <p class="summary"><?php echo $subtitle; ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
