<?php
/**
 * View: WSAL Reports
 *
 * WSAL setup class file.
 *
 * @since 5.1.0
 * @package    wsal
 * @subpackage views
 */

declare(strict_types=1);

namespace WSAL\Extensions\Helpers;

use WSAL\Helpers\WP_Helper;
use WSAL\Helpers\DateTime_Formatter_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( '\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters' ) ) {
	/**
	 * Class: WSAL reports.
	 *
	 * @package    wsal
	 * @subpackage views
	 *
	 * @since 5.0.0
	 */
	class Helper_Modifiers_Formatters {

		/**
		 * Holds report users by usernames local cache.
		 * The username is the key, the value is the id.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $report_users_username = array();

		/**
		 * Holds report users by ids local cache.
		 * The id is the key, the value is the username.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $report_users_id = array();

		/**
		 * Holds report users local cache.
		 * The users are stored by id as key and data array as value.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $report_users = array();

		/**
		 * Extracts and returns the correct time based on parameters.
		 *
		 * @param array  $row - The current row with values.
		 * @param string $type - What type of the date formatting.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_date_info( array $row, string $type ): string {
			if ( isset( $row['created_on'] ) ) {
				return DateTime_Formatter_Helper::get_formatted_date_time( $row['created_on'], $type );
			}

			return '';
		}

		/**
		 * Collects all the user info based on the type of the column. It could use user_id or username as a base. This method also uses internal caching mechanism of the users extracted, so it doesn't need to repeat calls to the user tables.
		 *
		 * @param array  $row - Current row to extract data from (if username or user_id is set).
		 * @param string $type - String representation of the type.
		 *
		 * @return mixed
		 *
		 * @since 5.0.0
		 */
		public static function get_user_info( $row, $type ) {
			if ( isset( $row['username'] ) ) {
				if ( 'Plugin' === $row['username'] ) {
					return __( 'Plugin', 'wp-security-audit-log' );
				} elseif ( 'Plugins' === $row['username'] ) {
					return __( 'Plugins', 'wp-security-audit-log' );
				} elseif ( 'Website Visitor' === $row['username'] ) {
					return __( 'Website Visitor', 'wp-security-audit-log' );
				} elseif ( 'System' === $row['username'] ) {
					return __( 'System', 'wp-security-audit-log' );
				} elseif ( 'WooCommerce System' === $row['username'] ) {
					return __( 'WooCommerce System', 'wp-security-audit-log' );
				}
			}

			$user = null;

			if ( isset( $row['username'] ) ) {
				if ( ! isset( self::$report_users_username[ $row['username'] ] ) ) {
					$user = \get_user_by( 'login', $row['username'] );
					if ( \is_a( $user, '\WP_User' ) ) {
						self::$report_users_username[ $row['username'] ] = $user->ID;
						self::$report_users[ $user->ID ]                 = array(
							'username'       => $user->user_login,
							'firstname'      => $user->user_firstname,
							'lastname'       => $user->user_lastname,
							'display_name'   => $user->display_name,
							'user_email'     => $user->user_email,
							'nicename'       => $user->user_nicename,
							'first_and_last' => $user->user_firstname . ' ' . $user->user_lastname,
						);
						$user = self::$report_users[ $user->ID ];
					}
				} else {
					$user = isset( self::$report_users[ self::$report_users_username[ $row['username'] ] ] ) ? self::$report_users[ self::$report_users_username[ $row['username'] ] ] : \null;
				}
			} elseif ( isset( $row['user_id'] ) ) {
				if ( ! isset( self::$report_users_id[ $row['user_id'] ] ) ) {
					$user = get_userdata( $row['user_id'] );
					if ( \is_a( $user, '\WP_User' ) ) {
						self::$report_users_username[ $row['user_id'] ] = $user->ID;
						self::$report_users[ $user->ID ]                = array(
							'username'       => $user->user_login,
							'firstname'      => $user->user_firstname,
							'lastname'       => $user->user_lastname,
							'display_name'   => $user->display_name,
							'user_email'     => $user->user_email,
							'nicename'       => $user->user_nicename,
							'first_and_last' => $user->user_firstname . ' ' . $user->user_lastname,
						);

						$user = self::$report_users[ $user->ID ];
					}
				} else {
					$user = isset( self::$report_users[ $row['user_id'] ] ) ? self::$report_users[ $row['user_id'] ] : null;
				}
			} elseif ( isset( $row['user'] ) ) {
				if ( false !== filter_var( $row['user'], FILTER_VALIDATE_INT ) ) {
					// Provided is an integer - lets check against the cache first or extract the info.
					if ( ! isset( self::$report_users_id[ $row['user'] ] ) ) {
						$user = \get_userdata( $row['user'] );
						if ( \is_a( $user, '\WP_User' ) ) {
							self::$report_users_username[ $row['user'] ] = $user->ID;
							self::$report_users[ $user->ID ]             = array(
								'username'       => $user->user_login,
								'firstname'      => $user->user_firstname,
								'lastname'       => $user->user_lastname,
								'display_name'   => $user->display_name,
								'user_email'     => $user->user_email,
								'nicename'       => $user->user_nicename,
								'first_and_last' => $user->user_firstname . ' ' . $user->user_lastname,
							);

							$user = self::$report_users[ $user->ID ];
						}
					} else {
						$user = isset( self::$report_users[ $row['user'] ] ) ? self::$report_users[ $row['user'] ] : null;
					}
				} elseif ( ! isset( self::$report_users_username[ $row['user'] ] ) ) { // Its a string - proceed.
					$user = \get_user_by( 'login', $row['user'] );
					if ( \is_a( $user, '\WP_User' ) ) {
						self::$report_users_username[ $row['user'] ] = $user->ID;
						self::$report_users[ $user->ID ]             = array(
							'username'       => $user->user_login,
							'firstname'      => $user->user_firstname,
							'lastname'       => $user->user_lastname,
							'display_name'   => $user->display_name,
							'user_email'     => $user->user_email,
							'nicename'       => $user->user_nicename,
							'first_and_last' => $user->user_firstname . ' ' . $user->user_lastname,
						);
						$user                                        = self::$report_users[ $user->ID ];
					}
				} else {
					$user = isset( self::$report_users[ self::$report_users_username[ $row['user'] ] ] ) ? self::$report_users[ self::$report_users_username[ $row['user'] ] ] : \null;
				}
			}

			if ( \is_null( $user ) ) {
				if ( 'username' === $type ) {
					return __( 'System', 'wp-security-audit-log' );
				} else {
					return '';
				}
			}

			$user = \apply_filters( 'wsal_get_user_details', $user, $row );

			if ( \is_bool( $user ) || ! isset( $user[ $type ] ) ) {
				return __( 'No user found or they have been deleted', 'wp-security-audit-log' );
			}

			return $user[ $type ];
		}

		/**
		 * Returns the post title
		 *
		 * @param int $row - The value of the post id.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_post_info( $row ): string {
			if ( ! empty( $row ) && 0 < (int) $row ) {
				$post = \get_post( $row );
				if ( ! is_null( $post ) && isset( $post ) && \is_object( $post ) ) {
					$title = ( mb_strlen( $post->post_title ) > 50 ) ? mb_substr( $post->post_title, 0, 49 ) . '...' : $post->post_title;

					return $title;
				} else {
					return $row . '(id)';
				}
			}

			return '';
		}

		/**
		 * Returns the unique event ids as string (comma separated)
		 *
		 * @param string $row - The collected events.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_events( $row ): string {
			if ( ! empty( $row ) ) {
				$events = \explode( ',', (string) $row );

				$events = \array_unique( \array_filter( $events ) );

				return implode( ', ', $events );
			}

			return '';
		}

		/**
		 * Returns the name of the blog
		 *
		 * @param int $row - The site id to extract the blog name from.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_blog_name_name_by_id( $row ): string {
			$blog_name = esc_html__( 'Unknown Site', 'wp-security-audit-log' );

			if ( ( ! empty( $row ) && 0 < (int) $row ) || 0 === (int) $row ) {
				$blog_info = WP_Helper::get_blog_info( (int) $row );

				return $blog_info['name'] . ' (' . $blog_info['url'] . ')';
			}

			return $blog_name;
		}
	}
}
