<?php
// adding post-type for blog-roll.php layout
if ($post_type == null) {
  $post_type = 'post';
}

 ?>

<div class="card-list <?php echo $post_type; ?>-items">

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

        <div class="card fill-white <?php echo $post_type; ?>-item">
            <div class="col">
                <?php if (get_the_post_thumbnail_url()): ?>
                    <div class="image" style="background-image: url( <?php the_post_thumbnail_url('large'); ?> );"></div>
                <?php else: ?>
                    <div class="image"></div>
                <?php endif; ?>

                    <?php //$thumbnail_id = get_post_thumbnail_id( get_the_id() ); ?>
                    <?php //echo wp_get_attachment_image( $thumbnail_id, 'medium' ); ?>
                <a href="<?php the_permalink(); ?>" class='h6 title-link'><?php the_title(); ?></a>
                <p><?php echo custom_excerpt(30); ?></p>
            </div>
        </div>

    <?php endwhile; ?>

</div>
<?php wp_reset_postdata(); ?>
