<?php
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section class="layout slider timeline" name="Timeline Slideshow" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
	<?php include(locate_template('layouts/component-intro.php')); ?>
    <?php
        if($layout_counter == 1){
            if($display_intro) {
                $timeline_date_tag = 'h2';
				$timeline_title_tag = 'h3';
            } else {
                $timeline_date_tag = 'h1';
				$timeline_title_tag = 'h2';
            }
        } else {
            if($display_intro) {
                $timeline_date_tag = 'h3';
				$timeline_title_tag = 'h4';
            } else {
                $timeline_date_tag = 'h2';
				$timeline_title_tag = 'h3';
            }                  
        }
    ?> 	
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
							<<?php echo $timeline_date_tag; ?> class="h6 date"><?php the_sub_field('date'); ?></<?php echo $timeline_date_tag; ?>>
							<<?php echo $timeline_title_tag; ?> class="h6 title"><?php the_sub_field('title'); ?></<?php echo $timeline_title_tag; ?>>
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
