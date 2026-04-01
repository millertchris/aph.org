<?php get_header(); ?>

	<div class="interior-page">
		<div class="wrapper">
			<h1><?php single_cat_title(); ?></h1>
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<article>
					<h2 class="h3"><?php the_title(); ?></h2>
					<p><?php echo custom_excerpt(30); ?></p>
					<a href="<?php the_permalink(); ?>" class="btn black">Read more</a>
				</article>
			<?php endwhile; endif; ?>
			<div class="pagination">
				<?php
					echo paginate_links( array(
						'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
						// 'total'        => $the_query->max_num_pages,
						'current'      => max( 1, get_query_var( 'paged' ) ),
						'format'       => '?paged=%#%',
						'show_all'     => false,
						'type'         => 'list',
						'end_size'     => 2,
						'mid_size'     => 1,
						'prev_next'    => true,
						'prev_text'    => sprintf(__('Newer Posts', 'text-domain')),
						'next_text'    => sprintf(__('Older Posts', 'text-domain')),
						'add_args'     => false,
						'add_fragment' => '',
					) );
				?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>
