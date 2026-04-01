<?php
/**
 * Custom Alerts for the LearnDash plugin.
 *
 * Class file for alert manager.
 *
 * @package wsal
 * @subpackage wsal-learndash
 *
 * @since 5.6.0
 */

declare(strict_types=1);

namespace WSAL\WP_Sensors\Alerts;

use WSAL\MainWP\MainWP_Addon;
use WSAL\Controllers\Constants;
use WSAL\WP_Sensors\Helpers\LearnDash_Helper;

// Exit if accessed directly.
if ( ! \defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! \class_exists( '\WSAL\WP_Sensors\Alerts\LearnDash_Custom_Alerts' ) ) {
	/**
	 * Custom sensor for the LearnDash plugin.
	 *
	 * @since 5.6.0
	 */
	class LearnDash_Custom_Alerts {

		/**
		 * Returns the structure of the alerts for extension.
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_custom_alerts(): array {
			if ( ( \method_exists( LearnDash_Helper::class, 'load_alerts_for_sensor' ) && LearnDash_Helper::load_alerts_for_sensor() ) || MainWP_Addon::check_mainwp_plugin_active() ) {
				return array(
					\esc_html__( 'LearnDash LMS', 'wp-security-audit-log' ) => array(
						\esc_html__( 'Courses', 'wp-security-audit-log' ) => self::get_courses_array(),
						\esc_html__( 'Lessons', 'wp-security-audit-log' ) => self::get_lessons_array(),
						\esc_html__( 'Topics', 'wp-security-audit-log' ) => self::get_topics_array(),
						\esc_html__( 'Groups', 'wp-security-audit-log' ) => self::get_groups_array(),
						\esc_html__( 'Certificates', 'wp-security-audit-log' ) => self::get_certificates_array(),
						\esc_html__( 'Students', 'wp-security-audit-log' ) => self::get_students_array(),
					),
				);
			}

			return array();
		}

		/**
		 * Returns the list of LearnDash alerts that should be disabled by default.
		 *
		 * These are student activity events that can generate a high log volume of events.
		 *
		 * @return int[] - List of alert IDs.
		 *
		 * @since 5.6.0
		 */
		public static function get_default_disabled_alerts(): array {
			return array( 11017, 11556, 11557, 11558, 11559, 11561, 11562, 11563, 11564 );
		}

		/**
		 * Adds LearnDash default disabled alerts to the global list.
		 *
		 * @param int[] $alerts - Current list of default disabled alert IDs.
		 *
		 * @return int[] - Updated list with LearnDash alerts included.
		 *
		 * @since 5.6.0
		 */
		public static function add_default_disabled_alerts( array $alerts ): array {
			return \array_merge( $alerts, self::get_default_disabled_alerts() );
		}

		/**
		 * Returns an array with all the events attached to the sensor (if there are different types of events, this method will merge them into one array - the events ids will be used as keys)
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_alerts_array(): array {

			return self::get_courses_array() +
			self::get_lessons_array() +
			self::get_topics_array() +
			self::get_groups_array() +
			self::get_certificates_array() +
			self::get_students_array();
		}

		/**
		 * Learndash Courses Events
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_courses_array(): array {
			return array(
				11000 => array(
					11000,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A course was created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Categories', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Course category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Course status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'created',
				),
				11001 => array(
					11001,
					WSAL_LOW,
					\esc_html__( 'A course was published', 'wp-security-audit-log' ),
					\esc_html__( 'Published the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Categories', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Course category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'published',
				),
				// @premium:start
				11002 => array(
					11002,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course author changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the author of the course %PostTitle% to %NewAuthor%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous author', 'wp-security-audit-log' ) => '%OldAuthor%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'modified',
				),
				// @premium:end
				11003 => array(
					11003,
					WSAL_HIGH,
					\esc_html__( 'A course was moved to trash', 'wp-security-audit-log' ),
					\esc_html__( 'Moved the course %PostTitle% to trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Categories', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Course category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Course status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					array(),
					'learndash_courses',
					'deleted',
				),
				11004 => array(
					11004,
					WSAL_HIGH,
					\esc_html__( 'A course was permanently deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Permanently deleted the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Categories', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Course category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_courses',
					'deleted',
				),
				11005 => array(
					11005,
					WSAL_LOW,
					\esc_html__( 'A course was restored from trash', 'wp-security-audit-log' ),
					\esc_html__( 'Restored the course %PostTitle% from trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Categories', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Course category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'restored',
				),
				11006 => array(
					11006,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A course was duplicated', 'wp-security-audit-log' ),
					\esc_html__( 'Duplicated the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Course category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_courses',
					'duplicated',
				),
				// @premium:start
				11007 => array(
					11007,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course categories changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the category(ies) of the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous category(ies)', 'wp-security-audit-log' ) => '%OldCategories%',
						\esc_html__( 'New category(ies)', 'wp-security-audit-log' ) => '%NewCategories%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'modified',
				),
				11008 => array(
					11008,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course course-categories changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the course category(ies) of the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous Course category(ies)', 'wp-security-audit-log' ) => '%OldCategories%',
						\esc_html__( 'New Course category(ies)', 'wp-security-audit-log' ) => '%NewCategories%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'modified',
				),
				11009 => array(
					11009,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course tags changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the tag(s) of the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous tag(s)', 'wp-security-audit-log' ) => '%OldTags%',
						\esc_html__( 'New tag(s)', 'wp-security-audit-log' ) => '%NewTags%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'modified',
				),
				11010 => array(
					11010,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course course-tags changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the course tag(s) of the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous Course tag(s)', 'wp-security-audit-log' ) => '%OldTags%',
						\esc_html__( 'New Course tag(s)', 'wp-security-audit-log' ) => '%NewTags%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_courses',
					'modified',
				),
				11011 => array(
					11011,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson added to course', 'wp-security-audit-log' ),
					\esc_html__( 'Lesson %LessonTitle% was added to the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'added',
				),
				11012 => array(
					11012,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson removed from course', 'wp-security-audit-log' ),
					\esc_html__( 'Lesson %LessonTitle% was removed from the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'removed',
				),
				11013 => array(
					11013,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic added to course', 'wp-security-audit-log' ),
					\esc_html__( 'Topic %TopicTitle% was added to the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'added',
				),
				11014 => array(
					11014,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic removed from course', 'wp-security-audit-log' ),
					\esc_html__( 'Topic %TopicTitle% was removed from the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic title', 'wp-security-audit-log' ) => '%TopicTitle%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'removed',
				),
				11015 => array(
					11015,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Quiz added to course', 'wp-security-audit-log' ),
					\esc_html__( 'Quiz %QuizTitle% was added to the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'added',
				),
				11016 => array(
					11016,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Quiz removed from course', 'wp-security-audit-log' ),
					\esc_html__( 'Quiz %QuizTitle% was removed from the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'removed',
				),
				11017 => array(
					11017,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson order in course changed', 'wp-security-audit-log' ),
					\esc_html__( 'Lesson order was modified in the course %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous order', 'wp-security-audit-log' ) => '%PreviousOrder%',
						\esc_html__( 'New order', 'wp-security-audit-log' ) => '%NewOrder%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11018 => array(
					11018,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Certificate added to course', 'wp-security-audit-log' ),
					\esc_html__( 'Added a certificate to the course %CourseName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old value', 'wp-security-audit-log' ) => '%OldCertificateTitle%',
						\esc_html__( 'New value', 'wp-security-audit-log' ) => '%NewCertificateTitle%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'added',
				),
				11019 => array(
					11019,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Certificate removed from course', 'wp-security-audit-log' ),
					\esc_html__( 'Removed a certificate from the course %CourseName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old value', 'wp-security-audit-log' ) => '%OldCertificateTitle%',
						\esc_html__( 'New value', 'wp-security-audit-log' ) => '%NewCertificateTitle%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'removed',
				),
				11050 => array(
					11050,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course enrollment status was changed', 'wp-security-audit-log' ),
					\esc_html__( 'Change the enrollment status of the course %PostTypeTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous enrollment status', 'wp-security-audit-log' ) => '%OldEnrollmentStatus%',
						\esc_html__( 'New enrollment status', 'wp-security-audit-log' ) => '%NewEnrollmentStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11051 => array(
					11051,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Disabled the requirements for enrollment of a course', 'wp-security-audit-log' ),
					\esc_html__( 'Disabled the requirements for enrollment of the course %CourseName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous requirements for enrollment', 'wp-security-audit-log' ) => '%OldRequirements%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'deactivated',
				),
				11052 => array(
					11052,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Changed the requirements for enrollment of a course', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the requirements for enrollment of the course %CourseName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous requirements for enrollment', 'wp-security-audit-log' ) => '%OldRequirements%',
						\esc_html__( 'New requirements for enrollment', 'wp-security-audit-log' ) => '%NewRequirements%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11053 => array(
					11053,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course access expiration set', 'wp-security-audit-log' ),
					\esc_html__( 'Access expiration of course %CourseName% was set to %ExpirationDays% days.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Data retention status', 'wp-security-audit-log' ) => '%RetentionStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11054 => array(
					11054,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course access expiration setting was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Access expiration setting of course %CourseName% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old data retention status', 'wp-security-audit-log' ) => '%OldRetentionStatus%',
						\esc_html__( 'New data retention status', 'wp-security-audit-log' ) => '%NewRetentionStatus%',
						\esc_html__( 'Previous days count', 'wp-security-audit-log' ) => '%OldExpirationDays%',
						\esc_html__( 'New days count', 'wp-security-audit-log' ) => '%NewExpirationDays%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11055 => array(
					11055,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course access expiration option was disabled', 'wp-security-audit-log' ),
					\esc_html__( 'Access expiration option of the course %CourseName% was disabled.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'deactivated',
				),
				11056 => array(
					11056,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course start/end date was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Start/End date of the course %PostTitle% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous start date', 'wp-security-audit-log' ) => '%OldStartDate%',
						\esc_html__( 'New start date', 'wp-security-audit-log' ) => '%NewStartDate%',
						\esc_html__( 'Previous end date', 'wp-security-audit-log' ) => '%OldEndDate%',
						\esc_html__( 'New end date', 'wp-security-audit-log' ) => '%NewEndDate%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11057 => array(
					11057,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course student limit was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Allowed student limit of the course %CourseName% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old value', 'wp-security-audit-log' ) => '%OldStudentLimit%',
						\esc_html__( 'New value', 'wp-security-audit-log' ) => '%NewStudentLimit%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11058 => array(
					11058,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course enrolled student list was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Enrolled student list of the course %CourseName% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old value(s)', 'wp-security-audit-log' ) => '%OldStudents%',
						\esc_html__( 'New value(s)', 'wp-security-audit-log' ) => '%NewStudents%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				11059 => array(
					11059,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course duration was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Duration of the course %CourseName% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Course ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old value', 'wp-security-audit-log' ) => '%OldDuration%',
						\esc_html__( 'New value', 'wp-security-audit-log' ) => '%NewDuration%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_courses',
					'modified',
				),
				// @premium:end
				11080 => array(
					11080,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course category created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the course category %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					Constants::wsaldefaults_build_links( array( 'CategoryLink' ) ),
					'learndash_courses',
					'created',
				),
				11081 => array(
					11081,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course category deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Deleted the course category %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array(),
					'learndash_courses',
					'deleted',
				),
				// @premium:start
				11082 => array(
					11082,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course category renamed', 'wp-security-audit-log' ),
					\esc_html__( 'Renamed the course category %OldName% to %NewName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					Constants::wsaldefaults_build_links( array( 'CategoryLink' ) ),
					'learndash_courses',
					'renamed',
				),
				11083 => array(
					11083,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course category modified', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the slug of the course category %TaxonomyName% to %NewSlug%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Previous slug', 'wp-security-audit-log' ) => '%OldSlug%',
					),
					Constants::wsaldefaults_build_links( array( 'CategoryLink' ) ),
					'learndash_courses',
					'modified',
				),
				// @premium:end
				11090 => array(
					11090,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course tag created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the course tag %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_courses',
					'created',
				),
				11091 => array(
					11091,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course tag deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Deleted the course tag %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array(),
					'learndash_courses',
					'deleted',
				),
				// @premium:start
				11092 => array(
					11092,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course tag renamed', 'wp-security-audit-log' ),
					\esc_html__( 'Renamed the course tag %OldName% to %NewName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_courses',
					'renamed',
				),
				11093 => array(
					11093,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Course tag modified', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the slug of the course tag %TaxonomyName% to %NewSlug%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Previous slug', 'wp-security-audit-log' ) => '%OldSlug%',
					),
					array( \esc_html__( 'View tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_courses',
					'modified',
				),
				// @premium:end
			);
		}

		/**
		 * Learndash Lessons Events
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_lessons_array(): array {
			return array(
				11200 => array(
					11200,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson was created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Lesson author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Lesson category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Lesson status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_lessons',
					'created',
				),
				11201 => array(
					11201,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson was published', 'wp-security-audit-log' ),
					\esc_html__( 'Published the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Lesson author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Lesson category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_lessons',
					'published',
				),
				// @premium:start
				11202 => array(
					11202,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson author changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the author of the lesson %PostTitle% to %NewAuthor%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous author', 'wp-security-audit-log' ) => '%OldAuthor%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_lessons',
					'modified',
				),
				11203 => array(
					11203,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson categories changed', 'wp-security-audit-log' ),
					\esc_html__( 'Modified the category(ies) of the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Lesson status', 'wp-security-audit-log' ) => '%PostStatus%',
						\esc_html__( 'Previous category(ies)', 'wp-security-audit-log' ) => '%OldCategories%',
						\esc_html__( 'New category(ies)', 'wp-security-audit-log' ) => '%NewCategories%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_lessons',
					'modified',
				),
				11204 => array(
					11204,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson lesson-categories changed', 'wp-security-audit-log' ),
					\esc_html__( 'Modified the lesson category(ies) of the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Lesson status', 'wp-security-audit-log' ) => '%PostStatus%',
						\esc_html__( 'Previous Lesson category(ies)', 'wp-security-audit-log' ) => '%OldCategories%',
						\esc_html__( 'New Lesson category(ies)', 'wp-security-audit-log' ) => '%NewCategories%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_lessons',
					'modified',
				),
				11205 => array(
					11205,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson lesson-tags changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the lesson tag(s) of the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous Lesson tag(s)', 'wp-security-audit-log' ) => '%OldTags%',
						\esc_html__( 'New Lesson tag(s)', 'wp-security-audit-log' ) => '%NewTags%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_lessons',
					'modified',
				),
				// @premium:end
				11206 => array(
					11206,
					WSAL_MEDIUM,
					\esc_html__( 'A lesson was moved to trash', 'wp-security-audit-log' ),
					\esc_html__( 'Moved the lesson %PostTitle% to trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Lesson category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Lesson status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'PostUrlIfPublished' ) ),
					'learndash_lessons',
					'deleted',
				),

				11207 => array(
					11207,
					WSAL_HIGH,
					\esc_html__( 'A lesson was permanently deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Permanently deleted the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Lesson category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_lessons',
					'deleted',
				),
				11208 => array(
					11208,
					WSAL_LOW,
					\esc_html__( 'A lesson was restored from trash', 'wp-security-audit-log' ),
					\esc_html__( 'Restored the lesson %PostTitle% from trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Lesson category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_lessons',
					'restored',
				),
				11209 => array(
					11209,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson was duplicated', 'wp-security-audit-log' ),
					\esc_html__( 'Duplicated the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Lesson category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_lessons',
					'duplicated',
				),
				// @premium:start
				11210 => array(
					11210,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic added to lesson', 'wp-security-audit-log' ),
					\esc_html__( 'Topic %TopicTitle% was added to the lesson %LessonTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_lessons',
					'added',
				),
				11211 => array(
					11211,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic removed from lesson', 'wp-security-audit-log' ),
					\esc_html__( 'Topic %TopicTitle% was removed from the lesson %LessonTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_lessons',
					'removed',
				),
				11212 => array(
					11212,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Quiz added to lesson', 'wp-security-audit-log' ),
					\esc_html__( 'Quiz %QuizTitle% was added to the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_lessons',
					'added',
				),
				11213 => array(
					11213,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Quiz removed from lesson', 'wp-security-audit-log' ),
					\esc_html__( 'Quiz %QuizTitle% was removed from the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_lessons',
					'removed',
				),
				11214 => array(
					11214,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson tags changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the tag(s) of the lesson %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Lesson author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous tag(s)', 'wp-security-audit-log' ) => '%OldTags%',
						\esc_html__( 'New tag(s)', 'wp-security-audit-log' ) => '%NewTags%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_lessons',
					'modified',
				),
				11250 => array(
					11250,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Lesson duration modified', 'wp-security-audit-log' ),
					\esc_html__( 'Duration of the lesson %LessonName% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Lesson ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old value', 'wp-security-audit-log' ) => '%OldDuration%',
						\esc_html__( 'New value', 'wp-security-audit-log' ) => '%NewDuration%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_lessons',
					'modified',
				),
				// @premium:end
				11300 => array(
					11300,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson category was created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the lesson category %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View lesson category', 'wp-security-audit-log' ) => '%CategoryLink%' ),
					'learndash_lessons',
					'created',
				),
				11301 => array(
					11301,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson category was deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Deleted the lesson category %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array(),
					'learndash_lessons',
					'deleted',
				),
				// @premium:start
				11302 => array(
					11302,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson category was renamed', 'wp-security-audit-log' ),
					\esc_html__( 'Renamed the lesson category %OldName% to %NewName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View lesson category', 'wp-security-audit-log' ) => '%CategoryLink%' ),
					'learndash_lessons',
					'renamed',
				),
				11303 => array(
					11303,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson category was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the slug of the lesson category %TaxonomyName% to %NewSlug%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Previous slug', 'wp-security-audit-log' ) => '%OldSlug%',
					),
					array( \esc_html__( 'View lesson category', 'wp-security-audit-log' ) => '%CategoryLink%' ),
					'learndash_lessons',
					'modified',
				),
				// @premium:end
				11350 => array(
					11350,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson tag was created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the lesson tag %TaxonomyTitle%', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View lesson tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_lessons',
					'created',
				),
				11351 => array(
					11351,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson tag was deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Deleted the lesson tag %TaxonomyTitle%', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array(),
					'learndash_lessons',
					'deleted',
				),
				// @premium:start
				11352 => array(
					11352,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson tag was renamed', 'wp-security-audit-log' ),
					\esc_html__( 'Renamed the lesson tag %OldName% to %NewName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View lesson tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_lessons',
					'renamed',
				),
				11353 => array(
					11353,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A lesson tag was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the slug of the lesson tag %TaxonomyName% to %NewSlug%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Previous slug', 'wp-security-audit-log' ) => '%OldSlug%',
					),
					array( \esc_html__( 'View lesson tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_lessons',
					'modified',
				),
				// @premium:end
			);
		}

		/**
		 * Learndash Topics Events
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_topics_array(): array {
			return array(
				11400 => array(
					11400,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A topic was created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Topic status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_topics',
					'created',
				),
				11401 => array(
					11401,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A topic was published', 'wp-security-audit-log' ),
					\esc_html__( 'Published the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_topics',
					'published',
				),
				// @premium:start
				11402 => array(
					11402,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic categories changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the category(ies) of the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic status', 'wp-security-audit-log' ) => '%PostStatus%',
						\esc_html__( 'Previous category(ies)', 'wp-security-audit-log' ) => '%OldCategories%',
						\esc_html__( 'New category(ies)', 'wp-security-audit-log' ) => '%NewCategories%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_topics',
					'modified',
				),
				11403 => array(
					11403,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic author changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the author of the topic %PostTitle% to %NewAuthor%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous author', 'wp-security-audit-log' ) => '%OldAuthor%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_topics',
					'modified',
				),
				// @premium:end
				11404 => array(
					11404,
					WSAL_MEDIUM,
					\esc_html__( 'A topic was moved to trash', 'wp-security-audit-log' ),
					\esc_html__( 'Moved the topic %PostTitle% to trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Topic status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_topics',
					'deleted',
				),
				11405 => array(
					11405,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A topic was permanently deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Permanently deleted the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_topics',
					'deleted',
				),
				11406 => array(
					11406,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A topic was duplicated', 'wp-security-audit-log' ),
					\esc_html__( 'Duplicated the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_topics',
					'duplicated',
				),
				// @premium:start
				11409 => array(
					11409,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic tags changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the tag(s) of the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous tag(s)', 'wp-security-audit-log' ) => '%OldTags%',
						\esc_html__( 'New tag(s)', 'wp-security-audit-log' ) => '%NewTags%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_topics',
					'modified',
				),
				11410 => array(
					11410,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Quiz added to topic', 'wp-security-audit-log' ),
					\esc_html__( 'Quiz %QuizTitle% was added to the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_topics',
					'added',
				),
				11411 => array(
					11411,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Quiz removed from topic', 'wp-security-audit-log' ),
					\esc_html__( 'Quiz %QuizTitle% was removed from the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_topics',
					'removed',
				),
				11412 => array(
					11412,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic topic-categories changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the topic category(ies) of the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous Topic category(ies)', 'wp-security-audit-log' ) => '%OldCategories%',
						\esc_html__( 'New Topic category(ies)', 'wp-security-audit-log' ) => '%NewCategories%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_topics',
					'modified',
				),
				11413 => array(
					11413,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic topic-tags changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the topic tag(s) of the topic %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Topic author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Previous Topic tag(s)', 'wp-security-audit-log' ) => '%OldTags%',
						\esc_html__( 'New Topic tag(s)', 'wp-security-audit-log' ) => '%NewTags%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_topics',
					'modified',
				),
				// @premium:end
				11450 => array(
					11450,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic tag created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the topic tag %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_topics',
					'created',
				),
				11451 => array(
					11451,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic tag deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Deleted the topic tag %TaxonomyTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array(),
					'learndash_topics',
					'deleted',
				),
				// @premium:start
				11452 => array(
					11452,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic tag renamed', 'wp-security-audit-log' ),
					\esc_html__( 'Renamed the topic tag %OldName% to %NewName%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Slug', 'wp-security-audit-log' ) => '%Slug%',
					),
					array( \esc_html__( 'View tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_topics',
					'renamed',
				),
				11453 => array(
					11453,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic tag modified', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the slug of the topic tag %TaxonomyName% to %NewSlug%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Previous slug', 'wp-security-audit-log' ) => '%OldSlug%',
					),
					array( \esc_html__( 'View tag', 'wp-security-audit-log' ) => '%TagLink%' ),
					'learndash_topics',
					'modified',
				),
				11480 => array(
					11480,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Topic duration was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Duration of the topic %TopicName% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Topic ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Old value', 'wp-security-audit-log' ) => '%OldDuration%',
						\esc_html__( 'New value', 'wp-security-audit-log' ) => '%NewDuration%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_topics',
					'modified',
				),
				// @premium:end
			);
		}

		/**
		 * Learndash Groups Events
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_groups_array(): array {
			return array(
				11500 => array(
					11500,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A group was created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the group %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Group author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Group category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Group status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_groups',
					'created',
				),
				11501 => array(
					11501,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A group was published', 'wp-security-audit-log' ),
					\esc_html__( 'Published the group %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Group author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Group category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_groups',
					'published',
				),
				// @premium:start
				11502 => array(
					11502,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Group author changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the author of the group %PostTitle% to %NewAuthor%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Previous author', 'wp-security-audit-log' ) => '%OldAuthor%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_groups',
					'modified',
				),
				// @premium:end
				11503 => array(
					11503,
					WSAL_MEDIUM,
					\esc_html__( 'A group was moved to trash', 'wp-security-audit-log' ),
					\esc_html__( 'Moved the group %PostTitle% to trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Group author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Group category', 'wp-security-audit-log' ) => '%LdPostCategory%',
						\esc_html__( 'Group status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_groups',
					'deleted',
				),
				11504 => array(
					11504,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A group was permanently deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Permanently deleted the group %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Group category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					array(),
					'learndash_groups',
					'deleted',
				),
				11505 => array(
					11505,
					WSAL_LOW,
					\esc_html__( 'A group was restored from trash', 'wp-security-audit-log' ),
					\esc_html__( 'Restored the group %PostTitle% from trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Group author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Category', 'wp-security-audit-log' ) => '%Categories%',
						\esc_html__( 'Group category', 'wp-security-audit-log' ) => '%LdPostCategory%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_groups',
					'restored',
				),
				// @premium:start
				11507 => array(
					11507,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Group start/end date was modified', 'wp-security-audit-log' ),
					\esc_html__( 'Start/End date of the group %PostTitle% was modified.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Group ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Previous start date', 'wp-security-audit-log' ) => '%OldStartDate%',
						\esc_html__( 'New start date', 'wp-security-audit-log' ) => '%NewStartDate%',
						\esc_html__( 'Previous end date', 'wp-security-audit-log' ) => '%OldEndDate%',
						\esc_html__( 'New end date', 'wp-security-audit-log' ) => '%NewEndDate%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_groups',
					'modified',
				),
				// @premium:end
			);
		}

		/**
		 * Learndash Certificates Events
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_certificates_array(): array {
			return array(
				// @premium:start
				11600 => array(
					11600,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A certificate was created', 'wp-security-audit-log' ),
					\esc_html__( 'Created the certificate %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Certificate ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Certificate author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Certificate status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost' ) ),
					'learndash_certificates',
					'created',
				),
				11601 => array(
					11601,
					WSAL_LOW,
					\esc_html__( 'A certificate was published', 'wp-security-audit-log' ),
					\esc_html__( 'Published the certificate %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Certificate ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Certificate author', 'wp-security-audit-log' ) => '%PostAuthor%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_certificates',
					'published',
				),
				11602 => array(
					11602,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Certificate author changed', 'wp-security-audit-log' ),
					\esc_html__( 'Changed the author of the certificate %PostTitle% to %NewAuthor%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Previous author', 'wp-security-audit-log' ) => '%OldAuthor%',
						\esc_html__( 'Certificate status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_certificates',
					'modified',
				),
				11603 => array(
					11603,
					WSAL_MEDIUM,
					\esc_html__( 'A certificate was moved to trash', 'wp-security-audit-log' ),
					\esc_html__( 'Moved the certificate %PostTitle% to trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Certificate ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Certificate author', 'wp-security-audit-log' ) => '%PostAuthor%',
						\esc_html__( 'Certificate status', 'wp-security-audit-log' ) => '%PostStatus%',
					),
					array(),
					'learndash_certificates',
					'deleted',
				),
				11604 => array(
					11604,
					WSAL_HIGH,
					\esc_html__( 'A certificate was permanently deleted', 'wp-security-audit-log' ),
					\esc_html__( 'Permanently deleted the certificate %PostTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Certificate ID', 'wp-security-audit-log' ) => '%PostID%',
					),
					array(),
					'learndash_certificates',
					'deleted',
				),
				11605 => array(
					11605,
					WSAL_LOW,
					\esc_html__( 'A certificate was restored from trash', 'wp-security-audit-log' ),
					\esc_html__( 'Restored the certificate %PostTitle% from trash.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Certificate ID', 'wp-security-audit-log' ) => '%PostID%',
						\esc_html__( 'Certificate author', 'wp-security-audit-log' ) => '%PostAuthor%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkPost', 'PostUrlIfPublished' ) ),
					'learndash_certificates',
					'restored',
				),
				// @premium:end
			);
		}

		/**
		 * Learndash Students Events
		 *
		 * @return array
		 *
		 * @since 5.6.0
		 */
		public static function get_students_array(): array {
			return array(
				// @premium:start
				11550 => array(
					11550,
					WSAL_LOW,
					\esc_html__( 'User was manually enrolled to a course', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% was manually enrolled to %CourseTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User ID', 'wp-security-audit-log' ) => '%TargetUserID%',
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Previous course(s) enrolled to', 'wp-security-audit-log' ) => '%OldCourses%',
						\esc_html__( 'New course(s) enrolled to', 'wp-security-audit-log' ) => '%NewCourses%',
					),
					Constants::wsaldefaults_build_links( array( 'EditUserLink' ) ),
					'learndash_students',
					'added',
				),
				11551 => array(
					11551,
					WSAL_LOW,
					\esc_html__( 'User was unenrolled from a course', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% was unenrolled from the course %CourseTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User ID', 'wp-security-audit-log' ) => '%TargetUserID%',
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Previous course(s) enrolled to', 'wp-security-audit-log' ) => '%OldCourses%',
						\esc_html__( 'New course(s) enrolled to', 'wp-security-audit-log' ) => '%NewCourses%',
					),
					Constants::wsaldefaults_build_links( array( 'EditUserLink' ) ),
					'learndash_students',
					'removed',
				),
				11552 => array(
					11552,
					WSAL_LOW,
					\esc_html__( 'User was manually enrolled to a group', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% was manually enrolled to the group %GroupTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User ID', 'wp-security-audit-log' ) => '%TargetUserID%',
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Group price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Previous group(s) enrolled to', 'wp-security-audit-log' ) => '%OldGroups%',
						\esc_html__( 'New group(s) enrolled to', 'wp-security-audit-log' ) => '%NewGroups%',
					),
					Constants::wsaldefaults_build_links( array( 'EditUserLink' ) ),
					'learndash_students',
					'added',
				),
				11553 => array(
					11553,
					WSAL_LOW,
					\esc_html__( 'User was manually unenrolled from a group', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% was manually unenrolled from the group %GroupTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User ID', 'wp-security-audit-log' ) => '%TargetUserID%',
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Group price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Previous group(s) enrolled to', 'wp-security-audit-log' ) => '%OldGroups%',
						\esc_html__( 'New group(s) enrolled to', 'wp-security-audit-log' ) => '%NewGroups%',
					),
					Constants::wsaldefaults_build_links( array( 'EditUserLink' ) ),
					'learndash_students',
					'removed',
				),
				11554 => array(
					11554,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A new user has enrolled in a course', 'wp-security-audit-log' ),
					\esc_html__( 'A new user %TargetUsername% has enrolled in the course %CourseTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Enrolled user first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'Enrolled user last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Course author', 'wp-security-audit-log' ) => '%CourseAuthor%',
						\esc_html__( 'Course category', 'wp-security-audit-log' ) => '%CourseCategory%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Course price amount', 'wp-security-audit-log' ) => '%PriceAmount%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkOrder' ) ),
					'learndash_students',
					'created',
				),
				11555 => array(
					11555,
					WSAL_INFORMATIONAL,
					\esc_html__( 'A new user has enrolled in a group', 'wp-security-audit-log' ),
					\esc_html__( 'A new user %TargetUsername% has enrolled in the course group %GroupTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'Enrolled user first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'Enrolled user last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Group author', 'wp-security-audit-log' ) => '%GroupAuthor%',
						\esc_html__( 'Available courses', 'wp-security-audit-log' ) => '%AvailableCourses%',
						\esc_html__( 'Group price type', 'wp-security-audit-log' ) => '%PriceType%',
						\esc_html__( 'Group price amount', 'wp-security-audit-log' ) => '%PriceAmount%',
					),
					Constants::wsaldefaults_build_links( array( 'EditorLinkOrder' ) ),
					'learndash_students',
					'created',
				),
				11556 => array(
					11556,
					WSAL_INFORMATIONAL,
					\esc_html__( 'User started a topic', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% has started the topic %TopicTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Lesson', 'wp-security-audit-log' ) => '%LessonTitle%',
						\esc_html__( 'Course title', 'wp-security-audit-log' ) => '%CourseTitle%',
						\esc_html__( 'Course group', 'wp-security-audit-log' ) => '%CourseGroup%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'started',
				),
				11557 => array(
					11557,
					WSAL_INFORMATIONAL,
					\esc_html__( 'User completed a topic', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% has completed the topic %TopicTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Lesson progress', 'wp-security-audit-log' ) => '%LessonProgress%',
						\esc_html__( 'Lesson title', 'wp-security-audit-log' ) => '%LessonTitle%',
						\esc_html__( 'Course title', 'wp-security-audit-log' ) => '%CourseTitle%',
						\esc_html__( 'Course group', 'wp-security-audit-log' ) => '%CourseGroup%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'completed',
				),
				11558 => array(
					11558,
					WSAL_INFORMATIONAL,
					\esc_html__( 'User started a lesson', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% has started the lesson %LessonTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Lesson', 'wp-security-audit-log' ) => '%LessonTitle%',
						\esc_html__( 'Course title', 'wp-security-audit-log' ) => '%CourseTitle%',
						\esc_html__( 'Course group', 'wp-security-audit-log' ) => '%CourseGroup%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'started',
				),
				11559 => array(
					11559,
					WSAL_INFORMATIONAL,
					\esc_html__( 'User completed a lesson', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% has completed the lesson %LessonTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Course title', 'wp-security-audit-log' ) => '%CourseTitle%',
						\esc_html__( 'Course progress', 'wp-security-audit-log' ) => '%CourseProgress%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'completed',
				),
				11561 => array(
					11561,
					WSAL_INFORMATIONAL,
					\esc_html__( 'User submitted an answer to a quiz', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% submitted an answer to the quiz %QuizTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Quiz progress', 'wp-security-audit-log' ) => '%QuizProgress%',
						\esc_html__( 'Course group', 'wp-security-audit-log' ) => '%CourseGroup%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'modified',
				),
				11562 => array(
					11562,
					WSAL_INFORMATIONAL,
					\esc_html__( 'User completed a quiz', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% completed the quiz %QuizTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'User first name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'User last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Quiz result', 'wp-security-audit-log' ) => '%QuizResult%',
						\esc_html__( 'Quiz type', 'wp-security-audit-log' ) => '%QuizType%',
						\esc_html__( 'Course title', 'wp-security-audit-log' ) => '%CourseTitle%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'completed',
				),
				11563 => array(
					11563,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Learndash course started', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% has started the course %CourseTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'First name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'Last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Course group', 'wp-security-audit-log' ) => '%CourseGroup%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'started',
				),
				11564 => array(
					11564,
					WSAL_INFORMATIONAL,
					\esc_html__( 'Learndash course completed', 'wp-security-audit-log' ),
					\esc_html__( 'User %TargetUsername% has completed the course %CourseTitle%.', 'wp-security-audit-log' ),
					array(
						\esc_html__( 'First name', 'wp-security-audit-log' ) => '%FirstName%',
						\esc_html__( 'Last name', 'wp-security-audit-log' ) => '%LastName%',
						\esc_html__( 'Course group', 'wp-security-audit-log' ) => '%CourseGroup%',
						\esc_html__( 'Course price type', 'wp-security-audit-log' ) => '%PriceType%',
					),
					array(),
					'learndash_students',
					'completed',
				),
				// @premium:end
			);
		}
	}
}
