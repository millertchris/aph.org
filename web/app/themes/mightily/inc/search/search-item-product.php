<?php
    global $product;
    $post_type = get_post_type();
    $catalog_no_label = 'Catalog Number';

    if($product->is_type('grouped') && !get_field('grouped_product_override', $product->get_id())){
        $sku = [];
        foreach($product->get_children() as $child_id){
            $child_product = wc_get_product($child_id);
            $sku[] = $child_product->get_sku();
        }
        if(count($sku) > 1){
            $catalog_no_label = 'Catalog Numbers';
        }
        if(count($sku) > 0){
            $sku = implode(', ', $sku);
        } else {
            $sku = false;
        }
        if($product->get_price_html() == '$0' || $product->get_price_html() == '$0.00' || $product->get_price_html() == 'Free!'){
            $price = false;
        } else {
            $price = $product->get_price_html();
        }
    } else {
        $sku = $product->get_sku();
        $price =  $product->get_price_html();
    }
    $fq = $product->get_attribute('federal-quota-funds');
    $is_purchasable = $product->is_purchasable();
    $is_in_stock = $product->is_in_stock();

    if($fq == 'Available') {
        $fq = 'Federal Quota Eligible';
    } else {
        $fq = 'Not Federal Quota Eligible';
    }

    $replacement_part = $product->get_attribute('replacement-part');
    $format = $product->get_attribute('format');
    $braille = $product->get_attribute('braille');
    $id = get_the_ID();
    $isbn13 = get_field('isbn_13', $id);

    // Quantity vars
    $step = 1;
    $min = 0;
    $max  = apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product );
    if($max <= 0){
        $max = 1;
    }
    // $product_quantity = sprintf( '<input type="number" name="cart[%s][qty]" step="%s" min="%s" max="%s" value="%s" size="4" title="' . _x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ) . '"'. "class='input-text qty text' maxlength='12' data-key='%s' data-query='%s'  />", $wishlist_item_key, $step, $min, $max, esc_attr( $product_quantity_value ), $wishlist_item_key, json_encode($quantity_query));
    $search_item_heading_level = (isset($GLOBALS['is_nested']) && $GLOBALS['is_nested']) ? 'h3' : 'h2';
?>
<div id="post-<?php the_ID(); ?>" class="item <?php echo $post_type; ?>">
    <?php
        if(get_field('discontinued', $id)){
            echo '<p><strong><span class="fa fa-exclamation-triangle"></span> This product is discontinued.</strong></p>';
        }    
    ?>
    <<?php echo $search_item_heading_level; ?> class="title h6">
        <a href="<?php the_permalink(); ?>">
            <?php echo get_the_title(); ?>
            <div class="item-search-thumb">
                <img src="<?php echo get_product_image_data($product, 'url'); ?>" alt="<?php echo get_product_image_data($product, 'alt'); ?>" />
            </div>
        </a>
    </<?php echo $search_item_heading_level; ?>>
    <?php if(get_field('subtitle', $id)) : ?>
        <p class="subtitle h6"><?php the_field('subtitle', $id); ?></p>
    <?php endif; ?>
    <?php if(get_field('variance', $id)) : ?>
        <p class="subtitle h6"><?php the_field('variance', $id); ?></p>
    <?php endif; ?>    
    <p><?php echo wp_trim_words(get_the_excerpt(), $num_words = 20, $more = null); ?></p>
    <div class="product-details">
        <?php if($price) : ?>
            <p class="product-price"><?php echo $price; ?></p>
        <?php endif; ?>
        <?php if($sku) : ?>
            <p class="product-sku"><span><?php echo $catalog_no_label; ?>:</span> <?php echo $sku; ?></p>
        <?php endif; ?>
        <?php if($format != '') : ?>
            <p class="product-format">Format: <?php echo $format; ?></p>
        <?php endif; ?>
        <?php if($braille != '') : ?>
            <p class="product-braille">Braille: <?php echo $braille; ?></p>
        <?php endif; ?>                
        <?php if($isbn13) : ?>
            <p class="product-isbn"><span>ISBN:</span> <?php echo $isbn13; ?></p>
        <?php endif; ?>
        <?php if($replacement_part != '') : ?>
            <p class="product-replacement-part"><?php echo $replacement_part; ?></p>
        <?php endif; ?>                         
        <p class="product-fq"><?php echo $fq; ?></p>
        <?php if($is_purchasable && $is_in_stock) : ?>
            <?php if(is_user_role('eot') || is_user_role('eot-assistant') || is_user_role('teacher')) : ?>
                <?php if($fq == 'Federal Quota Eligible') : ?>
                    <?php get_template_part( 'woocommerce/single-product/add-to-cart/simple'); ?>
                    <!-- <div class="quantity">
                        <?php // echo $product_quantity; ?>
                    </div>
                    <a class="btn white" href="/?add-to-cart=<?php echo $id; ?>">Add To Cart</a> -->
                <?php endif; ?>
            <?php else : ?>
                <?php get_template_part( 'woocommerce/single-product/add-to-cart/simple'); ?>
                <!-- <div class="quantity">
                    <?php // echo $product_quantity; ?>
                </div>                
                <a class="btn white" href="/?add-to-cart=<?php echo $id; ?>">Add To Cart</a> -->
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <p class="post-type"><?php echo $post_type; ?></p>
</div>