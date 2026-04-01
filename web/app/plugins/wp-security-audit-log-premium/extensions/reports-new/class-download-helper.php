<?php
/**
 * Helper: WSAL Reports
 *
 * @since 5.1.0
 * @package    wsal
 * @subpackage views
 */

declare(strict_types=1);

namespace WSAL\Extensions\Helpers;

use WSAL\Helpers\Settings_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\Extensions\Helpers\Download_Helper' ) ) {
	/**
	 * Class: WSAL report downloads helper.
	 *
	 * @package    wsal
	 * @subpackage views
	 *
	 * @since 5.0.0
	 */
	class Download_Helper {

		/**
		 * Local cache for information about data formats. Don't access directly, user function _get_all.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $formats = array(
			'html' => array(
				'label'        => 'HTML',
				'report_class' => 'WSAL_Rep_HtmlReportGenerator',
				'content_type' => 'text/html',
			),
			'csv'  => array(
				'label'        => 'CSV',
				'report_class' => 'WSAL_Rep_CsvReportGenerator',
				'content_type' => 'text/csv',
			),
			'json' => array(
				'label'        => 'JSON',
				'report_class' => 'WSAL_Rep_JsonReportGenerator',
				'content_type' => 'application/json',
			),
			'pdf'  => array(
				'label'        => 'PDF',
				'report_class' => 'WSAL_Rep_PdfReportGenerator',
				'content_type' => 'application/pdf',
			),
		);

		/**
		 * Inits the class and sets the hooks
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function init() {
			\add_action( 'wp_ajax_wsal_file_download', array( __CLASS__, 'process_file_download' ) );
		}

		/**
		 * Checks if the data format is valid.
		 *
		 * @param int|string $format - Report format, could be string (pdf, json etc.) or integer which will be used to check against the format array (transferred to non-associative array).
		 *
		 * @return bool
		 */
		public static function is_valid( $format ) {
			return in_array( strtolower( (string) $format ), self::get_all(), true ) || isset( self::get_all()[ $format ] );
		}

		/**
		 * Retrieves a list of all data formats.
		 *
		 * @return int[]
		 *
		 * @since 5.0.0
		 */
		public static function get_all() {
			return array_keys( self::$formats );
		}

		/**
		 * Determines a mime content type for given data format.
		 *
		 * @param int|string $format Data format.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_content_type( $format ) {
			$formats = self::$formats;

			return array_key_exists( strtolower( (string) $format ), $formats ) ? $formats[ $format ]['content_type'] : ( isset( self::$formats[ self::get_all()[ $format ] ] ) ? self::$formats[ self::get_all()[ $format ] ]['content_type'] : '' );
		}

		/**
		 * Executes file download sequence.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function process_file_download() {
			// #! No  cache
			if ( ! headers_sent() ) {
				header( 'Expires: Mon, 26 Jul 1990 05:00:00 GMT' );
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				header( 'Cache-Control: no-store, no-cache, must-revalidate' );
				header( 'Cache-Control: post-check=0, pre-check=0', false );
				header( 'Pragma: no-cache' );
			}

			$strm = '[WSAL Reporting Plugin] Requesting download';

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( $strm . ' without sufficient rights [code: 0000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			// Validate nonce.
			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_GET['nonce'] ) ), 'wpsal_reporting_nonce_action' ) ) {
				wp_die( $strm . ' with a missing or invalid nonce [code: 1000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			// Missing f param from url.
			if ( ! isset( $_GET['f'] ) ) {
				wp_die( $strm . ' without the "f" parameter [code: 2000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			// Missing ctype param from url.
			if ( ! isset( $_GET['ctype'] ) ) {
				wp_die( $strm . ' without the "ctype" parameter [code: 3000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			// Invalid fn provided in the url.
			$fn = base64_decode( \sanitize_text_field( \wp_unslash( $_GET['f'] ) ) );
			if ( false === $fn ) {
				wp_die( $strm . ' without a valid base64 encoded file name [code: 4000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$sub_dir = 'reports';
			// Extract subdir.
			if ( isset( $_GET['dir'] ) ) {
				$sub_dir = (string) \sanitize_text_field( \wp_unslash( $_GET['dir'] ) );
			}

			$dir       = Settings_Helper::get_working_dir_path_static( $sub_dir, true );
			$file_path = $dir . $fn;

			// Directory traversal attacks won't work here.
			if ( preg_match( '/\.\./', $file_path ) ) {
				wp_die( $strm . ' with an invalid file name (' . $fn . ') [code: 6000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			if ( ! is_file( $file_path ) ) {
				wp_die( $strm . ' with an invalid file name (' . $fn . ') [code: 7000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$data_format = ( wp_unslash( $_GET['ctype'] ) );
			if ( ! self::is_valid( $data_format ) ) {
				// Content type is not valid.
				wp_die( $strm . ' with an invalid content type [code: 7000]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$content_type = self::get_content_type( $data_format );
			$file_size    = filesize( $file_path );
			$file         = fopen( $file_path, 'rb' );

			// - turn off compression on the server - that is, if we can...
			ini_set( 'zlib.output_compression', 'Off' );
			// set the headers, prevent caching + IE fixes.
			header( 'Pragma: public' );
			header( 'Expires: -1' );
			header( 'Cache-Control: public, must-revalidate, post-check=0, pre-check=0' );
			if ( 'text/html' !== $content_type ) {
				header( 'Content-Disposition: attachment; filename="' . $fn . '"' );
			}
			header( "Content-Length: $file_size" );
			header( "Content-Type: {$content_type}" );
			set_time_limit( 0 );
			while ( ! feof( $file ) ) {
				print( fread( $file, 1024 * 8 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				ob_flush();
				flush();
				if ( connection_status() != 0 ) {
					fclose( $file );
					exit;
				}
			}
			// File save was a success.
			fclose( $file );

			exit;
		}
	}
}
