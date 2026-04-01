    <?php
        // Exclude children products from search. If a grouped product has children assigned, those children should not be returned
        // Get all grouped products
        // Get all children from grouped products
        // Pass array of all children to be excluded from the query

        $grouped_products_query = array(
            'post_type'  => 'product',
            'posts_per_page' => '-1',
            'tax_query' => array(
                array(
                    'taxonomy'  => 'product_type',
                    'field'     => 'name',
                    'terms'     => array('grouped')
                )
            ),
			'meta_query' => array(
				array(
					'key'     => 'grouped_product_override',
					'value'   => '0', // Or whatever value you're filtering by
					'compare' => '='
				)
			)
        );
        $grouped_products = get_posts($grouped_products_query);
        $child_products = [];
        foreach($grouped_products as $grouped_product){
            if($grouped_product->_children){
                //var_dump($grouped_product->_children);   
                $child_products = array_merge($child_products, $grouped_product->_children);
            }
        }
        $child_products = array_unique($child_products);
        $args = array(
            'post_type' => array('product', 'post', 'page', 'documents', 'event'),
            'post_status' => 'publish',
            'posts_per_page' => 24,
            'facetwp' => true,
            'post__not_in' => $child_products,
        );
        $query = new WP_Query( $args );
        $total_results = $query->found_posts;
    ?>
    <!-- <div class="loading-search">
        <div class="loading-wrapper">
            <p>Loading search <i class="fas fa-spinner fa-spin"></i></p>
        </div>
    </div> -->
    <div id="search-results-main" class="line-items">
        <h2 class="search-results-heading h6" aria-live="polite">
            <?php if($total_results == 0) : ?>
                No results found
            <?php elseif($total_results == 1) : ?>
                1 result found
            <?php else : ?>
                <?php echo $total_results; ?> results found
            <?php endif; ?>
        </h2>
        <?php if($query->have_posts()) : while($query->have_posts()) : $query->the_post(); ?>
            <?php $post_type = get_post_type(); ?>
            <?php if($post_type == 'product') : ?>
                <?php get_template_part('inc/search/search-item-product'); ?>
            <?php else : ?>
                <?php get_template_part('inc/search/search-item-other'); ?>
            <?php endif; ?>
            
        <?php endwhile; endif; ?>
    </div>
</div>
<?php echo facetwp_display( 'pager' ); ?>