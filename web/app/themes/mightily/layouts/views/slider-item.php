<?php if($posts) : ?>
    <div class="slide-list <?php echo $post_type; ?>-items">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <div class="card fill-white slide <?php echo $post_type; ?>-item">
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
                    <?php the_excerpt(); ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
   <?php wp_reset_postdata(); ?>
<?php endif; ?>
