<?php
/**
 * Custom Sensors for LearnDash plugin.
 *
 * Class file for alert manager.
 *
 * @package wsal
 * @subpackage wsal-learndash
 *
 * @since 5.6.0
 */

declare(strict_types=1);

namespace WSAL\WP_Sensors;

use WSAL\Helpers\Settings_Helper;
use WSAL\Controllers\Alert_Manager;
use WSAL\Helpers\DateTime_Formatter_Helper;
use WSAL\WP_Sensors\Helpers\LearnDash_Helper;
use WSAL\WP_Sensors\Alerts\LearnDash_Custom_Alerts;

// Exit if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! \class_exists( '\WSAL\WP_Sensors\LearnDash_Sensor' ) ) {
	/**
	 * Custom sensor for LearnDash plugin.
	 *
	 * @since 5.6.0
	 */
	class LearnDash_Sensor {

		/**
		 * Course steps metadata store before updating metadata.
		 *
		 * Structure: [ (string) $post_id => array( 'lessons' => [...], 'topics' => [...], 'quizzes' => [...] ) ]
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $before_course_steps = array();

		/**
		 * Course metadata store before updating metadata.
		 * ! used specifically to track changes in the _sfwd-courses metadata.
		 *
		 * Structure: [ (string) $post_id => array( 'certificate' => $cert_id, 'price_type' => $price_type ) ]
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $before_course_meta = array();

		/**
		 * Group metadata store before updating metadata.
		 * Used specifically to track changes in the _groups metadata.
		 *
		 * Structure: [ (string) $post_id => array( 'groups_group_start_date' => $timestamp, 'groups_group_end_date' => $timestamp ) ]
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $before_group_meta = array();

		/**
		 * Miscellaneous single-value metadata store for tracking before-state of metadata updates.
		 * Structure: [ (string) $post_id ][ $meta_key ] = $meta_value
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $ld_misc_metadata = array();

		/**
		 * Course users before update.
		 * Structure: [ (int) $course_id => array( $user_id_1, $user_id_2, ... ) ]
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $before_course_users = array();

		/**
		 * Group users before update.
		 * Structure: [ (int) $group_id => array( $user_id_1, $user_id_2, ... ) ]
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $before_group_users = array();

		/**
		 * Stores the source post ID during a duplication/cloning request.
		 *
		 * @var int|null
		 *
		 * @since 5.6.0
		 */
		private static $duplication_source_id = null;

		/**
		 * Original LearnDash dates captured early in admin_init before any meta updates.
		 *
		 * Structure: [ (int) $post_id => array(
		 *     'post_type'  => 'sfwd-courses' or 'groups',
		 *     'start_date' => $timestamp,
		 *     'end_date'   => $timestamp,
		 * )]
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $before_ld_start_end_dates = array();

		/**
		 * Pending date change events to be checked after post save completes.
		 * Used to prevent duplicate events from LearnDash's multiple meta updates,
		 * since Learndash will trigger update meta twice: one for start date and one for end date.
		 *
		 * Structure: [ (int) $post_id => array(
		 *     'meta_key' => '_sfwd-courses' or '_groups',
		 *     'checked'  => false,
		 * )]
		 *
		 * @var array
		 *
		 * @since 5.6.0
		 */
		private static $pending_date_events = array();

		/**
		 * Loads all the plugin dependencies early.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function early_init() {
			if ( LearnDash_Helper::is_learndash_active() ) {
				\add_filter(
					'wsal_default_disabled_alerts',
					array( LearnDash_Custom_Alerts::class, 'add_default_disabled_alerts' )
				);

				self::maybe_apply_first_detection_disabled_alerts();

				\add_filter(
					'wsal_event_objects',
					array( LearnDash_Helper::class, 'wsal_learndash_add_custom_event_objects' ),
					10,
					2
				);

				\add_filter(
					'wsal_ignored_custom_post_types',
					array( LearnDash_Helper::class, 'wsal_learndash_add_custom_ignored_cpt' )
				);

				// @premium:start
				\add_action( 'learndash_transaction_created', array( __CLASS__, 'learndash_transaction_created_trigger' ), 10, 1 );
				// @premium:end
			}
		}

		/**
		 * Hook events related to sensor.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function init() {
			if ( LearnDash_Helper::is_learndash_active() && \is_user_logged_in() ) {
				\add_action( 'admin_init', array( __CLASS__, 'store_prev_user_activity_data' ) );
				\add_action( 'admin_init', array( __CLASS__, 'maybe_save_duplicate_action_post_id' ) );
				\add_action( 'wp_after_insert_post', array( __CLASS__, 'ld_saved_post_event_triggers' ), 10, 4 );
				\add_action( 'wp_trash_post', array( __CLASS__, 'ld_post_trashed' ), 10, 1 );
				\add_action( 'before_delete_post', array( __CLASS__, 'ld_post_perma_deleted' ), 10, 2 );
				\add_action( 'untrash_post', array( __CLASS__, 'ld_post_restored' ), 10, 1 );

				\add_action( 'create_term', array( __CLASS__, 'ld_taxonomy_creation_triggers' ), 10, 4 );
				\add_action( 'delete_term', array( __CLASS__, 'ld_taxonomy_deletion_triggers' ), 10, 5 );

				// @premium:start
				\add_action( 'admin_init', array( __CLASS__, 'capture_before_ld_start_end_dates' ) );
				\add_action( 'set_object_terms', array( __CLASS__, 'post_terms_changed' ), 10, 6 );
				\add_action( 'update_post_meta', array( __CLASS__, 'ld_before_meta_update' ), 10, 4 );
				\add_action( 'updated_post_meta', array( __CLASS__, 'ld_after_meta_update' ), 10, 4 );

				\add_filter( 'wp_update_term_data', array( __CLASS__, 'maybe_trigger_ld_taxonomy_change_events' ), 10, 4 );
				\add_action( 'learndash_update_course_access', array( __CLASS__, 'track_student_enroll_triggers' ), 10, 4 );
				\add_action( 'ld_added_group_access', array( __CLASS__, 'track_group_student_enroll_triggers' ), 10, 2 );
				\add_filter( 'wsal_ignore_user_meta_event', array( __CLASS__, 'ignore_learndash_user_meta_events' ), 10, 4 );
				\add_action( 'learndash_update_user_activity', array( __CLASS__, 'track_activity_events' ), 10, 1 );
				\add_action( 'wp_pro_quiz_completed_quiz', array( __CLASS__, 'track_quiz_answers' ), 10, 1 );
				// @premium:end
			}
		}

		/**
		 * Triggered when a user accesses the admin area.
		 *
		 * @since 5.6.0
		 */
		public static function store_prev_user_activity_data() {
			// Verify nonce before accessing any POST data.
			$nonce   = isset( $_POST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_POST['_wpnonce'] ) ) : '';
			$post_id = isset( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;

			if ( empty( $nonce ) || ! \wp_verify_nonce( $nonce, 'update-post_' . $post_id ) ) {
				return;
			}

			$post_type = \sanitize_text_field( \wp_unslash( $_POST['post_type'] ?? '' ) );

			if ( 'sfwd-courses' === $post_type ) {
				if ( isset( $_POST['post_ID'] ) && isset( $_POST['learndash_course_users_nonce'] ) ) {
					$post_id = (int) $_POST['post_ID'];

					if ( \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST['learndash_course_users_nonce'] ) ), 'learndash_course_users_nonce_' . $post_id ) ) {
						if ( \function_exists( 'learndash_get_course_users_access_from_meta' ) ) {
							self::$before_course_users[ $post_id ] = \learndash_get_course_users_access_from_meta( $post_id );
						}
					}
				}
			}

			if ( 'groups' === $post_type ) {
				if ( isset( $_POST['post_ID'] ) ) {
					$post_id      = (int) $_POST['post_ID'];
					$nonce_field  = 'learndash_group_users-' . $post_id . '-nonce';
					$nonce_action = 'learndash_group_users-' . $post_id;

					if ( isset( $_POST[ $nonce_field ] ) ) {
						if ( \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST[ $nonce_field ] ) ), $nonce_action ) ) {
							if ( \function_exists( 'learndash_get_groups_user_ids' ) ) {
								self::$before_group_users[ $post_id ] = \learndash_get_groups_user_ids( $post_id, true );
							}
						}
					}
				}
			}
		}

		/**
		 * Captures original LearnDash dates from DB before any meta updates.
		 * This method handles both courses and groups.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function capture_before_ld_start_end_dates() {
			// Verify nonce before accessing any POST data.
			$nonce   = isset( $_POST['_wpnonce'] ) ? \sanitize_text_field( \wp_unslash( $_POST['_wpnonce'] ) ) : '';
			$post_id = isset( $_POST['post_ID'] ) ? (int) $_POST['post_ID'] : 0;

			if ( empty( $nonce ) || ! \wp_verify_nonce( $nonce, 'update-post_' . $post_id ) ) {
				return;
			}

			$post_type = \sanitize_text_field( \wp_unslash( $_POST['post_type'] ?? '' ) );

			// Only process courses and groups.
			if ( 'sfwd-courses' !== $post_type && 'groups' !== $post_type ) {
				return;
			}

			$post_dates = LearnDash_Helper::get_ld_start_end_dates_meta( $post_id, $post_type );

			if ( isset( $post_dates['start_date'] ) && isset( $post_dates['end_date'] ) ) {
				// Store original dates.
				self::$before_ld_start_end_dates[ $post_id ] = array(
					'post_type'  => $post_type,
					'start_date' => $post_dates['start_date'],
					'end_date'   => $post_dates['end_date'],
				);
			}
		}

		/**
		 * Checks and triggers pending date change events after post save completes.
		 * This fires once per request after all LearnDash meta updates are complete,
		 * preventing duplicate events from multiple meta_update hook calls that would happen with LearnDash.
		 *
		 * @param int $post_id - Post ID.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function maybe_trigger_start_end_date_events( int $post_id ) {

			// Check if this post has pending date events to check.
			if ( ! isset( self::$pending_date_events[ $post_id ] ) ) {
				return;
			}

			// Verify we have original dates captured.
			if ( ! isset( self::$before_ld_start_end_dates[ $post_id ] ) ) {
				return;
			}

			// Skip if already checked.
			if ( self::$pending_date_events[ $post_id ]['checked'] ) {
				return;
			}

			// Mark as checked to prevent duplicate processing.
			self::$pending_date_events[ $post_id ]['checked'] = true;

			// Get the meta key to check.
			$meta_key = self::$pending_date_events[ $post_id ]['meta_key'];

			// Get values after updates from database after all meta updates.
			$final_meta     = \get_post_meta( $post_id, $meta_key, true );
			$final_metadata = \maybe_unserialize( $final_meta );

			if ( ! \is_array( $final_metadata ) ) {

				// Clean up for this post.
				unset( self::$pending_date_events[ $post_id ] );
				unset( self::$before_ld_start_end_dates[ $post_id ] );
				return;
			}

			// Get original dates from Phase 1 capture.
			$original_dates = self::$before_ld_start_end_dates[ $post_id ];

			// Determine event ID and date keys based on meta_key.
			if ( '_sfwd-courses' === $meta_key ) {
				$start_date_key = 'sfwd-courses_course_start_date';
				$end_date_key   = 'sfwd-courses_course_end_date';
				$event_id       = 11056;
			} elseif ( '_groups' === $meta_key ) {
				$start_date_key = 'groups_group_start_date';
				$end_date_key   = 'groups_group_end_date';
				$event_id       = 11507;
			} else {
				return;
			}

			/**
			 * Compare dates
			 */
			$original_start = LearnDash_Helper::cast_date_to_float( $original_dates['start_date'] );
			$original_end   = LearnDash_Helper::cast_date_to_float( $original_dates['end_date'] );
			$final_start    = LearnDash_Helper::cast_date_to_float( $final_metadata[ $start_date_key ] ?? '' );
			$final_end      = LearnDash_Helper::cast_date_to_float( $final_metadata[ $end_date_key ] ?? '' );

			// Only trigger if something actually changed.
			if ( $original_start === $final_start && $original_end === $final_end ) {
				// Clean up for this post.
				unset( self::$pending_date_events[ $post_id ] );
				unset( self::$before_ld_start_end_dates[ $post_id ] );
				return;
			}

			/**
			 * Format dates for display.
			 *
			 * LD format in case of need:
			 * $formatted_old_start = empty( $before_start_date ) ? $not_set_string : \date_i18n( 'd F Y @ H:i', (int) $before_start_date );
			 * $formatted_new_start = empty( $after_start_date ) ? $not_set_string : \date_i18n( 'd F Y @ H:i', (int) $after_start_date );
			 * $formatted_old_end   = empty( $before_end_date ) ? $not_set_string : \date_i18n( 'd F Y @ H:i', (int) $before_end_date );
			 * $formatted_new_end   = empty( $after_end_date ) ? $not_set_string : \date_i18n( 'd F Y @ H:i', (int) $after_end_date );
			 */
			$not_set_string      = \esc_html__( 'Not set', 'wp-security-audit-log' );
			$formatted_old_start = empty( $original_start ) ? $not_set_string : DateTime_Formatter_Helper::get_formatted_date_time( $original_start, 'datetime' );
			$formatted_new_start = empty( $final_start ) ? $not_set_string : DateTime_Formatter_Helper::get_formatted_date_time( $final_start, 'datetime' );
			$formatted_old_end   = empty( $original_end ) ? $not_set_string : DateTime_Formatter_Helper::get_formatted_date_time( $original_end, 'datetime' );
			$formatted_new_end   = empty( $final_end ) ? $not_set_string : DateTime_Formatter_Helper::get_formatted_date_time( $final_end, 'datetime' );

			$event_variables = array(
				'PostTitle'      => \get_the_title( $post_id ),
				'PostID'         => $post_id,
				'OldStartDate'   => $formatted_old_start,
				'NewStartDate'   => $formatted_new_start,
				'OldEndDate'     => $formatted_old_end,
				'NewEndDate'     => $formatted_new_end,
				'EditorLinkPost' => \esc_url( \get_edit_post_link( $post_id ) ),
			);

			Alert_Manager::trigger_event( $event_id, $event_variables );

			// Clean up for this post.
			unset( self::$pending_date_events[ $post_id ] );
			unset( self::$before_ld_start_end_dates[ $post_id ] );
		}

		/**
		 * Detects if a duplication action is being performed and stores the source post ID.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function maybe_save_duplicate_action_post_id() {

			// Check for common duplication actions in $_REQUEST.
			$action = isset( $_REQUEST['action'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['action'] ) ) : '';

			$ld_clone_actions = array(
				'learndash_cloning_action_course',
				'learndash_cloning_action_lesson',
				'learndash_cloning_action_topic',
			);

			if ( empty( $action ) || ! in_array( $action, $ld_clone_actions, true ) ) {
				return;
			}

			// Extract and sanitize object_id and nonce from REQUEST.
			$object_id = isset( $_REQUEST['object_id'] ) ? (int) $_REQUEST['object_id'] : 0;
			$nonce     = isset( $_REQUEST['nonce'] ) ? \sanitize_text_field( \wp_unslash( $_REQUEST['nonce'] ) ) : '';

			// Verify nonce with LearnDash's action format.
			if ( empty( $nonce ) || ! \wp_verify_nonce( $nonce, $action . $object_id ) ) {
				return;
			}

			// Store the clone source post ID for later use.
			self::$duplication_source_id = $object_id;
		}

		/**
		 * Ignore LearnDash user meta events.
		 *
		 * @param bool   $ignore_event - True if ignore meta event, false if not.
		 * @param string $meta_key     - Meta key.
		 * @param mixed  $meta_value   - Meta value.
		 * @param int    $user_id      - User ID.
		 *
		 * @return bool
		 *
		 * @since 5.6.0
		 */
		public static function ignore_learndash_user_meta_events( $ignore_event, $meta_key, $meta_value, $user_id ) {

			if ( $ignore_event ) {
				return $ignore_event;
			}

			/**
			 * Ignore LearnDash course enrollment meta keys.
			 */
			if ( \preg_match( '/^learndash_course_\d+_enrolled_at$/', $meta_key ) || \preg_match( '/^course_\d+_access_from$/', $meta_key ) ) {
				return true;
			}

			/**
			 * Ignore LearnDash group enrollment meta keys.
			 */
			if ( \preg_match( '/^learndash_group_\d+_enrolled_at$/', $meta_key ) || \preg_match( '/^group_\d+_access_from$/', $meta_key ) || \preg_match( '/^learndash_group_users_\d+$/', $meta_key ) ) {
				return true;
			}

			/**
			 * Ignore LearnDash quiz-related meta keys.
			 */
			if ( \preg_match( '/^quiz_time_\d+$/', $meta_key ) ) {
				return true;
			}

			return $ignore_event;
		}

		/**
		 * Trigger the create event for LearnDash posts
		 *
		 * @param string $post_type - The post type, used to trigger the correct event.
		 * @param array  $event_variables - Event variables.
		 *
		 * @since 5.6.0
		 */
		public static function trigger_ld_created_post_event( $post_type, $event_variables ) {
			if ( 'sfwd-courses' === $post_type ) {
				Alert_Manager::trigger_event( 11000, $event_variables );
			} elseif ( 'sfwd-lessons' === $post_type ) {
				Alert_Manager::trigger_event( 11200, $event_variables );
			} elseif ( 'sfwd-topic' === $post_type ) {
				Alert_Manager::trigger_event( 11400, $event_variables );
			} elseif ( 'groups' === $post_type ) {
				Alert_Manager::trigger_event( 11500, $event_variables );
			} elseif ( 'sfwd-certificates' === $post_type ) {
				// @premium:start
				Alert_Manager::trigger_event( 11600, $event_variables );
				// @premium:end
			}
		}

		/**
		 * Trigger the publish event for LearnDash posts
		 *
		 * @param string $post_type - The post type, used to trigger the correct event.
		 * @param array  $event_variables - Event variables.
		 *
		 * @since 5.6.0
		 */
		public static function trigger_ld_published_post_event( $post_type, $event_variables ) {
			if ( 'sfwd-courses' === $post_type ) {
				Alert_Manager::trigger_event( 11001, $event_variables );
			} elseif ( 'sfwd-lessons' === $post_type ) {
				Alert_Manager::trigger_event( 11201, $event_variables );
			} elseif ( 'sfwd-topic' === $post_type ) {
				Alert_Manager::trigger_event( 11401, $event_variables );
			} elseif ( 'groups' === $post_type ) {
				Alert_Manager::trigger_event( 11501, $event_variables );
			} elseif ( 'sfwd-certificates' === $post_type ) {
				// @premium:start
				Alert_Manager::trigger_event( 11601, $event_variables );
				// @premium:end
			}
		}

		/**
		 * Triggers duplication event if a source post ID was detected.
		 *
		 * @param \WP_Post $post                 - The duplicated post object.
		 * @param array    $post_event_variables - Event variables for the post.
		 *
		 * @return bool - True if duplication event was triggered, false otherwise.
		 *
		 * @since 5.6.0
		 */
		public static function maybe_trigger_duplication_event( $post, $post_event_variables ) {

			if ( self::$duplication_source_id && LearnDash_Helper::is_post_duplicate( $post, self::$duplication_source_id ) ) {

				// Override PostTitle with source post.
				$post_event_variables['PostTitle'] = \esc_html( \get_the_title( self::$duplication_source_id ) );

				// Get categories from the SOURCE post.
				$post_event_variables['Categories']     = LearnDash_Helper::get_post_categories( self::$duplication_source_id );
				$post_event_variables['LdPostCategory'] = LearnDash_Helper::get_ld_post_categories( self::$duplication_source_id, $post->post_type );

				if ( 'sfwd-courses' === $post->post_type ) {
					Alert_Manager::trigger_event( 11006, $post_event_variables );

					return true;
				} elseif ( 'sfwd-lessons' === $post->post_type ) {
					Alert_Manager::trigger_event( 11209, $post_event_variables );

					return true;
				} elseif ( 'sfwd-topic' === $post->post_type ) {
					Alert_Manager::trigger_event( 11406, $post_event_variables );

					return true;
				}
			}

			return false;
		}

		/**
		 * Learndash event triggers whenever a user saves a post: create, publish, update.
		 *
		 * @param int           $post_id - Post ID.
		 * @param \WP_Post      $post - Post object that was just updated.
		 * @param bool          $update - Whether this is an existing post being updated.
		 * @param null|\WP_Post $post_before - Null for new posts, the WP_Post object prior to the update for updated posts.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_saved_post_event_triggers( $post_id, $post, $update, $post_before ) {

			if ( 'auto-draft' === $post->post_status || 'trash' === $post->post_status ) {
				return;
			}

			$ld_post_types = LearnDash_Helper::get_custom_post_types();

			if ( ! \in_array( $post->post_type, $ld_post_types, true ) ) {
				return;
			}

			$post_event_variables                   = LearnDash_Helper::build_ld_post_event_variables( $post );
			$post_event_variables['PostStatus']     = $post->post_status;
			$post_event_variables['EditorLinkPost'] = \esc_url( \get_edit_post_link( $post->ID ) );

			// Check for course UI post creation.
			if ( $post_before && Learndash_Helper::ld_post_created_via_ld_course_builder( $post_before, $post, $update ) ) {
				self::trigger_ld_created_post_event( $post->post_type, $post_event_variables );

				// Return to avoid further processing.
				return;
			}

			// Check for duplication.
			if ( self::maybe_trigger_duplication_event( $post, $post_event_variables ) ) {
				// Return to avoid further processing.
				return;
			}

			/**
			 * A post is considered truly created if it's displayed in its post list.
			 */
			if ( ( 'draft' === $post->post_status || 'publish' === $post->post_status ) && $update && 'auto-draft' === $post_before->post_status ) {
				self::trigger_ld_created_post_event( $post->post_type, $post_event_variables );
			}

			if ( 'publish' === $post->post_status && $update && ( 'draft' === $post_before->post_status || 'auto-draft' === $post_before->post_status ) ) {

				// Unset PostStatus from variables, it's not needed in published event.
				unset( $post_event_variables['PostStatus'] );

				self::trigger_ld_published_post_event( $post->post_type, $post_event_variables );

				// @premium:start
				self::check_author_change( $post_before, $post );
				// @premium:end

				// Return to avoid further processing.
				return;

			} elseif ( $post_before && 'auto-draft' !== $post_before->post_status ) {

				// @premium:start
				self::check_author_change( $post_before, $post );
				self::check_group_users_change( $post_id );

				// Check for pending date events (fires once after all meta updates complete).
				self::maybe_trigger_start_end_date_events( $post_id );

				self::check_course_users_change( $post_id );
				// @premium:end
			}
		}

		/**
		 * Detect when a Learndash post is moved to trash.
		 *
		 * @param int $post_id - Post ID.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_post_trashed( $post_id ) {
			$post = \get_post( $post_id );

			$ld_post_types = LearnDash_Helper::get_custom_post_types();

			if ( ! in_array( $post->post_type, $ld_post_types, true ) ) {
				return;
			}

			$event_variables               = LearnDash_Helper::build_ld_post_event_variables( $post );
			$event_variables['PostStatus'] = $post->post_status;

			if ( 'sfwd-courses' === $post->post_type ) {
				Alert_Manager::trigger_event( 11003, $event_variables );
			} elseif ( 'sfwd-lessons' === $post->post_type ) {
				Alert_Manager::trigger_event( 11206, $event_variables );
			} elseif ( 'sfwd-topic' === $post->post_type ) {
				Alert_Manager::trigger_event( 11404, $event_variables );
			} elseif ( 'groups' === $post->post_type ) {
				Alert_Manager::trigger_event( 11503, $event_variables );
			} elseif ( 'sfwd-certificates' === $post->post_type ) {
				// @premium:start
				Alert_Manager::trigger_event( 11603, $event_variables );
				// @premium:end
			}
		}

		/**
		 * Detect when a Learndash post is permanently deleted.
		 *
		 * @param int      $post_id - Post ID.
		 * @param \WP_Post $post - Post object.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_post_perma_deleted( $post_id, $post ) {

			$ld_post_types = LearnDash_Helper::get_custom_post_types();

			if ( ! in_array( $post->post_type, $ld_post_types, true ) ) {
				return;
			}

			$event_variables = LearnDash_Helper::build_ld_post_event_variables( $post );

			if ( 'sfwd-courses' === $post->post_type ) {
				Alert_Manager::trigger_event( 11004, $event_variables );
			} elseif ( 'sfwd-lessons' === $post->post_type ) {
				Alert_Manager::trigger_event( 11207, $event_variables );
			} elseif ( 'sfwd-topic' === $post->post_type ) {
				Alert_Manager::trigger_event( 11405, $event_variables );
			} elseif ( 'groups' === $post->post_type ) {
				Alert_Manager::trigger_event( 11504, $event_variables );
			} elseif ( 'sfwd-certificates' === $post->post_type ) {
				// @premium:start
				Alert_Manager::trigger_event( 11604, $event_variables );
				// @premium:end
			}
		}

		/**
		 * Detect when a Learndash post is restored from trash.
		 *
		 * @param int $post_id - Post ID.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_post_restored( $post_id ) {
			$post = \get_post( $post_id );

			$ld_post_types = LearnDash_Helper::get_custom_post_types();

			if ( ! in_array( $post->post_type, $ld_post_types, true ) ) {
				return;
			}

			$event_variables                   = LearnDash_Helper::build_ld_post_event_variables( $post );
			$event_variables['EditorLinkPost'] = \esc_url( \get_edit_post_link( $post->ID ) );

			if ( 'sfwd-courses' === $post->post_type ) {
				Alert_Manager::trigger_event( 11005, $event_variables );
			} elseif ( 'sfwd-lessons' === $post->post_type ) {
				Alert_Manager::trigger_event( 11208, $event_variables );
			} elseif ( 'groups' === $post->post_type ) {
				Alert_Manager::trigger_event( 11505, $event_variables );
			} elseif ( 'sfwd-certificates' === $post->post_type ) {
				// @premium:start
				Alert_Manager::trigger_event( 11605, $event_variables );
				// @premium:end
			}
		}

		// @premium:start
		/**
		 * Check if the author of a LearnDash post has changed.
		 *
		 * @param \WP_Post $oldpost - Old post object.
		 * @param \WP_Post $newpost - New post object.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function check_author_change( $oldpost, $newpost ) {
			$ld_post_types = LearnDash_Helper::get_custom_post_types();

			if ( ! \in_array( $newpost->post_type, $ld_post_types, true ) ) {
				return;
			}

			if ( $oldpost->post_author !== $newpost->post_author ) {
				$old_author      = \get_userdata( (int) $oldpost->post_author );
				$old_author_name = ( is_object( $old_author ) ) ? $old_author->display_name : \esc_html__( 'N/A', 'wp-security-audit-log' );
				$new_author      = \get_userdata( (int) $newpost->post_author );
				$new_author_name = ( is_object( $new_author ) ) ? $new_author->display_name : \esc_html__( 'N/A', 'wp-security-audit-log' );

				$event_variables = array(
					'PostID'         => $newpost->ID,
					'PostTitle'      => $newpost->post_title,
					'PostStatus'     => $newpost->post_status,
					'OldAuthor'      => $old_author_name,
					'NewAuthor'      => $new_author_name,
					'EditorLinkPost' => \esc_url( \get_edit_post_link( $newpost->ID ) ),
				);

				if ( 'sfwd-courses' === $newpost->post_type ) {
					Alert_Manager::trigger_event( 11002, $event_variables );
				} elseif ( 'sfwd-lessons' === $newpost->post_type ) {
					Alert_Manager::trigger_event( 11202, $event_variables );
				} elseif ( 'sfwd-topic' === $newpost->post_type ) {
					Alert_Manager::trigger_event( 11403, $event_variables );
				} elseif ( 'groups' === $newpost->post_type ) {
					Alert_Manager::trigger_event( 11502, $event_variables );
				} elseif ( 'sfwd-certificates' === $newpost->post_type ) {
					Alert_Manager::trigger_event( 11602, $event_variables );
				}
			}
		}

		/**
		 * Handles term changes for a given object.
		 *
		 * @param int    $object_id   Object ID.
		 * @param array  $terms       An array of object term IDs or slugs.
		 * @param array  $tt_ids      An array of term taxonomy IDs.
		 * @param string $taxonomy    Taxonomy slug.
		 * @param bool   $append      Whether to append new terms to the old terms.
		 * @param array  $old_tt_ids  Old array of term taxonomy IDs.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function post_terms_changed( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
			$post = \get_post( $object_id );

			if ( \is_wp_error( $post ) ) {
				return;
			}

			if ( null === $post ) {
				return;
			}

			// Avoid triggering term change events if the post was just cloned.
			$course_was_cloned_recently = Alert_Manager::was_triggered_recently( 11006 );
			$lesson_was_cloned_recently = Alert_Manager::was_triggered_recently( 11209 );
			$topic_was_cloned_recently  = Alert_Manager::was_triggered_recently( 11406 );

			if ( $course_was_cloned_recently || $lesson_was_cloned_recently || $topic_was_cloned_recently ) {
				return;
			}

			// Check if post type is one of learndash custom post types.
			$ld_post_types = LearnDash_Helper::get_custom_post_types();

			if ( ! \in_array( $post->post_type, $ld_post_types, true ) ) {
				return;
			}

			if ( 'auto-draft' === $post->post_status ) {
				return;
			}

			// If there are no changes, return early.
			if ( array_diff( $tt_ids, $old_tt_ids ) === array() && array_diff( $old_tt_ids, $tt_ids ) === array() ) {
				return;
			}

			$no_categories_string = \esc_html__( 'No categories', 'wp-security-audit-log' );
			$no_tags_string       = \esc_html__( 'No tags', 'wp-security-audit-log' );

			$old_taxonomy = LearnDash_Helper::get_term_names_from_ids( $old_tt_ids, $taxonomy );
			$new_taxonomy = LearnDash_Helper::get_term_names_from_ids( $tt_ids, $taxonomy );

			$event_variables                       = LearnDash_Helper::build_ld_post_event_variables( $post );
			$event_variables['PostStatus']         = $post->post_status;
			$event_variables['OldCategories']      = $old_taxonomy ? $old_taxonomy : $no_categories_string;
			$event_variables['NewCategories']      = $new_taxonomy ? $new_taxonomy : $no_categories_string;
			$event_variables['EditorLinkPost']     = \esc_url( \get_edit_post_link( $post->ID ) );
			$event_variables['PostUrlIfPublished'] = \get_permalink( $post->ID );

			if ( 'post_tag' === $taxonomy || 'ld_course_tag' === $taxonomy || 'ld_lesson_tag' === $taxonomy || 'ld_topic_tag' === $taxonomy || 'ld_group_tag' === $taxonomy ) {
				$event_variables['OldTags'] = $old_taxonomy ? $old_taxonomy : $no_tags_string;
				$event_variables['NewTags'] = $new_taxonomy ? $new_taxonomy : $no_tags_string;
			}

			// If this is the native WP category taxonomy, we trigger specific events.
			if ( 'category' === $taxonomy ) {
				if ( 'sfwd-courses' === $post->post_type ) {
					Alert_Manager::trigger_event( 11007, $event_variables );
				} elseif ( 'sfwd-lessons' === $post->post_type ) {
					Alert_Manager::trigger_event( 11203, $event_variables );
				} elseif ( 'sfwd-topic' === $post->post_type ) {
					Alert_Manager::trigger_event( 11402, $event_variables );
				}
			} elseif ( 'post_tag' === $taxonomy ) {
				if ( 'sfwd-courses' === $post->post_type ) {
					Alert_Manager::trigger_event( 11009, $event_variables );
				} elseif ( 'sfwd-lessons' === $post->post_type ) {
					Alert_Manager::trigger_event( 11214, $event_variables );
				} elseif ( 'sfwd-topic' === $post->post_type ) {
					Alert_Manager::trigger_event( 11409, $event_variables );
				}
			} elseif ( 'ld_course_category' === $taxonomy ) {
				if ( 'sfwd-courses' === $post->post_type ) {
					Alert_Manager::trigger_event( 11008, $event_variables );
				}
			} elseif ( 'ld_course_tag' === $taxonomy ) {
				if ( 'sfwd-courses' === $post->post_type ) {
					Alert_Manager::trigger_event( 11010, $event_variables );
				}
			} elseif ( 'ld_lesson_category' === $taxonomy ) {
				Alert_Manager::trigger_event( 11204, $event_variables );
			} elseif ( 'ld_lesson_tag' === $taxonomy ) {
				Alert_Manager::trigger_event( 11205, $event_variables );
			} elseif ( 'ld_topic_category' === $taxonomy ) {
				Alert_Manager::trigger_event( 11412, $event_variables );
			} elseif ( 'ld_topic_tag' === $taxonomy ) {
				Alert_Manager::trigger_event( 11413, $event_variables );
			}
		}

		/**
		 * Trigger lesson added/removed events based on ld_course_steps changes, if needed
		 *
		 * @param array $data - The new value of the ld_course_steps meta.
		 * @param int   $post_id - The post ID the meta is associated with.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_lesson_change_events( $data, $post_id ) {
			$sfwd_lessons = LearnDash_Helper::extract_learndash_items( $data, 'sfwd-lessons' );

			// Build current lesson IDs from structure.
			$current_lesson_ids = array_keys( $sfwd_lessons );

			$previous_lesson_ids = array();

			if ( isset( self::$before_course_steps[ (string) $post_id ] ) && \is_array( self::$before_course_steps[ (string) $post_id ] ) ) {
				$prev_course_steps = self::$before_course_steps[ (string) $post_id ];

				if ( isset( $prev_course_steps['lessons'] ) && \is_array( $prev_course_steps['lessons'] ) ) {
					$previous_lesson_ids = $prev_course_steps['lessons'];
				} else {
					$previous_lesson_ids = $prev_course_steps;
				}
			}

			$event_variables = array(
				'PostTitle'      => \get_the_title( $post_id ),
				'PostID'         => $post_id,
				'EditorLinkPost' => \esc_url( \get_edit_post_link( $post_id ) ),
			);

			// Detect newly added lessons.
			$added = array_values( array_diff( $current_lesson_ids, $previous_lesson_ids ) );

			if ( ! empty( $added ) ) {
				foreach ( $added as $lesson_id ) {
					$event_variables['LessonID']    = $lesson_id;
					$event_variables['LessonTitle'] = \get_the_title( $lesson_id );

					Alert_Manager::trigger_event( 11011, $event_variables );
				}
			}

			// Detect removed lessons.
			$removed = array_values( array_diff( $previous_lesson_ids, $current_lesson_ids ) );

			if ( ! empty( $removed ) ) {
				foreach ( $removed as $lesson_id ) {
					$event_variables['LessonID']    = $lesson_id;
					$event_variables['LessonTitle'] = \get_the_title( $lesson_id );

					Alert_Manager::trigger_event( 11012, $event_variables );
				}
			}

			// Detect lesson order changes (only for lessons that exist in both arrays).
			$common_lessons = \array_intersect( $previous_lesson_ids, $current_lesson_ids );

			// If there are common lessons, check if their order changed.
			if ( ! empty( $common_lessons ) ) {
				// Filter both arrays to only include common lessons, maintaining order.
				$prev_order = \array_values( \array_intersect( $previous_lesson_ids, $common_lessons ) );
				$curr_order = \array_values( \array_intersect( $current_lesson_ids, $common_lessons ) );

				// Check if the order is different.
				if ( $prev_order !== $curr_order ) {
					$event_variables['PreviousOrder'] = LearnDash_Helper::format_lesson_order_for_display( $previous_lesson_ids );
					$event_variables['NewOrder']      = LearnDash_Helper::format_lesson_order_for_display( $current_lesson_ids );

					Alert_Manager::trigger_event( 11017, $event_variables );
				}
			}
		}

		/**
		 * Trigger topic added/removed events based on ld_course_steps changes, if needed
		 *
		 * @param array $data - The new value of the ld_course_steps meta.
		 * @param int   $post_id - The post ID the meta is associated with.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_topic_change_events( $data, $post_id ) {
			$current_topics = LearnDash_Helper::extract_learndash_items( $data, 'sfwd-topic' );

			$previous_topics = array();

			if ( isset( self::$before_course_steps[ (string) $post_id ] ) && \is_array( self::$before_course_steps[ (string) $post_id ] ) ) {
				$prev_snap = self::$before_course_steps[ (string) $post_id ];
				if ( isset( $prev_snap['topics'] ) && \is_array( $prev_snap['topics'] ) ) {
					$previous_topics = $prev_snap['topics'];
				}
			}

			$course_event_variables = array(
				'PostTitle'      => \get_the_title( $post_id ),
				'EditorLinkPost' => \esc_url( \get_edit_post_link( $post_id ) ),
			);

			// Detect newly added topics.
			$added_topics = array_values( array_diff( $current_topics, $previous_topics ) );

			if ( ! empty( $added_topics ) ) {

				$current_lessons_in_course = $data['steps']['h']['sfwd-lessons'] ?? array();

				foreach ( $current_lessons_in_course as $lesson_id => $lesson_data ) {
					$lesson_topics = $lesson_data['sfwd-topic'] ?? array();

					foreach ( $lesson_topics as $lesson_topic_id => $lesson_topic_data ) {
						if ( in_array( $lesson_topic_id, $added_topics, true ) ) {
							// Course event level for added topic.
							$course_event_variables['PostID']     = $post_id;
							$course_event_variables['TopicID']    = $lesson_topic_id;
							$course_event_variables['TopicTitle'] = \get_the_title( $lesson_topic_id );

							Alert_Manager::trigger_event( 11013, $course_event_variables );

							// Lesson event level for added topic.
							$lesson_event_variables = array(
								'PostID'         => $lesson_id,
								'LessonTitle'    => \get_the_title( $lesson_id ),
								'TopicTitle'     => \get_the_title( $lesson_topic_id ),
								'EditorLinkPost' => \esc_url( \get_edit_post_link( $lesson_id ) ),
							);

							Alert_Manager::trigger_event( 11210, $lesson_event_variables );
						}
					}
				}
			}

			// Detect removed topics.
			$removed_topics = array_values( array_diff( $previous_topics, $current_topics ) );

			if ( ! empty( $removed_topics ) ) {
				$prev_lessons_in_course = $prev_snap['raw_metadata']['steps']['h']['sfwd-lessons'] ?? array();

				foreach ( $prev_lessons_in_course as $lesson_id => $lesson_data ) {
					$lesson_topics = $lesson_data['sfwd-topic'] ?? array();

					foreach ( $lesson_topics as $lesson_topic_id => $lesson_topic_data ) {
						if ( in_array( $lesson_topic_id, $removed_topics, true ) ) {

							// Course event level for removed topic.
							$course_event_variables['PostID']     = $post_id;
							$course_event_variables['TopicID']    = $lesson_topic_id;
							$course_event_variables['TopicTitle'] = \get_the_title( $lesson_topic_id );

							Alert_Manager::trigger_event( 11014, $course_event_variables );

							// Lesson event level for removed topic.
							$lesson_event_variables = array(
								'PostID'      => $lesson_id,
								'LessonTitle' => \get_the_title( $lesson_id ),
								'TopicTitle'  => \get_the_title( $lesson_topic_id ),
							);

							Alert_Manager::trigger_event( 11211, $lesson_event_variables );
						}
					}
				}
			}
		}

		/**
		 * Trigger quiz added/removed events at course, lesson, and topic levels based on ld_course_steps changes.
		 *
		 * @param array $data - The new value of the ld_course_steps meta.
		 * @param int   $post_id - The post ID the meta is associated with.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_quiz_change_events_by_level( $data, $post_id ) {
			$current_quiz_map = LearnDash_Helper::map_quizzes_by_level( $data['steps']['h'] ?? array(), $post_id );

			$previous_quiz_map = array(
				'course_level' => array(),
				'lesson_level' => array(),
				'topic_level'  => array(),
			);

			if ( isset( self::$before_course_steps[ (string) $post_id ] ) && \is_array( self::$before_course_steps[ (string) $post_id ] ) ) {
				$prev_snap = self::$before_course_steps[ (string) $post_id ];

				if ( isset( $prev_snap['raw_metadata']['steps']['h'] ) ) {
					$previous_quiz_map = LearnDash_Helper::map_quizzes_by_level( $prev_snap['raw_metadata']['steps']['h'] ?? array(), $post_id );
				}
			}

			// Early return if no changes at any level.
			if ( $current_quiz_map === $previous_quiz_map ) {
				return;
			}

			$level_config = array(
				'course_level' => array(
					'add'    => 11015,
					'remove' => 11016,
				),
				'lesson_level' => array(
					'add'    => 11212,
					'remove' => 11213,
				),
				'topic_level'  => array(
					'add'    => 11410,
					'remove' => 11411,
				),
			);

			foreach ( $level_config as $level => $events ) {
				$current_ids  = array_column( $current_quiz_map[ $level ], 'quiz_id' );
				$previous_ids = array_column( $previous_quiz_map[ $level ], 'quiz_id' );

				$added   = array_diff( $current_ids, $previous_ids );
				$removed = array_diff( $previous_ids, $current_ids );

				foreach ( $current_quiz_map[ $level ] as $item ) {
					if ( in_array( $item['quiz_id'], $added, true ) ) {
						$event_variables = array(
							'PostID'         => $item['parent_id'],
							'QuizTitle'      => \get_the_title( $item['quiz_id'] ),
							'EditorLinkPost' => \esc_url( \get_edit_post_link( $item['parent_id'] ) ),
						);

						if ( 'course_level' === $level ) {
							$event_variables['PostTitle'] = \get_the_title( $post_id );
						} else {
							$event_variables['PostTitle'] = \get_the_title( $item['parent_id'] );
						}

						Alert_Manager::trigger_event( $events['add'], $event_variables );
					}
				}

				foreach ( $previous_quiz_map[ $level ] as $item ) {
					if ( in_array( $item['quiz_id'], $removed, true ) ) {
						$event_variables = array(
							'PostID'         => $item['parent_id'],
							'QuizTitle'      => \get_the_title( $item['quiz_id'] ),
							'EditorLinkPost' => \esc_url( \get_edit_post_link( $item['parent_id'] ) ),
						);

						if ( 'course_level' === $level ) {
							$event_variables['PostTitle'] = \get_the_title( $post_id );
						} else {
							$event_variables['PostTitle'] = \get_the_title( $item['parent_id'] );
						}

						Alert_Manager::trigger_event( $events['remove'], $event_variables );
					}
				}
			}
		}

		/**
		 * Triggers enrollment status change event when price type changes.
		 *
		 * @param int    $course_id - The course ID.
		 * @param string $before_price_type - Price type before the update.
		 * @param string $after_price_type - Price type after the update.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_enrollment_status_change_event( int $course_id, string $before_price_type, string $after_price_type ): void {
			$enrollment_labels = array(
				'open'      => \esc_html__( 'Open', 'wp-security-audit-log' ),
				'free'      => \esc_html__( 'Free', 'wp-security-audit-log' ),
				'paynow'    => \esc_html__( 'Buy Now', 'wp-security-audit-log' ),
				'subscribe' => \esc_html__( 'Recurring', 'wp-security-audit-log' ),
				'closed'    => \esc_html__( 'Closed', 'wp-security-audit-log' ),
			);

			$old_label = $enrollment_labels[ $before_price_type ] ?? $before_price_type;
			$new_label = $enrollment_labels[ $after_price_type ] ?? $after_price_type;

			Alert_Manager::trigger_event(
				11050,
				array(
					'PostTypeTitle'       => \get_the_title( $course_id ),
					'PostID'              => $course_id,
					'OldEnrollmentStatus' => $old_label,
					'NewEnrollmentStatus' => $new_label,
					'EditorLinkPost'      => \esc_url( \get_edit_post_link( $course_id ) ),
				)
			);
		}

		/**
		 * Triggers enrollment requirements change events.
		 *
		 * @param int   $course_id - The course ID.
		 * @param array $old_meta - The old course meta.
		 * @param array $new_meta - The new course meta.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_enrollment_requirements_change_events( int $course_id, $old_meta, $new_meta ): void {

			$before_course_req = $old_meta['sfwd-courses_requirements_for_enrollment'] ?? 'none';
			$after_course_req  = $new_meta['sfwd-courses_requirements_for_enrollment'] ?? 'none';

			$before_prereqs = $old_meta['sfwd-courses_course_prerequisite'] ?? array();
			$after_prereqs  = $new_meta['sfwd-courses_course_prerequisite'] ?? array();

			$before_course_points_access = $old_meta['sfwd-courses_course_points_access'] ?? '';
			$after_course_points_access  = $new_meta['sfwd-courses_course_points_access'] ?? '';

			$event_variables = array(
				'CourseName'      => \get_the_title( $course_id ),
				'PostID'          => $course_id,
				'EditorLinkPost'  => \esc_url( \get_edit_post_link( $course_id ) ),
				'OldRequirements' => LearnDash_Helper::get_enrollment_status_label( $old_meta ),
			);

			/**
			 * Event 11051: Requirements were disabled (either type disabled)
			 */
			if ( empty( $after_course_req ) && ! empty( $before_course_req ) ) {
				$event_variables['OldRequirements'] = LearnDash_Helper::get_enrollment_status_label( $old_meta );

				Alert_Manager::trigger_event( 11051, $event_variables );

				return;
			}

			/**
			 * Event 11052: Requirements were changed
			 */
			$course_requirements_changed     = $before_course_req !== $after_course_req;
			$course_required_courses_changed = $before_prereqs !== $after_prereqs;
			$course_required_points_changed  = $before_course_points_access !== $after_course_points_access;

			if ( 'course_prerequisite_enabled' === $after_course_req || 'course_points_enabled' === $after_course_req ) {

				if ( $course_requirements_changed || $course_required_courses_changed || $course_required_points_changed ) {
					$event_variables['NewRequirements'] = LearnDash_Helper::get_enrollment_status_label( $new_meta );

					// Double check the requirements key, did they really change?
					if ( $event_variables['OldRequirements'] === $event_variables['NewRequirements'] ) {
						return;
					}

					Alert_Manager::trigger_event( 11052, $event_variables );
				}
			}
		}

		/**
		 * Triggers access expiration change events.
		 *
		 * @param int   $course_id - The course ID.
		 * @param array $old_metadata - The old course metadata.
		 * @param array $new_metadata - The new course metadata.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_access_expiration_change_events( int $course_id, array $old_metadata, array $new_metadata ): void {

			$before_expire_access = $old_metadata['sfwd-courses_expire_access'] ?? '';
			$after_expire_access  = $new_metadata['sfwd-courses_expire_access'] ?? '';

			$before_expire_days = $old_metadata['sfwd-courses_expire_access_days'] ?? '';
			$after_expire_days  = $new_metadata['sfwd-courses_expire_access_days'] ?? '';

			$before_delete_progress = $old_metadata['sfwd-courses_expire_access_delete_progress'] ?? '';
			$after_delete_progress  = $new_metadata['sfwd-courses_expire_access_delete_progress'] ?? '';

			// Early return if nothing changed.
			if ( $before_expire_access === $after_expire_access && $before_expire_days === $after_expire_days && $before_delete_progress === $after_delete_progress ) {
				return;
			}

			$delete_progress_string = \esc_html__( 'Delete progress', 'wp-security-audit-log' );
			$keep_progress_string   = \esc_html__( 'Keep progress', 'wp-security-audit-log' );

			$event_variables = array(
				'CourseName'     => \get_the_title( $course_id ),
				'PostID'         => $course_id,
				'EditorLinkPost' => \esc_url( \get_edit_post_link( $course_id ) ),
			);

			// Event 11053: Expiration was set (enabled from disabled).
			if ( ( 'on' !== $before_expire_access && 'on' === $after_expire_access && (int) $after_expire_days > 0 ) || ( 0 === (int) $before_expire_days && (int) $after_expire_days > 0 ) ) {
				$event_variables['ExpirationDays']  = $after_expire_days;
				$event_variables['RetentionStatus'] = 'on' === $after_delete_progress ? $delete_progress_string : $keep_progress_string;

				Alert_Manager::trigger_event( 11053, $event_variables );

				// Exit early, to avoid triggering the other events as well.
				return;
			}

			/**
			 * Event 11055: Expiration was disabled.
			 */
			if ( ( 'on' === $before_expire_access && 'on' !== $after_expire_access ) || ( 'on' === $after_expire_access && 0 === (int) $after_expire_days ) ) {
				Alert_Manager::trigger_event( 11055, $event_variables );

				// Exit early, to avoid triggering edit event as well.
				return;
			}

			/**
			 * Event 11054: Expiration setting was modified.
			 */
			// Don't trigger if expiration is not active (either not 'on' or days are 0).
			if ( 'on' !== $after_expire_access || 0 === (int) $after_expire_days ) {
				return;
			}

			$event_variables['OldRetentionStatus'] = 'on' === $before_delete_progress ? $delete_progress_string : $keep_progress_string;
			$event_variables['NewRetentionStatus'] = 'on' === $after_delete_progress ? $delete_progress_string : $keep_progress_string;
			$event_variables['OldExpirationDays']  = (string) $before_expire_days;
			$event_variables['NewExpirationDays']  = (string) $after_expire_days;

			Alert_Manager::trigger_event( 11054, $event_variables );
		}

		/**
		 * Triggers group settings change events by checking for modifications in group metadata.
		 *
		 * @param int   $post_id - The group ID.
		 * @param array $old_metadata - The previous group metadata.
		 * @param array $new_metadata - The new group metadata.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function maybe_trigger_group_settings_change_events( $post_id, $old_metadata, $new_metadata ) {
			if ( \is_array( $new_metadata ) ) {
				// Check for group date changes - deferred to ld_saved_post_event_triggers to prevent duplicates.
				if ( ! isset( self::$pending_date_events[ $post_id ] ) ) {
					self::$pending_date_events[ $post_id ] = array(
						'meta_key' => '_groups', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'checked'  => false,
					);
				}
			}
		}

		/**
		 * Triggers certificate change events based on comparing previous and current certificate values.
		 *
		 * @param int          $course_id - The course ID.
		 * @param string|array $before_certificate - Certificate ID(s) before the update.
		 * @param string|array $after_certificate - Certificate ID(s) after the update.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_certificate_change_events( int $course_id, $before_certificate, $after_certificate ): void {
			$before_certs = \is_array( $before_certificate ) ? $before_certificate : ( empty( $before_certificate ) ? array() : array( $before_certificate ) );
			$after_certs  = \is_array( $after_certificate ) ? $after_certificate : ( empty( $after_certificate ) ? array() : array( $after_certificate ) );

			$event_variables = array(
				'CourseName'     => \get_the_title( $course_id ),
				'PostID'         => $course_id,
				'EditorLinkPost' => \esc_url( \get_edit_post_link( $course_id ) ),
			);

			// Find added certificates.
			$added_certs = \array_diff( $after_certs, $before_certs );
			foreach ( $added_certs as $cert_id ) {
				$event_variables['OldCertificateTitle'] = empty( $before_certs ) ? \esc_html__( 'no certificate', 'wp-security-audit-log' ) : \get_the_title( (int) \reset( $before_certs ) );
				$event_variables['NewCertificateTitle'] = \get_the_title( (int) $cert_id );

				Alert_Manager::trigger_event( 11018, $event_variables );
			}

			// Find removed certificates.
			$removed_certs = \array_diff( $before_certs, $after_certs );
			foreach ( $removed_certs as $cert_id ) {
				$event_variables['OldCertificateTitle'] = \get_the_title( (int) $cert_id );
				$event_variables['NewCertificateTitle'] = empty( $after_certs ) ? \esc_html__( 'no certificate', 'wp-security-audit-log' ) : \get_the_title( (int) \reset( $after_certs ) );

				Alert_Manager::trigger_event( 11019, $event_variables );
			}
		}

		/**
		 * Triggers student limit change event when seats limit changes.
		 *
		 * @param int   $course_id - The course ID.
		 * @param array $old_metadata - The old course metadata.
		 * @param array $new_metadata - The new course metadata.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_seats_limit_change_event( int $course_id, array $old_metadata, array $new_metadata ): void {
			$before_seats_limit = $old_metadata['sfwd-courses_course_seats_limit'] ?? '';
			$after_seats_limit  = $new_metadata['sfwd-courses_course_seats_limit'] ?? '';

			// Cast to string for comparison.
			$before_seats_limit = (string) $before_seats_limit;
			$after_seats_limit  = (string) $after_seats_limit;

			// Early return if nothing changed.
			if ( $before_seats_limit === $after_seats_limit ) {
				return;
			}

			$no_limit_string = \esc_html__( 'No limit', 'wp-security-audit-log' );

			// Format the limit values for display.
			$old_limit = ( empty( $before_seats_limit ) || '0' === $before_seats_limit ) ? $no_limit_string : $before_seats_limit;
			$new_limit = ( empty( $after_seats_limit ) || '0' === $after_seats_limit ) ? $no_limit_string : $after_seats_limit;

			Alert_Manager::trigger_event(
				11057,
				array(
					'CourseName'      => \get_the_title( $course_id ),
					'PostID'          => $course_id,
					'OldStudentLimit' => $old_limit,
					'NewStudentLimit' => $new_limit,
					'EditorLinkPost'  => \esc_url( \get_edit_post_link( $course_id ) ),
				)
			);
		}

		/**
		 * Triggers course, lesson, or topic duration change event.
		 *
		 * @param int   $post_id - The post ID (course, lesson, or topic).
		 * @param mixed $old_duration - Old duration in seconds.
		 * @param mixed $new_duration - New duration in seconds.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_trigger_duration_change_event( int $post_id, $old_duration, $new_duration ): void {
			$before_duration = (string) $old_duration;
			$after_duration  = (string) $new_duration;

			if ( $before_duration === $after_duration ) {
				return;
			}

			$formatted_old = LearnDash_Helper::format_duration_for_display( $old_duration );
			$formatted_new = LearnDash_Helper::format_duration_for_display( $new_duration );

			$post_type = \get_post_type( $post_id );

			if ( 'sfwd-courses' === $post_type ) {
				Alert_Manager::trigger_event(
					11059,
					array(
						'CourseName'     => \get_the_title( $post_id ),
						'PostID'         => $post_id,
						'OldDuration'    => $formatted_old,
						'NewDuration'    => $formatted_new,
						'EditorLinkPost' => \esc_url( \get_edit_post_link( $post_id ) ),
					)
				);
			} elseif ( 'sfwd-lessons' === $post_type ) {
				Alert_Manager::trigger_event(
					11250,
					array(
						'LessonName'     => \get_the_title( $post_id ),
						'PostID'         => $post_id,
						'OldDuration'    => $formatted_old,
						'NewDuration'    => $formatted_new,
						'EditorLinkPost' => \esc_url( \get_edit_post_link( $post_id ) ),
					)
				);
			} elseif ( 'sfwd-topic' === $post_type ) {
				Alert_Manager::trigger_event(
					11480,
					array(
						'TopicName'      => \get_the_title( $post_id ),
						'PostID'         => $post_id,
						'OldDuration'    => $formatted_old,
						'NewDuration'    => $formatted_new,
						'EditorLinkPost' => \esc_url( \get_edit_post_link( $post_id ) ),
					)
				);
			}
		}

		/**
		 * Fires just before post meta is updated. Capture ld_course_steps previous value.
		 *
		 * @param int    $meta_id   Meta ID.
		 * @param int    $post_id   Post ID.
		 * @param string $meta_key  Meta key being updated.
		 * @param mixed  $_meta_value New meta value (about to be set).
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_before_meta_update( $meta_id, $post_id, $meta_key, $_meta_value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

			if ( 'ld_course_steps' === $meta_key ) {
				self::$before_course_steps[ (string) $post_id ] = LearnDash_Helper::format_course_steps_metadata( $post_id, $meta_key );

			} elseif ( '_sfwd-courses' === $meta_key ) {
				$old_meta_record   = \get_post_meta( $post_id, $meta_key, true );
				$unserialized_meta = \maybe_unserialize( $old_meta_record );

				if ( \is_array( $unserialized_meta ) ) {

					self::$before_course_meta[ (string) $post_id ] = array(
						'certificate'                      => $unserialized_meta['sfwd-courses_certificate'] ?? '',
						'price_type'                       => $unserialized_meta['sfwd-courses_course_price_type'] ?? '',
						'sfwd-courses_course_prerequisite_enabled' => $unserialized_meta['sfwd-courses_course_prerequisite_enabled'] ?? '',
						'sfwd-courses_course_prerequisite' => $unserialized_meta['sfwd-courses_course_prerequisite'] ?? array(),
						'sfwd-courses_course_points_enabled' => $unserialized_meta['sfwd-courses_course_points_enabled'] ?? '',
						'sfwd-courses_course_points_access' => (string) ( $unserialized_meta['sfwd-courses_course_points_access'] ?? '' ),
						'sfwd-courses_requirements_for_enrollment' => $unserialized_meta['sfwd-courses_requirements_for_enrollment'] ?? '',
						'sfwd-courses_expire_access'       => $unserialized_meta['sfwd-courses_expire_access'] ?? '',
						'sfwd-courses_expire_access_days'  => $unserialized_meta['sfwd-courses_expire_access_days'] ?? '',
						'sfwd-courses_expire_access_delete_progress' => $unserialized_meta['sfwd-courses_expire_access_delete_progress'] ?? '',
						'sfwd-courses_course_start_date'   => $unserialized_meta['sfwd-courses_course_start_date'] ?? '',
						'sfwd-courses_course_end_date'     => $unserialized_meta['sfwd-courses_course_end_date'] ?? '',
						'sfwd-courses_course_seats_limit'  => $unserialized_meta['sfwd-courses_course_seats_limit'] ?? '',

					);
				}
			} elseif ( '_groups' === $meta_key ) {
				$old_meta_record   = \get_post_meta( $post_id, $meta_key, true );
				$unserialized_meta = \maybe_unserialize( $old_meta_record );

				if ( \is_array( $unserialized_meta ) ) {
					self::$before_group_meta[ (string) $post_id ] = array(
						'groups_group_start_date' => $unserialized_meta['groups_group_start_date'] ?? '',
						'groups_group_end_date'   => $unserialized_meta['groups_group_end_date'] ?? '',
					);
				}
			} elseif ( '_learndash_course_grid_duration' === $meta_key ) {
				$old_duration = \get_post_meta( $post_id, $meta_key, true );
				self::$ld_misc_metadata[ (string) $post_id ]['_learndash_course_grid_duration'] = $old_duration;
			}
		}

		/**
		 * Track changes to course content that happen via ld_course_steps meta. These are mostly content updates: lesson changes, topic changes, and similar.
		 *
		 * @param int   $post_id - Post ID of the course being updated.
		 * @param array $new_metadata - New meta value for ld_course_steps.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function maybe_trigger_course_content_edit_events( $post_id, array $new_metadata ) {

			// Extract all sfwd - lessons information from the meta structure.
			if ( \is_array( $new_metadata ) ) {

				self::maybe_trigger_lesson_change_events( $new_metadata, $post_id );
				self::maybe_trigger_topic_change_events( $new_metadata, $post_id );
				self::maybe_trigger_quiz_change_events_by_level( $new_metadata, $post_id );
			}
		}

		/**
		 * Track changes to course settings that happen via _sfwd-courses meta.
		 *
		 * @param int   $post_id - Post ID of the course being updated.
		 * @param array $old_metadata - Old meta value for _sfwd-courses.
		 * @param array $new_metadata - New meta value for _sfwd-courses.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function maybe_trigger_course_settings_change_events( $post_id, $old_metadata, $new_metadata ) {
			if ( Alert_Manager::was_triggered_recently( 11000 ) ) {
				return;
			}

			if ( \is_array( $new_metadata ) ) {
				$after_cert  = $new_metadata['sfwd-courses_certificate'] ?? '';
				$after_price = $new_metadata['sfwd-courses_course_price_type'] ?? '';

				// Check for certificate changes.
				if ( $old_metadata['certificate'] !== $after_cert ) {
					self::maybe_trigger_certificate_change_events(
						$post_id,
						$old_metadata['certificate'],
						$after_cert
					);
				}

				// Check for enrollment status (price type) changes.
				if ( $old_metadata['price_type'] !== $after_price ) {
					self::maybe_trigger_enrollment_status_change_event(
						$post_id,
						$old_metadata['price_type'],
						$after_price
					);
				}

				// Check for enrollment requirements (prerequisite or points) changes.
				self::maybe_trigger_enrollment_requirements_change_events(
					$post_id,
					$old_metadata,
					$new_metadata
				);

				// Check for access expiration changes.
				self::maybe_trigger_access_expiration_change_events( $post_id, $old_metadata, $new_metadata );

				// Check for course date changes - deferred to ld_saved_post_event_triggers to prevent duplicates.
				if ( ! isset( self::$pending_date_events[ $post_id ] ) ) {
					self::$pending_date_events[ $post_id ] = array(
						'meta_key' => '_sfwd-courses', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'checked'  => false,
					);
				}

				// Check for seats limit changes.
				self::maybe_trigger_seats_limit_change_event( $post_id, $old_metadata, $new_metadata );
			}
		}

		/**
		 * Track changes that happen via Learndash metadata.
		 *
		 * @param int    $meta_id   - ID of updated metadata entry.
		 * @param int    $post_id   - Post ID.
		 * @param string $meta_key  - Metadata key.
		 * @param mixed  $meta_value - Metadata value. This will be a PHP-serialized string representation of the value if the value is an array, an object, or itself a PHP-serialized string.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_after_meta_update( $meta_id, $post_id, $meta_key, $meta_value ) {
			$new_metadata = \maybe_unserialize( $meta_value );

			if ( '_edit_lock' === $meta_key ) {
				return;
			}

			if ( 'ld_course_steps' === $meta_key ) {
				self::maybe_trigger_course_content_edit_events( $post_id, $new_metadata );
			} elseif ( '_sfwd-courses' === $meta_key && isset( self::$before_course_meta[ (string) $post_id ] ) ) {
				$old_metadata = self::$before_course_meta[ (string) $post_id ];

				self::maybe_trigger_course_settings_change_events( $post_id, $old_metadata, $new_metadata );

				// Clean up stored state.
				unset( self::$before_course_meta[ (string) $post_id ] );
			} elseif ( '_groups' === $meta_key && isset( self::$before_group_meta[ (string) $post_id ] ) ) {
				$old_metadata = self::$before_group_meta[ (string) $post_id ];

				self::maybe_trigger_group_settings_change_events( $post_id, $old_metadata, $new_metadata );

				// Clean up stored state.
				unset( self::$before_group_meta[ (string) $post_id ] );
			} elseif ( '_learndash_course_grid_duration' === $meta_key && isset( self::$ld_misc_metadata[ (string) $post_id ]['_learndash_course_grid_duration'] ) ) {
				$old_duration = self::$ld_misc_metadata[ (string) $post_id ]['_learndash_course_grid_duration'];

				self::maybe_trigger_duration_change_event( $post_id, $old_duration, $new_metadata );

				/**
				 * Clean up stored state
				 */
				unset( self::$ld_misc_metadata[ (string) $post_id ]['_learndash_course_grid_duration'] );

				if ( empty( self::$ld_misc_metadata[ (string) $post_id ] ) ) {
					unset( self::$ld_misc_metadata[ (string) $post_id ] );
				}
			}
		}

		/**
		 * Triggered when LearnDash taxonomy term data is updated.
		 *
		 * @param array  $data - Term data to be updated.
		 * @param int    $term_id - Term ID.
		 * @param string $taxonomy - Taxonomy slug.
		 * @param array  $args - Arguments passed to wp_update_term().
		 *
		 * @return array - Return original data for the filter.
		 *
		 * @since 5.6.0
		 */
		public static function maybe_trigger_ld_taxonomy_change_events( $data, $term_id, $taxonomy, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			$ld_taxonomies = LearnDash_Helper::get_custom_taxonomies();

			if ( ! \in_array( $taxonomy, $ld_taxonomies, true ) ) {
				return $data;
			}

			// Get old data from the existing term.
			$term     = \get_term( $term_id, $taxonomy );
			$old_name = $term->name;
			$old_slug = $term->slug;

			// Get new values from the data being updated.
			$new_name = isset( $data['name'] ) ? $data['name'] : false;
			$new_slug = isset( $data['slug'] ) ? $data['slug'] : false;

			if ( $old_name !== $new_name ) {
				$event_variables = array(
					'OldName' => \esc_html( $old_name ),
					'NewName' => \esc_html( $new_name ),
					'Slug'    => \esc_html( $term->slug ),
				);

				if ( 'ld_course_category' === $taxonomy ) {
					$event_variables['CategoryLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_course_category', 'sfwd-courses' );

					Alert_Manager::trigger_event( 11082, $event_variables );
				} elseif ( 'ld_lesson_category' === $taxonomy ) {
					$event_variables['CategoryLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_lesson_category', 'sfwd-lessons' );

					Alert_Manager::trigger_event( 11302, $event_variables );
				}

				if ( 'ld_course_tag' === $taxonomy ) {
					$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_course_tag', 'sfwd-courses' );

					Alert_Manager::trigger_event( 11092, $event_variables );
				} elseif ( 'ld_lesson_tag' === $taxonomy ) {
					$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_lesson_tag', 'sfwd-lessons' );

					Alert_Manager::trigger_event( 11352, $event_variables );
				}

				if ( 'ld_topic_tag' === $taxonomy ) {
					$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_topic_tag', 'sfwd-topic' );

					Alert_Manager::trigger_event( 11452, $event_variables );
				}
			}

			if ( $old_slug !== $new_slug ) {
				$event_variables = array(
					'TaxonomyName' => \esc_html( $new_name ),
					'NewSlug'      => \esc_html( $new_slug ),
					'OldSlug'      => \esc_html( $old_slug ),
				);

				if ( 'ld_course_category' === $taxonomy ) {
					$event_variables['CategoryLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_course_category', 'sfwd-courses' );

					Alert_Manager::trigger_event( 11083, $event_variables );
				} elseif ( 'ld_lesson_category' === $taxonomy ) {
					$event_variables['CategoryLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_lesson_category', 'sfwd-lessons' );

					Alert_Manager::trigger_event( 11303, $event_variables );
				}

				if ( 'ld_course_tag' === $taxonomy ) {
					$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_course_tag', 'sfwd-courses' );

					Alert_Manager::trigger_event( 11093, $event_variables );
				} elseif ( 'ld_lesson_tag' === $taxonomy ) {
					$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_lesson_tag', 'sfwd-lessons' );

					Alert_Manager::trigger_event( 11353, $event_variables );
				}

				if ( 'ld_topic_tag' === $taxonomy ) {
					$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_topic_tag', 'sfwd-topic' );

					Alert_Manager::trigger_event( 11453, $event_variables );
				}
			}

			return $data;
		}
		// @premium:end

		/**
		 * New course category created trigger.
		 *
		 * @param int    $term_id - Term ID.
		 * @param int    $tt_id - Term taxonomy ID.
		 * @param string $taxonomy - Taxonomy slug.
		 * @param array  $args - Arguments passed to wp_insert_term().
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_taxonomy_creation_triggers( $term_id, $tt_id, $taxonomy, $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			$ld_taxonomies = LearnDash_Helper::get_custom_taxonomies();

			if ( ! \in_array( $taxonomy, $ld_taxonomies, true ) ) {
				return;
			}

			$term = \get_term( $term_id, $taxonomy );

			$event_variables = array(
				'TaxonomyTitle' => $term->name,
				'Slug'          => $term->slug,
			);

			if ( 'ld_course_category' === $taxonomy ) {
				$event_variables['CategoryLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_course_category', 'sfwd-courses' );

				Alert_Manager::trigger_event( 11080, $event_variables );
			} elseif ( 'ld_lesson_category' === $taxonomy ) {
				$event_variables['CategoryLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_lesson_category', 'sfwd-lessons' );

				Alert_Manager::trigger_event( 11300, $event_variables );
			} elseif ( 'ld_course_tag' === $taxonomy ) {
				$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_course_tag', 'sfwd-courses' );

				Alert_Manager::trigger_event( 11090, $event_variables );
			} elseif ( 'ld_lesson_tag' === $taxonomy ) {
				$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_lesson_tag', 'sfwd-lessons' );

				Alert_Manager::trigger_event( 11350, $event_variables );
			} elseif ( 'ld_topic_tag' === $taxonomy ) {
				$event_variables['TagLink'] = LearnDash_Helper::get_ld_taxonomy_edit_link( $term_id, 'ld_topic_tag', 'sfwd-topic' );

				Alert_Manager::trigger_event( 11450, $event_variables );
			}
		}

		/**
		 * Course category deleted trigger.
		 *
		 * @param int      $term_id - Term ID.
		 * @param int      $tt_id - Term taxonomy ID.
		 * @param string   $taxonomy - Taxonomy slug.
		 * @param \WP_Term $deleted_term - Copy of the already-deleted term.
		 * @param array    $object_ids - List of term object IDs.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function ld_taxonomy_deletion_triggers( $term_id, $tt_id, $taxonomy, $deleted_term, $object_ids ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
			$ld_taxonomies = LearnDash_Helper::get_custom_taxonomies();

			if ( ! \in_array( $taxonomy, $ld_taxonomies, true ) ) {
				return;
			}

			$event_variables = array(
				'TaxonomyTitle' => $deleted_term->name,
				'Slug'          => $deleted_term->slug,
			);

			if ( 'ld_course_category' === $taxonomy ) {
				Alert_Manager::trigger_event( 11081, $event_variables );
			} elseif ( 'ld_lesson_category' === $taxonomy ) {
				Alert_Manager::trigger_event( 11301, $event_variables );
			} elseif ( 'ld_course_tag' === $taxonomy ) {
				Alert_Manager::trigger_event( 11091, $event_variables );
			} elseif ( 'ld_lesson_tag' === $taxonomy ) {
				Alert_Manager::trigger_event( 11351, $event_variables );
			} elseif ( 'ld_topic_tag' === $taxonomy ) {
				Alert_Manager::trigger_event( 11451, $event_variables );
			}
		}

		// @premium:start
		/**
		 * Track student enrollments triggered via LearnDash functions.
		 *
		 * @param int         $user_id            User ID.
		 * @param int         $course_id          Course ID.
		 * @param string|null $course_access_list A comma-separated list of user IDs used for the course_access_list field.
		 * Note: Used if `learndash_use_legacy_course_access_list()` returns true. Otherwise null is sent.
		 * @param boolean     $remove             Whether to remove course access from the user.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function track_student_enroll_triggers( $user_id, $course_id, $course_access_list, $remove ) {

			// Get all post meta for the course.
			$course_meta = \get_post_meta( $course_id );

			$current_courses = LearnDash_Helper::get_user_enrolled_courses( $user_id );

			// Derive the "previous" courses depending on whether this call is removing or adding access.
			if ( $remove ) {
				// When removing, the previous state included the removed course.
				$old_courses = \array_values(
					\array_unique(
						\array_merge( (array) $current_courses, array( (int) $course_id ) )
					)
				);
			} else {
				// When adding, the previous state is current minus the newly added course.
				$old_courses = \array_values( \array_diff( (array) $current_courses, array( (int) $course_id ) ) );
			}

			$user = \get_userdata( $user_id );

			if ( ! $user ) {
				return;
			}

			$course_title = \get_the_title( $course_id );

			$prev_course_titles = implode( ', ', LearnDash_Helper::get_post_titles_array( $old_courses ) );
			$new_course_titles  = implode( ', ', LearnDash_Helper::get_post_titles_array( $current_courses ) );

			$price_label = LearnDash_Helper::get_ld_post_price_type( $course_id );

			// Get Course Author.
			$course_post   = \get_post( $course_id );
			$author_id     = (int) $course_post->post_author;
			$author_data   = \get_userdata( $author_id );
			$course_author = $author_data ? $author_data->display_name : LearnDash_Helper::get_ld_unknown_string();

			// Get Price Amount.
			$sfwd_courses = isset( $course_meta['_sfwd-courses'][0] ) ? \maybe_unserialize( $course_meta['_sfwd-courses'][0] ) : array();
			$price_amount = isset( $sfwd_courses['sfwd-courses_course_price'] ) ? $sfwd_courses['sfwd-courses_course_price'] : '';

			// Get Course Categories.
			$categories        = \wp_get_post_terms( $course_id, 'ld_course_category', array( 'fields' => 'names' ) );
			$course_categories = ! empty( $categories ) && ! \is_wp_error( $categories ) ? implode( ', ', $categories ) : \esc_html__( 'No categories', 'wp-security-audit-log' );

			$event_variables = array(
				'TargetUsername' => $user->user_login,
				'TargetUserID'   => $user_id,
				'FirstName'      => $user->first_name ?? '',
				'LastName'       => $user->last_name ?? '',
				'CourseTitle'    => $course_title,
				'PriceType'      => $price_label,
				'CourseAuthor'   => $course_author,
				'PriceAmount'    => $price_amount,
				'CourseCategory' => $course_categories,
				'OldCourses'     => ! empty( $prev_course_titles ) ? $prev_course_titles : LearnDash_Helper::get_ld_none_string(),
				'NewCourses'     => ! empty( $new_course_titles ) ? $new_course_titles : LearnDash_Helper::get_ld_none_string(),
				'EditUserLink'   => \esc_url( \get_edit_user_link( $user_id ) ),
			);

			if ( $remove ) {
				Alert_Manager::trigger_event( 11551, $event_variables );
			} else {
				// If this specific 11554 trigger happens, then price will always be zero. This is not a transaction.
				$event_variables['PriceAmount'] = 0;
				Alert_Manager::trigger_event( 11554, $event_variables );
			}
		}

		/**
		 * Trigger enroll event when a student is enrolled in a group.
		 *
		 * @param \WP_User $user - User object.
		 * @param \WP_Post $group_post - Group post object.
		 *
		 * @return array - Event variables array for group enrollment events (paid and free).
		 *
		 * @since 5.6.0
		 */
		public static function get_student_enroll_to_group_event_data( $user, $group_post ) {
			$event_variables = array();

			if ( $user && $group_post ) {
				$user_id = (int) $user->ID;

				$event_variables = array(
					'TargetUsername'   => \esc_html( $user->user_login ),
					'TargetUserID'     => $user_id,
					'FirstName'        => \esc_html( $user->first_name ?? '' ),
					'LastName'         => \esc_html( $user->last_name ?? '' ),
					'GroupTitle'       => \esc_html( $group_post->post_title ),
					'GroupAuthor'      => \esc_html( LearnDash_Helper::get_author_display_name( $group_post ) ),
					'AvailableCourses' => \esc_html( LearnDash_Helper::get_ld_courses_string_by_group_id( $group_post->ID ) ),
					'PriceType'        => \esc_html( LearnDash_Helper::get_ld_post_price_type( $group_post->ID ) ),
				);
			}

			return $event_variables;
		}

		/**
		 * Track when a student enrolls in a group for FREE. (event 11555 free version)
		 *
		 * @param int $user_id - User ID.
		 * @param int $group_id - Group ID.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function track_group_student_enroll_triggers( $user_id, $group_id ) {

			$user = \get_userdata( $user_id );

			if ( ! $user ) {
				return;
			}

			$group_post = \get_post( $group_id );

			if ( $group_post ) {
				$event_variables = self::get_student_enroll_to_group_event_data( $user, $group_post );

				$event_variables['PriceAmount'] = 0;

				Alert_Manager::trigger_event( 11555, $event_variables );
			}
		}

		/**
		 * Check for changes in course users.
		 *
		 * @param int $post_id Post ID.
		 *
		 * @since 5.6.0
		 */
		private static function check_course_users_change( $post_id ) {
			if ( ! isset( $_POST['learndash_course_users_nonce'] ) || ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST['learndash_course_users_nonce'] ) ), 'learndash_course_users_nonce_' . $post_id ) ) {
				return;
			}

			if ( isset( self::$before_course_users[ $post_id ] ) && isset( $_POST['learndash_course_users'][ $post_id ] ) ) {
				$old_users = self::$before_course_users[ $post_id ];

				$json_data = \sanitize_text_field( \wp_unslash( $_POST['learndash_course_users'][ $post_id ] ) );
				$new_users = (array) json_decode( $json_data );

				$new_users = array_map( 'intval', $new_users );

				$added_users   = \array_diff( $new_users, $old_users );
				$removed_users = \array_diff( $old_users, $new_users );

				if ( ! empty( $added_users ) ) {
					foreach ( $added_users as $user_id ) {
						$user = \get_userdata( $user_id );
						if ( ! $user ) {
							continue;
						}

						$course_title = \get_the_title( $post_id );
						$price_type   = LearnDash_Helper::get_ld_post_price_type( $post_id );

						$current_courses = LearnDash_Helper::get_user_enrolled_courses( $user_id );
						$new_courses     = $current_courses;
						$old_courses     = \array_diff( $current_courses, array( $post_id ) );

						$new_courses_str = \implode( ', ', LearnDash_Helper::get_post_titles_array( $new_courses ) );
						$old_courses_str = \implode( ', ', LearnDash_Helper::get_post_titles_array( $old_courses ) );

						Alert_Manager::trigger_event(
							11550,
							array(
								'TargetUsername' => $user->user_login,
								'TargetUserID'   => $user_id,
								'FirstName'      => $user->first_name,
								'LastName'       => $user->last_name,
								'CourseTitle'    => $course_title,
								'PriceType'      => $price_type,
								'OldCourses'     => ! empty( $old_courses_str ) ? $old_courses_str : LearnDash_Helper::get_ld_none_string(),
								'NewCourses'     => ! empty( $new_courses_str ) ? $new_courses_str : LearnDash_Helper::get_ld_none_string(),
								'EditUserLink'   => \esc_url( \get_edit_user_link( $user_id ) ),
							)
						);
					}
				}

				if ( ! empty( $added_users ) || ! empty( $removed_users ) ) {
					$old_count = count( $old_users );
					$new_count = count( $new_users );

					$old_students_str = LearnDash_Helper::get_student_count_label( $old_count );
					$new_students_str = LearnDash_Helper::get_student_count_label( $new_count );

					$event_variables = array(
						'CourseName'     => \get_the_title( $post_id ),
						'PostID'         => $post_id,
						'OldStudents'    => $old_students_str,
						'NewStudents'    => $new_students_str,
						'EditorLinkPost' => \esc_url( \get_edit_post_link( $post_id ) ),
					);

					Alert_Manager::trigger_event( 11058, $event_variables );
				}
			}
		}

		/**
		 * Check for changes in group users.
		 *
		 * @param int $post_id Post ID.
		 *
		 * @since 5.6.0
		 */
		private static function check_group_users_change( $post_id ) {
			$nonce_field  = 'learndash_group_users-' . $post_id . '-nonce';
			$nonce_action = 'learndash_group_users-' . $post_id;

			if ( ! isset( $_POST[ $nonce_field ] ) || ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST[ $nonce_field ] ) ), $nonce_action ) ) {
				return;
			}

			if ( isset( self::$before_group_users[ $post_id ] ) && isset( $_POST['learndash_group_users'][ $post_id ] ) ) {
				$old_users = self::$before_group_users[ $post_id ];

				$json_data = \sanitize_text_field( \wp_unslash( $_POST['learndash_group_users'][ $post_id ] ) );
				$new_users = (array) json_decode( $json_data );

				$new_users = array_map( 'intval', $new_users );

				$added_users   = \array_diff( $new_users, $old_users );
				$removed_users = \array_diff( $old_users, $new_users );

				if ( ! empty( $added_users ) ) {
					foreach ( $added_users as $user_id ) {
						self::trigger_group_enrollment_event( $user_id, $post_id, 'added' );
					}
				}

				if ( ! empty( $removed_users ) ) {
					foreach ( $removed_users as $user_id ) {
						self::trigger_group_enrollment_event( $user_id, $post_id, 'removed' );
					}
				}
			}
		}

		/**
		 * Trigger group enrollment event.
		 *
		 * @param int    $user_id - User ID.
		 * @param int    $group_id - Group ID.
		 * @param string $action - Action (added/removed).
		 *
		 * @since 5.6.0
		 */
		private static function trigger_group_enrollment_event( $user_id, $group_id, $action ) {
			$user = \get_userdata( $user_id );
			if ( ! $user ) {
				return;
			}

			$current_groups = LearnDash_Helper::get_user_enrolled_groups( $user_id );

			if ( 'added' === $action ) {
				$new_groups = \array_unique( \array_merge( $current_groups, array( $group_id ) ) );
				$old_groups = \array_diff( $new_groups, array( $group_id ) );
			} else {
				$new_groups = \array_diff( $current_groups, array( $group_id ) );
				$old_groups = \array_unique( \array_merge( $new_groups, array( $group_id ) ) );
			}

			$group_title = \get_the_title( $group_id );
			$price_type  = LearnDash_Helper::get_ld_post_price_type( $group_id );

			$old_groups_titles = implode( ', ', LearnDash_Helper::get_post_titles_array( $old_groups ) );
			$new_groups_titles = implode( ', ', LearnDash_Helper::get_post_titles_array( $new_groups ) );

			$event_variables = array(
				'TargetUsername' => $user->user_login,
				'TargetUserID'   => $user_id,
				'FirstName'      => $user->first_name ?? '',
				'LastName'       => $user->last_name ?? '',
				'GroupTitle'     => $group_title,
				'PriceType'      => $price_type,
				'OldGroups'      => ! empty( $old_groups_titles ) ? $old_groups_titles : LearnDash_Helper::get_ld_none_string(),
				'NewGroups'      => ! empty( $new_groups_titles ) ? $new_groups_titles : LearnDash_Helper::get_ld_none_string(),
				'EditUserLink'   => \esc_url( \get_edit_user_link( $user_id ) ),
			);

			if ( 'added' === $action ) {
				Alert_Manager::trigger_event( 11552, $event_variables );
			} else {
				Alert_Manager::trigger_event( 11553, $event_variables );
			}
		}

		/**
		 * Trigger enroll event when a student is enrolled in a course.
		 *
		 * @param \WP_User $user - User object.
		 * @param \WP_Post $course_post - Course post object.
		 *
		 * @return array - Event variables array for student enrollment events (both paid and free scenarios).
		 *
		 * @since 5.6.0
		 */
		public static function get_student_enroll_to_course_event_data( $user, $course_post ) {
			$event_variables = array();

			if ( $user && $course_post ) {
				$user_id = (int) $user->ID;

				$course_categories = LearnDash_Helper::get_ld_post_categories( $course_post->ID, $course_post->post_type );

				$event_variables = array(
					'TargetUsername' => \esc_html( $user->user_login ),
					'TargetUserID'   => $user_id,
					'FirstName'      => \esc_html( $user->first_name ?? '' ),
					'LastName'       => \esc_html( $user->last_name ?? '' ),
					'CourseTitle'    => \esc_html( $course_post->post_title ),
					'CourseAuthor'   => \esc_html( LearnDash_Helper::get_author_display_name( $course_post ) ),
					'CourseCategory' => \esc_html( $course_categories ),
					'PriceType'      => \esc_html( LearnDash_Helper::get_ld_post_price_type( $course_post->ID ) ),
				);
			}

			return $event_variables;
		}

		/**
		 * Track when a student enrolls in a paid group or course. (event 11555 and 11554 paid version)
		 *
		 * @param int $transaction_id - Transaction post ID.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function learndash_transaction_created_trigger( $transaction_id ) {

			$transaction_post = \get_post( $transaction_id );

			if ( ! $transaction_post || 'sfwd-transactions' !== $transaction_post->post_type ) {
				return;
			}

			$user_id = (int) $transaction_post->post_author;

			if ( $user_id <= 0 ) {
				return;
			}

			$user = \get_userdata( $user_id );

			if ( ! $user ) {
				return;
			}

			$post_id = (int) \get_post_meta( $transaction_id, 'post_id', true );

			if ( $post_id <= 0 ) {
				return;
			}

			$post_object = \get_post( $post_id );

			if ( ! $post_object ) {
				return;
			}

			if ( 'groups' === $post_object->post_type ) {
				$event_variables = self::get_student_enroll_to_group_event_data( $user, $post_object );

				$event_variables['EditorLinkOrder'] = \esc_url_raw( LearnDash_Helper::get_ld_tx_post_edit_link( $transaction_post ) );
				$event_variables['PriceAmount']     = \esc_html( LearnDash_Helper::get_ld_price_amount_with_currency( $transaction_id ) );

				Alert_Manager::trigger_event( 11555, $event_variables );

			} elseif ( 'sfwd-courses' === $post_object->post_type ) {
				$event_variables = self::get_student_enroll_to_course_event_data( $user, $post_object );

				$event_variables['EditorLinkOrder'] = \esc_url_raw( LearnDash_Helper::get_ld_tx_post_edit_link( $transaction_post ) );
				$event_variables['PriceAmount']     = \esc_html( LearnDash_Helper::get_ld_price_amount_with_currency( $transaction_id ) );

				Alert_Manager::trigger_event( 11554, $event_variables );
			}
		}

		/**
		 * Track LearnDash lesson and topic activity events.
		 *
		 * @param array $args - LearnDash activity arguments.
		 *
		 * @since 5.6.0
		 */
		public static function track_activity_events( $args ) {
			if ( empty( $args ) || empty( $args['activity_type'] ) ) {
				return;
			}

			$activity_type      = (string) $args['activity_type'];
			$activity_action    = (string) $args['activity_action'];
			$activity_completed = (int) ( $args['activity_completed'] ?? 0 );

			if ( ! in_array( $activity_type, array( 'course', 'lesson', 'topic', 'quiz' ), true ) ) {
				return;
			}

			if ( ! in_array( $activity_action, array( 'insert', 'update' ), true ) ) {
				return;
			}

			$user_id   = $args['user_id'] ?? null;
			$course_id = $args['course_id'] ?? null;
			$post_id   = $args['post_id'] ?? null;

			if ( ! $user_id || ! $course_id || ! $post_id ) {
				return;
			}

			$user = \get_userdata( $user_id );

			if ( ! $user ) {
				return;
			}

			$activity_started   = $args['activity_started'] ?? '';
			$activity_completed = $args['activity_completed'] ?? '';

			$event_id = 0;

			if ( 'insert' === $activity_action && ! empty( $activity_started ) ) {
				if ( 'course' === $activity_type ) {
					// Course started event.
					$event_id = 11563;
				}

				if ( 'lesson' === $activity_type ) {
					// Lesson started event.
					$event_id = 11558;
				}

				if ( 'topic' === $activity_type ) {
					// Topic started event.
					$event_id = 11556;
				}

				// Quiz is an exception, when we complete it, it's an insert action!
				if ( 'quiz' === $activity_type ) {
					// Quiz completed event.
					$event_id = 11562;
				}
			} elseif ( 'update' === $activity_action && ! empty( $activity_completed ) ) {

				if ( 0 === $activity_completed ) {
					return;
				}

				if ( 'course' === $activity_type ) {
					// Course completed event.
					$event_id = 11564;
				}

				if ( 'lesson' === $activity_type ) {
					// Lesson completed event - but only if not already triggered.
					if ( ! Alert_Manager::has_triggered( 11559 ) ) {
						$event_id = 11559;
					}
				}

				if ( 'topic' === $activity_type ) {
					// Topic completed event.
					$event_id = 11557;
				}
			}

			if ( ! $event_id ) {
				return;
			}

			$event_variables = self::build_activity_event_variables( $user, $course_id, $post_id, $activity_type, $event_id );

			Alert_Manager::trigger_event( $event_id, $event_variables );

			// Check if this was a topic completion that resulted in 100% lesson progress, in this case we need to trigger lesson completed event.
			if ( 11557 === $event_id && isset( $event_variables['LessonProgress'] ) && '100%' === $event_variables['LessonProgress'] ) {
				// ...but double check we didn't trigger this already!
				if ( ! Alert_Manager::has_triggered( 11559 ) ) {
					// Rebuild variables for lesson completion event with correct metadata.
					$lesson_id              = (int) LearnDash_Helper::get_topic_lesson_id( $post_id );
					$lesson_event_variables = self::build_activity_event_variables( $user, $course_id, $lesson_id, 'lesson', 11559 );

					Alert_Manager::trigger_event( 11559, $lesson_event_variables );
				}
			}
		}

		/**
		 * Build event variables for activity events.
		 *
		 * @param \WP_User $user - User object.
		 * @param int      $course_id - Course ID.
		 * @param int      $post_id - LearnDash Post ID.
		 * @param string   $activity_type - 'lesson', 'topic', or 'quiz'.
		 * @param int      $event_id - Event ID.
		 *
		 * @return array Event variables.
		 *
		 * @since 5.6.0
		 */
		private static function build_activity_event_variables( $user, $course_id, $post_id, $activity_type, $event_id ) {
			$base_variables = array(
				'TargetUsername' => \esc_html( $user->user_login ),
				'FirstName'      => \esc_html( $user->first_name ?? '' ),
				'LastName'       => \esc_html( $user->last_name ?? '' ),
				'CourseTitle'    => \esc_html( \get_the_title( $course_id ) ),
				'CourseGroup'    => \esc_html( LearnDash_Helper::get_ld_post_groups_string( $course_id ) ),
				'PriceType'      => \esc_html( LearnDash_Helper::get_ld_post_price_type( $course_id ) ),
				'EditUserLink'   => \esc_url_raw( \get_edit_user_link( $user->ID ) ),
			);

			if ( 'lesson' === $activity_type ) {
				$base_variables['LessonTitle'] = \esc_html( \get_the_title( $post_id ) );

				if ( in_array( $event_id, array( 11559 ), true ) ) {
					$base_variables['CourseProgress'] = \esc_html( LearnDash_Helper::calculate_course_progress( $user->ID, $course_id ) );
				}
			} elseif ( 'topic' === $activity_type ) {
				$base_variables['TopicTitle'] = \esc_html( \get_the_title( $post_id ) );

				$lesson_id = (int) LearnDash_Helper::get_topic_lesson_id( $post_id );

				if ( $lesson_id ) {
					$base_variables['LessonTitle'] = \esc_html( \get_the_title( $lesson_id ) );

					if ( in_array( $event_id, array( 11557 ), true ) ) {
						$base_variables['LessonProgress'] = \esc_html( LearnDash_Helper::calculate_lesson_progress( $user->ID, $lesson_id ) );
					}
				}
			} elseif ( 'quiz' === $activity_type ) {
				$base_variables['QuizTitle'] = \esc_html( \get_the_title( $post_id ) );

				if ( in_array( $event_id, array( 11562 ), true ) ) {
					$base_variables['QuizResult'] = \esc_html( LearnDash_Helper::get_quiz_result( $user->ID, $post_id ) );
					$base_variables['QuizType']   = \esc_html( LearnDash_Helper::get_quiz_type( $post_id ) );
				}
			}

			return $base_variables;
		}

		/**
		 * Track quiz answers and trigger event 11561 for each question answered.
		 *
		 * @param int|bool $statistic_ref_id The statistic reference ID from the database, or false if statistics not saved.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		public static function track_quiz_answers( $statistic_ref_id ) {
			global $wpdb;

			// If statistics weren't saved, bail early.
			if ( empty( $statistic_ref_id ) || false === $statistic_ref_id ) {
				return;
			}

			// Get table names.
			$ref_table  = \LDLMS_DB::get_table_name( 'quiz_statistic_ref' );
			$stat_table = \LDLMS_DB::get_table_name( 'quiz_statistic' );

			// Query the quiz attempt record.
			$quiz_attempt = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$ref_table} WHERE statistic_ref_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$statistic_ref_id
				)
			);

			if ( ! $quiz_attempt ) {
				return;
			}

			// Query all answers for this attempt.
			$answers = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$stat_table} WHERE statistic_ref_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$statistic_ref_id
				)
			);

			if ( empty( $answers ) ) {
				return;
			}

			$events_data = LearnDash_Helper::get_quiz_answers_event_data( $quiz_attempt, $answers );

			foreach ( $events_data as $event_data ) {
				Alert_Manager::trigger_event( 11561, $event_data );
			}
		}
		// @premium:end

		/**
		 * Applies default disabled alerts when LearnDash is first detected by an existing WSAL install.
		 *
		 * This ensures high-volume events are disabled even when WSAL was installed before LearnDash.
		 *
		 * @return void
		 *
		 * @since 5.6.0
		 */
		private static function maybe_apply_first_detection_disabled_alerts() {
			$processed_plugins = \get_option( 'wsal_detected_plugins_processed', array() );

			if ( in_array( 'sfwd-lms', $processed_plugins, true ) ) {
				return;
			}

			$disabled_alerts = Settings_Helper::get_disabled_alerts();

			$learndash_defaults = LearnDash_Custom_Alerts::get_default_disabled_alerts();
			$disabled_alerts    = array_unique( array_merge( $disabled_alerts, $learndash_defaults ) );

			Settings_Helper::set_disabled_alerts( $disabled_alerts );

			$processed_plugins[] = 'sfwd-lms';

			\update_option( 'wsal_detected_plugins_processed', $processed_plugins );
		}
	}
}
