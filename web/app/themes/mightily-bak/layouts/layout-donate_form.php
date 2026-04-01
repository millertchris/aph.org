<?php
    $image = get_sub_field('image');
    $donate_btn = get_sub_field('donate_button');
    $first_btn = $donate_btn[0]; // get the first button
    $button = get_sub_field('button');
    $margin_bottom = get_sub_field('layout_spacing');

    if ($image) {
        $image = '<div class="image" style="background-image: url(' . $image['url'] . ');"></div>';
    }

    $hero_size = get_sub_field('hero_size');
    $align_image = get_sub_field('align_image');
    $display_image = get_sub_field('display_image');

    $classes =  $hero_size . ' ' . $align_image . ' ' . $display_image;
?>

<section class="layout donate hero <?php echo $classes; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
	<div class="donate-wrapper">
		<div class="wrapper">
			<div class="row">
				<div class="col">
					<?php if (have_rows('donate_button')): ?>
						<h1 class="amount"><?php echo $first_btn['amount']; ?></h1>
						<h2 class="description"><?php echo $first_btn['description']; ?></h2>
					<?php endif; ?>
				</div>
	            <?php if ($hero_size == 'small'): ?>
	                <div class="col">
	                    <?php echo $image; ?>
	                </div>
	            <?php endif; ?>
			</div>
	        <?php if ($display_image == 'no_image'): ?>
	            <div class="accent-lines">
	    			<span class="vertical pink"></span>
	    			<span class="vertical green"></span>
	    			<span class="vertical yellow"></span>
	    		</div>
	        <?php endif; ?>
		</div>
	    <?php if ($hero_size !== 'small'): ?>
	        <?php echo $image; ?>
	    <?php endif; ?>
	</div>
	<?php if (have_rows('donate_button')): ?>
		<div class="wrapper donate-form">
			<ul class="donate-buttons">
				<?php $i = 0; while (have_rows('donate_button')): the_row(); $i++; ?>
					<li><button class="donate<?php if ($i==1):?> active<?php endif;?>" data-value="$<?php echo get_sub_field('amount');?>" data-description="<?php echo get_sub_field('description');?>">$<?php echo get_sub_field('amount');?></button></li>
				<?php endwhile; ?>
			</ul>
			<?php
                if ($button) {
                    $button_url = $button['url'];
                    $button_title = $button['title'];
                    $button_target = $button['target'];

                    if ($button_target == null) {
                        $button_target = '_self';
                    }
                    echo '<div class="buttons">';
                    echo '<a href="' . $button_url . '" target="' . $button_target . '" class="btn">' . $button_title . '</a>';
                    echo '</div>';
                }
            ?>
		</div>
	<?php endif; ?>
</section>
