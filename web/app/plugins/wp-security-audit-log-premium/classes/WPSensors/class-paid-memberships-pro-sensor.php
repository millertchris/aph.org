<?php
/**
 * Custom sensor for Paid Memberships Pro
 *
 * Class file for alert manager.
 *
 * @since 5.5.2
 *
 * @package wsal
 * @subpackage wsal-paid-memberships-pro
 */

declare(strict_types=1);

namespace WSAL\WP_Sensors;

use WSAL\Controllers\Alert_Manager;
use WSAL\WP_Sensors\Helpers\Paid_Memberships_Pro_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Plugin_Sensors\Paid_Memberships_Pro_Sensor' ) ) {
	/**
	 * Custom sensor for Paid Memberships Pro
	 *
	 * @since 5.5.2
	 */
	class Paid_Memberships_Pro_Sensor {
		/**
		 * Init sensors
		 *
		 * @since 5.5.2
		 */
		public static function init() {

			if ( Paid_Memberships_Pro_Helper::is_pmp_active() ) {

				/**
				 * Members/Users events.
				 */
				add_action( 'pmpro_after_change_membership_level', array( __CLASS__, 'pmp_membership_assigned_to_user' ), 10, 2 );
				add_action( 'pmpro_after_change_membership_level', array( __CLASS__, 'pmp_membership_removed_from_user' ), 10, 3 );

				/**
				 * Changes to membership levels.
				 */
				add_action( 'pmpro_save_membership_level', array( __CLASS__, 'pmp_created_membership_level_event' ), 10 );
				add_action( 'pmpro_delete_membership_level', array( __CLASS__, 'pmp_deleted_membership_level_event' ), 10 );

				// @premium:start
				add_action( 'pmpro_save_membership_level', array( __CLASS__, 'pmpro_update_membership_level_event' ), 10 );

				\add_filter(
					'pmpro_member_edit_panels',
					array( Paid_Memberships_Pro_Helper::class, 'wsal_paid_memberships_pro_include_member_panel' ),
					10,
					2
				);
				// @premium:end

			}
		}

		/**
		 * Loads all the plugin dependencies early.
		 *
		 * @return void
		 *
		 * @since 5.5.2
		 */
		public static function early_init() {
			if ( Paid_Memberships_Pro_Helper::is_pmp_active() ) {
				\add_filter(
					'wsal_event_objects',
					array( Paid_Memberships_Pro_Helper::class, 'wsal_paid_memberships_pro_add_custom_event_objects' ),
					10,
					2
				);

				if ( Paid_Memberships_Pro_Helper::is_pmp_active() ) {
					\add_filter(
						'wsal_format_custom_meta',
						array( Paid_Memberships_Pro_Helper::class, 'wsal_pmp_format_membership_changes' ),
						10,
						4
					);
				}

				// @premium:start
				/**
				 * Orders
				 */
				add_action( 'pmpro_added_order', array( __CLASS__, 'pmp_added_order_event' ), 10, 1 );
				add_action( 'pmpro_delete_order', array( __CLASS__, 'pmp_delete_order_event' ), 10, 2 );

				// Trigger update order event before an order is updated, to compare new and old values.
				add_action( 'pmpro_update_order', array( __CLASS__, 'pmp_update_order_event' ), 10, 1 );

				/**
				 * Checkout
				 */
				add_action( 'pmpro_after_checkout', array( __CLASS__, 'pmp_after_checkout_event' ), 10, 2 );
				// @premium:end

			}
		}

		/**
		 * Trigger event when a membership level is created.
		 *
		 * @param int $save_id The membership level ID that is saved/created.
		 *
		 * @since 5.5.2
		 */
		public static function pmp_created_membership_level_event( $save_id ) {

			if ( ! isset( $_REQUEST['pmpro_membershiplevels_nonce'] ) ) {
				return;
			}

			$nonce       = \sanitize_text_field( \wp_unslash( $_REQUEST['pmpro_membershiplevels_nonce'] ) );
			$valid_nonce = \wp_verify_nonce( $nonce, 'save_membershiplevel' );

			// Return early if nonce is not valid.
			if ( ! $valid_nonce ) {
				return;
			}

			/**
			 * Only trigger this for event for level creation.
			 * ! Note: $_REQUEST['saveid'] never matches $save_id upon membership level creation.
			 */
			$request_saveid = isset( $_REQUEST['saveid'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['saveid'] ) ) : 0;

			// If we have a $request_saveid in the request and it is greater than 0, then this is an update and not a create, so we return early.
			if ( (int) $request_saveid > 0 ) {
				return;
			}

			$level = \pmpro_getLevel( $save_id );

			$variables = array(
				'MembershipID'       => (int) $save_id,
				'LevelName'          => \esc_html( $level->name ),
				'MembershipCostText' => \esc_html( \wp_strip_all_tags( pmpro_getLevelCost( $level, false, true ) ) ),
				'ExpirationText'     => ! empty( $level->expiration_number ) ? $level->expiration_number . ' ' . \pmpro_translate_billing_period( $level->expiration_period, $level->expiration_number ) : \esc_html__( 'Never', 'wp-security-audit-log' ),
				'ViewLink'           => \esc_url( \admin_url( 'admin.php?page=pmpro-membershiplevels&edit=' . $save_id ) ),
			);

			Alert_Manager::trigger_event( 9501, $variables );
		}

		/**
		 * Trigger event when a membership level is deleted.
		 *
		 * @param int $level_id The membership level ID that was deleted.
		 *
		 * @return void
		 *
		 * @since 5.5.2
		 */
		public static function pmp_deleted_membership_level_event( $level_id ) {
			$level = \pmpro_getLevel( $level_id );

			$variables = array(
				'MembershipID' => ! empty( $level_id ) ? (int) $level_id : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'LevelName'    => ! empty( $level->name ) ? \esc_html( $level->name ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
			);

			Alert_Manager::trigger_event( 9502, $variables );
		}

		// @premium:start
		/**
		 * Trigger event when a membership level is updated and it's details are changed
		 *
		 * @param int $save_id The membership level ID that is saved/created.
		 *
		 * @since 5.5.2
		 */
		public static function pmpro_update_membership_level_event( $save_id ) {

			if ( ! isset( $_REQUEST['pmpro_membershiplevels_nonce'] ) ) {
				return;
			}

			$nonce       = \sanitize_text_field( \wp_unslash( $_REQUEST['pmpro_membershiplevels_nonce'] ) );
			$valid_nonce = \wp_verify_nonce( $nonce, 'save_membershiplevel' );

			// Return early if nonce is not valid.
			if ( ! $valid_nonce ) {
				return;
			}

			/**
			 * Only trigger this for event for level creation.
			 * ! Note: $_REQUEST['saveid'] never matches $save_id upon membership level creation.
			 */
			$request_saveid = isset( $_REQUEST['saveid'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['saveid'] ) ) : 0;

			// If we have a $request_saveid in the request and it is smaller than 0, then this is a create action and not an update.
			if ( (int) $request_saveid < 0 ) {
				return;
			}

			// Get the old level settings before the update.
			$old_values = Paid_Memberships_Pro_Helper::extract_current_membership_values( $save_id );

			/**
			 * New values from the form submission.
			 */
			$level_name                 = isset( $_REQUEST['name'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['name'] ) ) : null;
			$level_description          = isset( $_REQUEST['description'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['description'] ) ) : null;
			$level_confirm_message      = isset( $_REQUEST['confirmation'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['confirmation'] ) ) : null;
			$membership_account_message = isset( $_REQUEST['membership_account_message'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['membership_account_message'] ) ) : null;
			$initial_payment            = isset( $_REQUEST['initial_payment'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['initial_payment'] ) ) : null;
			$billing_amount             = isset( $_REQUEST['billing_amount'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['billing_amount'] ) ) : null;
			$cycle_number               = isset( $_REQUEST['cycle_number'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['cycle_number'] ) ) : null;
			$is_recurring               = isset( $_REQUEST['recurring'] ) ? filter_var( \wp_unslash( $_REQUEST['recurring'] ), FILTER_VALIDATE_BOOLEAN ) : null;
			$is_custom_trial            = isset( $_REQUEST['custom_trial'] ) ? filter_var( \wp_unslash( $_REQUEST['custom_trial'] ), FILTER_VALIDATE_BOOLEAN ) : null;
			$cycle_period               = isset( $_REQUEST['cycle_period'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['cycle_period'] ) ) : null;
			$billing_limit              = isset( $_REQUEST['billing_limit'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['billing_limit'] ) ) : null;
			$trial_amount               = isset( $_REQUEST['trial_amount'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['trial_amount'] ) ) : null;
			$trial_limit                = isset( $_REQUEST['trial_limit'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['trial_limit'] ) ) : null;
			$expiration_number          = isset( $_REQUEST['expiration_number'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['expiration_number'] ) ) : null;
			$expiration_period          = isset( $_REQUEST['expiration_period'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['expiration_period'] ) ) : null;
			$disable_signups            = isset( $_REQUEST['disable_signups'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['disable_signups'] ) ) : null;

			$new_values = array(
				'name'            => $level_name,
				'description'     => $level_description,
				'confirmation'    => $level_confirm_message,
				'initial_payment' => $initial_payment,
				'billing_amount'  => $billing_amount,
				'billing_limit'   => $billing_limit,
				'disable_signups' => $disable_signups,
			);

			if ( null !== $trial_amount && null !== $trial_limit ) {
				$new_values['trial'] = Paid_Memberships_Pro_Helper::build_membership_trial_string( $trial_amount, $trial_limit, $is_custom_trial );
			}

			if ( null !== $billing_amount && null !== $cycle_number && null !== $cycle_period ) {
				$new_values['cycle_period'] = Paid_Memberships_Pro_Helper::build_membership_billing_cycle_string( $cycle_number, $cycle_period, $is_recurring, $billing_amount );
			}

			if ( null !== $expiration_number && null !== $expiration_period ) {
				$new_values['expiration_period'] = Paid_Memberships_Pro_Helper::build_membership_time_period_string( $expiration_number, $expiration_period );
			}

			$changed_fields_string = Paid_Memberships_Pro_Helper::compare_membership_values_change_after_update( $old_values, $new_values );

			// Trigger alert only if we have changes.
			if ( ! empty( $changed_fields_string ) ) {
				$variables = array(
					'LevelName'            => \esc_html( $level_name ),
					'Membership_ID'        => (int) $save_id,
					'PMPMembershipChanges' => \esc_html( $changed_fields_string ),
					'ViewLink'             => \esc_url( \admin_url( 'admin.php?page=pmpro-membershiplevels&edit=' . $save_id ) ),
				);

				Alert_Manager::trigger_event( 9503, $variables );
			}
		}
		// @premium:end

		/**
		 * Member received a membership level.
		 *
		 * @param int $level_id - The membership level ID from Paid Memberships Pro.
		 * @param int $user_id - The WordPress user ID.
		 *
		 * @since 5.5.2
		 */
		public static function pmp_membership_assigned_to_user( $level_id, $user_id ) {

			// If this is zero this is a cancellation, not an assignment, so in this case we return early.
			if ( 0 === (int) $level_id ) {
				return;
			}

			$level = \pmpro_getLevel( $level_id );
			$user  = \get_user_by( 'ID', $user_id );

			$variables = array(
				'ID'         => ! empty( $cancel_level_id ) ? (int) $cancel_level_id : 0,
				'LevelName'  => \esc_html( $level->name ),
				'LevelId'    => (int) $level->id,
				'UserID'     => (int) $user_id,
				'UserName'   => \esc_html( $user->user_login ),
				'Email'      => \esc_html( $user->user_email ),
				'FirstName'  => ! empty( \get_user_meta( $user_id, 'first_name', true ) ) ? \esc_html( \get_user_meta( $user_id, 'first_name', true ) ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'LastName'   => ! empty( \get_user_meta( $user_id, 'last_name', true ) ) ? \esc_html( \get_user_meta( $user_id, 'last_name', true ) ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'Role'       => \esc_html( implode( ', ', $user->roles ) ),
				'ViewMember' => \esc_url( \admin_url( '?page=pmpro-member&user_id=' . $user_id ) ),
			);

			Alert_Manager::trigger_event( 9504, $variables );
		}

		/**
		 * Trigger event when a membership level is removed/canceled from a user.
		 *
		 * @param int $save_id - Save ID is from $_REQUEST and helps to determine if this is a removal or an assignment.
		 * @param int $user_id - The affected WordPress user ID.
		 * @param int $cancel_level_id - The level ID of the membership that was removed from this user.
		 *
		 * @since 5.5.2
		 */
		public static function pmp_membership_removed_from_user( $save_id, $user_id, $cancel_level_id ) {

			// Save id is === 0 only on cancellations, so return early if this is not equal to 0.
			if ( 0 !== $save_id ) {
				return;
			}

			$level = \pmpro_getLevel( $cancel_level_id );
			$user  = \get_user_by( 'ID', $user_id );

			$variables = array(
				'LevelName'  => \esc_html( $level->name ),
				'LevelID'    => (int) $cancel_level_id,
				'UserID'     => (int) $user_id,
				'UserName'   => \esc_html( $user->user_login ),
				'Email'      => \esc_html( $user->user_email ),
				'FirstName'  => ! empty( \get_user_meta( $user_id, 'first_name', true ) ) ? \esc_html( \get_user_meta( $user_id, 'first_name', true ) ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'LastName'   => ! empty( \get_user_meta( $user_id, 'last_name', true ) ) ? \esc_html( \get_user_meta( $user_id, 'last_name', true ) ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'Role'       => \esc_html( implode( ', ', $user->roles ) ),
				'ViewMember' => \esc_url( \admin_url( '?page=pmpro-member&user_id=' . $user_id ) ),
			);

			Alert_Manager::trigger_event( 9505, $variables );
		}

		// @premium:start

		/**
		 * Trigger event when an order is created.
		 *
		 * @param \MemberOrder $order - The member order object for the order.
		 *
		 * @since 5.5.2
		 */
		public static function pmp_added_order_event( $order ) {

			$order_code       = $order->code;
			$user             = \get_user_by( 'ID', $order->user_id );
			$membership_level = \pmpro_getLevel( $order->membership_id );

			$fallback_gateway = Paid_Memberships_Pro_Helper::get_pmp_free_levels_gateway();

			// Variables we want to share with the alert.
			$variables = array(
				'OrderCode' => ! empty( $order_code ) ? \esc_html( $order_code ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'UserId'    => ! empty( $user->ID ) ? (int) $user->ID : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'UserName'  => ! empty( $user->user_login ) ? \esc_html( $user->user_login ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'Status'    => ! empty( $order->status ) ? \esc_html( $order->status ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'Gateway'   => ! empty( $order->gateway ) ? \esc_html( $order->gateway ) : $fallback_gateway,
				'Total'     => \esc_html( \pmpro_formatPrice( $order->total ) ),
				'LevelName' => ! empty( $membership_level->name ) ? \esc_html( $membership_level->name ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				'LevelID'   => ! empty( $membership_level->id ) ? (int) $membership_level->id : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
				// Admin_url instead of network_admin_url or it will not work for PMP.
				'OrderLink' => \esc_url( \admin_url( 'admin.php?page=pmpro-orders&order=' . $order->id ) ),
			);

			Alert_Manager::trigger_event( 9506, $variables );
		}

		/**
		 * Trigger event when an order is updated.
		 *
		 * Regardless of using pmpro_update_order or pmpro_updated_order hooks there's no easy way to get the
		 * old order values before the update here, so we are getting the older order version from the DB.
		 *
		 * @param \MemberOrder $updated_order - The member order object for the order.
		 *
		 * @since 5.5.2
		 */
		public static function pmp_update_order_event( $updated_order ) {

			$order_id = $updated_order->id ?? null;

			if ( null === $order_id ) {
				return;
			}

			/**
			 * Array of the order saved in the DB before this update
			 */
			$old_order_array = Paid_Memberships_Pro_Helper::get_pmp_order_by_id( $order_id );

			$fallback_gateway = Paid_Memberships_Pro_Helper::get_pmp_free_levels_gateway();

			/**
			 * Array of updated order field values.
			 */
			$updated_order_array = array(
				'user_id'             => $updated_order->user_id ?? null,
				'membership_id'       => $updated_order->membership_id ?? null,
				'subtotal'            => $updated_order->subtotal ?? null,
				'tax'                 => $updated_order->tax ?? null,
				'total'               => $updated_order->total ?? null,
				'payment_type'        => $updated_order->payment_type ?? null,
				'card_type'           => $updated_order->cardtype ?? null,
				'account_number'      => $updated_order->accountnumber ?? null,
				'expiration_month'    => $updated_order->expirationmonth ?? null,
				'expiration_year'     => $updated_order->expirationyear ?? null,
				'status'              => $updated_order->status ?? null,
				'gateway'             => $updated_order->gateway ? $updated_order->gateway : $fallback_gateway,
				'gateway_environment' => $updated_order->gateway_environment ?? null,
				'order_date'          => $updated_order->timestamp ? \date_i18n( \get_option( 'date_format' ) . ' H:i:s', $updated_order->timestamp ) : null,
				'notes'               => $updated_order->notes ?? null,
			);

			$changed_fields = Paid_Memberships_Pro_Helper::get_array_changed_fields( $old_order_array, $updated_order_array );

			// Trigger alert only if we have changes.
			if ( ! empty( $changed_fields ) ) {
				$changed_fields_string = Paid_Memberships_Pro_Helper::convert_changed_fields_array_into_string( $changed_fields );

				$membership_level = \pmpro_getLevel( $updated_order->membership_id );

				$variables = array(
					'OrderCode'       => ! empty( $updated_order->code ) ? \esc_html( $updated_order->code ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
					'UserName'        => $updated_order->user_id ? \get_the_author_meta( 'user_login', $updated_order->user_id ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
					'Status'          => ! empty( $updated_order->status ) ? \esc_html( $updated_order->status ) : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
					'Gateway'         => $updated_order->gateway ? \esc_html( $updated_order->gateway ) : $fallback_gateway,
					'LevelName'       => ! empty( $membership_level->name ) ? \esc_html( $membership_level->name ) : \esc_html__( 'None', 'wp-security-audit-log' ),
					'LevelID'         => ! empty( $membership_level->id ) ? (int) $membership_level->id : \esc_html__( 'Not provided', 'wp-security-audit-log' ),
					// Admin_url instead of network_admin_url or it will not work for PMP.
						'OrderLink'   => \esc_url( \admin_url( 'admin.php?page=pmpro-orders&order=' . $order_id ) ),
					'PMPOrderChanges' => \esc_html( $changed_fields_string ),
				);
				Alert_Manager::trigger_event( 9507, $variables );
			}
		}

		/**
		 * Trigger event when an order is deleted.
		 *
		 * @param int          $order_id The ID of the order being deleted.
		 * @param \MemberOrder $order The order object being deleted.
		 *
		 * @since 5.5.2
		 */
		public static function pmp_delete_order_event( $order_id, $order ) {

			$order_code       = $order->code;
			$user             = \get_user_by( 'ID', $order->user_id );
			$membership_level = \pmpro_getLevel( $order->membership_id );

			$variables = array(
				'OrderCode' => \esc_html( $order_code ),
				'UserName'  => \esc_html( $user->user_login ),
				'LevelName' => \esc_html( $membership_level->name ),
				'LevelID'   => (int) $membership_level->id,
			);

			Alert_Manager::trigger_event( 9508, $variables );
		}

		/**
		 * Trigger event when a user completes a checkout.
		 *
		 * @param int          $user_id The ID of the user completing the checkout.
		 * @param \MemberOrder $order The order object being deleted.
		 *
		 * @since 5.5.2
		 */
		public static function pmp_after_checkout_event( $user_id, $order ) {

			$user = \get_user_by( 'ID', $user_id );

			$variables = array(
				'UserName'  => \esc_html( $user->user_nicename ),
				'UserID'    => (int) $user_id,
				'OrderCode' => \esc_html( $order->code ),
				'Total'     => \esc_html( \pmpro_formatPrice( $order->total ) ),
				'LevelName' => \esc_html( $order->membership_level->name ),
				'LevelId'   => (int) $order->membership_level->id,
				'Gateway'   => \esc_html( $order->gateway ),
				// Admin_url instead of network_admin_url or it will not work for PMP.
				'OrderLink' => \esc_url( \admin_url( 'admin.php?page=pmpro-orders&order=' . $order->id ) ),
			);

			Alert_Manager::trigger_event( 9509, $variables );
		}

		// @premium:end
	}
}
