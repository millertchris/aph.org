<?php
/**
 * Filter: User Role Filter
 *
 * User Role filter for search.
 *
 * @since 3.1
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_UserMailFilter' ) ) :

	/**
	 * WSAL_AS_Filters_UserMailFilter.
	 *
	 * User Role filter class.
	 *
	 * @since 3.1
	 */
	class WSAL_AS_Filters_UserMailFilter extends WSAL_AS_Filters_AbstractUserAttributeFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'User Email', 'wp-security-audit-log' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'usermail' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			// Intialize single select widget class.
			$widget = new WSAL_AS_Filters_UserMailWidget( $this, 'usermail', esc_html__( 'User Email', 'wp-security-audit-log' ) );

			return array( $widget );
		}

		/**
		 * Method: Get WP User roles.
		 *
		 * @return array
		 */
		protected function get_users_for_lookup( $value ) {
			$users = array();
			foreach ( $value as $username ) {
				$user = get_user_by( 'email', $username );

				if ( $user instanceof WP_User ) {
					array_push( $users, $user );
				}
			}

			return $users;
		}
	}

endif;
