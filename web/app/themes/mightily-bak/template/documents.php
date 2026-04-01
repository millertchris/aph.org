<?php

// Template name: Documents
get_header();

$args = array(  
    'post_type' => 'documents',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title', 
    'order' => 'ASC', 
);

$query = new WP_Query( $args ); 

?>
<div class="interior-page">
    <section class="layout basic-content wide" style="margin-bottom: 50px;">
        <div class="content">
            <div class="wrapper">
                <div class="col">
                    <h1 class="h2">Product Manuals and Downloads</h1>
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </section>  
    <section class="layout search-results search-document-type">
        <div class="wrapper">
            <div class="row">
                <div class="fieldset search-field">
                    <div class="search-field-wrapper">
                        <?php echo facetwp_display( 'facet', 'document_search' ); ?>
                    </div>
                </div>
                <div class="fieldset filter-by-content">
                    <div class="content-types">
                        <?php echo facetwp_display( 'facet', 'document_type' ); ?>
                    </div>
                </div>                
                <?php echo facetwp_display( 'facet', 'document_alpha' );?>
            </div>
            <div class="row">
                <ul class="document-list">
                <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                    <?php
                        $file = get_field('file');
                        $description = get_field('description');
                        $catalog_number = get_field('catalog_number');
                        $date = str_replace('-', '/', $file['date']); 
                        $date = strtotime($date); 
                        $extension = pathinfo($file['filename'], PATHINFO_EXTENSION);
                    ?>
                    <li class="document-item">
                        <figure>
                            <a href="<?php echo $file['url']; ?>" 
                            class="document-item-link" 
                            download="<?php echo $file['filename']; ?>"
                            type="<?php echo $file['mime_type']; ?>">
                                <?php the_title(); ?> (<?php echo strtoupper($extension); ?>)
                            </a>
                            <figcaption>
                                Size: <?php echo formatSizeUnits($file['filesize']); ?>,
                                Uploaded: <time datetime="<?php echo $file['date']; ?>"><?php echo date('M j, Y', $date); ?></time>
                                <?php if($catalog_number) : ?>
                                    <?php
                                        $product_link = false;
                                        $product_id = wc_get_product_id_by_sku($catalog_number);
                                        if($product_id != 0){
                                            $product_status = get_post_status($product_id);
                                            if($product_status == 'publish'){
                                                $product_link = get_permalink($product_id);
                                            }
                                        }
                                    ?>
                                    <?php if($product_link) : ?>
                                        <p class="document-item-catalog-no">Catalog Number: <a href="<?php echo $product_link; ?>"><?php echo $catalog_number; ?></a></p>
                                    <?php else : ?>
                                        <p class="document-item-catalog-no">Catalog Number: <?php echo $catalog_number; ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if($description) : ?>
                                    <?php echo $description; ?>
                                <?php endif; ?>                                
                            </figcaption>
                        </figure> 
                    </li>                   
                <?php endwhile; ?>
                </ul>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>