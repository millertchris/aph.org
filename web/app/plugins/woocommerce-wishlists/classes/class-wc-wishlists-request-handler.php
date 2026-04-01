<?php

class WC_Wishlists_Request_Handler {

	public static function process_request() {
		// Check if we have the required action parameter from either GET or POST
		if (!isset($_REQUEST['wlaction'])) {
			return;
		}

		$action = sanitize_text_field(wp_unslash($_REQUEST['wlaction']));

		// Verify nonce for security
		if (!WC_Wishlists_Plugin::verify_nonce($action)) {
			wp_die(esc_html__('Action failed. Please refresh the page and retry.', 'wc_wishlist'));
		}

		// Handle special case for adding all items
		if (isset($_REQUEST['wladdall']) && !empty($_REQUEST['wladdall'])) {
			$action = 'add-cart-items';
		}

		$result = false;

		switch ($action) {
			case 'create-list':
				$result = self::create_list();
				break;

			case 'delete-list':
				$result = self::delete_list();
				break;

			case 'edit-list':
				$result = self::edit_list();
				break;

			case 'edit-lists':
				$result = self::edit_lists();
				break;

			case 'wishlists-remove-from-list':
				$result = self::remove_from_list();
				break;

			case 'manage-list':
				$result = self::handle_manage_list_actions();
				break;

			case 'add-cart-item':
				$result = self::add_to_cart();
				break;

			case 'add-cart-items':
				$result = self::add_all_to_cart();
				break;

			case 'clear-session-items':
				$result = self::clear_session_items();
				break;

			default:
				// Log unknown action for debugging
				error_log('WC Wishlists: Unknown action attempted - ' . $action);
				return;
		}

		// Handle successful results that require redirect
		if ($result !== false && $result !== true) {
			self::safe_redirect($result);
		}
	}


	/**
	 * Handle manage-list bulk actions
	 *
	 * @return mixed
	 */
	private static function handle_manage_list_actions() {
		if (!isset($_REQUEST['wlupdateaction'])) {
			return false;
		}

		$bulk_action = sanitize_text_field(wp_unslash($_REQUEST['wlupdateaction']));

		switch ($bulk_action) {
			case 'quantity':
				return self::bulk_update_action();

			case 'quantity-add-to-cart':
				// Update quantity first, then add to cart
				self::bulk_update_action();
				return self::bulk_edit_action();

			default:
				return self::bulk_edit_action();
		}
	}

	/**
	 * Safely redirect with proper headers
	 *
	 * @param string $url The URL to redirect to
	 */
	private static function safe_redirect($url) {
		// Set cache control headers
		nocache_headers();

		// Add robots meta for SEO
		header('X-Robots-Tag: noindex, nofollow', true);

		// Perform the redirect with a unique parameter to prevent caching
		$redirect_url = add_query_arg(['wlm' => wp_generate_uuid4()], $url);
		wp_safe_redirect(esc_url_raw($redirect_url));
		exit;
	}

	public static function last_updated_class( $list_id ) {
		_deprecated_function( 'last_updated_class', '1.8.1' );

		return '';
	}

	private static function create_list() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$args = $_POST;

		WC_Wishlists_User::set_cookie();
		$args['wishlist_owner'] = WC_Wishlists_User::get_wishlist_key();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['wishlist_title'] ) ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Please name your list', 'wc_wishlist' ), 'error' );

			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$title = sanitize_text_field(wp_unslash($_POST['wishlist_title']));

		$args = [];
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		foreach ( $_POST as $key => $value ) {
			$args[ $key ] = sanitize_text_field( $value );
		}

		$current_user = wp_get_current_user();

		$defaults = [
			'wishlist_title'               => $title,
			'wishlist_description'         => '',
			'wishlist_type'                => 'list',
			'wishlist_sharing'             => 'Private',
			'wishlist_status'              => is_user_logged_in() ? 'active' : 'temporary',
			'wishlist_owner_email'         => is_user_logged_in() ? $current_user->user_email : '',
			'wishlist_owner_notifications' => false,
			'wishlist_first_name'          => is_user_logged_in() ? $current_user->user_firstname : '',
			'wishlist_last_name'           => is_user_logged_in() ? $current_user->user_lastname : '',
		];

		$args              = wp_parse_args( $args, $defaults );
		$validation_result = apply_filters( 'woocommerce_validate_wishlist_create', true, $args );

		if ( $validation_result !== true ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( $validation_result, 'wc_wishlist' ), 'error' );
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$result = WC_Wishlists_Wishlist::create_list( sanitize_text_field( wp_unslash($_POST['wishlist_title'] )), $args );

		if ( $result ) {
			$moved         = false;
			$session_items = WC_Wishlists_Wishlist_Item_Collection::get_items_from_session();
			if ( $session_items && count( $session_items ) ) {
				$moved = 0;
				foreach ( $session_items as $wishlist_item_key => $session_data ) {
					$moved += WC_Wishlists_Wishlist_Item_Collection::move_item_to_list_from_session( $result, $wishlist_item_key );
				}
			}

			if ( $moved ) {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Wishlist successfully created. %s items moved', 'wc_wishlist' ), $moved ) );
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( __( 'Wishlist successfully created', 'wc_wishlist' ) );
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if (WC_Wishlists_Settings::get_setting('woocommerce_wishlist_redirect_after_add_to_cart', 'yes') === 'no' && isset($_POST['wl_return_to'])) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$return_to_id = absint(wp_unslash($_POST['wl_return_to']));
				if ($return_to_id > 0) {
					return get_permalink($return_to_id);
				}
			}

			return WC_Wishlists_Wishlist::get_the_url_edit($result);
		}

		return false;
	}

	private static function delete_list() {
		// Sanitize and validate the wishlist ID
		$post_id = isset($_REQUEST['wlid']) ? absint(wp_unslash($_REQUEST['wlid'])) : 0;

		if ($post_id <= 0) {
			wp_die(esc_html__('Invalid wishlist ID', 'wc_wishlist'));
		}

		$post = get_post($post_id);

		// Check if post exists and is the correct type
		if (!$post || $post->post_type !== 'wishlist') {
			wp_die(esc_html__('Unable to locate wishlist', 'wc_wishlist'));
		}

		$wishlist = new WC_Wishlists_Wishlist($post_id);

		// Permission check for non-admin users
		if (!is_admin()) {
			$wl_owner = $wishlist->get_wishlist_owner();
			$current_owner_key = WC_Wishlists_User::get_wishlist_key();

			if ($wl_owner !== $current_owner_key && !current_user_can('manage_woocommerce')) {
				wp_die(esc_html__('You can only manage your own lists', 'wc_wishlist'));
			}
		}

		$result = WC_Wishlists_Wishlist::delete_list($post_id);

		// Show success message for non-admin users
		if ($result && !is_admin()) {
			WC_Wishlist_Compatibility::wc_add_notice(esc_html__('Wishlist successfully deleted', 'wc_wishlist'));
		}

		return WC_Wishlists_Pages::get_url_for('my-lists');
	}

	private static function edit_list() {
		// Sanitize and validate the wishlist ID
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post_id = isset($_POST['wlid']) ? absint(wp_unslash($_POST['wlid'])) : 0;

		if ($post_id <= 0) {
			wp_die(esc_html__('Invalid wishlist ID', 'wc_wishlist'));
		}

		$post = get_post($post_id);

		// Check if post exists and is the correct type
		if (!$post || $post->post_type !== 'wishlist') {
			wp_die(esc_html__('Unable to locate wishlist for updating', 'wc_wishlist'));
		}

		// Validate required fields
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$wishlist_title = isset($_POST['wishlist_title']) ? sanitize_text_field(wp_unslash($_POST['wishlist_title'])) : '';
		if (empty($wishlist_title)) {
			WC_Wishlist_Compatibility::wc_add_notice(esc_html__('Name can not be empty', 'wc_wishlist'), 'error');
			return false;
		}

		// Permission check - fix the logic error
		$wishlist = new WC_Wishlists_Wishlist($post_id);
		if (!is_admin()) {
			$wl_owner = $wishlist->get_wishlist_owner();
			$current_owner_key = WC_Wishlists_User::get_wishlist_key();

			if ($wl_owner !== $current_owner_key && !current_user_can('manage_woocommerce')) {
				wp_die(esc_html__('You can only update your own lists', 'wc_wishlist'));
			}
		}

		//phpcs:ignore
		$args   = $_POST;
		$result = WC_Wishlists_Wishlist::update_list( $post_id, $args );
		if ( $result ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Wishlist successfully updated', 'wc_wishlist' ) );
		}

		return WC_Wishlists_Wishlist::get_the_url_edit($result) . '#tab-wl-settings';
	}

	/**
	 * Sanitize POST data for wishlist updates
	 *
	 * @param array $post_data Raw POST data
	 * @return array Sanitized data
	 */
	private static function sanitize_post_data($post_data) {
		$sanitized = [];

		// Define expected fields and their sanitization methods
		$field_map = [
			'wishlist_title' => 'sanitize_text_field',
			'wishlist_description' => 'wp_kses_post',
			'wishlist_visibility' => 'sanitize_key',
			// Add other expected fields as needed
		];

		foreach ($field_map as $field => $sanitize_func) {
			if (isset($post_data[$field])) {
				$sanitized[$field] = $sanitize_func(wp_unslash($post_data[$field]));
			}
		}

		return $sanitized;
	}

	private static function edit_lists() {
		$result = true;

		// Sanitize and validate the sharing data
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		$list_ids = isset($_POST['sharing']) ? self::sanitize_sharing_data(wp_unslash($_POST['sharing'])) : [];

		if (empty($list_ids)) {
			return false;
		}

		foreach ($list_ids as $id => $sharing) {
			// Validate the list ID
			$id = absint($id);
			if ($id <= 0) {
				continue; // Skip invalid IDs
			}

			// Permission check
			$wl_owner = WC_Wishlists_Wishlist::get_the_wishlist_owner($id);
			$current_owner_key = WC_Wishlists_User::get_wishlist_key();

			if ($wl_owner != $current_owner_key && !current_user_can('manage_woocommerce')) {
				wp_die(esc_html__('You can only update your own lists', 'wc_wishlist'));
			}

			// Update the list
			$update_result = WC_Wishlists_Wishlist::update_list($id, [
				'wishlist_sharing' => $sharing
			]);

			$result = $result && (bool) $update_result;
		}

		// Show appropriate message
		if ($result) {
			WC_Wishlist_Compatibility::wc_add_notice(esc_html__('Lists successfully updated', 'wc_wishlist'));
		} else {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('There was an error updating your lists. Please refresh the page and try again.', 'wc_wishlist'),
				'error'
			);
		}

		return WC_Wishlists_Pages::get_url_for('my-lists');
	}

	/**
	 * Sanitize sharing data from POST request
	 *
	 * @param mixed $sharing_data Raw sharing data from POST
	 * @return array Sanitized sharing data
	 */
	private static function sanitize_sharing_data($sharing_data) {
		if (!is_array($sharing_data)) {
			return [];
		}

		$sanitized = [];
		$allowed_sharing_values = ['public', 'shared', 'private']; // Define your allowed values

		foreach ($sharing_data as $id => $sharing) {
			$clean_id = absint($id);
			$clean_sharing = sanitize_key(wp_unslash($sharing));

			// Only include valid IDs and sharing values
			if ($clean_id > 0 && in_array($clean_sharing, $allowed_sharing_values, true)) {
				$sanitized[$clean_id] = $clean_sharing;
			}
		}

		return $sanitized;
	}

	private static function remove_from_list() {
		// Sanitize and validate required parameters
		$wishlist_id = isset($_REQUEST['wlid']) ? absint(wp_unslash($_REQUEST['wlid'])) : 0;
		$wishlist_item_key = isset($_REQUEST['wishlist-item-key']) ? sanitize_key(wp_unslash($_REQUEST['wishlist-item-key'])) : '';

		// Validate required data
		if ($wishlist_id <= 0 || empty($wishlist_item_key)) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('Unable to remove item. Please try again', 'wc_wishlist'),
				'error'
			);

			// Return to wishlist page or fallback URL
			if ($wishlist_id > 0) {
				return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
			}
			return WC_Wishlists_Pages::get_url_for('my-lists');
		}

		// Permission check
		$wl_owner = WC_Wishlists_Wishlist::get_the_wishlist_owner($wishlist_id);
		$current_owner_key = WC_Wishlists_User::get_wishlist_key();

		if ($wl_owner !== $current_owner_key && !current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('You can only update your own lists', 'wc_wishlist'));
		}

		// Attempt to remove the item
		$result = WC_Wishlists_Wishlist_Item_Collection::remove_item($wishlist_id, $wishlist_item_key);

		// Show success message
		if ($result) {
			WC_Wishlist_Compatibility::wc_add_notice(esc_html__('Item removed from your list', 'wc_wishlist'));
		} else {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('Failed to remove item. Please try again', 'wc_wishlist'),
				'error'
			);
		}

		return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
	}

	private static function bulk_edit_action() {
		// Sanitize and validate wishlist ID
		$wishlist_id = isset($_REQUEST['wlid']) ? absint(wp_unslash($_REQUEST['wlid'])) : 0;

		if ($wishlist_id <= 0) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('Unable to edit list. Please try again', 'wc_wishlist'),
				'error'
			);
			return WC_Wishlists_Pages::get_url_for('my-lists');
		}

		// Sanitize and validate bulk action
		$bulk_action = isset($_REQUEST['wlupdateaction']) ? sanitize_key(wp_unslash($_REQUEST['wlupdateaction'])) : '';

		if (empty($bulk_action)) {
			return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
		}

		// Permission check
		$wl_owner = WC_Wishlists_Wishlist::get_the_wishlist_owner($wishlist_id);
		$current_owner_key = WC_Wishlists_User::get_wishlist_key();

		if ($wl_owner !== $current_owner_key && !current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('You can only update your own lists', 'wc_wishlist'));
		}

		// Sanitize items array
		// Sanitized below,
		// phpcs:ignore
		$items = self::sanitize_wishlist_items(wp_unslash($_REQUEST['wlitem'] )?? []);

		if (empty($items)) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('Please select at least one item before applying an action', 'wc_wishlist'),
				'error'
			);
			return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
		}

		// Handle different bulk actions
		switch ($bulk_action) {
			case 'remove':
				return self::handle_bulk_remove($wishlist_id, $items);

			case 'create':
				return self::handle_bulk_create($wishlist_id, $items);

			case 'add-to-cart':
			case 'quantity-add-to-cart':
				return self::handle_bulk_add_to_cart($wishlist_id, $items);

			default:
				return self::handle_bulk_move($wishlist_id, $bulk_action, $items);
		}
	}

	/**
	 * Sanitize wishlist items array
	 *
	 * @param mixed $items Raw items data
	 * @return array Sanitized items
	 */
	private static function sanitize_wishlist_items($items) {
		if (!is_array($items)) {
			return [];
		}

		$sanitized = [];
		foreach ($items as $item_key) {
			$clean_key = sanitize_key(wp_unslash($item_key));
			if (!empty($clean_key)) {
				$sanitized[] = $clean_key;
			}
		}

		return $sanitized;
	}

	/**
	 * Handle bulk remove action
	 *
	 * @param int $wishlist_id
	 * @param array $items
	 * @return string Redirect URL
	 */
	private static function handle_bulk_remove($wishlist_id, $items) {
		$result = 0;

		foreach ($items as $wishlist_item_key) {
			if (WC_Wishlists_Wishlist_Item_Collection::remove_item($wishlist_id, $wishlist_item_key)) {
				$result++;
			}
		}

		if ($result > 0) {
			WC_Wishlist_Compatibility::wc_add_notice(
				sprintf(
				/* translators: %s: number of items */
					esc_html__('%s items removed from your list', 'wc_wishlist'),
					$result
				)
			);
		} else {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('No items were removed. Please try again', 'wc_wishlist'),
				'error'
			);
		}

		return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
	}

	/**
	 * Handle bulk create action
	 *
	 * @param int $wishlist_id
	 * @param array $items
	 * @return string Redirect URL
	 */
	private static function handle_bulk_create($wishlist_id, $items) {
		$result = 0;

		foreach ($items as $wishlist_item_key) {
			if (WC_Wishlists_Wishlist_Item_Collection::move_item_to_session($wishlist_id, $wishlist_item_key)) {
				$result++;
			}
		}

		if ($result > 0) {
			$session_items = WC_Wishlists_Wishlist_Item_Collection::get_items_from_session();
			// Successfully moved items to session
		} else {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('No items were moved. Please try again', 'wc_wishlist'),
				'error'
			);
		}

		return WC_Wishlists_Pages::get_url_for('create-a-list');
	}

	/**
	 * Handle bulk add to cart action
	 *
	 * @param int $wishlist_id
	 * @param array $items
	 * @return string Redirect URL
	 */
	private static function handle_bulk_add_to_cart($wishlist_id, $items) {
		$result = 0;

		foreach ($items as $wishlist_item_key) {
			if (self::add_to_cart($wishlist_id, $wishlist_item_key, true) !== false) {
				$result++;
			}
		}

		if ($result > 0) {
			WC_Wishlist_Compatibility::wc_add_notice(
				sprintf(
				/* translators: %s: number of items */
					esc_html__('%s items have been added to the cart', 'wc_wishlist'),
					$result
				)
			);
		} else {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('No items were added to cart. Please try again', 'wc_wishlist'),
				'error'
			);
		}

		return self::get_add_to_cart_redirect_url($wishlist_id);
	}

	/**
	 * Handle bulk move action
	 *
	 * @param int $wishlist_id
	 * @param string $destination_id
	 * @param array $items
	 * @return string Redirect URL
	 */
	private static function handle_bulk_move($wishlist_id, $destination_id, $items) {
		$destination_id = absint($destination_id);

		if ($destination_id <= 0) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('Invalid destination list', 'wc_wishlist'),
				'error'
			);
			return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
		}

		$destination_list = new WC_Wishlists_Wishlist($destination_id);

		// Verify ownership of destination list
		if ($destination_list->get_wishlist_owner() !== WC_Wishlists_User::get_wishlist_key()) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('You can only move items to your own lists', 'wc_wishlist'),
				'error'
			);
			return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
		}

		$result = 0;
		foreach ($items as $wishlist_item_key) {
			if (WC_Wishlists_Wishlist_Item_Collection::move_item($wishlist_id, $destination_id, $wishlist_item_key)) {
				$result++;
			}
		}

		if ($result > 0) {
			$destination_title = get_the_title($destination_id);
			$edit_url = WC_Wishlists_Wishlist::get_the_url_edit($destination_id);

			WC_Wishlist_Compatibility::wc_add_notice(
				sprintf(
				/* translators: %1$s: number of items, %2$s: destination list title, %3$s: edit list URL */
					esc_html__('%1$s items successfully moved to %2$s', 'wc_wishlist') . ' <a class="button" href="%3$s">' . esc_html__('Edit List', 'wc_wishlist') . '</a>',
					$result,
					esc_html($destination_title),
					esc_url($edit_url)
				)
			);
		} else {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('No items were moved. Please try again', 'wc_wishlist'),
				'error'
			);
		}

		return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
	}

	private static function bulk_update_action() {
		// Sanitize and validate wishlist ID
		$wishlist_id = isset($_REQUEST['wlid']) ? absint(wp_unslash($_REQUEST['wlid'])) : 0;

		if ($wishlist_id <= 0) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('Unable to edit list. Please try again', 'wc_wishlist'),
				'error'
			);
			return WC_Wishlists_Pages::get_url_for('my-lists');
		}

		// Permission check
		$wl_owner = WC_Wishlists_Wishlist::get_the_wishlist_owner($wishlist_id);
		$current_owner_key = WC_Wishlists_User::get_wishlist_key();

		if ($wl_owner !== $current_owner_key && !current_user_can('manage_woocommerce')) {
			wp_die(esc_html__('You can only update your own lists', 'wc_wishlist'));
		}

		$result = 0;

		// Check if cart data exists and is valid
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if (!isset($_POST['cart']) || !is_array($_POST['cart'])) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('No quantity data provided', 'wc_wishlist'),
				'error'
			);
			return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
		}

		// Get and sanitize items array
		// phpcs:ignore
		$items = self::sanitize_wishlist_items($_REQUEST['wlitem'] ?? []);

		if (empty($items)) {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('Please select at least one item before applying an action', 'wc_wishlist'),
				'error'
			);
			return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
		}

		// Process each selected item
		foreach ($items as $key) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if (!isset($_POST['cart'][$key])) {
				continue; // Skip items without cart data
			}

			// phpcs:ignore
			$cart_data = self::sanitize_cart_item_data($_POST['cart'][$key]);

			if ($cart_data === false) {
				continue; // Skip invalid cart data
			}

			// Update item quantity
			if (isset($cart_data['qty'])) {
				if (WC_Wishlists_Wishlist_Item_Collection::update_item_quantity($wishlist_id, $key, $cart_data['qty'])) {
					$result++;
				}
			}

			// Update ordered quantity if provided
			if (isset($cart_data['ordered_qty'])) {
				WC_Wishlists_Wishlist_Item_Collection::update_item_ordered_quantity($wishlist_id, $key, $cart_data['ordered_qty']);
			}
		}

		// Show appropriate message
		if ($result > 0) {
			WC_Wishlist_Compatibility::wc_add_notice(
				sprintf(
				/* translators: %s: number of items */
					esc_html__('%s items updated', 'wc_wishlist'),
					$result
				)
			);
		} else {
			WC_Wishlist_Compatibility::wc_add_notice(
				esc_html__('No items were updated. Please check your quantities and try again', 'wc_wishlist'),
				'error'
			);
		}

		return WC_Wishlists_Wishlist::get_the_url_edit($wishlist_id);
	}

	/**
	 * Sanitize cart item data
	 *
	 * @param mixed $cart_item_data Raw cart item data
	 * @return array|false Sanitized data or false if invalid
	 */
	private static function sanitize_cart_item_data($cart_item_data) {
		if (!is_array($cart_item_data)) {
			return false;
		}

		$sanitized = [];

		// Sanitize quantity
		if (isset($cart_item_data['qty'])) {
			$qty = wp_unslash($cart_item_data['qty']);
			$sanitized['qty'] = absint($qty);
		}

		// Sanitize ordered quantity
		if (isset($cart_item_data['ordered_qty'])) {
			$ordered_qty = wp_unslash($cart_item_data['ordered_qty']);
			$sanitized['ordered_qty'] = absint($ordered_qty);
		}

		// Return false if no valid data found
		if (empty($sanitized)) {
			return false;
		}

		return $sanitized;
	}

	private static function add_all_to_cart() {
		// Determine if this is POST or GET and handle accordingly.
		$action_type = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING );
		if ( $action_type == 'POST' ) {
			$wishlist_id = filter_input( INPUT_POST, 'wlid', FILTER_SANITIZE_NUMBER_INT );
		} else {
			$wishlist_id = filter_input( INPUT_GET, 'wlid', FILTER_SANITIZE_NUMBER_INT );
		}

		$items = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist_id );
		if ( $items ) {
			$result = false;
			foreach ( $items as $wishlist_item_key => $data ) {
				$_product = $data['data'];
				if ( $_product->get_type() != 'external' ) {
					$result += ( self::add_to_cart( $wishlist_id, $wishlist_item_key, true ) !== false );
				}
			}

			if ( $result ) {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s have been added to the cart', 'wc_wishlist' ), $result ) );
			} else {
				WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please select at least one item before applying an action', 'wc_wishlist' ), $result ), 'error' );
			}
		}

		$url = self::get_add_to_cart_redirect_url( $wishlist_id );
		if ( isset( $_GET['preview'] ) ) {
			return esc_url( add_query_arg( [ 'preview' => 'true' ], $url ) );
		} else {
			return esc_url( $url );
		}
	}

	/**
	 * @throws Exception
	 */
	private static function add_to_cart( $wishlist_id = false, $wishlist_item_key = false, $suppress_messages = false ) {
		$result = false;

		$input_type = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING );
		$input_type = $input_type == 'POST' ? INPUT_POST : INPUT_GET;
		if ( ! $wishlist_id ) {
			$wishlist_id = filter_input( $input_type, 'wlid', FILTER_SANITIZE_NUMBER_INT );
		}

		if ( ! $wishlist_item_key ) {
			$wishlist_item_key = filter_input( $input_type, 'wishlist-item-key', FILTER_SANITIZE_STRING );
		}

		if ( ! $wishlist_id ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ), 'error' );

			return false;
		}

		$screen = 'view';
		// If the input mode === POST and wladdall is set and the wladdall-screen is set to edit, then we are adding all items to the cart from the edit screen.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $input_type === INPUT_POST && isset( $_POST['wladdall'] ) && isset( $_POST['wladdall-screen'] ) && $_POST['wladdall-screen'] == 'edit' ) {
			$screen = 'edit';
		}

		$wishlist = WC_Wishlists_Wishlist::get_wishlist( $wishlist_id );
		if ( ! $wishlist ) {
			WC_Wishlist_Compatibility::wc_add_notice( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ), 'error' );

			return false;
		}

		$wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist->id );

		if ( $wishlist_item_key ) {
			if ( sizeof( $wishlist_items ) > 0 && isset( $wishlist_items[ $wishlist_item_key ] ) ) {

				$wishlist_item = $wishlist_items[ $wishlist_item_key ];
				if ( isset( $wishlist_item['wl_price'] ) ) {
					unset( $wishlist_item['wl_price'] );
				}

				$core_keys   = [ 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'date' ];
				$add_on_data = [];
				$cart_item   = [];

				foreach ( $wishlist_item as $key => $value ) {
					if ( ! in_array( $key, $core_keys ) ) {
						$add_on_data[ $key ] = $value;
					} else {
						$cart_item[ $key ] = $value;
					}
				}

				$wishlist_prefix = WC_Wishlists_Settings::get_setting( 'wc_wishlists_cart_label', __( 'From Wishlist', 'wc_wishlist' ) );

				$add_on_data = apply_filters( 'woocommerce_copy_cart_item_data', $add_on_data, (int) $wishlist_item['product_id'], $wishlist_item );
				// Generate a ID based on product ID, variation ID, variation data, and other cart item data
				$check_cart_id = WC()->cart->generate_cart_id( (int) $cart_item['product_id'], $cart_item['variation_id'], $cart_item['variation'], $add_on_data );

				// See if this product and its options is already in the cart
				$check_cart_item_key = WC()->cart->find_product_in_cart( $check_cart_id );

				$product_data = wc_get_product( isset( $cart_item['variation_id'] ) && ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'] );
				if ( ! $product_data ) {
					WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to add product to the cart. Product no longer exists', 'wc_wishlist' ), 'error' );

					return false;
				}

				if ( ! apply_filters( 'woocommerce_wishlist_user_can_purchase', true, $product_data ) ) {
					WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Purchases are currently disabled for %s', 'wc_wishlist' ), $product_data->get_title() ), 'error' );

					return false;
				}

				if ( $product_data->get_type() == 'external' ) {
					WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( 'Please use the external site to purchase %s', 'wc_wishlist' ), $product_data->get_title() ), 'error' );

					return false;
				}

				if ( $product_data->is_sold_individually() ) {
					$in_cart_quantity = $check_cart_item_key ? WC()->cart->cart_contents[ $check_cart_item_key ]['quantity'] : 0;

					if ( $in_cart_quantity > 0 ) {
						WC_Wishlist_Compatibility::wc_add_notice( sprintf(
							'<a href="%s" class="button wc-forward">%s</a> %s', wc_get_cart_url(), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You cannot add another &quot;%s&quot; to your cart.', 'woocommerce' ), $product_data->get_title() )
						), 'error' );

						return false;
					}
				}


				$add_on_data['wishlist-data']['list'] = [
					'name'    => $wishlist_prefix,
					'value'   => $wishlist->id,
					'display' => get_the_title( $wishlist->id ),
					'price'   => false
				];

				$add_on_data['wishlist-data']['item'] = [
					'name'    => false,
					'value'   => $wishlist_item_key,
					'display' => false,
					'price'   => false
				];

				$add_on_data['wishlist-data']['customer'] = [
					'name'    => false,
					'value'   => $wishlist->get_wishlist_owner(),
					'display' => false,
					'price'   => false
				];

				$add_on_data = apply_filters( 'woocommerce_copy_cart_item_data', $add_on_data, (int) $wishlist_item['product_id'], $wishlist_item );

				// If it's a POST request, get the quantity of the item from the quantity input field, cart[$wishlist_item_key][qty] if it exists. Otherwise, use the quantity from the wishlist item.
				// If it's a GET request, use the quantity from the query string, otherwise use the quantity from the wishlist item.
				$quantity = $wishlist_item['quantity']; // Default fallback
				if ($input_type === INPUT_POST) {
					// phpcs:ignore WordPress.Security.NonceVerification.Missing
					if (isset($_POST['cart'][$wishlist_item_key]['qty'])) {
						// phpcs:ignore WordPress.Security.NonceVerification.Missing
						$quantity = sanitize_text_field(wp_unslash($_POST['cart'][$wishlist_item_key]['qty']));
					}
				} else {
					if (isset($_GET['quantity'])) {
						$quantity = sanitize_text_field(wp_unslash($_GET['quantity'])) ?? 1;
					}
				}

				// Ensure quantity is a positive integer
				$quantity = absint($quantity);

				$passed_validation = apply_filters( 'woocommerce_add_to_cart_from_wishlist_validation', true, $cart_item['product_id'], $quantity, $cart_item['variation_id'], $cart_item['variation'], $add_on_data );
				if ( $passed_validation && WC()->cart->add_to_cart( (int) $cart_item['product_id'], $quantity, $cart_item['variation_id'], $cart_item['variation'], $add_on_data ) ) {

					// Update the list item quantity.  This is new as of 2.2.11.  It helps with the UX since updating list quantities is not currently obvious.
					if ( $screen == 'edit' ) {
						WC_Wishlists_Wishlist_Item_Collection::update_item_quantity( $wishlist->id, $wishlist_item_key, $quantity );
					}

					if ( ! $suppress_messages ) {
						$message = __( 'Product successfully added to your cart.', 'wc_wishlist' );
						$message = apply_filters( 'wc_add_to_cart_message_html', $message, [ $cart_item['product_id'] => $quantity ], false ); // hacked by MRV
						WC_Wishlist_Compatibility::wc_add_notice( $message );
					}

					$result = self::get_add_to_cart_redirect_url( $wishlist->id );
				} else {
					WC_Wishlist_Compatibility::wc_add_notice( __( 'Unable to add product to the cart. Please try again', 'wc_wishlist' ), 'error' );
					$result = self::get_add_to_cart_redirect_url( $wishlist->id );
				}
			}
		}

		return $result;
	}

	private static function get_add_to_cart_redirect_url( $wishlist_id ) {
		$wishlist = new WC_Wishlists_Wishlist( $wishlist_id );

		if ( is_page( WC_Wishlists_Pages::get_page_id( 'edit-my-list' ) ) ) {
			$w_url = WC_Wishlists_Wishlist::get_the_url_edit( $wishlist->id );
		} else {
			$wishlist_sharing = $wishlist->get_wishlist_sharing();
			$w_url            = '';
			if ( $wishlist_sharing == 'Public' ) {
				$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id );
			} else if ( $wishlist_sharing == 'Shared' ) {
				if ( WC_Wishlists_User::get_wishlist_key() != $wishlist->get_wishlist_owner() ) {
					$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id, true );
				} else {
					$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id );
				}
			} else {
				$w_url = WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id );
			}
		}

		$c_url = apply_filters( 'add_to_cart_from_wishlist_redirect_url', false );
		// If has custom URL redirect there
		if ( $c_url ) {
			$result = $c_url;
		} else {
			$result = $w_url;
		}

		return $result;
	}


	public static function clear_session_items() {
		WC_Wishlist_Compatibility::WC()->session->set( '_wishlist_items', false );
		return true;
	}


}
