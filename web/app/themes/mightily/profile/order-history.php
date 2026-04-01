<?php
	// $current_user defined in parent file
	// wp_get_users_of_group()
	// wp_get_terms_for_user($user) = Get terms for a user and a taxonomy
	// wp_set_terms_for_user() = Save taxonomy terms for a specific user
	// wp_get_user_groups() = Get all user groups

	$my_orders = \APH\OrderHistory::get_my_orders();

	$eot_ooa_orders = \APH\OrderHistory::get_eot_ooa_orders();

	$teacher_orders = \APH\OrderHistory::get_teacher_orders();

?>

<div class="order-history">
<?php
// Change button text for Role - Teacher
 $userRole = get_current_user_role();
 $orderText = '';
 // echo $userRole;
 if ($userRole === 'role-teacher') {
	 $orderText = 'Request';
 } else {
	 $orderText = 'Order';
 }
 ?>
		<?php if($my_orders) : ?>

			<div class="layout list-of-items my-orders list-view">
				<div class="wrapper">
					<?php if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles) || in_array('teacher', $current_user->roles)) : ?>
						<p style="margin-bottom: 30px;">Certain purchases such as braille and large print books, device repairs, and digital downloads may not appear on your order dashboard. Please contact Customer Service for assistance with these purchases.</p>
					<?php endif; ?>
          			<h1 class="h2">My <?php echo $orderText; ?>s</h1>
					<div class="layout-options">
						<a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
						<a class="layout-button btn" href="#" data-view="grid">Grid View</a>
					</div>
					<div class="line-items">
						<?php
						$i = 1;
						foreach ($my_orders as $order):
							$x = $i + 1;

                            $quota = $order->get_meta('_fq_account_name');
                            if (empty($quota)) $quota = 'none';

							?>
 						<div id="order-<?php echo $i; ?>" class="item">
						 	<div class="item-detail fq-account">
								 <h1 class="item-name h3 label" tabindex="0">Quota Acct: <span class="item-span"> <?php echo $quota; ?></span></h1>
								 
							</div>
							<div class="item-number medium">
								<h2 class="item-name h3 label" tabindex="0"><?php echo $orderText; ?> Number: <?php echo $order->get_id(); ?></h2>
								<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="edit-item"><i class="fas fa-eye" aria-hidden="true"></i> View order</a>
							</div>
							<!-- <a href="#order-<?php echo $x; ?>" class="skip-to-item"><span>Skip to next order</span></a> -->
							<div class="item-detail"><p>Date: <span class="item-span"><?php echo $order->get_date_created()->format('m/d/Y'); ?></span></p></div>
							<div class="item-detail"><p>PO number: <span class="item-span"><?php echo $order->get_meta('PO Number'); ?></span></p></div>
							<div class="item-detail"><p>Status: <span class="item-span"><?php echo wc_get_order_status_name($order->get_status()); ?></span></p></div>
							<div class="item-detail"><p><?php echo $orderText; ?> Total: <span class="item-span total"><?php echo $order->get_formatted_order_total(); ?></span></p></div>
							<div class="item-detail"><p>Track Shipment: <span class="item-span">none</span></p></div>
                            <div class="item-number small">
								<a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="edit-item"><i class="fas fa-eye" aria-hidden="true"></i> View order</a>
							</div>
						</div>
						<?php
						$i++;
					 endforeach; ?>
					</div>
				</div>
			</div>

		<?php endif; ?>

		<?php if($eot_ooa_orders) : ?>

			<div class="layout list-of-items teacher-orders list-view">
				<div class="wrapper">
			<h1 class="h2">Other FQ Orders</h1>
					<div class="layout-options">
						<a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
						<a class="layout-button btn grid-view" href="#" data-view="grid">Grid View</a>

					</div>
					<div class="line-items">
						<?php
						$i = 1;
						usort($eot_ooa_orders, function($a, $b)
						{
							return strcmp($b->get_date_created(), $a->get_date_created());
						});
						foreach ($eot_ooa_orders as $order):
							$x = $i + 1; 
							$quota = $order->get_meta('_fq_account_name');
							if (empty($quota)) $quota = 'none';
							?>
						<div id="order-<?php echo $i; ?>" class="item">
							<div class="item-detail"><p>Quota Acct: <span class="item-span"><?php echo $quota; ?></span></p></div>
							<div class="item-number medium">
								<h1 class="h3 label item-name" tabindex="0"><?php echo $orderText; ?> Number: <?php echo $order->get_id(); ?></h1>
								<?php if($order->get_status() == 'pending') :?>
									<?php $params = array('wc_order_id' => base64_encode($order->get_id()), 'wc_order_method' => 'approve'); ?>
									<div class="order-links">
										<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a><span aria-hidden="true"> | </span>
										<a class="edit-item order-edit" href="<?php echo $order->get_edit_order_url(); ?>" onclick="window.open(this.href, 'mywin',
										'left=20,top=20,width=1024,height=640,toolbar=1,resizable=0'); return false;"><i class="fas fa-edit" aria-hidden="true"></i> Edit Order</a>
									</div>
								<?php else : ?>
									<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a>
								<?php endif; ?>
							</div>
							<!-- <a href="#order-<?php echo $x; ?>" class="skip-to-item"><span>Skip to next order</span></a> -->
							<div class="item-detail"><p>Date: <span class="item-span"><?php echo $order->get_date_created()->format('m/d/Y'); ?></span></p></div>
							<div class="item-detail"><p>PO number: <span class="item-span"><?php echo $order->get_meta('PO Number'); ?></span></p></div>
							<?php
								$user_info = get_userdata($order->get_customer_id());

							?>
							<div class="item-detail"><p>Name: <span class="item-span"><?php echo $user_info->first_name; ?> <?php echo $user_info->last_name; ?></span></p></div>
							<div class="item-detail"><p>Status: <span class="item-span"><?php echo wc_get_order_status_name($order->get_status()); ?></span></p></div>
							<div class="item-detail"><p><?php echo $orderText; ?> Total: <span class="item-span total"><?php echo $order->get_formatted_order_total(); ?></span></p></div>
							<div class="item-number small">
								<?php if($order->get_status() == 'pending') :?>
									<?php $params = array('wc_order_id' => base64_encode($order->get_id()), 'wc_order_method' => 'approve'); ?>
									<div class="order-links">
										<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a><span>|</span>
										<a class="edit-item order-edit" href="<?php echo $order->get_edit_order_url(); ?>" onclick="window.open(this.href, 'mywin',
										'left=20,top=20,width=1024,height=640,toolbar=1,resizable=0'); return false;"><i class="fas fa-edit" aria-hidden="true"></i> Edit Order</a>
									</div>
								<?php else : ?>
									<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a>
								<?php endif; ?>
							</div>
						</div>
						<?php
						$i++;
					endforeach; ?>
					</div>
				</div>
			</div>

		<?php endif; ?>

		<?php if($teacher_orders) : ?>

			<div class="layout list-of-items teacher-orders list-view">
				<div class="wrapper">
          <h1 class="h2">Teacher Orders</h1>
					<div class="layout-options">
						<a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
						<a class="layout-button btn grid-view" href="#" data-view="grid">Grid View</a>

					</div>
					<div class="line-items">
						<?php
						$i = 1;
						usort($teacher_orders, function($a, $b)
						{
							return strcmp($b->get_date_created(), $a->get_date_created());
						});
						foreach ($teacher_orders as $order):
							$x = $i + 1; 
							 $quota = $order->get_meta('_fq_account_name');
                            if (empty($quota)) $quota = 'none';
							?>
						<div id="order-<?php echo $i; ?>" class="item">
						 	<div class="item-detail"><p>Quota Acct: <span class="item-span"><?php echo $quota; ?></span></p></div>
							<div class="item-number medium">
								<h1 class="h3 label item-name" tabindex="0"><?php echo $orderText; ?> Number: <?php echo $order->get_id(); ?></h1>
								<?php if($order->get_status() == 'pending') :?>
									<?php $params = array('wc_order_id' => base64_encode($order->get_id()), 'wc_order_method' => 'approve'); ?>
									<div class="order-links">
										<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a><span aria-hidden="true"> | </span>
										<a class="edit-item order-edit" href="<?php echo $order->get_edit_order_url(); ?>" onclick="window.open(this.href, 'mywin',
										'left=20,top=20,width=1024,height=640,toolbar=1,resizable=0'); return false;"><i class="fas fa-edit" aria-hidden="true"></i> Edit Order</a>
									</div>
								<?php else : ?>
									<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a>
								<?php endif; ?>
							</div>
							<div class="item-detail"><p>Date: <span class="item-span"><?php echo $order->get_date_created()->format('m/d/Y'); ?></span></p></div>
							<div class="item-detail"><p>PO number: <span class="item-span"><?php echo $order->get_meta('PO Number'); ?></span></p></div>
							<?php
								$user_info = get_userdata($order->get_customer_id());

							 ?>
							<div class="item-detail"><p>Name: <span class="item-span"><?php echo $user_info->first_name; ?> <?php echo $user_info->last_name; ?></span></p></div>
							<div class="item-detail"><p>Status: <span class="item-span"><?php echo wc_get_order_status_name($order->get_status()); ?></span></p></div>
							<div class="item-detail"><p><?php echo $orderText; ?> Total: <span class="item-span total"><?php echo $order->get_formatted_order_total(); ?></span></p></div>
							<div class="item-number small">
								<?php if($order->get_status() == 'pending') :?>
									<?php $params = array('wc_order_id' => base64_encode($order->get_id()), 'wc_order_method' => 'approve'); ?>
									<div class="order-links">
										<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a><span>|</span>
										<a class="edit-item order-edit" href="<?php echo $order->get_edit_order_url(); ?>" onclick="window.open(this.href, 'mywin',
										'left=20,top=20,width=1024,height=640,toolbar=1,resizable=0'); return false;"><i class="fas fa-edit" aria-hidden="true"></i> Edit Order</a>
									</div>
								<?php else : ?>
									<a class="edit-item order-edit" href="<?php echo esc_url($order->get_view_order_url()); ?>"><i class="far fa-eye" aria-hidden="true"></i> View order</a>
								<?php endif; ?>
							</div>
						</div>
						<?php
						$i++;
					 endforeach; ?>
					</div>
				</div>
			</div>

		<?php endif; ?>
</div>
