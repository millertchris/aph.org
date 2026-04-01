<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-attributes.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="line-items">
	<div class="item shop_attributes">
		<?php if ( $display_dimensions && $product->has_weight() ) : ?>
			<div class="item-detail"><p><?php esc_html_e( 'Weight:', 'woocommerce' ) ?> <span class="item-span"><?php echo esc_html( wc_format_weight( $product->get_weight() ) ); ?></span></p></div>
		<?php endif; ?>
		<?php if ( $display_dimensions && $product->has_dimensions() ) : ?>
			<div class="item-detail"><p><?php esc_html_e( 'Dimensions:', 'woocommerce' ) ?> <span class="item-span"><?php echo esc_html( wc_format_dimensions( $product->get_dimensions( false ) ) ); ?></span></p></div>
		<?php endif; ?>

		<?php foreach ( $attributes as $attribute ) : ?>
			<div class="item-detail">
				<p><?php echo wc_attribute_label( $attribute->get_name() ); ?>:
					<span class="item-span"><?php
						$values = array();

						if ( $attribute->is_taxonomy() ) {
							$attribute_taxonomy = $attribute->get_taxonomy_object();
							$attribute_values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );

							foreach ( $attribute_values as $attribute_value ) {
								$value_name = esc_html( $attribute_value->name );

								if ( $attribute_taxonomy->attribute_public ) {
									$values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute->get_name() ) ) . '" rel="tag">' . $value_name . '</a>';
								} else {
									$values[] = $value_name;
								}
							}

						} else {
								$values = $attribute->get_options();
								foreach ( $values as &$value ) {
									$value = make_clickable( esc_html( $value ) );
								}
							}

						echo apply_filters( 'woocommerce_attribute',  wptexturize( implode( ', ', $values ) ), $attribute, $values );?>
					</span>
				</p>
			</div>
		<?php endforeach; ?>

	</div>
</div>
