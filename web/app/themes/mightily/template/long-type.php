<?php
// Template name: Long Type

get_header(); ?>

<div class="interior-page long-type-page">
        <?php if (post_password_required()) : ?>
			<section class="layout catch-all">
				<div class="wrapper">
					<div class="row">
						<div class="col">
							<h1 class="h3" style="text-align: left;">Password Required</h1>
							<?php echo get_the_password_form(); ?>
						</div>
					</div>
				</div>
			</section>
		<?php else : ?>
			<?php if(have_rows('layouts')) : $layout_counter = 1; ?>

				<?php while (have_rows('layouts')) : the_row(); ?>

					<?php $layout_type = get_row_layout(); ?>
					<?php include(locate_template('layouts/layout-' . $layout_type . '.php')); ?>

				<?php $layout_counter++; endwhile; ?>

			<?php else: ?>

				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<section class="layout catch-all">
						<div class="wrapper">
							<div class="row">
								<div class="col">
									<?php if (is_wc_endpoint_url( 'view-order' ) && is_user_role('teacher')): ?>
										<?php if (get_fqa_from_url()) : ?>
												<h1 class="h6 fq-title">Quota Account: <?php echo get_fqa_from_url(); ?></h1>
												<h2 class="h1">Request #<?php echo get_order_number_from_url(); ?></h2>
										<?php else : ?>
											<h1>Request #<?php echo get_order_number_from_url(); ?></h1>
										<?php endif; ?>
									<?php elseif (is_wc_endpoint_url( 'order-received' ) && is_user_role('teacher')) : ?>
										<?php if (get_fqa_from_url()) : ?>
												<h1 class="h6 fq-title">Quota Account: <?php echo get_fqa_from_url(); ?></h1>
												<h2 class="h1">Request Recieved</h2>
										<?php else : ?>
											<h1>Request Received</h1>
										<?php endif; ?>
									<?php else: ?>
										<?php if (get_fqa_from_url()) : ?>
											<h1 class="h6 fq-title">Quota Account: <?php echo get_fqa_from_url(); ?></h1>
											<h2 class="h1"><?php the_title(); ?></h2>
										<?php else : ?>
											<h1><?php the_title(); ?></h1>
										<?php endif; ?>
									<?php endif; ?>
									<?php the_content(); ?>
								</div>
							</div>
						</div>
					</section>
				<?php endwhile; endif; ?>

			<?php endif; ?>
		<?php endif; ?>
	</div>

<?php get_footer(); ?>
