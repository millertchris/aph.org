<?php
    $margin_bottom = get_sub_field('layout_spacing');
?>
<section class="layout blog-roll" style="margin-bottom:  <?php echo $margin_bottom; ?>px;">
    <?php include(locate_template('layouts/component-intro.php')); ?>
    <?php
        if($layout_counter == 1){
            if($display_intro) {
                $blog_roll_heading_tag = 'h2';
            } else {
                $blog_roll_heading_tag = 'h1';
            }
        } else {
            if($display_intro) {
                $blog_roll_heading_tag = 'h3';
            } else {
                $blog_roll_heading_tag = 'h2';
            }                  
        }
    ?>
	<div class="wrapper">
        <div class="row">

            <div class="col">
                <aside class="side-bar">
                    <ul class="categories blog">
                        <?php
                            $args = array(
                                'hide_empty' => 0,
                                'show_count' => 0,
                                'title_li'   => '',
                            );
                        ?>
                        <?php wp_list_categories($args); ?>
                    </ul>
                </aside>
            </div>

            <?php
                $args = array(
                    // 'post_type'			=> '',
                    'posts_per_page'	=> 1,
                    // 'orderby'           => $order_by,
                );

                $the_query = new WP_Query($args);
            ?>

            <div class="col">
                <?php if ($the_query->have_posts()) : while ($the_query->have_posts()) : $the_query->the_post(); ?>

                <div class="featured-article">
                    <?php if (get_the_post_thumbnail_url()): ?>
                        <div class="image">
                            <?php 
                                $thumbnail_id = get_post_thumbnail_id();
                                $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                            ?>
                            <img src="<?php the_post_thumbnail_url('large'); ?>" alt="<?php echo $image_alt; ?>"/>
                        </div>
                    <?php else: ?>
                        <div class="image">
                            <img src="<?php echo get_template_directory_uri(); ?>/app/assets/img/image-placeholder.jpg" alt="Missing Image Placeholder"/>
                        </div>
                    <?php endif; ?>
                    <<?php echo $blog_roll_heading_tag; ?>><?php the_title(); ?></<?php echo $blog_roll_heading_tag; ?>>
                    <p><?php echo custom_excerpt(160); ?></p>
                    <a href="<?php the_permalink(); ?>" class="btn black">Read Article</a>
                </div>

                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <section class="layout cards medium">
        <div class="wrapper">
                <?php
                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                    $args = array(
                        'posts_per_page'	=> 6,
                        'paged' => $paged
                    );

                    $the_query = new WP_Query($args);
                ?>

                <?php if ($the_query->have_posts()) : ?>


                	<?php include(locate_template('layouts/views/grid-item.php')); ?>

                    <div class="pagination">
                        <?php
                            echo paginate_links(array(
                                'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                                'total'        => $the_query->max_num_pages,
                                'current'      => max(1, get_query_var('paged')),
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
                            ));
                        ?>
                    </div>

    			<?php endif; ?>
        </div>
    </section>
</section>
<?php $blog_roll_heading_tag = false; ?>
