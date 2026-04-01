<?php

/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.7.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders);
// Change button text for Role - Teacher
$userRole = get_current_user_role();
$orderText = '';
// echo $userRole;
if ($userRole === 'role-teacher') {
	$orderText = 'Request';
} else {
	$orderText = 'Order';
}


if ($has_orders) : ?>

	<div class="layout list-of-items list-view">
		<div class="wrapper">
			<div class="layout-options">
				<a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
				<a class="layout-button btn grid-view" href="#" data-view="grid">Grid View</a>
			</div>
			<div class="line-items">
				<?php
				$i = 1;
				// // order by date.
				// $args = array(
				//     'orderby' => 'date',
				// 		'order' => 'ASC',
				// );
				// $customer_orders = wc_get_orders( $args );
				foreach ($customer_orders->orders as $customer_order) :
					$order      = wc_get_order($customer_order);
					$item_count = $order->get_item_count();
					$x = $i + 1;
				?>
					<div id="order-<?php echo $i; ?>" class="item">
						<div class="item-number small">
							<h1 class="h3 label item-name" tabindex="0"><?php echo $orderText; ?> <?php echo _x('#', 'hash before order number', 'woocommerce') . $order->get_order_number(); ?></h1>
							<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="edit-item"><i class="fas fa-eye" aria-hidden="true"></i> View order</a>
						</div>
						<div class="item-detail">
							<p>Date: <span class="item-span"><?php echo $order->get_date_created()->format('m/d/Y'); ?></span></p>
						</div>
						<div class="item-detail">
							<p>Quantity: <span class="item-span"><?php echo $order->get_item_count(); ?></span></p>
						</div>

						<div class="item-detail">
							<p>Status: <span class="item-span"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></p>
						</div>
						<div class="item-detail">
							<p><?php echo $orderText; ?> Total: <span class="item-span">$<?php echo $order->get_total(); ?></span></p>
						</div>
						<div class="item-number small"><a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="edit-item"><i class="fas fa-eye" aria-hidden="true"></i> View order</a></div>
					</div>
				<?php
					$i++;
				endforeach; ?>
			</div>
		</div>
	</div>

	<?php do_action('woocommerce_before_account_orders_pagination'); ?>

	<?php if (1 < $customer_orders->max_num_pages) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if (1 !== $current_page) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>"><?php esc_html_e('Previous', 'woocommerce'); ?></a>
			<?php endif; ?>

			<?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>"><?php esc_html_e('Next', 'woocommerce'); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>
	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>"><?php esc_html_e('Go shop', 'woocommerce') ?></a>
		<?php esc_html_e('No order has been made yet.', 'woocommerce'); ?>
	</div>
<?php endif; ?>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>