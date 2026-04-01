<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $tabs ) ) : ?>

	<div class="accordion-wrapper">
		<ul data-accordion class="bx--accordion tabs wc-tabs">
			<?php $i = 1; ?>
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<li data-accordion-item class="bx--accordion__item tab">
					<button class="bx--accordion__heading h4" aria-expanded="false" aria-controls="pane<?php echo $i; ?>">
						<h1 class="bx--accordion__title"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key ); ?></h1>
					</button>
					<div id="pane<?php echo $i; ?>" class="bx--accordion__content">
						<div class="bx--accordion__content-wrapper">
							<?php if ( isset( $tab['callback'] ) ) { call_user_func( $tab['callback'], $key, $tab ); } ?>
						</div>
					</div>
				</li>
			<?php $i++; endforeach; ?>
		</ul>
	</div>

<?php endif; ?>
