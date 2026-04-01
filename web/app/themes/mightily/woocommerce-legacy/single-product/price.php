<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

?>

<?php if(!($product->is_type('grouped') && $product->get_price_html() == '$0' || $product->get_price_html() == '$0.00' || $product->get_price_html() == 'Free!')) : ?>
	<p class="<?php echo esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) );?>"><?php echo $product->get_price_html(); ?></p>
<?php endif; ?>
<?php if(get_field('discontinued')) : ?>
	<p class="h6 discontinued-label"><span class="fa fa-exclamation-triangle"></span> This product is discontinued.</p>
<?php endif; ?>
<?php if ($product->get_attribute( 'federal-quota-funds' ) == 'Available'): ?>
	<p class="fq-status">Federal Quota Eligible</p>
<?php endif; ?>
<?php if ($product->get_shipping_class() == 'free-matter-for-the-blind') : ?>
	<p class="fq-status">Eligible for Free Matter for the Blind Shipping</p>
<?php endif; ?>
