<?php
/**
 * Copy Event Data class
 *
 * @since 5.6.0
 *
 * @package   wsal
 * @subpackage copy-event-data
 */

declare(strict_types=1);

namespace WSAL\CopyEventData;

use WSAL\Helpers\WP_Helper;
use WSAL\Controllers\Connection;
use WSAL\Helpers\Settings_Helper;
use WSAL\Entities\Occurrences_Entity;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\CopyEventData\Copy_Event_Data' ) ) {
	/**
	 * Copy Event Data class
	 *
	 * @since 5.6.0
	 */
	class Copy_Event_Data {

		/**
		 * Init class
		 *
		 * @since 5.6.0
		 */
		public static function init() {
			add_action( 'wp_ajax_get_event_details', array( __CLASS__, 'ajax_event_details' ) );

			\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
		}

		/**
		 * Add required styles and scripts
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function enqueue_admin_scripts() {
			$screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

			// Return early if not on the WSAL Audit Log admin page.
			if ( ! $screen || strpos( $screen->id, 'toplevel_page_wsal-auditlog' ) !== 0 ) {
				return;
			}

			\wp_enqueue_style(
				'wsal-copy-event-data',
				WSAL_BASE_URL . 'extensions/copy-event-data/css/copy-event-data.css',
				array(),
				WSAL_VERSION
			);

			\wp_enqueue_script(
				'wsal-copy-event-data',
				WSAL_BASE_URL . 'extensions/copy-event-data/js/copy-event-data.js',
				array( 'jquery', 'darktooltip' ),
				WSAL_VERSION,
				true
			);
		}

		/**
		 * Render copy data buttons
		 *
		 * @param array $event_item - Array with the current row event values as used in List_Events::format_column_value().
		 *
		 * @return string $copy_event_btns - the HTML of the buttons to copy event data.
		 *
		 * @since 5.6.0
		 */
		public static function render_copy_event_data_triggers( $event_item ) {

			$event_id = $event_item['id'] ?? null;

			if ( ! $event_id ) {
				return '';
			}

			$spinner_icon = '<div style="display: none;" class="wsal-copy-spinner"><div></div><div></div><div></div><div></div></div>';

			$copy_general_event_info = '<a href="#" class="wsal-copy-event-data-simple-button" data-darktooltip="' . esc_attr__( 'Copy event data', 'wp-security-audit-log' ) . '" aria-label="Copy event data"><span class="dashicons dashicons-media-default"></span></a>';

			$copy_detailed_event_info = '<a href="#" class="wsal-copy-event-data-extended-button" event-id="' . esc_attr( $event_id ) . '" data-darktooltip=" ' . esc_attr__( 'Copy detailed event data', 'wp-security-audit-log' ) . '" aria-label="Copy detailed event data">' . $spinner_icon . '<span class="dashicons dashicons-media-text"></span></a>';

			$copy_event_btns = '<div class="wsal-copy-event-btns">' . $copy_general_event_info . ' ' . $copy_detailed_event_info . '</div>';

			return $copy_event_btns;
		}

		/**
		 * Get the event details via AJAX
		 *
		 * @since 5.6.0
		 */
		public static function ajax_event_details() {
			if ( ! Settings_Helper::current_user_can( 'view' ) ) {
				die( 'Access Denied.' );
			}

			\check_ajax_referer( 'wsal_auditlog_viewer_nonce', 'nonce' );

			// Get occurence/event id.
			$occurrence_id = (int) \sanitize_text_field( \wp_unslash( $_POST['event_db_id'] ?? null ) );

			if (
			empty( $occurrence_id ) ||
			! is_int( $occurrence_id ) ||
			$occurrence_id < 1
			) {
				\wp_send_json_error( \esc_html__( 'Invalid occurrence ID.', 'wp-security-audit-log' ) );
			}

			$wsal_db = Connection::get_connection();

			// Get selected db.
			$selected_db      = WP_Helper::get_transient( 'wsal_wp_selected_db' );
			$selected_db_user = (int) WP_Helper::get_transient( 'wsal_wp_selected_db_user' );

			// Check if archive db is enabled and the current user matches the one who selected archive db.
			if ( ! empty( $selected_db ) && 'archive' === $selected_db && get_current_user_id() === $selected_db_user ) {
				Settings_Helper::switch_to_archive_db();
			}

			if ( Connection::is_archive_mode() ) {
				$connection_name = Settings_Helper::get_option_value( 'archive-connection' );

				$wsal_db = Connection::get_connection( $connection_name );
			}

			$alert_meta = Occurrences_Entity::get_meta_array( (int) $occurrence_id, array(), $wsal_db );

			\wp_send_json_success(
				$alert_meta
			);
		}
	}
}
