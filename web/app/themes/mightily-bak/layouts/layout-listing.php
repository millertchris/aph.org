<?php
    $post_type = get_sub_field('post_type');
    $post_ids = get_sub_field('post_selection');
    $posts_to_show = get_sub_field('posts_to_show');
    $number_of_posts = get_sub_field('number_of_posts');
    $order = get_sub_field('order_by');
    $view = get_sub_field('view');
    $margin_bottom = get_sub_field('layout_spacing');
    $search = get_sub_field('search');
    $args = 'no args';

    // creating css classes to inject into the layout div
    $classes = $post_type . ' ' . $view;
    if ($view == 'grid') {
        $classes .= ' cards medium';
    }
    if ($view == 'slider') {
        $classes .= ' grid cards medium';
    }

    // setting the posts to show based on a users selection
    if ($posts_to_show == 'all') {
        $posts_per_page = -1;
    } else {
        $posts_per_page = $number_of_posts;
    }

    // when a taxonomy term is selected, retrieve both the taxonomy name and the term id
    $term_obj = get_sub_field($post_type . '_taxonomy');
    if (($term_obj) && ($term_obj->name !== 'EOT')) {
        $args = array(
            'post_type'			=> $post_type,
            'posts_per_page'	=> $posts_per_page,
            'order'             => $order,
            'tax_query' => array(
                array(
                    'taxonomy' => $term_obj->taxonomy,
                    'terms'    => $term_obj->term_id,
                ),
            ),
        );
        $the_query = new WP_Query($args);
    }

    // special query for Trustee Directory

    if (($term_obj) && (
        $term_obj->name == 'EOT' ||
        $term_obj->name == 'NC EOT' ||
        $term_obj->name == 'NE EOT' ||
        $term_obj->name == 'NW EOT' ||
        $term_obj->name == 'SC EOT' ||
        $term_obj->name == 'SE EOT' ||
        $term_obj->name == 'SW EOT'
    )) {
        $args = array(
            'post_type'			=> $post_type,
            'posts_per_page'	=> $posts_per_page,
            'order'             => $order,
            'tax_query' => array(
                array(
                    'taxonomy' => $term_obj->taxonomy,
                    'terms'    => $term_obj->term_id,
                ),
            ),
            'orderby'          => 'meta_value',
            'meta_key'           => 'state_represented',

        );
        $the_query = new WP_Query($args);
    }



    // if the post type is custom, return ids and pass through the query
    if ($post_type == 'custom') {
        // $ids = get_field('post_selection', false, false);
        // $ids = get_sub_field('post_selection');
        $args = array(
            'post_type'		 => 'any',
            'posts_per_page' => -1,
            'orderby'        => 'none',
            'post__in'       => $post_ids,
        );

        $the_query = new WP_Query($args);

        $ordered_posts = array();
        foreach ($post_ids as $rpid) {
            foreach ($the_query->posts as $index => $fpid) {
                if ($fpid->ID === $rpid) {
                    $ordered_posts[] = $the_query->posts[$index];
                }
            }
        }

        $the_query->posts = $ordered_posts;
    }

    // var_dump($the_query);

?>

<section class="layout listing <?php echo $classes; ?>" style="margin-bottom: <?php echo $margin_bottom; ?>px;">

	<?php include(locate_template('layouts/component-intro.php')); ?>

    <div class="wrapper">

      <?php if($view !== 'slider') : ?>
        <div class="content">
      <?php endif; ?>

			<?php if (isset($the_query) && $the_query->have_posts()) : ?>

            	<?php include(locate_template('layouts/views/'.$view.'-item.php')); ?>

			<?php else: ?>

				<h2 style="text-align: center; color: red; background: black;">ERROR </br> You must select a category. Please check your settings to make sure they are correct.</h2>

			<?php endif; ?>

			<?php include(locate_template('layouts/component-button.php')); ?>
      <?php if($view !== 'slider') : ?>
        </div>
      <?php endif; ?>
    </div>
</section>
