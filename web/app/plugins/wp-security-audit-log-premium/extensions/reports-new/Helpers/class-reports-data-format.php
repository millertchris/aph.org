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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( '\WSAL\Extensions\Helpers\Reports_Data_Format' ) ) {

	/**
	 * Class defines different data format types along with some helper methods.
	 *
	 * @package    wsal
	 * @subpackage reports
	 * @since 5.1.0
	 */
	class Reports_Data_Format {

		const HTML = 0;

		const CSV = 1;

		const JSON = 2;

		const PDF = 3;

		/**
		 * Local cache for information about data formats. Don't access directly, user function _get_all.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $formats = array();

		/**
		 * Retrieves the default data format.
		 *
		 * @return int
		 *
		 * @since 5.0.0
		 */
		public static function get_default() {
			return self::HTML;
		}

		/**
		 * Determines a label for given data format.
		 *
		 * @param int $format Report format.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_label( $format ) {
			$formats = self::get_all_complete();

			return array_key_exists( $format, $formats ) ? $formats[ $format ]['label'] : 'Unknown';
		}

		/**
		 * Internal function to hold additional information about each data format.
		 *
		 * @return array {
		 * @type string $label
		 * @type string $report_class
		 * @type string $content_type
		 * }
		 *
		 * @since 5.0.0
		 */
		private static function get_all_complete() {
			if ( empty( self::$formats ) ) {
				self::$formats = array(
					self::HTML => array(
						'label'        => 'HTML',
						'report_class' => 'WSAL_Rep_HtmlReportGenerator',
						'content_type' => 'text/html',
					),
					self::CSV  => array(
						'label'        => 'CSV',
						'report_class' => 'WSAL_Rep_CsvReportGenerator',
						'content_type' => 'text/csv',
					),
					self::JSON => array(
						'label'        => 'JSON',
						'report_class' => 'WSAL_Rep_JsonReportGenerator',
						'content_type' => 'application/json',
					),
					self::PDF  => array(
						'label'        => 'PDF',
						'report_class' => 'WSAL_Rep_PdfReportGenerator',
						'content_type' => 'application/pdf',
					),
				);
			}

			return self::$formats;
		}

		/**
		 * Checks if the data format is valid.
		 *
		 * @param int $format Report format.
		 *
		 * @return bool
		 *
		 * @since 5.0.0
		 */
		public static function is_valid( $format ) {
			return in_array( $format, self::get_all(), true );
		}

		/**
		 * Retrieves a list of all data formats.
		 *
		 * @return int[]
		 *
		 * @since 5.0.0
		 */
		public static function get_all() {
			return array_keys( self::get_all_complete() );
		}

		/**
		 * Builds a report generator based on given data format.
		 *
		 * @param WpSecurityAuditLog $plugin           Plugin instance.
		 * @param int                $format           Data format.
		 * @param string             $reports_dir_path Reports directory path.
		 * @param array              $filters          Report filters.
		 *
		 * @return WSAL_Rep_ReportGeneratorInterface|null
		 *
		 * @since 5.0.0
		 */
		public static function build_report_generator( $plugin, $format, $reports_dir_path, $filters ) {
			$formats = self::get_all_complete();
			if ( ! array_key_exists( $format, $formats ) ) {
				return null;
			}

			$class_name = $formats[ $format ]['report_class'];
			if ( ! class_exists( $class_name ) ) {
				return null;
			}

			return new $class_name( $plugin, $reports_dir_path, $format, $filters );
		}

		/**
		 * Determines a mime content type for given data format.
		 *
		 * @param int $format Data format.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_content_type( $format ) {
			$formats = self::get_all_complete();

			return array_key_exists( $format, $formats ) ? $formats[ $format ]['content_type'] : '';
		}
	}
}
