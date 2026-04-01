<?php global $post;

// retrieve our search query if applicable
$query = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';

// retrieve our pagination if applicable
$swppg = isset( $_REQUEST['swppg'] ) ? absint( $_REQUEST['swppg'] ) : 1;

if (isset($_GET['engine'])) {
	$post_count = 9;
} else {
	$post_count = 3;
}

if ( class_exists( 'SWP_Query' ) ) {

	// $engine = 'product_search'; // taken from the SearchWP settings screen

	$swp_query = new SWP_Query(
		// see all args at https://searchwp.com/docs/swp_query/
		array(
			's'      => $query,
			'engine' => $engine,
			'page'   => $swppg,
			'posts_per_page' => $post_count,
		)
	);

	// set up pagination
	$pagination = paginate_links( array(
		'format'  => '?swppg=%#%',
		'current' => $swppg,
		'total'   => $swp_query->max_num_pages,
	) );
}

?>

<?php if ($engine == 'page_search'): ?>
	<section class="layout listing list">

		<div class="intro style-2">
			<div class="wrapper">
	            <?php if ($engine == 'product_search'): ?>
	                <h2 class="h4 title"><img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/icon-product.svg" class="icon" alt="Product Icon"> Products</h2>
	            <?php endif; ?>
				<?php if ($engine == 'page_search'): ?>
	                <h2 class="h4 title"><img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/icon-page.svg" class="icon" alt="Page Icon"> Pages</h2>
	            <?php endif; ?>
	            <?php if ($engine == 'post_search'): ?>
	                <h2 class="h4 title"><img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/icon-post.svg" class="icon" alt="Post Icon"> Posts</h2>
	            <?php endif; ?>
			</div>
		</div>

		<div class="wrapper">
			<div class="content">

	            <?php $i = 0; ?>

				<?php if ( ! empty( $query ) && isset( $swp_query ) && ! empty( $swp_query->posts ) ) : ?>

					<div class="list-items">

						<?php foreach ( $swp_query->posts as $post ) : ?>
	                        <?php $i++; ?>
							<?php setup_postdata( $post ); ?>

							<div class="item">
								<div class="col">
									<a href="<?php the_permalink(); ?>" class="h6"><?php the_title(); ?></a>
									<?php echo custom_excerpt(30); ?>
								</div>
							</div>

						<?php endforeach; ?>
					</div>

					<?php wp_reset_postdata(); ?>

					<?php
					if ( $swp_query->max_num_pages > 1 ) : ?>
						<?php $arr_params = array( 'engine' => $engine, 's' => $_GET['s'] ); ?>
						<a href="<?php echo esc_url( add_query_arg( $arr_params ) ); ?>" class="btn more-results">More results</a>
					<?php else: ?>

					<?php endif; ?>

	            <?php else: ?>

	                <h3 class="h5 title">No results.</h3>

				<?php endif; ?>

			</div>
		</div>
	</section>
<?php else: ?>
	<section class="layout listing grid cards medium">

		<div class="intro style-2">
			<div class="wrapper">
	            <?php if ($engine == 'product_search'): ?>
	                <h2 class="h4 title"><img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/icon-product.svg" class="icon" alt="Product Icon"> Products</h2>
	            <?php endif; ?>
				<?php if ($engine == 'page_search'): ?>
	                <h2 class="h4 title"><img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/icon-page.svg" class="icon" alt="Page Icon"> Pages</h2>
	            <?php endif; ?>
	            <?php if ($engine == 'post_search'): ?>
	                <h2 class="h4 title"><img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/icon-post.svg" class="icon" alt="Post Icon"> Posts</h2>
	            <?php endif; ?>
			</div>
		</div>

		<div class="wrapper">
			<div class="content">

	            <?php $i = 0; ?>

				<?php if ( ! empty( $query ) && isset( $swp_query ) && ! empty( $swp_query->posts ) ) : ?>

					<div class="card-list">

						<?php foreach ( $swp_query->posts as $post ) : ?>
	                        <?php $i++; ?>
							<?php setup_postdata( $post ); ?>

							<div class="card fill-white">
								<div class="col">
									<?php if (get_the_post_thumbnail_url()): ?>
										<div class="image">
											<?php 
												$thumbnail_id = get_post_thumbnail_id();
												$image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
											?>
											<img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php echo $image_alt; ?>"/>
										</div>
					                <?php else: ?>
										<div class="image">
											<img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/image-placeholder.jpg" alt="Missing Image Placeholder"/>
										</div>
					                <?php endif; ?>
									<a href="<?php the_permalink(); ?>" class="h6 title-link"><?php the_title(); ?></a>
									<?php echo custom_excerpt(30); ?>
								</div>

							</div>

						<?php endforeach; ?>
					</div>

					<?php wp_reset_postdata(); ?>

					<?php
					if ( $swp_query->max_num_pages > 1 ) : ?>
						<?php $arr_params = array( 'engine' => $engine, 's' => $_GET['s'] ); ?>
						<a href="<?php echo esc_url( add_query_arg( $arr_params ) ); ?>" class="btn more-results">More results</a>
					<?php else: ?>
						<!-- <p>No results found.</p> -->
					<?php endif; ?>

	            <?php else: ?>

	                <h3 class="h5 title">No results.</h3>

				<?php endif; ?>

			</div>
		</div>
	</section>
<?php endif; ?>
