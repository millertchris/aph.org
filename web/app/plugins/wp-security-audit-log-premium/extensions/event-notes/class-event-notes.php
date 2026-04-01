<?php
/**
 * Event Notes class
 *
 * @since 5.5.0
 *
 * @package   wsal
 * @subpackage event-notes
 */

declare(strict_types=1);

namespace WSAL\EventNotes;

use WSAL\Helpers\WP_Helper;
use WSAL\Controllers\Connection;
use WSAL\Helpers\Settings_Helper;
use WSAL\Entities\Metadata_Entity;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WSAL\EventNotes\Event_Notes' ) ) {
	/**
	 * Event Notes class
	 *
	 * @since 5.5.0
	 */
	class Event_Notes {

		/**
		 * Init class
		 *
		 * @since 5.5.0
		 */
		public static function init() {
			\add_action( 'admin_footer', array( __CLASS__, 'render_add_note_form' ) );
			\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_notes_script' ) );

			\add_action( 'wp_ajax_wsal_save_note_to_event', array( __CLASS__, 'save_note_to_event' ) );
			\add_action( 'wp_ajax_wsal_get_note_of_event', array( __CLASS__, 'get_note_from_db' ) );
			\add_action( 'wp_ajax_wsal_delete_note_of_event', array( __CLASS__, 'delete_note' ) );
		}

		/**
		 * Add required styles and scripts
		 *
		 * @return void
		 *
		 * @since 5.5.0
		 */
		public static function enqueue_admin_notes_script() {
			$screen = function_exists( 'get_current_screen' ) ? \get_current_screen() : null;

			// Return early if not on the WSAL Audit Log admin page.
			if ( ! $screen || strpos( $screen->id, 'toplevel_page_wsal-auditlog' ) !== 0 ) {
				return;
			}

			\wp_enqueue_style(
				'wsal-connections-css',
				WSAL_BASE_URL . 'extensions/event-notes/css/event-notes.css',
				array( 'wp-jquery-ui-dialog' ),
				WSAL_VERSION
			);

			\wp_enqueue_script(
				'wsal-event-notes',
				WSAL_BASE_URL . 'extensions/event-notes/js/event-notes.js',
				array( 'jquery', 'jquery-ui-dialog', 'wp-i18n', 'darktooltip' ),
				WSAL_VERSION,
				true
			);
		}

		/**
		 * Render a button to open the add/edit note modal
		 *
		 * @param array $event_item - Array with the current row event values as used in List_Events::format_column_value().
		 *
		 * @return string $add_note_btn - the HTML of the add note trigger modal button.
		 *
		 * @since 5.5.0
		 */
		public static function render_add_note_to_event_trigger( $event_item ) {
			$has_note      = ! empty( $event_item['meta_values']['event_note'] ?? '' );
			$has_note_attr = $has_note ? 'data-has-db-note' : '';
			$has_note_icon = $has_note ? 'edit' : 'plus';

			$note_btn_label = $has_note ? \esc_html__( 'Edit Note', 'wp-security-audit-log' ) : \esc_html__( 'Add Note', 'wp-security-audit-log' );

			$note_tooltip = $has_note ? \esc_attr__( 'This event has a note. Click to view or edit.', 'wp-security-audit-log' ) : \esc_attr__( 'Add a note to this event', 'wp-security-audit-log' );

			$add_note_btn = '<a class="wsal-add-note button button-secondary" data-darktooltip="' . $note_tooltip . '" href="#" data-event-db-id="' . $event_item['id'] . '" ' . $has_note_attr . '><span class="wsal-note-icon dashicons dashicons-' . $has_note_icon . '"></span>' . $note_btn_label . '</a>';

			return $add_note_btn;
		}

		/**
		 * Render a modal where users can add, edit, or delete a note associated with an event.
		 *
		 * @since 5.5.0
		 */
		public static function render_add_note_form() {

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			// Return early if not on the WSAL Audit Log admin page.
			if ( ! $screen || strpos( $screen->id, 'toplevel_page_wsal-auditlog' ) !== 0 ) {
				return;
			}

			$wsal_notes_nonce = \wp_create_nonce( 'wsal-notes-nonce' );
			?>
			<div id="wsal-event-notes" style="display:none;">
				<form id="wsal-note-form">
					<input type="hidden" id="wsal-notes-nonce" value="<?php echo \esc_attr( $wsal_notes_nonce ); ?>">
					<textarea id="wsal-note-text" name="note" rows="8" cols="40" placeholder="<?php echo \esc_attr__( 'Enter additional information about this event', 'wp-security-audit-log' ); ?>" required></textarea>
					<div data-response-notice></div>
					<div class="wsal-event-notes-actions">
						<button  id="wsal-save-note-btn" style="background-color: #009344;" type="button">
						<?php echo \esc_html__( 'Save Note', 'wp-security-audit-log' ); ?>
						</button>

						<button type="button" style="display: none; background-color: #b00909;" id="wsal-delete-note-btn">
						<?php echo \esc_html__( 'Delete', 'wp-security-audit-log' ); ?>
						</button>
					</div>
					<small><?php echo \esc_html__( 'Plain text only. HTML, links, or other code will not be stored or displayed.', 'wp-security-audit-log' ); ?></small>
				</form>
			</div>

			<?php
		}

		/**
		 * Create or update a note of an event in the database, on the metadata table
		 *
		 * @param int    $event_db_id - the ID of the Event associated with this note.
		 * @param string $value - the content of the note.
		 *
		 * @return bool - true if we successfully updated the table, false otherwise.
		 *
		 * @since 5.5.0
		 */
		public static function upsert_note_to_db( $event_db_id, $value ) {

			$success = false;
			$note    = \maybe_serialize( $value );

			$wsal_db = self::get_proper_connection();

			$upsert = Metadata_Entity::update_by_name_and_occurrence_id( 'event_note', $note, $event_db_id, $wsal_db );

			if ( 0 !== $upsert ) {
				$success = true;
			}

			return $success;
		}

		/**
		 * Get a note of an event from the database, if it exists.
		 *
		 * @since 5.5.0
		 */
		public static function get_note_from_db() {
			\check_ajax_referer( 'wsal-notes-nonce', 'nonce' );

			// Check if user is Admin.
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_send_json_error( \esc_html__( "You don't have permission to read event notes.", 'wp-security-audit-log' ) );
			}

			// Check if event id is provided.
			if ( empty( $_POST['event_db_id'] ) ) {
				\wp_send_json_error( \esc_html__( 'Event ID is missing.', 'wp-security-audit-log' ) );
			}

			$wsal_db = self::get_proper_connection();

			$note_metadata = Metadata_Entity::load_by_name_and_occurrence_id( 'event_note', intval( $_POST['event_db_id'] ), $wsal_db );
			$note_value    = $note_metadata['value'] ?? null;

			if ( ! $note_metadata && ! $note_value ) {
				\wp_send_json_error( \esc_html__( 'Failed to get note. Please try again.', 'wp-security-audit-log' ) );
			}

			\wp_send_json_success(
				array(
					'message' => esc_html__( 'Note fetched.', 'wp-security-audit-log' ),
					'note'    => $note_value,
				)
			);
		}


		/**
		 * Delete a note associated with an event from the database, if it exists.
		 *
		 * @param int $event_db_id - the database ID of the Event associated with this note.
		 *
		 * @return array|bool - the deleted note if successful, false otherwise.
		 *
		 * @since 5.5.0
		 */
		public static function delete_note_from_db( $event_db_id ) {

			$wsal_db = self::get_proper_connection();

			$note_metadata = Metadata_Entity::load_by_name_and_occurrence_id( 'event_note', intval( $event_db_id ), $wsal_db );

			if ( ! $note_metadata ) {
				return false;
			}

			$result = Metadata_Entity::delete_by_id( (int) $note_metadata['id'], $wsal_db );

			return $result;
		}

		/**
		 * Delete note AJAX action handler
		 *
		 * @since 5.5.0
		 */
		public static function delete_note() {
			\check_ajax_referer( 'wsal-notes-nonce', 'nonce' );

			// Check if user is Admin.
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_send_json_error( \esc_html__( "You don't have permission to delete event notes.", 'wp-security-audit-log' ) );
			}

			// Check if event id is provided.
			if ( empty( $_POST['event_db_id'] ) ) {
				\wp_send_json_error( \esc_html__( 'Event ID is missing.', 'wp-security-audit-log' ) );
			}

			$result = self::delete_note_from_db( intval( $_POST['event_db_id'] ) );

			if ( ! $result ) {
				\wp_send_json_error( \esc_html__( 'Failed to delete note. Please try again.', 'wp-security-audit-log' ) );
			}

			\wp_send_json_success(
				array(
					'message' => esc_html__( 'Note deleted successfully.', 'wp-security-audit-log' ),
					'deleted' => $result,
				)
			);
		}

		/**
		 * Server side handler to add note to event in the database
		 *
		 * @since 5.5.0
		 */
		public static function save_note_to_event() {
			\check_ajax_referer( 'wsal-notes-nonce', 'nonce' );

			// Check if user is Admin.
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_send_json_error( \esc_html__( "You don't have permission to save event notes.", 'wp-security-audit-log' ) );
			}

			// Check if note is provided.
			if ( empty( $_POST['note'] ) ) {
				\wp_send_json_error( \esc_html__( 'Note details are missing.', 'wp-security-audit-log' ) );
			}

			// Check if event id is provided.
			if ( empty( $_POST['event_db_id'] ) ) {
				\wp_send_json_error( \esc_html__( 'Event ID is missing.', 'wp-security-audit-log' ) );
			}

			$note = \wp_strip_all_tags( \wp_unslash( $_POST['note'] ) );

			$event_id = \absint( $_POST['event_db_id'] );

			$save_note = self::upsert_note_to_db( $event_id, $note );

			if ( ! $save_note ) {
				\wp_send_json_error( \esc_html__( 'Failed to save note. Please try again.', 'wp-security-audit-log' ) );
			}

			\wp_send_json_success(
				array(
					'message' => esc_html__( 'Note saved successfully.', 'wp-security-audit-log' ),
					'note'    => $note,
					'event'   => $event_id,
				)
			);
		}

		/**
		 * Tries to guess the proper connection and returns it.
		 *
		 * @return null|\wpdb
		 *
		 * @since 5.5.0
		 */
		private static function get_proper_connection() {

			$wsal_db = null;

			$selected_db      = WP_Helper::get_transient( 'wsal_wp_selected_db' );
			$selected_db_user = (int) WP_Helper::get_transient( 'wsal_wp_selected_db_user' );

			// Check if archive db is enabled and the current user matches the one who selected archive db.
			if ( ! empty( $selected_db ) && 'archive' === $selected_db && get_current_user_id() === $selected_db_user ) {
				Settings_Helper::switch_to_archive_db(); // Switch to archive DB.
			}
			if ( Connection::is_archive_mode() ) {
				$connection_name = Settings_Helper::get_option_value( 'archive-connection' );

				$wsal_db = Connection::get_connection( $connection_name );
			}

			return $wsal_db;
		}
	}
}
