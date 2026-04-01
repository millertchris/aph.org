<?php
    $post_type = get_post_type();
    $post_type_labels = array(
        'post' => 'Article',
        'page' => 'Web Page'
    );
?>
<div id="post-<?php the_ID(); ?>" class="item <?php echo $post_type; ?>">
    <?php if($post_type == 'documents') : ?>
        <h2 class="title h6">
            <a href="/manuals-downloads/?fwp_document_search=<?php the_title(); ?>">
                <?php echo get_the_title(); ?>
                <div class="item-search-thumb">
                    <img src="<?php echo get_image_data($post, 'url'); ?>" alt="<?php echo get_image_data($post, 'alt'); ?>" />
                </div>                
            </a>
        </h2>
    <?php else : ?>
        <h2 class="title h6">
            <a href="<?php the_permalink(); ?>">
                <?php echo get_the_title(); ?>
                <div class="item-search-thumb">
                    <img src="<?php echo get_image_data($post, 'url'); ?>" alt="<?php echo get_image_data($post, 'alt'); ?>" />
                </div>                 
            </a>
        </h2>
    <?php endif; ?>
    <?php if($post_type == 'page') : ?>
        <p><?php echo get_post_meta($id, '_yoast_wpseo_metadesc', true); ?></p>
    <?php else : ?>
        <p><?php echo wp_trim_words(get_the_excerpt(), $num_words = 20, $more = null); ?></p>
    <?php endif; ?>
    <p class="post-type"><?php echo (isset($post_type_labels[$post_type])) ? $post_type_labels[$post_type] : $post_type; ?></p>
</div>
