<?php
    $basic_content = get_sub_field('basic_content');
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section class="layout basic-content" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
	<div class="content">
		<div class="wrapper">
			<div class="col">
                <?php echo $basic_content; ?>
			</div>
		</div>
        <?php include(locate_template('layouts/component-button.php')); ?>
	</div>
</section>
