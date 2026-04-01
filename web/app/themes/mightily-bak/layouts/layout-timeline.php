<?php
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section class="layout slider timeline" name="Timeline Slideshow" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
	<?php include(locate_template('layouts/component-intro.php')); ?>
	<div class="wrapper">
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

					<div class="slide">
						<div class="content">
							<h2 class="h6 date"><?php the_sub_field('date'); ?></h2>
							<h3 class="h6 title"><?php the_sub_field('title'); ?></h3>
							<div class="info">
								<?php the_sub_field('content'); ?>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
