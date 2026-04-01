<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ACF_To_REST_API_Options_Controller' ) ) {
	class ACF_To_REST_API_Options_Controller extends ACF_To_REST_API_Controller {
		public function __construct() {
			$this->type      = 'option';
			$this->rest_base = 'options';
			parent::__construct();
		}

		public function register_routes() {
			register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\w\-\_]+)/?(?P<field>[\w\-\_]+)?', array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
				),
			) );
		}

		/**
		 * Check permission for reading options.
		 * Options require manage_options capability as they often contain sensitive configuration.
		 *
		 * @since 3.3.5
		 * @return bool
		 */
		protected function check_read_permission() {
			if ( $this->allow_public_access() ) {
				return true;
			}
			return current_user_can( 'manage_options' );
		}

		/**
		 * Check permission for updating options.
		 *
		 * @since 3.3.5
		 * @param WP_REST_Request $request
		 * @return bool
		 */
		public function update_item_permissions_check( $request ) {
			return apply_filters( 'acf/rest_api/item_permissions/update', current_user_can( 'manage_options' ), $request, $this->type );
		}

	}
}
