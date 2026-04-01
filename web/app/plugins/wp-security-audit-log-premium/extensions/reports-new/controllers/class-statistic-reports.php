<?php
/**
 * Controller: Reports.
 *
 * Reports entity class.
 *
 * @package wsal
 */

declare(strict_types=1);

namespace WSAL\Reports\Controllers;

use WSAL\Helpers\WP_Helper;
use WSAL\Writers\CSV_Writer;
use WSAL\MainWP\MainWP_Addon;
use WSAL\Writers\HTML_Writer;
use WSAL\Entities\Base_Fields;
use WSAL\MainWP\MainWP_Helper;
use WSAL\Controllers\Connection;
use WSAL\Helpers\Settings_Helper;
use WSAL\Entities\Metadata_Entity;
use WSAL\Extensions\Views\Reports;
use WSAL\Entities\Occurrences_Entity;
use WSAL\Entities\Generated_Reports_Entity;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Reports\Controllers\Statistic_Reports' ) ) {

	/**
	 * Responsible for the reports storage.
	 */
	class Statistic_Reports {

		public const REPORT_PERIOD = array(
			'day'   => 'day',
			'week'  => 'week',
			'month' => 'month',
		);

		public const REPORT_TYPES = array(
			'logins_all_users', // Number of logins for all users.
			'newly_registered_users', // Number of newly registered users.
			'logins_for_users', // Number of logins for user(s).
			'logins_for_roles', // Number of logins for users with the role(s) of.
			'profile_changes', // Number of profile changes for all users.
			'profile_changes_users', // Number of profile changes for user(s).
			'profile_changes_roles', // Number of profile changes for users with the role(s) of.
			'views_posts', // Number of views for all posts.
			'views_posts_users', // Number of views for user(s).
			'views_posts_roles', // Number of views for users with the role(s) of.
			'views_specific_post', // Number of views for a specific post.
			'published_by_all_users', // Number of published content for all users.
			'published_by_users', // Number of published content for user(s).
			'published_by_roles', // Number of published content for users with the role(s) of.
			'password_changes_and_resets', // User password changes and password resets.
			'ips_for_users', // Different IP addresses for Usernames.
			'ips_accessed', // List of IP addresses that accessed the website.
			'users_accessed', // List of users who accessed the website.
			'last_login_date_time_all_users', // Last login date and time for all users.
			'last_login_date_time_roles', // Last login date and time for users with the role(s) of.
		);

		/**
		 * Keeps status of the report (empty or not). To be used only when first wave is in place, can not be relied otherwise.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $empty_report = false;

		/**
		 * Checks and validates the data for the reports provided and stores or creates the report.
		 *
		 * @param array $report_options - The array with all the data provided.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function prepare_and_generate_report( array $report_options ) {

			$report_type = isset( $report_options['statistic_report_type'] ) ? $report_options['statistic_report_type'] : 'logins_all_users';

			$period = ( isset( $report_options['time_format'] ) ? $report_options['time_format'] : '' );

			if ( ! \in_array( $period, self::REPORT_PERIOD, true ) ) {
				$period = 'day';
			}

			if ( ! \in_array( $report_type, self::REPORT_TYPES, \true ) ) {
				$report_type = 'logins_all_users';
			}

			$report_options['report_tag'] = ( array_key_exists( 'statistic_report_tag', $report_options ) ) ? \sanitize_text_field( \wp_unslash( $report_options['statistic_report_tag'] ) ) : '';

			$report_filters = array();

			// Set Filters normalization - by type, and if type requires it - the additional filters if they are provided.
			$report_filters[ esc_html__( 'Report Type', 'wp-security-audit-log' ) ] = self::get_statistical_report_title()[ $report_type ] . self::get_additional_report_filters_data( $report_options );

			$report_filters['no_meta'] = false;

			$default_alerts = array( 1000 );

			if ( isset( self::get_report_type_event_ids()[ $report_type ] ) ) {
				$default_alerts = self::get_report_type_event_ids()[ $report_type ];
				if ( 'ips_for_users' === $report_type && ( isset( $report_options['statistic_report_login_ips_only'] ) && true === (bool) $report_options['statistic_report_login_ips_only'] ) ) {
					$default_alerts = array( 1000 );
				}
			}

			$grouping = array(
				'site_id' => 'site_id',
				'period' => 'period',
				'user' => 'user',
			);

			$ordering = array(
				'site_id' => 'site_id',
				'period' => 'period DESC',
				'user' => 'user',
			);

			$select_fields = array(
				'occ.created_on',
				'site_id',
				'period' => 'DATE_FORMAT( FROM_UNIXTIME( occ.created_on ), "%Y-%m-%d" ) AS period',
				'DATE_FORMAT( FROM_UNIXTIME( occ.created_on ), "%Y-%u" ) AS week',
				'DATE_FORMAT( FROM_UNIXTIME( occ.created_on ), "%Y-%m" ) AS month',
				'IF ( occ.user_id>0, occ.user_id, occ.username ) as user',
				'COUNT(*) as count',
				'GROUP_CONCAT(id SEPARATOR ",") as ids',
				'GROUP_CONCAT(alert_id SEPARATOR ",") as events',
				'post_id as post',
				'alert_id',
				'username',
			);

			if ( false !== \strpos( $report_type, 'time' ) ) {
				$select_fields['period'] = 'DATE_FORMAT( FROM_UNIXTIME( occ.created_on ), "%Y-%m-%d %h:%i:%s" ) AS period';
			}

			$having        = '';
			$search_string = '';

			if ( 0 === strpos( $report_type, 'ip' ) || 'users_accessed' === $report_type ) {
				$grouping = array(
					'site_id' => 'site_id',
					'period' => 'period',
					'client_ip' => 'client_ip',
					// 'created_on',
				);

				$ordering = array(
					'site_id' => 'site_id',
					'period' => 'period DESC',
					'client_ip' => 'client_ip',
					// 'created_on DESC',
				);

				$select_fields = array(
					'occ.created_on',
					'DATE_FORMAT( FROM_UNIXTIME( created_on ), "%Y-%m-%d" ) AS period',
					'DATE_FORMAT( FROM_UNIXTIME( occ.created_on ), "%Y-%u" ) AS week',
					'DATE_FORMAT( FROM_UNIXTIME( occ.created_on ), "%Y-%m" ) AS month',
					'site_id',
					'client_ip',
					'IF ( occ.user_id>0, occ.user_id, occ.username ) as user',
					'username',
				);

				$having = ' HAVING user IS NOT NULL AND user NOT IN ( \'Unregistered user\', \'Plugins\', \'Plugin\') ';

				if ( empty( $default_alerts ) ) {
					$search_string = ' 1 = 1 ';
				}
			}

			foreach ( $default_alerts as $alert_id ) {
				$search_string .= ' alert_id:' . $alert_id;
			}

			if ( ! empty( $default_alerts ) ) {
				$report_filters[ esc_html__( 'Report Alerts', 'wp-security-audit-log' ) ] = \implode( ', ', $default_alerts );
			}

			if ( isset( $report_options['statistic_posts_select'] ) && \is_array( $report_options['statistic_posts_select'] ) ) {
				foreach ( $report_options['statistic_posts_select'] as $post_id ) {
					$search_string .= ' post_id:' . $post_id;
				}
			}

			if ( isset( $report_options['statistic_users_select'] ) && \is_array( $report_options['statistic_users_select'] ) ) {
				foreach ( $report_options['statistic_users_select'] as $user_id ) {
					$search_string .= ' user_id:' . $user_id;
				}
			}

			if ( isset( $report_options['statistic_roles_select'] ) && \is_array( $report_options['statistic_roles_select'] ) ) {
				foreach ( $report_options['statistic_roles_select'] as $user_role ) {
					$search_string .= ' user_role:' . $user_role;
				}
			}

			// Date processing.
			if ( isset( $report_options['report_start_date'] ) && ! empty( $report_options['report_start_date'] ) ) {
				$search_string .= ' start_date:' . $report_options['report_start_date'];
				$report_filters[ esc_html__( 'Start date', 'wp-security-audit-log' ) ] = $report_options['report_start_date'];
			}

			if ( isset( $report_options['report_end_date'] ) && ! empty( $report_options['report_end_date'] ) ) {
				$search_string .= ' end_date:' . $report_options['report_end_date'];
				$report_filters[ esc_html__( 'End date', 'wp-security-audit-log' ) ] = $report_options['report_end_date'];
			}

			// Include archive.
			$report_options['statistic_report_include_archive'] = ( array_key_exists( 'statistic_report_include_archive', $report_options ) ) ? filter_var( $report_options['statistic_report_include_archive'], FILTER_VALIDATE_BOOLEAN ) : false;

			if ( $report_options['statistic_report_include_archive'] ) {
				$report_options['statistic_report_only_archive'] = ( array_key_exists( 'statistic_report_only_archive', $report_options ) ) ? filter_var( $report_options['statistic_report_only_archive'], FILTER_VALIDATE_BOOLEAN ) : false;
			} else {
				$report_options['statistic_report_only_archive'] = false;
			}

			$wsal_db = null;

			if ( ! isset( $report_options['use_archive_db'] ) ) {
				$report_options['use_archive_db'] = false;
			}

			if ( isset( $report_options['statistic_report_include_archive'] ) ) {

				if ( ( true === (bool) $report_options['statistic_report_include_archive'] && Settings_Helper::is_archiving_enabled() && $report_options['statistic_report_only_archive'] ) || ( true === (bool) $report_options['statistic_report_include_archive'] && Settings_Helper::is_archiving_enabled() && $report_options['use_archive_db'] ) ) {

					$connection_name = Settings_Helper::get_option_value( 'archive-connection' );

					$wsal_db = Connection::get_connection( $connection_name );
				}
			}

			$where_clause = Base_Fields::string_to_search( $search_string );

			$sql = 'SELECT ' . implode( ',', $select_fields ) . ' FROM ' . Occurrences_Entity::get_table_name( $wsal_db ) . ' AS occ ';

			$sql .= ' WHERE ' . $where_clause;

			// Time to fix the grouping part.
			if ( 'day' !== $period ) {
				$grouping['period'] = $period;
				$ordering['period'] = $period . ' DESC ';
			}

			// if ( "views_specific_post" === $report_type ) {
			// 	unset( $grouping['user'] );
			// 	unset( $ordering['user'] );

			// 	$grouping['post_id'] = 'post_id';
			// 	$ordering['post_id'] = 'post_id';
			// }

			$sql .= ' GROUP BY ' . implode( ',', $grouping );

			$sql .= $having; // Some of the reports need HAVING section - but that is a small part of them.

			$sql .= ' ORDER BY ' . implode( ',', $ordering );

			$records = Reports::REPORT_LIMIT;

			$report_settings = Settings_Helper::get_option_value( Reports::REPORT_GENERATE_COLUMNS_SETTINGS_NAME, array() );

			if ( isset( $report_settings['cron_records_to_process'] ) ) {
				$records = (int) $report_settings['cron_records_to_process'];
				if ( 10 > $records || 10000 < $records ) {
					$records = Reports::REPORT_LIMIT;
				}
			}

			$user_id = \get_current_user_id();
			if ( ! $user_id ) {
				$username = __( 'System', 'wp-security-audit-log' );
			} else {
				$username = \get_userdata( $user_id )->user_login;
			}

			$occurrences = Occurrences_Entity::load_query( $sql . ' LIMIT ' . $records, $wsal_db );

			if ( empty( $occurrences ) ) {

				self::$empty_report = true;

				return;
			}

			$header_columns = self::get_report_headers( $period );

			$header_columns = $header_columns[ $report_type ];

			\add_filter(
				'wsal_reports_columns_settings',
				function ( $columns ) use ( $header_columns ) {
					$return_columns = array();

					foreach ( $header_columns as $key => $column ) {
						$return_columns[ $key ] = true;
					}

					return $return_columns;
				}
			);

			Reports::clear_reps();

			$first_date = 0;

			$first_event = Occurrences_Entity::build_query(
				array(),
				array(),
				array( 'created_on' => 'DESC' ),
				array( 1 ),
				array(),
				$wsal_db
			);

			if ( ! empty( $first_event ) ) {
				$first_date = $first_event[0]['created_on'];
			}

			Reports::normalize_columns( $occurrences );

			if ( 'newly_registered_users' === $report_type ) {
				self::normalize_roles( $occurrences, $wsal_db );
			}

			$finished = false;

			$report_options['limit_to_start'] = count( $occurrences );

			if ( $records > count( $occurrences ) ) {
				$finished                         = true;
				$report_options['limit_to_start'] = 0;
				if ( isset( $report_options['statistic_report_include_archive'] ) ) {
					if ( true === (bool) $report_options['statistic_report_include_archive'] && Settings_Helper::is_archiving_enabled() && ! $report_options['use_archive_db'] ) {
						if ( ! $report_options['statistic_report_only_archive'] ) {
							$report_options['use_archive_db'] = true;
							$finished                         = false;
						} else {
							$report_options['archive_db_only'] = true;
						}
					}
				}
			}

			$file_name = Reports::generate_report_filename();

			CSV_Writer::set_header_columns( $header_columns );

			CSV_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.csv' );

			CSV_Writer::write_csv( 1, $occurrences );

			HTML_Writer::set_header_columns( $header_columns );
			HTML_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.html' );
			HTML_Writer::write_html( 1, $occurrences, $report_filters );

			$report_filters['sql']            = $sql;
			$report_filters['statistic_type'] = $report_type;

			$data = array(
				'generated_report_user_id'            => $user_id,
				'generated_report_username'           => $username,
				'generated_report_filters'            => $report_options,
				'generated_report_filters_normalized' => $report_filters,
				'generated_report_header_columns'     => $header_columns,
				'generated_report_where_clause'       => $where_clause,
				'generated_report_finished'           => $finished,
				'generated_report_number_of_records'  => (int) count( $occurrences ),
				'generated_report_name'               => $file_name,
				'generated_report_to_date'            => $first_date,
				'generated_report_tag'                => $report_options['report_tag'],
				'generated_report_format'             => 999,
			);

			Generated_Reports_Entity::save( $data );
		}

		/**
		 * Returns the header for the given report based on type of the report and the selected period.
		 *
		 * @param string $period - The selected report period.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_report_headers( string $period ): array {
			$result = array();

			if ( ! \in_array( $period, self::REPORT_PERIOD, true ) ) {
				$period = 'day';
			}

			// Period grouping column will be always first.
			$result['period'] = esc_html__( 'Day', 'wp-security-audit-log' );
			if ( ! is_null( $period ) ) {
				switch ( $period ) {
					case 'day':
						$result['period'] = esc_html__( 'Day', 'wp-security-audit-log' );
						break;
					case 'week':
						$result['week'] = esc_html__( 'Week', 'wp-security-audit-log' );
						break;
					case 'month':
						$result['month'] = esc_html__( 'Month', 'wp-security-audit-log' );
						break;
				}
			}

			$headers = array(
				'logins_all_users'               => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Number of logins', 'wp-security-audit-log' ),
				), // Number of logins for all users.
				'newly_registered_users'         => array(
					'count' => esc_html__( 'Total', 'wp-security-audit-log' ),
				), // Number of newly registered users.
				'logins_for_users'               => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Number of logins', 'wp-security-audit-log' ),
				), // Number of logins for user(s).
				'logins_for_roles'               => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Number of logins', 'wp-security-audit-log' ),
				), // Number of logins for users with the role(s) of.
				'profile_changes'                => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'events'         => esc_html__( 'Events IDs', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Count', 'wp-security-audit-log' ),
				), // Number of profile changes for all users.
				'profile_changes_users'          => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'events'         => esc_html__( 'Events IDs', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Count', 'wp-security-audit-log' ),
				), // Number of profile changes for user(s).
				'profile_changes_roles'          => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'events'         => esc_html__( 'Events IDs', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Count', 'wp-security-audit-log' ),
				), // Number of profile changes for users with the role(s) of.
				'views_posts'                    => array(
					'post'  => esc_html__( 'Post title', 'wp-security-audit-log' ),
					'count' => esc_html__( 'Views', 'wp-security-audit-log' ),
				), // Number of views for all posts.
				'views_posts_users'              => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'post'           => esc_html__( 'Post title', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Views', 'wp-security-audit-log' ),
				), // Number of views for user(s).
				'views_posts_roles'              => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'post'           => esc_html__( 'Post title', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Views', 'wp-security-audit-log' ),
				), // Number of views for users with the role(s) of.
				'views_specific_post'            => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Views', 'wp-security-audit-log' ),
				), // Number of views for a specific post.
				'published_by_all_users'         => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Published', 'wp-security-audit-log' ),
				), // Number of published content for all users.
				'published_by_users'             => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Published', 'wp-security-audit-log' ),
				), // Number of published content for user(s).
				'published_by_roles'             => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Published', 'wp-security-audit-log' ),
				), // Number of published content for users with the role(s) of.
				'password_changes_and_resets'    => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
					'events'         => esc_html__( 'Event', 'wp-security-audit-log' ),
					'count'          => esc_html__( 'Count', 'wp-security-audit-log' ),
				), // User password changes and password resets.
				'ips_for_users'                  => array(
					'client_ip'      => esc_html__( 'List of IP addresses', 'wp-security-audit-log' ),
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
				), // Different IP addresses for Usernames.
				'ips_accessed'                   => array(
					'client_ip' => esc_html__( 'List of IP addresses', 'wp-security-audit-log' ),
				), // List of IP addresses that accessed the website.
				'users_accessed'                 => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
				), // List of users who accessed the website.
				'last_login_date_time_all_users' => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
				), // Last login date and time for all users.
				'last_login_date_time_roles'     => array(
					'username'       => esc_html__( 'Username', 'wp-security-audit-log' ),
					'display_name'   => esc_html__( 'Display name', 'wp-security-audit-log' ),
					'first_and_last' => esc_html__( 'First & last name', 'wp-security-audit-log' ),
					'user_email'     => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
				), // Last login date and time for users with the role(s) of.
			);

			if ( ! function_exists( '\get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$roles = \get_editable_roles();
			// Add the role names to the newly registered users header.
			foreach ( $roles as $role_name => $info ) {
				$headers['newly_registered_users'][ $role_name ] = $info['name'];
			}

			foreach ( $headers  as $type => $columns ) {
				$headers[ $type ] = \array_merge( $result, $columns );
			}

			if ( WP_Helper::is_multisite() ) {
				$site =
					array(
						'site_id' => esc_html__( 'Blog name', 'wp-security-audit-log' ),
					);
				foreach ( $headers  as $type => $columns ) {
					$headers[ $type ] = $site + $columns;
				}
			}

			return $headers;
		}

		/**
		 * Returns array with all of the event IDs associated with the given report type.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_report_type_event_ids(): array {
			return array(
				'logins_all_users'               => array( 1000, 1005 ), // Number of logins for all users.
				'newly_registered_users'         => array( 4000, 4001 ), // Number of newly registered users.
				'logins_for_users'               => array( 1000, 1005 ), // Number of logins for user(s).
				'logins_for_roles'               => array( 1000, 1005 ), // Number of logins for users with the role(s) of.
				'last_login_date_time_all_users' => array( 1000 ), // Last login date and time for all users.
				'last_login_date_time_roles'     => array( 1000 ), // Last login date and time for users with the role(s) of.
				'profile_changes'                => array(
					4000,
					4001,
					4002,
					// 4003,
					// 4004,
					4005,
					4006,
					4007,
					// 4014,
					4015,
					4016,
					4017,
					4018,
					4019,
					4020,
					4021,
					// 4025,
					// 4026,
					// 4027,
					// 4028,
					// 4029,
					4008,
					4009,
					4010,
					4011,
					4012,
					4013,
					4024,
				), // Number of profile changes for all users.
				'profile_changes_users'          => array(
					4000,
					4001,
					4002,
					// 4003,
					// 4004,
					4005,
					4006,
					4007,
					// 4014,
					4015,
					4016,
					4017,
					4018,
					4019,
					4020,
					4021,
					// 4025,
					// 4026,
					// 4027,
					// 4028,
					// 4029,
					4008,
					4009,
					4010,
					4011,
					4012,
					4013,
					4024,
				), // Number of profile changes for user(s).
				'profile_changes_roles'          => array(
					4000,
					4001,
					4002,
					// 4003,
					// 4004,
					4005,
					4006,
					4007,
					// 4014,
					4015,
					4016,
					4017,
					4018,
					4019,
					4020,
					4021,
					// 4025,
					// 4026,
					// 4027,
					// 4028,
					// 4029,
					4008,
					4009,
					4010,
					4011,
					4012,
					4013,
					4024,
				), // Number of profile changes for users with the role(s) of.
				'views_posts'                    => array( 2101 ), // Number of views for all posts.
				'views_posts_users'              => array( 2101 ), // Number of views for user(s).
				'views_posts_roles'              => array( 2101 ), // Number of views for users with the role(s) of.
				'views_specific_post'            => array( 2101 ), // Number of views for a specific post.
				'published_by_all_users'         => array( 2001, 2030, 9001 ), // Number of published content for all users.
				'published_by_users'             => array( 2001, 2030, 9001 ), // Number of published content for user(s).
				'published_by_roles'             => array( 2001, 2030, 9001 ), // Number of published content for users with the role(s) of.
				'password_changes_and_resets'    => array( 1010, 4003, 4004, 4029 ), // User password changes and password resets.
				'ips_for_users'                  => array(), // Different IP addresses for Usernames.
				'ips_accessed'                   => array(), // List of IP addresses that accessed the website.
				'users_accessed'                 => array(), // List of users who accessed the website.
			);
		}

		/**
		 * Gets the title for a statistical report.\
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_statistical_report_title() {
			return array(
				'logins_all_users'               => esc_html__( 'Number of Logins per user', 'wp-security-audit-log' ), // Number of logins for all users.
				'newly_registered_users'         => esc_html__( 'Number of newly registered users', 'wp-security-audit-log' ), // Number of newly registered users.
				'logins_for_users'               => esc_html__( 'Number of Logins per user', 'wp-security-audit-log' ), // Number of logins for user(s).
				'logins_for_roles'               => esc_html__( 'Number of Logins per user', 'wp-security-audit-log' ), // Number of logins for users with the role(s) of.
				'profile_changes'                => esc_html__( 'Number of profile changes per user', 'wp-security-audit-log' ), // Number of profile changes for all users.
				'profile_changes_users'          => esc_html__( 'Number of profile changes per user', 'wp-security-audit-log' ), // Number of profile changes for user(s).
				'profile_changes_roles'          => esc_html__( 'Number of profile changes per user', 'wp-security-audit-log' ), // Number of profile changes for users with the role(s) of.
				'views_posts'                    => esc_html__( 'Number of viewed posts per user', 'wp-security-audit-log' ), // Number of views for all posts.
				'views_posts_users'              => esc_html__( 'Number of viewed posts per user', 'wp-security-audit-log' ), // Number of views for user(s).
				'views_posts_roles'              => esc_html__( 'Number of viewed posts per user', 'wp-security-audit-log' ), // Number of views for users with the role(s) of.
				'views_specific_post'            => esc_html__( 'Number of post views per user', 'wp-security-audit-log' ), // Number of views for a specific post.
				'published_by_all_users'         => esc_html__( 'Number of published posts per user', 'wp-security-audit-log' ), // Number of published content for all users.
				'published_by_users'             => esc_html__( 'Number of published posts per user', 'wp-security-audit-log' ), // Number of published content for user(s).
				'published_by_roles'             => esc_html__( 'Number of published posts per user', 'wp-security-audit-log' ), // Number of published content for users with the role(s) of.
				'password_changes_and_resets'    => esc_html__( 'Number of password changes and password resets per user', 'wp-security-audit-log' ), // User password changes and password resets.
				'ips_for_users'                  => esc_html__( 'List of unique IP addresses per user', 'wp-security-audit-log' ), // Different IP addresses for Usernames.
				'ips_accessed'                   => esc_html__( 'List of IP addresses that accessed the website', 'wp-security-audit-log' ), // List of IP addresses that accessed the website.
				'users_accessed'                 => esc_html__( 'List of users that accessed the website', 'wp-security-audit-log' ), // List of users who accessed the website.
				'last_login_date_time_all_users' => esc_html__( 'Last login date and time for all users', 'wp-security-audit-log' ), // Last login date and time for all users.
				'last_login_date_time_roles'     => esc_html__( 'Last login date and time for users with the role(s) of', 'wp-security-audit-log' ), // Last login date and time for users with the role(s) of.
			);
		}

		/**
		 * Uses legacy report type (ids) and returns new report types array.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_statistical_report_from_legacy_type(): array {
			return array(
				10 => 'logins_all_users', // Number of logins for all users.
				75 => 'newly_registered_users', // Number of newly registered users.
				1  => 'logins_for_users', // Number of logins for user(s).
				2  => 'logins_for_roles', // Number of logins for users with the role(s) of.
				70 => 'profile_changes', // Number of profile changes for all users.
				71 => 'profile_changes_users', // Number of profile changes for user(s).
				72 => 'profile_changes_roles', // Number of profile changes for users with the role(s) of.
				20 => 'views_posts', // Number of views for all posts.
				3  => 'views_posts_users', // Number of views for user(s).
				4  => 'views_posts_roles', // Number of views for users with the role(s) of.
				25 => 'views_specific_post', // Number of views for a specific post.
				30 => 'published_by_all_users', // Number of published content for all users.
				5  => 'published_by_users', // Number of published content for user(s).
				6  => 'published_by_roles', // Number of published content for users with the role(s) of.
				60 => 'password_changes_and_resets', // User password changes and password resets.
				40 => 'ips_for_users', // Different IP addresses for Usernames.
				50 => 'ips_accessed', // List of IP addresses that accessed the website.
				7  => 'users_accessed', // List of users who accessed the website.
			);
		}

		/**
		 * Collects the data for the user roles (when report is for newly registered users).
		 *
		 * @param array $occurrences - The array with all of the occurrences (from the select query) that are collected for the report.
		 * @param \wpdb $connection - The connection to be used to ping the proper meta data table.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function normalize_roles( array &$occurrences, $connection = null ): array {

			if ( ! function_exists( '\get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$roles = \get_editable_roles();

			$role_to_check = array_key_first( $roles );

			foreach ( $occurrences as &$row ) {
				if ( ! isset( $row[ $role_to_check ] ) ) {
					$row = array_merge( array_fill_keys( array_keys( $roles ), 0 ), $row );
				}

				$records = Metadata_Entity::get_user_data_by_occ_ids( $row['ids'], $connection );

				$item          = array();
				$item['roles'] = array();

				foreach ( $records as $role ) {
					$roles_obj = isset( $role['value'] ) ? maybe_unserialize( $role['value'] ) : false;
					if ( isset( $roles_obj->Roles ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$item['roles'] = \array_merge( $item['roles'], (array) $roles_obj->Roles ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					} else {
						$user          = \get_userdata( intval( $roles_obj ) );
						$item['roles'] = \array_merge( $item['roles'], $user->roles );
					}
				}

				$row = array_merge( $row, array_count_values( $item['roles'] ) );
			}

			return $occurrences;
		}

		/**
		 * Generates the cron reports
		 *
		 * @param array $report - The array with all of the collected report data.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function cron_report( array $report ) {

			$wsal_db = null;

			$records = Reports::REPORT_LIMIT;

			$report_settings = Settings_Helper::get_option_value( Reports::REPORT_GENERATE_COLUMNS_SETTINGS_NAME, array() );

			if ( isset( $report_settings['cron_records_to_process'] ) ) {
				$records = (int) $report_settings['cron_records_to_process'];
				if ( 10 > $records || 10000 < $records ) {
					$records = Reports::REPORT_LIMIT;
				}
			}

			$report['generated_report_filters_normalized'] = \json_decode( $report['generated_report_filters_normalized'], \true );
			$report['generated_report_filters']            = \json_decode( $report['generated_report_filters'], \true );

			$sql = $report['generated_report_filters_normalized']['sql'];

			$sql = explode( ' GROUP BY ', $sql );

			$first_date = $report['generated_report_to_date'];

			$sql[0] .= ' AND created_on <= ' . $first_date;

			$sql = \implode( ' GROUP BY ', $sql );

			$report_type = $report['generated_report_filters_normalized']['statistic_type'];

			$report_options = $report['generated_report_filters'];

			$report_filters = $report['generated_report_filters_normalized'];

			$user_id = $report['generated_report_user_id'];

			$username = $report['generated_report_username'];

			$where_clause = $report['generated_report_where_clause'];

			$report_id = $report['id'];

			if ( ! isset( $report_options['use_archive_db'] ) ) {
				$report_options['use_archive_db'] = false;
			}

			if ( isset( $report_options['statistic_report_include_archive'] ) ) {
				if ( ( true === (bool) $report_options['statistic_report_include_archive'] && Settings_Helper::is_archiving_enabled() && $report_options['statistic_report_only_archive'] ) || ( true === (bool) $report_options['statistic_report_include_archive'] && Settings_Helper::is_archiving_enabled() && $report_options['use_archive_db'] ) ) {
					Settings_Helper::switch_to_archive_db();

					if ( Connection::is_archive_mode() ) {
						$connection_name = Settings_Helper::get_option_value( 'archive-connection' );

						$wsal_db = Connection::get_connection( $connection_name );
					}
				}
			}

			$sql                      = explode( ' FROM ', $sql );
			$table_name_removal_array = explode( ' AS ', $sql[1] );
			$sql[1]                   = ' ' . Occurrences_Entity::get_table_name( $wsal_db ) . ' AS ' . $table_name_removal_array[1];

			$sql = \implode( ' FROM ', $sql );

			$limit_start = $report_options['limit_to_start'];

			$occurrences = Occurrences_Entity::load_query( $sql . ' LIMIT ' . $limit_start . ', ' . $records, $wsal_db );

			$header_columns = self::get_report_headers( ( isset( $report_options['time_format'] ) ? $report_options['time_format'] : '' ) );

			$header_columns = $header_columns[ $report_type ];

			\add_filter(
				'wsal_reports_columns_settings',
				function ( $columns ) use ( $header_columns ) {
					$return_columns = array();

					foreach ( $header_columns as $key => $column ) {
						$return_columns[ $key ] = true;
					}

					return $return_columns;
				}
			);

			Reports::clear_reps();

			Reports::normalize_columns( $occurrences );

			if ( 'newly_registered_users' === $report_type ) {
				self::normalize_roles( $occurrences, $wsal_db );
			}

			$finished = false;

			$report_options['limit_to_start'] = (int) ( $report['generated_report_number_of_records'] ) + count( $occurrences );

			if ( $records > count( $occurrences ) ) {
				$finished                         = true;
				$report_options['limit_to_start'] = 0;
				if ( isset( $report_options['statistic_report_include_archive'] ) ) {
					if ( true === (bool) $report_options['statistic_report_include_archive'] && Settings_Helper::is_archiving_enabled() && ! $report_options['use_archive_db'] ) {
						if ( ! $report_options['report_only_archive'] ) {
							$report_options['use_archive_db'] = true;
							$finished                         = false;
						} else {
							$report_options['archive_db_only'] = true;
						}
					}
				}
			}

			$file_name = $report['generated_report_name'];

			CSV_Writer::set_header_columns( $header_columns );

			CSV_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.csv' );

			CSV_Writer::write_csv( 2, $occurrences );

			HTML_Writer::set_header_columns( $header_columns );
			HTML_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.html' );
			HTML_Writer::write_html( 2, $occurrences, $report_filters );

			if ( $finished ) {
				// Thats the end of the report - close the HTML file.

				HTML_Writer::set_footer();
			}

			$data = array(
				'generated_report_user_id'            => $user_id,
				'generated_report_username'           => $username,
				'generated_report_filters'            => $report_options,
				'generated_report_filters_normalized' => $report_filters,
				'generated_report_header_columns'     => $header_columns,
				'generated_report_where_clause'       => $where_clause,
				'generated_report_finished'           => $finished,
				'generated_report_number_of_records'  => (int) ( $report['generated_report_number_of_records'] ) + count( $occurrences ),
				'generated_report_name'               => $file_name,
				'generated_report_to_date'            => $first_date,
				'generated_report_tag'                => $report_options['report_tag'],
				'generated_report_format'             => 999,
				'id'                                  => $report_id,
			);

			Generated_Reports_Entity::save( $data );
		}

		/**
		 * Returns the stored value in clas empty_report
		 *
		 * @return bool
		 *
		 * @since 5.0.0
		 */
		public static function get_empty_report() {
			return self::$empty_report;
		}

		/**
		 * Returns additional selected info for the report based on its type.
		 *
		 * @param array $report_options - Array with the selected report options.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		private static function get_additional_report_filters_data( array $report_options ): string {
			$ret_val = '';

			if ( str_ends_with( $report_options['statistic_report_type'], '_users' ) && 'ips_for_users' !== $report_options['statistic_report_type'] ) {
				if ( isset( $report_options['statistic_users_select'] ) && ! empty( $report_options['statistic_users_select'] ) && \is_array( $report_options['statistic_users_select'] ) ) {
					foreach ( $report_options['statistic_users_select'] as $user_id ) {
						$user = \get_user_by( 'id', (int) $user_id );
						if ( is_a( $user, '\WP_User' ) ) {
							$ret_val .= $user->user_login . ' (' . $user->user_email . '), ';
						}
						if ( empty( $user ) && ( MainWP_Addon::check_mainwp_plugin_active() ) ) {
							$user     = reset( MainWP_Helper::find_users_by( array( 'ID' ), array( $user_id ) ) );
							$ret_val .= $user->user_login . ' (' . $user->user_email . '), ';
						}
					}

					$ret_val = \rtrim( $ret_val, ', ' );
				}
			}

			if ( str_ends_with( $report_options['statistic_report_type'], '_roles' ) ) {
				if ( isset( $report_options['statistic_roles_select'] ) && ! empty( $report_options['statistic_roles_select'] ) && \is_array( $report_options['statistic_roles_select'] ) ) {
					foreach ( $report_options['statistic_roles_select'] as $role ) {
						$role_names = WP_Helper::get_translated_roles();
						if ( isset( $role_names[ $role ] ) ) {
							$ret_val .= $role_names[ $role ] . ', ';
						}
					}

					$ret_val = \rtrim( $ret_val, ', ' );
				}
			}

			if ( str_ends_with( $report_options['statistic_report_type'], '_post' ) ) {
				if ( isset( $report_options['statistic_posts_select'] ) && ! empty( $report_options['statistic_posts_select'] ) && \is_array( $report_options['statistic_posts_select'] ) ) {
					foreach ( $report_options['statistic_posts_select'] as $post_id ) {
						$post = \get_post( (int) $post_id );
						if ( is_a( $post, '\WP_Post' ) ) {
							// $title = ( mb_strlen( $post->post_title ) > 50 ) ? mb_substr( $post->post_title, 0, 49 ) . '...' : $post->post_title;
							$title = $post->post_title;

							$ret_val .= $title . ', ';
						}
					}

					$ret_val = \rtrim( $ret_val, ', ' );
				}
			}

			return ( ! empty( $ret_val ) ) ? ' : ' . $ret_val : '';
		}
	}
}
