<?php
    $margin_bottom = get_sub_field('layout_spacing');
?>

<section class="layout social" style="margin-bottom: <?php echo $margin_bottom; ?>px;">
	<?php include(locate_template('layouts/component-intro.php')); ?>
	<div class="content">
		<div class="wrapper">
			<div class="col">
				<?php if (have_rows('footer_social', 'option')): ?>

			    	<ul class="social">

			    	<?php while (have_rows('footer_social', 'option')): the_row(); ?>

			            <?php
                            $network = get_sub_field('network');
                            $link = get_sub_field('link');
                            $link_url = $link['url'];
                            $link_title = $link['title'];
                            $link_target = $link['target'];

                            if ($link_target == null) {
                                $link_target = '_self';
                            }
                        ?>

			    		<li class="social-item">
			                <?php if ($network == 'facebook'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Find us on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
			                <?php elseif ($network == 'twitter'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Tweet us on Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
			                <?php elseif ($network == 'instagram'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="We're on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
			                <?php elseif ($network == 'snapchat'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Connect with us on Snapchat"><i class="fab fa-snapchat-ghost" aria-hidden="true"></i></a>
			                <?php elseif ($network == 'pinterest'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Share Pins on Pinterest"><i class="fab fa-pinterest-p" aria-hidden="true"></i></a>
			                <?php elseif ($network == 'linkedin'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Connect with us on Linked In"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
			                <?php elseif ($network == 'youtube'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Visit our YouTube Channel"><i class="fab fa-youtube" aria-hidden="true"></i></a>
			                <?php elseif ($network == 'vimeo'): ?>
			                    <a href="<?php echo $link_url; ?>" class="<?php echo $network; ?>" target="<?php echo $link_target; ?>" aria-label="Visit our Vimeo"><i class="fab fa-vimeo-v" aria-hidden="true"></i></a>
			                <?php endif; ?>
			    		</li>

			    	<?php endwhile; ?>

			    	</ul>

			    <?php endif; ?>
			</div>
		</div>
	</div>
</section>
