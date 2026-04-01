<?php

// Template name: Vendor
get_header();


?>

<div class="interior-page">

<?php if(have_rows('layouts')) : $layout_counter = 1; ?>

<?php while (have_rows('layouts')) : the_row(); ?>

    <?php $layout_type = get_row_layout(); ?>
    <?php include(locate_template('layouts/layout-' . $layout_type . '.php')); ?>

<?php $layout_counter++; endwhile; ?>

<?php endif; ?>

</div>

<?php get_footer(); ?>
