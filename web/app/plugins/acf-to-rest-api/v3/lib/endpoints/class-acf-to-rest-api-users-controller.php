<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ACF_To_REST_API_Users_Controller' ) ) {
	class ACF_To_REST_API_Users_Controller extends ACF_To_REST_API_Controller {
		public function __construct() {
			$this->type      = 'user';
			$this->rest_base = 'users';
			parent::__construct();
		}

		public function get_items( $request ) {
			$this->controller = new WP_REST_Users_Controller;
			return parent::get_items( $request );
		}

		/**
		 * Check permission for reading a specific user's ACF data.
		 * Users can read their own ACF fields, or need list_users capability for others.
		 *
		 * @since 3.3.5
		 * @param WP_REST_Request $request
		 * @return bool
		 */
		public function get_item_permissions_check( $request ) {
			if ( $this->allow_public_access() ) {
				$default = true;
			} else {
				$user_id = $request->get_param( 'id' );
				// Allow users to view their own data, or require list_users for others
				$default = is_user_logged_in() && ( get_current_user_id() == $user_id || current_user_can( 'list_users' ) );
			}
			return apply_filters( 'acf/rest_api/item_permissions/get', $default, $request, $this->type );
		}

		/**
		 * Check permission for reading multiple users' ACF data.
		 * Requires list_users capability.
		 *
		 * @since 3.3.5
		 * @param WP_REST_Request $request
		 * @return bool
		 */
		public function get_items_permissions_check( $request ) {
			if ( $this->allow_public_access() ) {
				$default = true;
			} else {
				$default = current_user_can( 'list_users' );
			}
			return apply_filters( 'acf/rest_api/items_permissions/get', $default, $request, $this->type );
		}

		/**
		 * Check permission for updating user ACF data.
		 *
		 * @since 3.3.5
		 * @param WP_REST_Request $request
		 * @return bool
		 */
		public function update_item_permissions_check( $request ) {
			$user_id = $request->get_param( 'id' );
			// Allow users to edit their own data, or require edit_user capability for others
			$default = current_user_can( 'edit_user', $user_id );
			return apply_filters( 'acf/rest_api/item_permissions/update', $default, $request, $this->type );
		}
	}
}
