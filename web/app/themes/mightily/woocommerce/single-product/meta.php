<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * We are stripping the hotlinks from the tags and removing the pages for product_tags.
 * APH-469
 * APH-505 - we are now completely removing all tags.
 *
 * @param $id
 * @param $sep (unused)
 * @param $before
 * @param $after
 *
 * @return string
 */



global $product;

?>
<div class="product_meta">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php echo wc_get_product_category_list( $product->get_id(), '', '<p class="meta-item posted_in product-category-list">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</p>' ); ?>

    <?php // echo aph_get_product_tag_list( $product->get_id(), '', '<p class="meta-item tagged_as" style="font-weight: 400;"><span class="tag_labels" style="font-weight: 600;">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . '</span> ', '</p>' ); ?>
    <?php // echo wc_get_product_tag_list( $product->get_id(), '', '<p class="meta-item tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'woocommerce' ) . ' ', '</p>' ); ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>

