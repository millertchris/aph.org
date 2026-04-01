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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Entities\Reports_Entity' ) ) {

	/**
	 * Responsible for the reports storage.
	 */
	class Reports_Entity extends Abstract_Entity {

		/**
		 * Holds the DB records for the periodic reports
		 *
		 * @var \wpdb
		 *
		 * @since 5.0.0
		 */
		private static $connection = null;

		/**
		 * Holds the frequencies for the reports
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $frequencies = array();

		/**
		 * Holds the types for the reports
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $types = array();

		/**
		 * Contains the table name.
		 *
		 * @var string
		 *
		 * @since 5.0.0
		 */
		protected static $table = 'wsal_periodic_reports';

		/**
		 * Keeps the info about the columns of the table - name, type.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		protected static $fields = array(
			'id'               => 'bigint',
			'report_user_id'   => 'bigint',
			'report_username'  => 'varchar(60)',
			'report_name'      => 'varchar(128)',
			'report_frequency' => 'int',
			'report_format'    => 'int',
			'report_email'     => 'varchar(255)',
			'report_data'      => 'longtext',
			'report_tag'       => 'varchar(255)',
			'report_disabled'  => 'tinyint',
			'last_sent'        => 'bigint',
			'created_on'       => 'bigint',
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
			$format = array( '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%d' );
			$data   = array();

			if ( isset( $active_record['id'] ) ) {
				$data['id'] = (int) $active_record['id'];
				array_unshift( $format, '%d' );
			}

			$data_collect = array(
				'report_name'      => $active_record['report_name'],
				'report_user_id'   => $active_record['report_user_id'],
				'report_username'  => $active_record['report_username'],
				'report_frequency' => $active_record['report_frequency'],
				'report_format'    => ( isset( $active_record['report_format'] ) ) ? $active_record['report_format'] : 0,
				'report_email'     => $active_record['report_email'],
				'report_data'      => \is_array( $active_record['report_data'] ) ? json_encode( $active_record['report_data'] ) : $active_record['report_data'],
				'report_tag'       => $active_record['report_tag'],
				'report_disabled'  => $active_record['report_disabled'],
				'last_sent'        => ( isset( $active_record['last_sent'] ) ) ? $active_record['last_sent'] : 0,
			);

			$data = array_merge( $data, $data_collect );

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
					`report_user_id` bigint NOT NULL,' . PHP_EOL . /** User created the report (only id) */'
					`report_username` VARCHAR(60) NOT NULL,' . PHP_EOL . /** Username of the user created the report (only id) */'
					`report_name` VARCHAR(128) NOT NULL,' . PHP_EOL . '
					`report_frequency` int NOT NULL,' . PHP_EOL . '
					`report_format` int NOT NULL,' . PHP_EOL . '
					`report_email` VARCHAR(255)  NOT NULL,' . PHP_EOL . '
					`report_data` longtext NOT NULL,' . PHP_EOL . '
					`report_tag` VARCHAR(255),' . PHP_EOL . '
					`report_disabled` TINYINT(1) DEFAULT 0,' . PHP_EOL . /** Is that report disabled */'
					`last_sent` bigint NOT NULL,' . PHP_EOL . '
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
		 * Sets the reports frequency (uses integer for quick access)
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function set_frequencies() {
			if ( empty( self::$frequencies ) ) {
				self::$frequencies = array(
					// Periodic report frequencies.
					0 => __( 'Daily', 'wp-security-audit-log' ),
					1 => __( 'Weekly', 'wp-security-audit-log' ),
					2 => __( 'Monthly', 'wp-security-audit-log' ),
					3 => __( 'Quarterly', 'wp-security-audit-log' ),
				);
			}
		}

		/**
		 * Gets the human readable frequency name (in the table it is stored as integer)
		 *
		 * @param integer $frequency - The frequency index to retrieve value for.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_frequency( int $frequency = 0 ) {
			if ( empty( self::$frequencies ) ) {
				self::set_frequencies();
			}

			return ( isset( self::$frequencies[ $frequency ] ) ) ? self::$frequencies[ $frequency ] : self::$frequencies[0];
		}

		/**
		 * The stupidity explained:
		 * - Old reports are storing the frequency as translated string ?!? so in order to guess that, we have to reverse that logic and hope for the best. So - we just expect string here, and searching for the same in array.
		 *
		 * @param string $name - The name of the frequency to search for.
		 *
		 * @return int
		 *
		 * @since 5.0.0
		 */
		public static function get_frequency_from_name( string $name ): int {
			if ( empty( self::$frequencies ) ) {
				self::set_frequencies();
			}

			$flipped = \array_flip( self::$frequencies );

			if ( isset( $flipped[ $name ] ) ) {
				return $flipped[ $name ];
			}

			return 0;
		}

		/**
		 * Sets the reports type (should not be used anymore)
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function set_types() {
			if ( empty( self::$types ) ) {
				self::$types = array(
					// Periodic report frequencies.
					0 => __( 'HTML', 'wp-security-audit-log' ),
					1 => __( 'CSV', 'wp-security-audit-log' ),
					2 => __( 'JSON', 'wp-security-audit-log' ),
					3 => __( 'PDF', 'wp-security-audit-log' ),
				);
			}
		}

		/**
		 * Returns the types of the reports
		 *
		 * @param integer $type - The tyep index to retrieve value for.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_type( int $type = 0 ) {
			if ( empty( self::$types ) ) {
				self::set_types();
			}

			return ( isset( self::$types[ $type ] ) ) ? self::$types[ $type ] : self::$types[0];
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
		 * Returns all reports which have not been generated yet.
		 *
		 * @param integer $period - The period to extract periodic reports for.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_all_reports_for_period( int $period ) {
			$reports = self::load_array( 'report_frequency = %d AND report_disabled = %d', array( $period, 0 ) );

			return $reports;
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
		 * @since 5.0.0
		 */
		public static function disable_enable_by_id( int $id, bool $status, $connection = null ) {

			$record = self::load( 'id=%d', $id );

			self::save( array_merge( $record, array( 'report_disabled' => $status ) ) );
		}

		/**
		 * Returns array with fields to duplicate, gets rid of id and created_on columns.
		 *
		 * @param bool $duplicate_values - When called for duplication, gives the class ability to set fields that must have specific values in the database.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_duplicate_fields( bool $duplicate_values ): array {
			$fields = parent::get_duplicate_fields( $duplicate_values );
			if ( $duplicate_values ) {
				$key = array_search( 'last_sent', $fields, true );

				$fields[ $key ] = 0;
			}

			return $fields;
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

			$name = $current_record['report_name'];

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
				'SELECT * FROM ' . self::get_table_name() . ' WHERE report_name LIKE %s;',
				'%%' . $search . '%%'
			);

			$names = $_wpdb->get_results( $prepared_query, ARRAY_A );

			$new_index = 0;

			if ( ! empty( $names ) ) {
				foreach ( $names as $report ) {
					$name_array = explode( '_', $report['report_name'] );

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

			$current_record['id']          = $duplicated_id;
			$current_record['report_name'] = $new_name;

			self::save( $current_record );
		}
	}
}
