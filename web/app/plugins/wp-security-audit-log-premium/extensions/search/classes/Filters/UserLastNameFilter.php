<?php
/**
 * Filter: User Last Name Filter
 *
 * User last name filter for search.
 *
 * @since   1.1.7
 * @package wsal
 * @subpackage search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_UserLastNameFilter' ) ) :

	/**
	 * WSAL_AS_Filters_UserLastNameFilter.
	 *
	 * User last name filter class.
	 *
	 * @since 1.1.7
	 */
	class WSAL_AS_Filters_UserLastNameFilter extends WSAL_AS_Filters_AbstractUserAttributeFilter {

		/**
		 * {@inheritDoc}
		 */
		public function get_name() {
			return esc_html__( 'User' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_prefixes() {
			return array( 'lastname' );
		}

		/**
		 * {@inheritDoc}
		 */
		public function get_widgets() {
			return array( new WSAL_AS_Filters_UserLastNameWidget( $this, 'lastname', 'Last Name' ) );
		}

		/**
		 * {@inheritDoc}
		 */
		protected function get_users_for_lookup( $value ) {
			$users = array();
			foreach ( $value as $last_name ) {

				$args = array(
					'blog_id'    => 0,
					'meta_query' => array(
						array(
							'key'     => 'last_name',
							'value'   => $last_name,
							'compare' => 'REGEXP',
						),
					),
				);

				$users_array = get_users( $args );

				foreach ( $users_array as $user ) {
					$users[] = $user;
				}
			}
			return $users;
		}
	}

endif;
