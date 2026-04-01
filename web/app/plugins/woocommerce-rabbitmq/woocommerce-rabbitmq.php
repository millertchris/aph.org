<?php

/**
 * @package Woocommerce Rabbitmq
 */
/*
  Plugin Name: Woocommerce Rabbitmq
  Description: This is Woocommerce Rabbitmq plugin. This will use to push messages in rabbitmq at order creation and cancellation.
  Version: 1.0
  Author: Vivek Bansal
  Author URI: http://www.quorumresources.com
  Text Domain: Woocommerce Rabbitmq
 */

require_once('vendor/autoload.php');

include_once('admin-settings.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

add_action('woocommerce_order_status_changed', 'get_woocommerce_order_status', 99, 3);
add_action('profile_update', 'get_account_update_information', 10, 2);
// add_action('woocommerce_thankyou', 'get_woocommerce_order_add', 10, 1);
//add_action( 'delete_user', 'get_account_delete_information' );
add_action('user_register', 'get_account_register_information', 10, 1);

/**
 * Function to push message in rabbitmq when profile deleted
 *
 * @name   get_account_delete_information
 * @author Vivek Bansal
 */
function get_account_delete_information($user_id) {
	try {
		$options = get_option('woocommerce_rabbitmq_settings');
		if (!empty($options)) {
			$logger = wc_get_logger();
			$user_meta = get_userdata($user_id);
			$user_roles = $user_meta->roles;

			if (in_array("eot", $user_roles) || in_array("eot-assistant", $user_roles) || in_array("teacher", $user_roles)) {
				$connection = new AMQPStreamConnection($options['woocommerce_rabbitmq_host_name'], $options['woocommerce_rabbitmq_port_numer'], $options['woocommerce_rabbitmq_user_name'], $options['woocommerce_rabbitmq_password']);
				$channel = $connection->channel();
				$channel->queue_declare($options['woocommerce_rabbitmq_salesforce_queue_name'], true, false, false, null);
				$msg = new AMQPMessage('account_deleted,' . $user_id);
				$channel->basic_publish($msg, '', $options['woocommerce_rabbitmq_salesforce_queue_name']);
				$channel->close();
				$connection->close();
				$logger->debug('account_deleted message pushed in ' . $options['woocommerce_rabbitmq_salesforce_queue_name'] . '.' . $user_id, array('source' => 'fatal-errors'));
			}
		}
	} catch (Exception $e) {
		if (did_action('profile_update') === 1) {
			$logger = wc_get_logger();
			$logger->debug('account_deleted error message =>' . $e->getMessage(), array('source' => 'fatal-errors'));
			$logger->debug('account deleted message for userid ' . $user_id . ' pushed into queue', array('source' => 'fatal-errors'));
		}
	}
}

/**
 * Function to push message in rabbitmq when profile created
 *
 * @name   get_account_register_information
 * @author Vivek Bansal
 */
function get_account_register_information($user_id) {
	try {
		$options = get_option('woocommerce_rabbitmq_settings');
		if (!empty($options)) {
			$logger = wc_get_logger();
			$user_meta = get_userdata($user_id);
			$user_roles = $user_meta->roles;

			if (in_array("eot", $user_roles) || in_array("eot-assistant", $user_roles) || in_array("teacher", $user_roles) || in_array("former_eot", $user_roles) || in_array("former_ooa", $user_roles)) {
				$connection = new AMQPStreamConnection($options['woocommerce_rabbitmq_host_name'], $options['woocommerce_rabbitmq_port_numer'], $options['woocommerce_rabbitmq_user_name'], $options['woocommerce_rabbitmq_password']);
				$channel = $connection->channel();
				$channel->queue_declare($options['woocommerce_rabbitmq_salesforce_queue_name'], true, false, false, null);
				$msg = new AMQPMessage('account_created,' . $user_id);
				$channel->basic_publish($msg, '', $options['woocommerce_rabbitmq_salesforce_queue_name']);
				$channel->close();
				$connection->close();
				$logger->debug('user register successfully message pushed into queue ' . $options['woocommerce_rabbitmq_salesforce_queue_name'] . '.' . $user_id, array('source' => 'fatal-errors'));
			}
		}
	} catch (Exception $e) {
		$logger = wc_get_logger();
		$logger->debug('account_created error message =>' . $e->getMessage(), array('source' => 'fatal-errors'));
		$logger->debug('account_created message not pushed into queue', array('source' => 'fatal-errors'));
	}
}

/**
 * Function to push message in rabbitmq when profile updated
 *
 * @name   get_account_update_information
 * @author Vivek Bansal
 */
function get_account_update_information($user_id) {
	try {
		$options = get_option('woocommerce_rabbitmq_settings');
		if (!empty($options)) {
			$logger = wc_get_logger();
			if (did_action('profile_update') === 1) {
				$connection = new AMQPStreamConnection($options['woocommerce_rabbitmq_host_name'], $options['woocommerce_rabbitmq_port_numer'], $options['woocommerce_rabbitmq_user_name'], $options['woocommerce_rabbitmq_password']);
				$channel = $connection->channel();
				$user_meta = get_userdata($user_id);
				$user_roles = $user_meta->roles;
				$channel->queue_declare($options['woocommerce_rabbitmq_queue_name'], true, false, false, null);
				$msg = new AMQPMessage('account_update,' . $user_id);
				$channel->basic_publish($msg, '', $options['woocommerce_rabbitmq_queue_name']);
				// for salesforce rabbitmq queue
				if (in_array("eot", $user_roles) || in_array("former_eot", $user_roles) || in_array("former_ooa", $user_roles) || in_array("eot-assistant", $user_roles) || in_array("teacher", $user_roles)) {
					$channel->queue_declare($options['woocommerce_rabbitmq_salesforce_queue_name'], true, false, false, null);
					$msg = new AMQPMessage('account_update,' . $user_id);
					$channel->basic_publish($msg, '', $options['woocommerce_rabbitmq_salesforce_queue_name']);
					$logger->debug('account_update message pushed in ' . $options['woocommerce_rabbitmq_salesforce_queue_name'] . '.' . $user_id, array('source' => 'fatal-errors'));
				}

				$channel->close();
				$connection->close();
				$logger->debug('account_update message pushed.' . $user_id, array('source' => 'fatal-errors'));
			}
		}
	} catch (Exception $e) {
		if (did_action('profile_update') === 1) {
			$logger = wc_get_logger();
			$logger->debug('account_update error message =>' . $e->getMessage(), array('source' => 'fatal-errors'));
			$logger->debug('account update message for userid ' . $user_id . ' not pushed into queue', array('source' => 'fatal-errors'));
		}
	}
}

/**
 * Function to get the order status, invoke when order status changed
 *
 * @name   get_woocommerce_order_status
 * @author Vivek Bansal
 */
function get_woocommerce_order_status($order_id, $old_status, $new_status) {
	try {

		$options = get_option('woocommerce_rabbitmq_settings');
		if (!empty($options)) {
			$logger = wc_get_logger();
			$connection = new AMQPStreamConnection($options['woocommerce_rabbitmq_host_name'], $options['woocommerce_rabbitmq_port_numer'], $options['woocommerce_rabbitmq_user_name'], $options['woocommerce_rabbitmq_password']);
			$channel = $connection->channel();
			$channel->queue_declare($options['woocommerce_rabbitmq_queue_name'], true, false, false, null);
			$user = wp_get_current_user();
			$roles = $user->roles;

			if ((strcasecmp($roles[0], 'eot') == 0 || strcasecmp($roles[0], 'administrator') == 0 || strcasecmp($roles[0], 'copy_of_csr') == 0 || strcasecmp($roles[0], 'eot-assistant') == 0 || strcasecmp($roles[0], 'customer') == 0 || empty($roles[0]))  && strcasecmp($new_status, 'processing') == 0) {
				$msg = new AMQPMessage('createOrder,' . $order_id);
				$channel->basic_publish($msg, '', $options['woocommerce_rabbitmq_queue_name']);
				$logger->debug('createOrder -- message pushed orderid:- ' . $order_id . ' role:- ' . $roles[0], array('source' => 'fatal-errors'));
			}
			$channel->close();
			$connection->close();

			if ((strcasecmp($roles[0], 'administrator') == 0 || strcasecmp($roles[0], 'copy_of_csr') == 0)  && strcasecmp($new_status, 'processing') == 0) {
				$logger->debug('created new order by admin/csr orderid:- ' . $order_id . ' old_status:- ' . $old_status . ' new_status:- ' . $new_status . ' role:- ' . $roles[0], array('source' => 'fatal-errors'));
			}
			$logger->debug('change order orderid:- ' . $order_id . ' old_status:- ' . $old_status . ' new_status:- ' . $new_status . ' role:- ' . $roles[0], array('source' => 'fatal-errors'));
		}
	} catch (Exception $e) {
		$logger = wc_get_logger();
		$connection = new AMQPStreamConnection($options['woocommerce_rabbitmq_host_name'], $options['woocommerce_rabbitmq_port_numer'], $options['woocommerce_rabbitmq_user_name'], $options['woocommerce_rabbitmq_password']);
		$channel = $connection->channel();
		$channel->queue_declare($options['woocommerce_rabbitmq_queue_name'], true, false, false, null);
		$user = wp_get_current_user();
		$roles = $user->roles;
		if ((strcasecmp($roles[0], 'eot') == 0 || strcasecmp($roles[0], 'administrator') == 0 || strcasecmp($roles[0], 'copy_of_csr') == 0 || strcasecmp($roles[0], 'eot-assistant') == 0)  && strcasecmp($new_status, 'processing') == 0) {
			$logger = wc_get_logger();
			$logger->debug($order_id . ' Order id having issue ==> ' . $e->getMessage(), array('source' => 'fatal-errors'));
			$logger->debug('create order message for orderid ' . $order_id . ' not pushed into queue', array('source' => 'fatal-errors'));
		}
		try {
			$logger = wc_get_logger();
			$connection = new AMQPStreamConnection($options['woocommerce_rabbitmq_host_name'], $options['woocommerce_rabbitmq_port_numer'], $options['woocommerce_rabbitmq_user_name'], $options['woocommerce_rabbitmq_password']);
			$channel = $connection->channel();
			$channel->queue_declare($options['woocommerce_rabbitmq_queue_name'], true, false, false, null);
			$user = wp_get_current_user();
			$roles = $user->roles;
			if ((strcasecmp($roles[0], 'eot') == 0 || strcasecmp($roles[0], 'administrator') == 0 || strcasecmp($roles[0], 'copy_of_csr') == 0 || strcasecmp($roles[0], 'eot-assistant') == 0 || strcasecmp($roles[0], 'customer') == 0 || empty($roles[0]))  && strcasecmp($new_status, 'processing') == 0) {
				$msg = new AMQPMessage('createOrder,' . $order_id);
				$channel->basic_publish($msg, '', $options['woocommerce_rabbitmq_queue_name']);
				$logger = wc_get_logger();
				$logger->debug('createOrder -- message repushed orderid:- ' . $order_id . ' role:- ' . $roles[0], array('source' => 'fatal-errors'));
			}
		} catch (Exception $e) {
			$logger = wc_get_logger();
			$logger->debug($order_id . ' Order id having issue while repushing ==> ' . $e->getMessage(), array('source' => 'fatal-errors'));
			$logger->debug('create order message for orderid ' . $order_id . ' not repushed into queue', array('source' => 'fatal-errors'));
		}

		// $msg = 'Create order message for woocomerce orderid '.$order_id.' not pushed into queue \n' . $e->getMessage();
		// wp_mail('smitas@globalnestsolutions.com','Staging - RabbitMQ message sending failed!',$msg);
	}
}

/**
 * Function to get the order status, invoke when order status changed
 *
 * @name   get_woocommerce_order_status
 * @author Vivek Bansal
 */
/* function get_woocommerce_order_add($order_id) {
    try {
	  $user = wp_get_current_user();
	  $roles = $user->roles;
	  $logger = wc_get_logger();
	  if((!empty($roles[0]) && (strcasecmp($roles[0],'customer') == 0)) || empty($roles[0])){
        $options = get_option('woocommerce_rabbitmq_settings');
        if (!empty($options)) {
            $connection = new AMQPStreamConnection($options['woocommerce_rabbitmq_host_name'], $options['woocommerce_rabbitmq_port_numer'], $options['woocommerce_rabbitmq_user_name'], $options['woocommerce_rabbitmq_password']);
            $channel = $connection->channel();
            $channel->queue_declare($options['woocommerce_rabbitmq_queue_name'], true, false, false, null);
            $msg = new AMQPMessage('createOrder,' . $order_id);
            $channel->basic_publish($msg, '', $options['woocommerce_rabbitmq_queue_name']);
            $channel->close();
            $connection->close();
			$logger->debug( 'createOrder -- message pushed orderid:- '.$order_id.' role:- '. $roles[0], array( 'source' => 'fatal-errors' ) );
        }
	}
		
		$logger->debug( 'new order created orderid:- '.$order_id.' role:- '. $roles[0], array( 'source' => 'fatal-errors' ) );
    } catch (Exception $e) {
		$user = wp_get_current_user();
		$roles = $user->roles;
		if((!empty($roles[0]) && (strcasecmp($roles[0],'customer') == 0)) || empty($roles[0])){
			$logger = wc_get_logger();
			$logger->debug( $order_id. ' and roll '. $roles[0] . ' Order id having issue ==> ' . $e->getMessage() , array( 'source' => 'fatal-errors' ) );
			$logger->debug( 'create order message for orderid '.$order_id.' not pushed into queue', array( 'source' => 'fatal-errors' ) );
		}
    }
} */

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'plugin_add_settings_link');

/**
 * Function to add settings link into plugin
 *
 * @name   plugin_add_settings_link
 * @author Vivek Bansal
 */
function plugin_add_settings_link($links) {
	$settings_link = array(
		'<a href="' . admin_url('options-general.php?page=' . plugin_basename(dirname(__FILE__))) . '">' . __('Settings', plugin_basename(dirname(__FILE__))) . '</a>',
	);
	return array_merge($settings_link, $links);
}
