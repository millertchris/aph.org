<?php
/**
 * RFC 5424 Syslog Formatter.
 *
 * @package wsal
 * @subpackage external-db
 * @since 5.6.0
 */

namespace WSAL\Extensions\ExternalDB\Formatters;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Only proceed if Monolog's NormalizerFormatter class is available.
 */
if ( class_exists( '\WSAL_Vendor\Monolog\Formatter\NormalizerFormatter' ) ) {
	/**
	 * Formats messages according to RFC 5424.
	 *
	 * @package wsal
	 * @subpackage external-db
	 * @since 5.6.0
	 */
	class Rfc5424_Formatter extends \WSAL_Vendor\Monolog\Formatter\NormalizerFormatter {

		/**
		 * Constructor.
		 *
		 * @since 5.6.0
		 */
		public function __construct() {
			// Set timestamp format to RFC 5424 standard: 2025-11-10T14:30:45.123456+00:00.
			parent::__construct( 'Y-m-d\TH:i:s.uP' );
		}

		/**
		 * Formats a log record according to RFC 5424.
		 *
		 * @param array $record - Log record to format.
		 *
		 * @return string
		 *
		 * @since 5.6.0
		 */
		public function format( array $record ): string {
			$record = parent::format( $record );

			$structured_data = self::build_structured_data( $record );

			$message = self::build_message( $record );

			return \sprintf(
				'%s %s',
				$structured_data,
				$message
			);
		}

		/**
		 * Builds RFC 5424 structured data from context and extra fields.
		 *
		 * @param array $record - Log record.
		 *
		 * @return string
		 *
		 * @since 5.6.0
		 */
		private static function build_structured_data( array $record ): string {
			$elements = array();

			// Add event data.
			if ( ! empty( $record['context'] ) ) {
				$params = array();
				foreach ( $record['context'] as $key => $value ) {
					if ( \is_scalar( $value ) || null === $value ) {
						$key      = self::escape_sd_name( $key );
						$value    = self::escape_sd_value( (string) $value );
						$params[] = "{$key}=\"{$value}\"";
					}
				}
				if ( ! empty( $params ) ) {
					$elements[] = '[event ' . \implode( ' ', $params ) . ']';
				}
			}

			// Add origin information.
			$origin_params = array();
			if ( isset( $record['channel'] ) ) {
				$origin_params[] = 'source="' . self::escape_sd_value( $record['channel'] ) . '"';
			}
			$origin_params[] = 'software="WP Activity Log"';
			if ( \defined( 'WSAL_VERSION' ) ) {
				$origin_params[] = 'version="' . self::escape_sd_value( WSAL_VERSION ) . '"';
			}
			$elements[] = '[origin ' . \implode( ' ', $origin_params ) . ']';

			$result = '-';
			if ( ! empty( $elements ) ) {
				$result = \implode( '', $elements );
			}

			return $result;
		}

		/**
		 * Builds the message part.
		 *
		 * @param array $record - Log record.
		 *
		 * @return string
		 *
		 * @since 5.6.0
		 */
		private static function build_message( array $record ): string {
			$message = isset( $record['message'] ) ? (string) $record['message'] : '';

			// Remove duplicate trailing dots if present.
			if ( '..' === \substr( $message, -2 ) ) {
				$message = \substr( $message, 0, -1 );
			}

			return $message;
		}

		/**
		 * Escapes Structured Data PARAM-NAME (name/key) according to RFC 5424.
		 *
		 *  SD-NAME format = 1*32PRINTUSASCII; except '=', SP, ']', %d34 (")
		 *
		 * @param string $name - Name to escape.
		 *
		 * @return string
		 *
		 * @since 5.6.0
		 */
		private static function escape_sd_name( string $name ): string {
			// Replace characters not accepted by RFC 5424 in this context.
			$output = \preg_replace( '/[=\s\]"]/', '_', $name );

			// Max length is 32 characters.
			return \substr( $output, 0, 32 );
		}

		/**
		 * Escapes Structured Data PARAM-VALUE (value) according to RFC 5424.
		 *
		 * To comply with standard:
		 * - It must be encoded using UTF-8
		 * - the characters '"' (ABNF %d34), '\' (ABNF %d92), and ']' (ABNF %d93) must be escaped.  This is necessary to avoid parsing errors.  Each of these three characters must be escaped as '\"', '\\', and '\]' respectively.
		 *
		 * @param string $value - Value to escape.
		 *
		 * @return string
		 *
		 * @since 5.6.0
		 */
		private static function escape_sd_value( string $value ): string {
			// Escape backslash \, double quote ", and square bracket ].
			return \addcslashes( $value, '\\"\\]' );
		}
	}
}
