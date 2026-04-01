<?php
/**
 *
 * Weekly Order Review template
 *
 * The file is prone to modifications after plugin upgrade or alike; customizations are advised via hooks/filters
 *
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
// Create a timestamp for October 1, when the fq year restarts
$month = 10;
$day = 1;
$timestamp = mktime(0, 0, 0, $month, $day);
if ($timestamp > time()) {
	$timestamp = mktime(0, 0, 0, $month, $day, date('Y') - 1);
}
?>

<p>Thank  you for choosing APH. Here is an order summary to include your orders, OOA orders and orders by your TVI from
    <?php echo date("F j, Y", $timestamp); ?> to date.
</p>

<p>Please log in to process your open requests\orders: <a href="<?php echo get_home_url(); ?>/profile">View your profile</a></p>

<?php

// Query orders by user, placed after the fq start
$order_params = array(
	'user' => $user,
	'date_created' => '>='.$timestamp
);
$my_orders      = \APH\OrderHistory::get_my_orders($order_params);
$eot_ooa_orders = \APH\OrderHistory::get_eot_ooa_orders($order_params);
$teacher_orders = \APH\OrderHistory::get_teacher_orders($order_params);
// $all_pending_processing_orders will be an array of all processing and pending payment orders for all orders (my, eot, teacher). Loop through later and get out line items that are backordered
$all_pending_processing_orders = [];
$back_orders = [];
if($my_orders){
	echo '<p style="font-size: 24px;">My Orders</p>';
	$fq_groups = [];
	foreach($my_orders as $order){ 
		$fq_groups[$order->get_meta('_fq_account_name')][] = $order;
	}
	foreach($fq_groups as $fq_group => $orders){
		if($fq_group == ''){
			$fq_group = 'No FQ Account';
		}
		echo '<p style="font-weight: bold; margin-bottom: 5px;">'.$fq_group.'</p>';
		$order_statuses = [];
		foreach($orders as $order){
			$order_statuses[$order->get_status()][] = $order;
			if($order->get_status() == 'pending' || $order->get_status() == 'processing'){
				$all_pending_processing_orders[] = $order;
			}
		}
		$pending_count = (isset($order_statuses['pending'])) ? count($order_statuses['pending']) : 0;
		$processing_count = (isset($order_statuses['processing'])) ? count($order_statuses['processing']) : 0;
		$completed_count = (isset($order_statuses['completed'])) ? count($order_statuses['completed']) : 0;
		echo '<p style="margin-bottom: 30px;">';
		echo 'Pending Payment: ' . $pending_count . '<br />';
		echo 'Processing: ' . $processing_count . '<br />';
		echo 'Completed: ' . $completed_count;
		echo '</p>';
	}
	echo '<hr style="margin-bottom: 30px;"/>';
}
if($eot_ooa_orders){
	echo '<p style="font-size: 24px;">Other EOT Orders</p> ';
	foreach($eot_ooa_orders as $order){ 
		$fq_groups[$order->get_meta('_fq_account_name')][] = $order;
	}
	foreach($fq_groups as $fq_group => $orders){
		if($fq_group == ''){
			$fq_group = 'No FQ Account';
		}
		echo '<p style="font-weight: bold; margin-bottom: 5px;">'.$fq_group.'</p>';
		$order_statuses = [];
		foreach($orders as $order){
			$order_statuses[$order->get_status()][] = $order;
			if($order->get_status() == 'pending' || $order->get_status() == 'processing'){
				$all_pending_processing_orders[] = $order;
			}			
		}
		$pending_count = (isset($order_statuses['pending'])) ? count($order_statuses['pending']) : 0;
		$processing_count = (isset($order_statuses['processing'])) ? count($order_statuses['processing']) : 0;
		$completed_count = (isset($order_statuses['completed'])) ? count($order_statuses['completed']) : 0;
		echo '<p style="margin-bottom: 30px;">';
		echo 'Pending Payment: ' . $pending_count . '<br />';
		echo 'Processing: ' . $processing_count . '<br />';
		echo 'Completed: ' . $completed_count;                                
		echo '</p>';
	}
	echo '<hr style="margin-bottom: 30px;"/>';
}
if($teacher_orders){
	echo '<p style="font-size: 24px;">Teacher Orders</p> ';
	foreach($teacher_orders as $order){ 
		$fq_groups[$order->get_meta('_fq_account_name')][] = $order;
	}
	foreach($fq_groups as $fq_group => $orders){
		if($fq_group == ''){
			$fq_group = 'No FQ Account';
		}
		echo '<p style="font-weight: bold; margin-bottom: 5px;">'.$fq_group.'</p>';
		$order_statuses = [];
		foreach($orders as $order){
			$order_statuses[$order->get_status()][] = $order;
			if($order->get_status() == 'pending' || $order->get_status() == 'processing'){
				$all_pending_processing_orders[] = $order;
			}			
		}
		$pending_count = (isset($order_statuses['pending'])) ? count($order_statuses['pending']) : 0;
		$processing_count = (isset($order_statuses['processing'])) ? count($order_statuses['processing']) : 0;
		$completed_count = (isset($order_statuses['completed'])) ? count($order_statuses['completed']) : 0;
		echo '<p style="margin-bottom: 30px;">';
		echo 'Open Submitted Requests: ' . $pending_count . '<br />';
		echo 'Approved Requests: ' . $processing_count . '<br />';
		echo 'Completed Requests: ' . $completed_count;
		echo '</p>';                           
	}
	echo '<hr style="margin-bottom: 45px;"/>';
}

// Display backorder information as a separate section in the email
if(count($all_pending_processing_orders) > 0){
	foreach($all_pending_processing_orders as $order){
		$is_back_order = false;
		if(!$order->get_items()){
			return false;
		}
		foreach($order->get_items() as $order_item){
			if($order_item->get_meta('Backordered') && $order_item->get_meta('Backordered') == '1'){
				$is_back_order = true;
				break;
			}
		}
		if($is_back_order){
			$back_orders[] = $order;
		}
	}
}
if(count($back_orders) > 0){
	echo '<p style="font-size: 24px;">Back Orders</p>';
	echo '<p>Open Back Orders: ' . count($back_orders) . '</p>';
	echo '<hr style="margin-bottom: 45px;"/>';
}

?>

<?php
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );