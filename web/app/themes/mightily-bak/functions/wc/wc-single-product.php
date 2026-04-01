<?php
//  If product is not FQ Eligible, do not show add to cart buttons 
function conditional_add_to_cart() {
	global $product;
	$fqProduct = $product->get_attribute( 'federal-quota-funds' );
	if( ( is_user_role('eot') || is_user_role('eot-assistant') || is_user_role('teacher') ) && ($fqProduct !== 'Available') ) {
		echo '<p class="italic bold">This product is not FQ Eligible</p>';

	} else{
		woocommerce_template_single_add_to_cart();
	}
}

// Change all existing products to default attribute: federal-quota-funds: 'Available'
function aph_add_default_attribute() {

	$target_products = array(
	     'post_type' => 'product',
	     'posts_per_page'=>-1
	);

	$my_query = new WP_Query( $target_products );

	if( $my_query->have_posts() ) {
	   while ($my_query->have_posts()) : $my_query->the_post();

	     $term_taxonomy_ids = wp_set_object_terms( get_the_ID(), 'Available', 'pa_federal-quota-funds', true );
	     $thedata = Array('pa_federal-quota-funds'=>Array(
	       'name'=>'pa_federal-quota-funds',
	       'value'=>'Available',
	       'is_visible' => '1',
	       'is_taxonomy' => '1'
	     ));
	     update_post_meta( get_the_ID(),'_product_attributes',$thedata);

	   endwhile;
	}

	wp_reset_query();
}


// Adding 'FQ Eligible' to Front End of Single Products when applicable
function aph_product_fq_eligible() {
	global $product;
	$isbn13 = get_field('isbn_13', $product->get_id());
	$pubDate = get_field('publication_date', $product->get_id());
	$authors = $product->get_attribute( 'authors' );
	$publishers = $product->get_attribute( 'publishers' );
	$format = $product->get_attribute('format');
	$braille = $product->get_attribute('braille');
	$replacement = 	$product->get_attribute('replacement-part');
	$date_discontinued = $product->get_attribute('date-discontinued');
	?>
		<div class="item-detail product-attributes">
			<?php if ($date_discontinued) : ?>
				<p><span>Date Discontinued: <?php echo $date_discontinued; ?></span></p>
			<?php endif; ?>		
			<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
				<p><span><?php esc_html_e( 'Catalog Number:', 'woocommerce' ); ?> <?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span></p>
			<?php endif; ?>
			<?php if ($pubDate) : ?>
				<p><span>Publication Date: <?php echo $pubDate; ?></span></p>
			<?php endif; ?>			
			<?php if ($publishers) : ?>
				<p><span>Publishers: <?php echo $publishers; ?></span></p>
			<?php endif; ?>						
			<?php if ($authors) : ?>
				<p><span>Authors: <?php echo $authors; ?></span></p>
			<?php endif; ?>
			<?php if ($isbn13) : ?>
				<p><span>ISBN: <?php echo $isbn13; ?></span></p>
			<?php endif; ?>			
			<?php if ($format) : ?>
				<p><span>Format: <?php echo $format; ?></span></p>
			<?php endif; ?>
			<?php if ($braille) : ?>
				<p><span>Braille: <?php echo $braille; ?></span></p>
			<?php endif; ?>
			<?php if ($replacement) : ?>
				<p><span><?php echo $replacement; ?></span></p>
			<?php endif; ?>			
		</div>
	<?php

}

// if EOT, OOA, or TVI allow them to only add FQ Eligible items to the shopping cart
function aph_validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations= '' ) {
  $product = wc_get_product( $product_id );
  $fqProduct = $product->get_attribute( 'federal-quota-funds' );

    // do your validation, if not met switch $passed to false
    if ( ( is_user_role('eot') || is_user_role('eot-assistant') || is_user_role('teacher') ) && ($fqProduct !== 'Available') ){
        $passed = false;
        wc_add_notice( __( 'This item is not FQ Eligible', 'textdomain' ), 'error' );
    }
    return $passed;

}

// Adding wrap to product image thumbnails
function aph_get_gallery_image_html( $attachment_id, $main_image = false ) {
	$flexslider        = (bool) apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) );
	$gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
	$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
	$image_size        = apply_filters( 'woocommerce_gallery_image_size', $flexslider || $main_image ? 'woocommerce_single' : $thumbnail_size );
	$full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
	$thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
	$full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
	$image             = wp_get_attachment_image(
		$attachment_id,
		$image_size,
		false,
		apply_filters(
			'woocommerce_gallery_image_html_attachment_image_params',
			array(
				'title'                   => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'data-src'                => esc_url( $full_src[0] ),
				'data-large_image'        => esc_url( $full_src[0] ),
				'data-large_image_width'  => esc_attr( $full_src[1] ),
				'data-large_image_height' => esc_attr( $full_src[2] ),
				'class'                   => esc_attr( $main_image ? 'wp-post-image' : '' ),
			),
			$attachment_id,
			$image_size,
			$main_image
		)
	);

	return '<div data-thumb="' . esc_url( $thumbnail_src[0] ) . '" class="woocommerce-product-gallery__image"><a href="' . esc_url( $full_src[0] ) . '"><div class="img-wrapper"><p class="h3">Click to enlarge</p></div>' . $image . '</a></div>';
}

// Adding product details below the add to cart and meta section
function product_details() {
  echo '<div class="product-content">';
  echo '<h1 class="h3">Product Description</h1>';
  the_content();
  echo '</div>';
}

// Adding product video below the product details
function product_videos(){
	$id = get_the_ID();
	if (have_rows('videos', $id)) :
		echo '<div class="product-videos">';
			echo '<h1 class="h3">Videos</h1>';
			echo '<div class="product-videos-slider">';
				$video_count = 0;
				while (have_rows('videos', $id)) : the_row(); $video_count++;
					$vimeo_video_id = get_sub_field('vimeo_video_id');
				?>
					<div style="padding:56.25% 0 0 0;position:relative;">
						<iframe class="vimeo-iframe vimeo-iframe-<?php echo $video_count; ?>" src="https://player.vimeo.com/video/<?php echo $vimeo_video_id; ?>?title=0&byline=0&portrait=0" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
					</div>				
				<?php endwhile;
			echo '</div>';
			echo '<div class="product-videos-slider-nav">';
				while (have_rows('videos', $id)) : the_row();
					$vimeo_video_id = get_sub_field('vimeo_video_id');
					$vimeo_data = file_get_contents('https://vimeo.com/api/oembed.json?url=https://vimeo.com/'.$vimeo_video_id);
					$vimeo_data = json_decode($vimeo_data);
					echo '<div><img src="' . $vimeo_data->thumbnail_url . '" alt="Alt here"/></div>';
				endwhile;
			echo '</div>';
			echo '<script src="https://player.vimeo.com/api/player.js"></script>';
		echo '</div>';
	endif;
}

function has_product_toc(){
	if(get_field('table_of_contents') && get_field('table_of_contents') != ''){
		return true;
	} else {
		return false;
	}
}
function product_toc() {
	if(get_field('table_of_contents') && get_field('table_of_contents') != ''){
		echo get_field('table_of_contents');
	}
}
function has_product_testimonials(){
	if(get_field('testimonials') && get_field('testimonials') != ''){
		return true;
	} else {
		return false;
	}
}
function product_testimonials() {
	if(get_field('testimonials') && get_field('testimonials') != ''){
		echo get_field('testimonials');
	}
}

function has_product_features(){
	$id = get_the_ID();
	if (have_rows('features', $id)){
		return true;
	} else {
		return false;
	}
}
function product_features() {
	$id = get_the_ID();
	if (have_rows('features', $id)):
		echo '<ul>';
		while (have_rows('features', $id)) : the_row();
			echo '<li>' . get_sub_field('feature') . '</li>';
		endwhile;
		echo '</ul>';
	endif;
}
function has_product_includes(){
	$id = get_the_ID();
	if (have_rows('includes', $id)){
		return true;
	} else {
		return false;
	}
}
function product_includes() {
	$id = get_the_ID();
	if (have_rows('includes', $id)):
		echo '<ul>';
		while (have_rows('includes', $id)) : the_row();
			echo '<li>' . get_sub_field('item') . '</li>';
		endwhile;
		echo '</ul>';
	endif;
}
function has_product_replacements(){
	$posts = get_field('replacement_parts');
	if($posts){
		return true;
	} else {
		return false;
	}
}
function product_replacements() {
	$posts = get_field('replacement_parts');
	if($posts): ?>
		<div class="replacement-parts">
			<?php foreach( $posts as $p ) : ?>
				<div class="search-document-type">
					<ul class="document-list">  			
					<?php if($p->post_status == 'publish') : $product = wc_get_product($p->ID);?>
						<li class="document-item">
							<figure>
								<a href="<?php echo get_permalink( $p->ID ); ?>" class="document-item-link">
									<?php echo get_the_title( $p->ID ); ?>
									<?php echo (get_field('subtitle', $p->ID)) ? ' - '.get_field('subtitle', $p->ID) : ''; ?>
								</a>
								<figcaption>
									Catalog Number: <?php echo $product->get_sku(); ?>					                                
								</figcaption>
							</figure> 
						</li>                   				
					<?php endif; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif;
}

// function product_ordering() {
//     echo '<p>Pending information</p>';
// }
function product_support() {
  echo '<p class="h6">Customer Service and Technical Support</p>';
  echo '<p>'.do_shortcode('[customer_service]').'</p>';
}
function has_product_downloads(){
	$downloads = get_field('downloads');
	if($downloads){
		return true;
	} else {
		return false;
	}
}
function product_downloads() {
	$downloads = get_field('downloads');
	if($downloads) : ?>
		<div class="search-document-type">
			<ul class="document-list">
			<?php foreach($downloads as $download) : ?>
				<?php
					$file = get_field('file', $download->ID);
					$description = get_field('description', $download->ID);
					$catalog_number = get_field('catalog_number', $download->ID);
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
							<?php echo get_the_title($download->ID); ?> (<?php echo strtoupper($extension); ?>)
						</a>
						<figcaption>
							Size: <?php echo formatSizeUnits($file['filesize']); ?>,
							Uploaded: <time datetime="<?php echo $file['date']; ?>"><?php echo date('M j, Y', $date); ?></time>
							<?php if($catalog_number) : ?>
								<p class="document-item-catalog-no">Catalog Number: <?php echo $catalog_number; ?></p>
							<?php endif; ?>
							<?php if($description) : ?>
								<?php echo $description; ?>
							<?php endif; ?>                                
						</figcaption>
					</figure> 
				</li>                   
			<?php endforeach; ?>
			</ul>
		</div>
	<?php endif;
}
function has_product_manuals(){
	$manuals = get_field('manuals');
	if($manuals){
		return true;
	} else {
		return false;
	}
}
function product_manuals() {
	$manuals = get_field('manuals');
	if($manuals) { ?>
		<div class="search-document-type">
			<ul class="document-list">
			<?php foreach($manuals as $manual) : ?>
				<?php
					$file = get_field('file', $manual->ID);
					$description = get_field('description', $manual->ID);
					$catalog_number = get_field('catalog_number', $manual->ID);
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
							<?php echo get_the_title($manual->ID); ?> (<?php echo strtoupper($extension); ?>)
						</a>
						<figcaption>
							Size: <?php echo formatSizeUnits($file['filesize']); ?>,
							Uploaded: <time datetime="<?php echo $file['date']; ?>"><?php echo date('M j, Y', $date); ?></time>
							<?php if($catalog_number) : ?>
								<p class="document-item-catalog-no">Catalog Number: <?php echo $catalog_number; ?></p>
							<?php endif; ?>
							<?php if($description) : ?>
								<?php echo $description; ?>
							<?php endif; ?>                                
						</figcaption>
					</figure> 
				</li>                   
			<?php endforeach; ?>
			</ul>
		</div><?php
	} else {
		return false;
	}
}
function has_product_faqs(){
	if(get_field('faqs') && get_field('faqs') != ''){
		return true;
	} else {
		return false;
	}
}
function product_faqs() {
	if(get_field('faqs') && get_field('faqs') != ''){
		echo get_field('faqs');
	}
}
function has_product_warranty(){
	return true;
}
function product_warranty() {
	if(get_field('warranty') && get_field('warranty') != ''){
		echo get_field('warranty');
	} else {
		echo get_field('default_warranty_information', 'option');
	}
}

// Adding additional tabs
function woo_new_product_tab($tabs) {
	global $product;
	if(has_product_toc()){
		$tabs['toc'] = [
			'title'     => __('Table of Contents', 'woocommerce'),
			'priority'  => 1,
			'callback'  => 'product_toc'
		];
	}
	if(has_product_testimonials()){
		$tabs['testimonials'] = [
			'title'     => __('Testimonials', 'woocommerce'),
			'priority'  => 2,
			'callback'  => 'product_testimonials'
		];
	}	
	if(has_product_features()){
		$tabs['features'] = [
			'title'     => __('Features', 'woocommerce'),
			'priority'  => 3,
			'callback'  => 'product_features'
		];
	}	
	if(has_product_includes()){
		$tabs['includes'] = [
			'title'     => __('Includes', 'woocommerce'),
			'priority'  => 4,
			'callback'  => 'product_includes'
		];
	}
	// $tabs['ordering'] = array(
	//     'title'     => __( 'Ordering', 'woocommerce' ),
	//     'priority'  => 3,
	//     'callback'  => 'product_ordering'
	// );
	if(has_product_replacements()){
		$tabs['replacements'] = [
			'title'     => __('Optional and Replacement Items', 'woocommerce'),
			'priority'  => 5,
			'callback'  => 'product_replacements'
		];
	}
	$tabs['support'] = [
		'title'     => __('Support', 'woocommerce'),
		'priority'  => 6,
		'callback'  => 'product_support'
	];
	if(has_product_downloads()){
		$tabs['downloads'] = [
			'title'     => __('Downloads', 'woocommerce'),
			'priority'  => 7,
			'callback'  => 'product_downloads'
		];
	}
	if(has_product_manuals()){
		$tabs['manuals'] = [
			'title'     => __('Manuals', 'woocommerce'),
			'priority'  => 8,
			'callback'  => 'product_manuals'
		];
	}
	if(has_product_faqs()){
		$tabs['faqs'] = [
			'title'     => __('FAQs', 'woocommerce'),
			'priority'  => 9,
			'callback'  => 'product_faqs'
		];
	}	
	if(has_product_warranty()){
		$tabs['warranty'] = [
			'title'     => __('Warranty', 'woocommerce'),
			'priority'  => 10,
			'callback'  => 'product_warranty'
		]; 
	}	
	// Remove tabs whos callbacks return false
	// if(!product_manuals()){
	// 	unset($tabs['manuals']); 
	// }	

	unset($tabs['description']);      	    // Remove the description tab
	unset($tabs['reviews']); 			        // Remove the reviews tab
	// unset( $tabs['additional_information'] );  	// Remove the additional information tab
	if( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) { // Check if product has attributes, dimensions or weight
		$tabs['additional_information']['title'] = __('Specs');	// Rename the additional information tab
		$tabs['additional_information']['priority'] = 3;	// Additional information third
	} else {
		unset($tabs['additional_information']);
	}

	return $tabs;
}

// Add 'federal-quota-funds: Available' as default attribute for new products
//
// function on_all_status_transitions( $new_status, $old_status, $post ) {
// 	global $post;
//
// 	if ( $post->post_type !== 'product' ) return;
//
// 	if ( 'publish' !== $new_status or 'publish' === $old_status ) return;
// 	if ($new_status === 'publish') {
// 		$term_taxonomy_ids = wp_set_object_terms( get_the_ID(), 'Available', 'pa_federal-quota-funds', true );
// 		$thedata = Array('pa_federal-quota-funds'=>Array(
// 			'name'=>'pa_federal-quota-funds',
// 			'value'=>'Available',
// 			'is_visible' => '1',
// 			'is_taxonomy' => '1'
// 		));
// 		update_post_meta( get_the_ID(),'_product_attributes',$thedata);
// 		var_dump($post);
//
// 	}
// }
