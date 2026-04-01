<?php
    $basic_content = get_sub_field('basic_content');
    $margin_bottom = get_sub_field('layout_spacing');
	$enable_stripes = get_sub_field('enable_stripes');
?>

<?php if($enable_stripes): ?>
	<div class="accent-lines" data-aos="box" data-aos-anchor-placement="bottom-bottom">
        <span class="vertical pink" data-aos="line-animation" data-aos-anchor=".accent-lines" data-aos-anchor-placement="bottom-bottom" data-aos-delay="400"></span>
        <span class="vertical green" data-aos="line-animation" data-aos-anchor=".accent-lines" data-aos-anchor-placement="bottom-bottom" data-aos-delay="400"></span>
        <span class="vertical yellow" data-aos="line-animation" data-aos-anchor=".accent-lines" data-aos-anchor-placement="bottom-bottom" data-aos-delay="400"></span>
    </div>
<?php endif; ?>

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
