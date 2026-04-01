<?php
// adding post-type for blog-roll.php layout
if ($post_type == null) {
  $post_type = 'post';
}
if(isset($listing_heading_tag)){
    $grid_item_heading_tag = $listing_heading_tag;
} else {
    $grid_item_heading_tag = 'h3';
}
// if these grid items are used on blog roll, the headings should be redone
if(isset($blog_roll_heading_tag)){
    if($blog_roll_heading_tag == 'h1') {
        $grid_item_heading_tag = 'h2';
    } elseif($blog_roll_heading_tag == 'h2') {
        $grid_item_heading_tag = 'h2';
    } elseif($blog_roll_heading_tag == 'h3') {
        $grid_item_heading_tag = 'h3';
    }
}

?>

<div class="card-list <?php echo $post_type; ?>-items">

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

        <div class="card fill-white <?php echo $post_type; ?>-item">
            <div class="col">
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

                    <?php //$thumbnail_id = get_post_thumbnail_id( get_the_id() ); ?>
                    <?php //echo wp_get_attachment_image( $thumbnail_id, 'medium' ); ?>
                <<?php echo $grid_item_heading_tag; ?> class="h6 title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo $grid_item_heading_tag; ?>>
                <p><?php echo custom_excerpt(30); ?></p>
            </div>
        </div>

    <?php endwhile; ?>

</div>
<?php wp_reset_postdata(); ?>
