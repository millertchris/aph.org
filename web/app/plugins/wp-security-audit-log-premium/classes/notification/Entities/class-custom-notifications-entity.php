<?php
/**
 * Adapter: Reports.
 *
 * Reports entity class.
 *
 * @package wsal
 *
 * @since 5.2.1
 */

declare(strict_types=1);

namespace WSAL\Entities;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Entities\Custom_Notifications_Entity' ) ) {

	/**
	 * Responsible for the reports storage.
	 */
	class Custom_Notifications_Entity extends Abstract_Entity {
// phpcs:disable
/* @premium:start */
// phpcs:enable
		/**
		 * Holds the DB records for the periodic reports
		 *
		 * @var \wpdb
		 *
		 * @since 5.2.1
		 */
		private static $connection = null;

		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 5.2.1
		 */
		protected static $table = 'wsal_custom_notifications';

		/**
		 * Builds an upgrade query for the notifications table.
		 *
		 * @return string
		 *
		 * @since 5.3.4
		 */
		public static function get_upgrade_query_slack_template() {
			return 'ALTER TABLE `' . self::get_table_name() . '`' .
			' ADD `notification_slack_template` LONGTEXT NOT NULL AFTER `notification_sms_template`,' .
			' ADD `notification_slack` TEXT NOT NULL AFTER `notification_phone`;';
		}


		/**
		 * Builds an upgrade query for the notifications table.
		 *
		 * @return string
		 *
		 * @since 5.4.0
		 */
		public static function get_upgrade_query_settings_field() {
			return 'ALTER TABLE `' . self::get_table_name() . '`' .
			' ADD `notification_settings` LONGTEXT NOT NULL AFTER `notification_username`;';
		}

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 5.2.1
		 */
		protected static $fields = array(
			'id'                          => 'bigint',
			'notification_user_id'        => 'bigint',
			'notification_username'       => 'varchar(60)',
			'notification_settings'       => 'longtext',
			'notification_title'          => 'text',
			'notification_email'          => 'text',
			'notification_email_bcc'      => 'text',
			'notification_email_user'     => 'tinyint',
			'notification_phone'          => 'text',
			'notification_slack'          => 'text',
			'notification_query'          => 'longtext',
			'notification_query_sql'      => 'longtext',
			'notification_template'       => 'longtext',
			'notification_sms_template'   => 'longtext',
			'notification_slack_template' => 'longtext',
			'notification_status'         => 'tinyint',
			'notification_view_state'     => 'tinyint',
			'created_on'                  => 'bigint',
		);

		/**
		 * Saves record in the table
		 *
		 * @param array $active_record - An array with all the user data to insert.
		 * @param \wpdb $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return int|false
		 *
		 * @since 5.2.1
		 * @since 5.5.0  - Added connection parameter - This is for compatibility only - Notifications are stored in the local DB.
		 */
		public static function save( $active_record, $connection = null ) {

			$_wpdb  = self::get_connection();
			$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d' );

			$data = array();

			if ( isset( $active_record['id'] ) ) {
				$data['id'] = (int) $active_record['id'];
				array_unshift( $format, '%d' );
			}

			if ( ! isset( $active_record['notification_template'] ) ) {
				$active_record['notification_template'] = array();
			}

			if ( \is_array( $active_record['notification_template'] ) ) {
				$active_record['notification_template'] = json_encode( $active_record['notification_template'] );
			}

			if ( ! isset( $active_record['notification_sms_template'] ) ) {
				$active_record['notification_sms_template'] = array();
			}

			if ( \is_array( $active_record['notification_sms_template'] ) ) {
				$active_record['notification_sms_template'] = json_encode( $active_record['notification_sms_template'] );
			}

			if ( ! isset( $active_record['notification_settings'] ) ) {
				$active_record['notification_settings'] = array();
			}

			if ( \is_array( $active_record['notification_settings'] ) ) {
				$active_record['notification_settings'] = json_encode( $active_record['notification_settings'] );
			}

			if ( ! isset( $active_record['notification_slack_template'] ) ) {
				$active_record['notification_slack_template'] = array();
			}

			if ( \is_array( $active_record['notification_slack_template'] ) ) {
				$active_record['notification_slack_template'] = json_encode( $active_record['notification_slack_template'] );
			}

			if ( ! isset( $active_record['notification_query'] ) ) {
				$active_record['notification_query'] = array();
			}

			if ( \is_array( $active_record['notification_query'] ) ) {
				$active_record['notification_query'] = json_encode( $active_record['notification_query'] );
			}

			if ( ! isset( $active_record['notification_query_sql'] ) ) {
				$active_record['notification_query_sql'] = array();
			}

			if ( \is_array( $active_record['notification_query_sql'] ) ) {
				$active_record['notification_query_sql'] = json_encode( $active_record['notification_query_sql'] );
			}

			$data_collect = array(
				'notification_user_id'        => (int) $active_record['notification_user_id'],
				'notification_username'       => $active_record['notification_username'],
				'notification_settings'       => $active_record['notification_settings'],
				'notification_title'          => $active_record['notification_title'],
				'notification_email'          => $active_record['notification_email'],
				'notification_email_bcc'      => $active_record['notification_email_bcc'],
				'notification_email_user'     => (int) $active_record['notification_email_user'],
				'notification_phone'          => $active_record['notification_phone'],
				'notification_slack'          => $active_record['notification_slack'],
				'notification_template'       => $active_record['notification_template'],
				'notification_sms_template'   => $active_record['notification_sms_template'],
				'notification_slack_template' => $active_record['notification_slack_template'],
				'notification_query'          => $active_record['notification_query'],
				'notification_query_sql'      => $active_record['notification_query_sql'],
				'notification_status'         => (int) $active_record['notification_status'],
				'notification_view_state'     => (int) $active_record['notification_view_state'],
			);

			$data = \array_merge( $data, $data_collect );

			if ( ! isset( $active_record['created_on'] ) ) {
				$data['created_on'] = microtime( true );
			} else {
				$data['created_on'] = $active_record['created_on'];
			}

			$_wpdb->suppress_errors( true );

			$result = $_wpdb->replace( self::get_table_name(), $data, $format );

			if ( '' !== $_wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $_wpdb ) ) {
					if ( self::create_table() ) {
						$result = $_wpdb->replace( self::get_table_name(), $data, $format );
					}
				}
			}
			$_wpdb->suppress_errors( false );

			return $_wpdb->insert_id;
		}

		/**
		 * Creates table functionality
		 *
		 * @return bool
		 *
		 * @since 5.2.1
		 */
		public static function create_table(): bool {
			$table_name    = self::get_table_name();
			$wp_entity_sql = '
				CREATE TABLE `' . $table_name . '` (
					`id` bigint NOT NULL AUTO_INCREMENT,' . PHP_EOL . '
					`notification_user_id` bigint NOT NULL,' . PHP_EOL . /** User created the report (only id) */'
					`notification_username` VARCHAR(60) NOT NULL,' . PHP_EOL . /** Username of the user created the report (only id) */'
					`notification_settings` longtext NOT NULL,' . PHP_EOL . /** Username of the user created the report (only id) */'
					`notification_title` text NOT NULL,' . PHP_EOL . /** All the report filters use to generate the given report (raw format) */'
					`notification_email` text NOT NULL,' . PHP_EOL . /** Prepared filters used for generating the report (normalized array - human friendly) */'
					`notification_email_bcc` text NOT NULL,' . PHP_EOL . /** Prepared filters used for generating the report (normalized array - human friendly) */'
					`notification_email_user` TINYINT(1) DEFAULT 0,' . PHP_EOL . /** Prepared filters used for generating the report (normalized array - human friendly) */'
					`notification_phone` text NOT NULL,' . PHP_EOL . /** Prepared headers used for generating the report */'
					`notification_template` longtext NOT NULL,' . PHP_EOL . /** Parsed where clause (so there is no need to re-run the logic) */'
					`notification_sms_template` longtext NOT NULL,' . PHP_EOL . /** Parsed where clause (so there is no need to re-run the logic) */'
					`notification_slack` text NOT NULL,' . PHP_EOL . /** Parsed where clause (so there is no need to re-run the logic) */'
					`notification_slack_template` longtext NOT NULL,' . PHP_EOL . /** Parsed where clause (so there is no need to re-run the logic) */'
					`notification_query` longtext NOT NULL,' . PHP_EOL . /** Parsed where clause (so there is no need to re-run the logic) */'
					`notification_query_sql` longtext NOT NULL,' . PHP_EOL . /** Parsed where clause (so there is no need to re-run the logic) */'
					`notification_status` TINYINT(1) DEFAULT 0,' . PHP_EOL . /** Does the report finished or still need to be called */'
					`notification_view_state` TINYINT(1) DEFAULT 0,' . PHP_EOL . /** Last timestamp (from where this report needs to start next step) */'
					`created_on` bigint NOT NULL,' . PHP_EOL . '
				  PRIMARY KEY (`id`)' . PHP_EOL . '
				)
			  ' . self::get_connection()->get_charset_collate() . ';';

			return self::maybe_create_table( $table_name, $wp_entity_sql );
		}

		/**
		 * Returns the current connection. Reports are always stored in local database - that is the reason for overriding this method
		 *
		 * @return \WPDB @see
		 *
		 * @since 5.2.1
		 */
		public static function get_connection() {
			if ( null === self::$connection ) {
				global $wpdb;
				self::$connection = $wpdb;
			}
			return self::$connection;
		}

		/**
		 * Tries to retrieve an array orders by the field and order.
		 *
		 * @param string $ordered_by  - The field to order by.
		 * @param string $order       - The direction to order - either ASC or DESC.
		 * @param string $search_sql       - The prepared search sql string.
		 *
		 * @return array
		 *
		 * @since 5.2.1
		 */
		public static function load_array_ordered_by( $ordered_by = 'id', $order = 'ASC', $search_sql = '' ) {
			// ensure we have a correct order string.
			if ( 'ASC' !== $order && 'DESC' !== $order ) {
				$order = 'ASC';
			}
			if ( ! isset( $ordered_by ) || empty( $ordered_by ) ) {
				$ordered_by = 'id';
			}
			if ( ! isset( self::get_fields()[ \strtolower( $ordered_by ) ] ) ) {
				$ordered_by = 'id';
			}
			$_wpdb   = self::get_connection();
			$results = array();
			$query   = 'SELECT * FROM ' . self::get_table_name();

			$query .= ' WHERE 1 ' . $search_sql;

			$query .= ' ORDER BY `' . \sanitize_text_field( \wp_unslash( $ordered_by ) ) . '` ' . $order;

			$_wpdb->suppress_errors( true );

			$results = $_wpdb->get_results( $query, ARRAY_A );
			if ( '' !== $_wpdb->last_error ) {
				if ( 1146 === self::get_last_sql_error( $_wpdb ) ) {
					if ( self::create_table() ) {
						$results = $_wpdb->get_results( $query, ARRAY_A );
					}
				}
			}
			$_wpdb->suppress_errors( false );

			return $results;
		}

		/**
		 * Load object data from variable.
		 *
		 * @param array|object $data Data array or object.
		 * @throws \Exception - Unsupported type.
		 *
		 * @since 5.2.1
		 */
		public static function load_data( $data ) {
			return $data;
		}

		/**
		 * Duplicates report and sets new name of type "Copy_of_" name of the report to duplicate followed by number.
		 *
		 * @param integer $id - The ID of the report to duplicate.
		 * @param \wpdb   $connection - The database connection to use.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function duplicate_by_id( int $id, $connection ) {

			$current_record = self::load( 'id=%d', array( $id ) );

			$name = $current_record['notification_title'];

			$name_array = explode( '_', $name );

			$index = $name_array[ array_key_last( $name_array ) ];

			if ( false !== filter_var( $index, FILTER_VALIDATE_INT ) ) {

				\array_pop( $name_array );
			}

			if ( false !== \mb_strpos( $name, 'Copy_of_' ) ) {
				array_shift( $name_array );
				array_shift( $name_array );
			}

			$search = '';

			if ( empty( $name_array ) ) {
				$search = $name;
			} else {
				$search = \implode( '_', $name_array );
			}

			$_wpdb = self::get_connection();

			$prepared_query = $_wpdb->prepare( // phpcs:ignore
				'SELECT * FROM ' . self::get_table_name() . ' WHERE notification_title LIKE %s;',
				'%%' . $search . '%%'
			);

			$names = $_wpdb->get_results( $prepared_query, ARRAY_A );

			$new_index = 0;

			if ( ! empty( $names ) ) {
				foreach ( $names as $report ) {
					$name_array = explode( '_', $report['notification_title'] );

					$index = $name_array[ array_key_last( $name_array ) ];

					if ( false !== filter_var( $index, FILTER_VALIDATE_INT ) ) {

						if ( $index > $new_index ) {
							$new_index = $index;
						}
					}
				}
			}

			++$new_index;

			$new_name = 'Copy_of_' . $search . '_' . $new_index;

			$duplicated_id = parent::duplicate_by_id( $id, $connection );

			$current_record['id']                 = $duplicated_id;
			$current_record['notification_title'] = $new_name;

			self::save( $current_record );
		}

		/**
		 * Disable / enable method
		 *
		 * @param integer $id - The real id of the table.
		 * @param bool    $status - The boolean value to store in the report - enabled / disabled - true or false.
		 * @param \wpdb   $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return void
		 *
		 * @since 5.2.1
		 */
		public static function disable_enable_by_id( int $id, ?bool $status = null, $connection = null ) {

			$record = self::load( 'id=%d', $id );

			if ( null === $status ) {
				$status = 1;
				if ( $record['notification_status'] ) {
					$status = 0;
				}
			}

			self::save( array_merge( $record, array( 'notification_status' => (int) $status ) ) );
		}
// phpcs:disable
/* @premium:end */
// phpcs:enable
	}
}
