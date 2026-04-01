<?php if($posts) : ?>
    <div class="slide-list <?php echo $post_type; ?>-items">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <div class="card fill-white slide <?php echo $post_type; ?>-item">
                <div class="col">
                    <?php if (get_the_post_thumbnail_url()): ?>
                        <div class="image" style="background-image: url( <?php the_post_thumbnail_url('medium'); ?> );"></div>
                    <?php else: ?>
                        <div class="image"></div>
                    <?php endif; ?>
                    <a href="<?php the_permalink(); ?>" class="h6 title-link"><?php the_title(); ?></a>
                    <?php the_excerpt(); ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
   <?php wp_reset_postdata(); ?>
<?php endif; ?>
