<?php
/**
 * Adapter: Reports.
 *
 * Reports entity class.
 *
 * @package wsal
 */

declare(strict_types=1);

namespace WSAL\Entities;

use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Entities\Generated_Reports_Entity' ) ) {

	/**
	 * Responsible for the reports storage.
	 */
	class Generated_Reports_Entity extends Abstract_Entity {

		/**
		 * Holds the DB records for the periodic reports
		 *
		 * @var \wpdb
		 *
		 * @since 5.0.0
		 */
		private static $connection = null;

		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 5.0.0
		 */
		protected static $table = 'wsal_generated_reports';

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		protected static $fields = array(
			'id'                                  => 'bigint',
			'generated_report_user_id'            => 'bigint',
			'generated_report_username'           => 'varchar(60)',
			'generated_report_filters'            => 'longtext',
			'generated_report_filters_normalized' => 'longtext',
			'generated_report_header_columns'     => 'longtext',
			'generated_report_where_clause'       => 'text',
			'generated_report_finished'           => 'tinyint',
			'generated_report_to_date'            => 'double',
			'generated_report_name'               => 'varchar(128)',
			'generated_report_file'               => 'varchar(128)',
			'generated_report_tag'                => 'varchar(255)',
			'generated_report_format'             => 'int',
			'generated_report_number_of_records'  => 'int',
			'created_on'                          => 'bigint',
		);

		/**
		 * Saves record in the table
		 *
		 * @param array $active_record - An array with all the user data to insert.
		 * @param \wpdb $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return int|false
		 *
		 * @since 5.0.0
		 * @since 5.5.0  - Added connection parameter - This is for compatibility only - Notifications are stored in the local DB.
		 */
		public static function save( $active_record, $connection = null ) {

			$_wpdb  = self::get_connection();
			$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%s', '%s', '%s', '%d', '%d', '%d' );

			$data = array();

			if ( isset( $active_record['id'] ) ) {
				$data['id'] = (int) $active_record['id'];
				array_unshift( $format, '%d' );
			}

			$data_collect = array(
				'generated_report_user_id'            => $active_record['generated_report_user_id'],
				'generated_report_username'           => $active_record['generated_report_username'],
				'generated_report_filters'            => json_encode( $active_record['generated_report_filters'] ),
				'generated_report_filters_normalized' => json_encode( $active_record['generated_report_filters_normalized'] ),
				'generated_report_header_columns'     => json_encode( $active_record['generated_report_header_columns'] ),
				'generated_report_where_clause'       => $active_record['generated_report_where_clause'],
				'generated_report_finished'           => (int) $active_record['generated_report_finished'],
				'generated_report_to_date'            => $active_record['generated_report_to_date'],
				'generated_report_name'               => $active_record['generated_report_name'],
				'generated_report_file'               => ( isset( $active_record['generated_report_file'] ) ) ? $active_record['generated_report_file'] : $active_record['generated_report_name'],
				'generated_report_tag'                => ( isset( $active_record['generated_report_tag'] ) ) ? $active_record['generated_report_tag'] : '',
				'generated_report_format'             => ( isset( $active_record['generated_report_format'] ) ) ? $active_record['generated_report_format'] : 0,
				'generated_report_number_of_records'  => ( isset( $active_record['generated_report_number_of_records'] ) ) ? $active_record['generated_report_number_of_records'] : 0,
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
		 * @since 5.0.0
		 */
		public static function create_table(): bool {
			$table_name    = self::get_table_name();
			$wp_entity_sql = '
				CREATE TABLE `' . $table_name . '` (
					`id` bigint NOT NULL AUTO_INCREMENT,' . PHP_EOL . '
					`generated_report_user_id` bigint NOT NULL,' . PHP_EOL . /** User created the report (only id) */'
					`generated_report_username` VARCHAR(60) NOT NULL,' . PHP_EOL . /** Username of the user created the report (only id) */'
					`generated_report_filters` longtext NOT NULL,' . PHP_EOL . /** All the report filters use to generate the given report (raw format) */'
					`generated_report_filters_normalized` longtext NOT NULL,' . PHP_EOL . /** Prepared filters used for generating the report (normalized array - human friendly) */'
					`generated_report_header_columns` longtext NOT NULL,' . PHP_EOL . /** Prepared headers used for generating the report */'
					`generated_report_where_clause` text NOT NULL,' . PHP_EOL . /** Parsed where clause (so there is no need to re-run the logic) */'
					`generated_report_finished` TINYINT(1) DEFAULT 0,' . PHP_EOL . /** Does the report finished or still need to be called */'
					`generated_report_to_date` double,' . PHP_EOL . /** Last timestamp (from where this report needs to start next step) */'
					`generated_report_name` VARCHAR(128) NOT NULL,' . PHP_EOL . /** Report name (internal) */'
					`generated_report_file` VARCHAR(128) NOT NULL,' . PHP_EOL . '
					`generated_report_tag` VARCHAR(255) NOT NULL,' . PHP_EOL . '
					`generated_report_format` int NOT NULL,' . PHP_EOL . '
					`generated_report_number_of_records` int NOT NULL,' . PHP_EOL . '
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
		 * @since 5.0.0
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
		 * @since 5.0.0
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
		 * @since 5.0.0
		 */
		public static function load_data( $data ) {
			return $data;
		}

		/**
		 * Returns the path to the reports directory.
		 *
		 * @return string|\WP_Error
		 *
		 * @since 5.0.0
		 */
		public static function get_full_file_path() {
			return Settings_Helper::get_working_dir_path_static( 'reports', true );
		}

		/**
		 * Default delete method
		 *
		 * @param integer $id - The real id of the table.
		 * @param \wpdb   $connection - \wpdb connection to be used for name extraction.
		 *
		 * @return int|bool
		 *
		 * @since 5.0.0
		 */
		public static function delete_by_id( int $id, $connection = null ) {

			$record = self::load( 'id=%d', $id );

			if ( isset( $record['generated_report_file'] ) && \is_file( self::get_full_file_path() . $record['generated_report_file'] . '.csv' ) ) {
				\wp_delete_file( self::get_full_file_path() . $record['generated_report_file'] . '.csv' );
				\wp_delete_file( self::get_full_file_path() . $record['generated_report_file'] . '.html' );
			}

			return parent::delete_by_id( $id, $connection );
		}

		/**
		 * Returns files as array
		 *
		 * @param int $id - The id of the generated report.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_as_attachments( $id ): array {

			$record = self::load( 'id=%d', $id );

			$return = array();

			if ( isset( $record['generated_report_file'] ) && \is_file( self::get_full_file_path() . $record['generated_report_file'] . '.csv' ) ) {
				$return[] = self::get_full_file_path() . $record['generated_report_file'] . '.csv';
				$return[] = self::get_full_file_path() . $record['generated_report_file'] . '.html';
			}

			return $return;
		}

		/**
		 * Returns all reports which have not been generated yet.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_all_not_finished_reports() {
			$not_finished = self::load_array( 'generated_report_finished = %d', array( 0 ) );

			return $not_finished;
		}

		/**
		 * Returns all reports which are finished and generated before given number of days.
		 *
		 * @param integer $days - Number of days back to check for the reports.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_all_reports_older_than_days( int $days ): array {
			$reports = array();

			$reports = self::load_array(
				'generated_report_finished = %d AND created_on <= %d',
				array(
					1,
					strtotime( '-' . $days . ' day' ),
				)
			);

			return $reports;
		}
	}
}
