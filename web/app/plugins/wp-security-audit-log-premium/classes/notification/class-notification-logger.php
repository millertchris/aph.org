<?php
/**
 * Class: Notification Logger
 *
 * Logger class for wsal.
 *
 * @since 5.1.1
 * @package    wsal
 * @subpackage loggers
 */

namespace WSAL\Loggers;

use WSAL\Controllers\Alert;
use WSAL\Helpers\WP_Helper;
use WSAL\Views\Notifications;
use WSAL\Helpers\Email_Helper;
use WSAL\Controllers\Constants;
use WSAL\Controllers\Slack\Slack;
use WSAL\Helpers\Settings_Helper;
use WSAL\Controllers\Twilio\Twilio;
use WSAL\Controllers\Slack\Slack_API;
use WSAL\Entities\Occurrences_Entity;
use WSAL\WP_Sensors\WP_System_Sensor;
use WSAL\Controllers\Twilio\Twilio_API;
use WSAL\Helpers\DateTime_Formatter_Helper;
use WSAL\Entities\Custom_Notifications_Entity;
use WSAL\Helpers\Formatters\Formatter_Factory;
use WSAL\Extensions\Helpers\Notification_Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Logger class
 */
if ( ! class_exists( '\WSAL\Loggers\Notification_Logger' ) ) {
	/**
	 * This class triggers notifications if set.
	 *
	 * @package    wsal
	 * @subpackage loggers
	 *
	 * @since 5.1.1
	 */
	class Notification_Logger {
// phpcs:disable
/* @premium:start */
// phpcs:enable
		/**
		 * Caches the enabled notifications from DB.
		 *
		 * @var array
		 *
		 * @since 5.2.1
		 */
		private static $custom_notifications = array();

		/**
		 * Caches the zone sets to the DB.
		 *
		 * @var bool
		 *
		 * @since 5.2.1
		 */
		private static $zone_set = false;
// phpcs:disable
/* @premium:end */
// phpcs:enable
		/**
		 * Notifies if conditions match.
		 *
		 * @param integer $type    - Alert code.
		 * @param array   $data    - Metadata.
		 * @param integer $date    - (Optional) created_on.
		 * @param integer $site_id - (Optional) site_id.
		 *
		 * @since 5.2.1
		 */
		public static function log( $type, $data = array(), $date = null, $site_id = null ) {

			$proceed = true;

			/**
			 * Since latest
			 *
			 * Gives the ability to disable the notifications.
			 */
			$proceed = \apply_filters( 'wsal_enable_notifications', $proceed );

			if ( ! $proceed ) {
				return;
			}

			// phpcs:disable
			/* @premium:start */
			// phpcs:enable
			// PHP alerts logging was deprecated in version 4.2.0.
			if ( $type < 0010 ) {
				return;
			}

			$built_notifications = Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

			if ( empty( self::$custom_notifications ) ) {
				self::$custom_notifications = Custom_Notifications_Entity::load_array( 'notification_status = %d', array( 1 ) );
			}

			// Nothing to do here if the notification are empty - bounce.
			if ( empty( $built_notifications ) && empty( self::$custom_notifications ) ) {
				return;
			}

			// We need to remove the timestamp to prevent from saving it as meta.
			unset( $data['Timestamp'] );

			$site_id = ! is_null( $site_id ) ? $site_id : ( function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 0 );

			$site_id = \apply_filters( 'wsal_database_site_id_value', $site_id, $type, $data );

			$fire_event = false;

			$logins_count_ids = array(
				1002,
				1003,
			);

			if ( \is_array( $built_notifications ) && ! empty( $built_notifications ) && isset( $built_notifications['notification_ids'] ) ) {
				$alert_ids = $built_notifications['notification_ids'];

				if ( \in_array( $type, $alert_ids ) ) {

					$fire = true;

					if ( \in_array( $type, $logins_count_ids ) ) {

						$fire = false;

						$site_id        = function_exists( 'get_current_blog_id' ) ? \get_current_blog_id() : 0;
						$counted_logins = self::count_login_failure( $data['ClientIP'], $site_id, ( ( isset( $data['CurrentUserID'] ) && $data['CurrentUserID'] ) ? $data['CurrentUserID'] : null ) );

						if ( $counted_logins >= $built_notifications[ 'event_' . $type . '_failed_more_than' ] ) {
							$fire = \true;
						}
					}
					if ( $fire ) {
						$sms_sent   = self::send_sms_notification( $type, $data, $date );
						$mail_sent  = self::send_mail_notification( $type, $data, $date );
						$slack_sent = self::send_slack_notification( $type, $data, $date );
						$fire_event = ( $sms_sent || $mail_sent || $slack_sent );
					}
				}
			}

			if ( \is_array( $built_notifications ) && ! empty( $built_notifications ) && isset( $built_notifications['notification_severities'] ) ) {

				foreach ( $built_notifications['notification_severities'] as $severity ) {

					if ( ( isset( $data['severity'] ) && (int) $severity === (int) $data['severity'] ) || ( isset( $data['Severity'] ) && (int) $severity === (int) $data['Severity'] ) ) {
						$sms_sent   = self::send_sms_notification( $type, $data, $date, $severity );
						$mail_sent  = self::send_mail_notification( $type, $data, $date, $severity );
						$slack_sent = self::send_slack_notification( $type, $data, $date, $severity );
						$fire_event = ( $sms_sent || $mail_sent || $slack_sent );
					}
				}
			}

			if ( ! empty( self::$custom_notifications ) ) {
				global $wpdb;

				foreach ( self::$custom_notifications as $notification ) {

					if ( ! empty( $notification['notification_query_sql'] ) ) {

						$notification['notification_query_sql'] = \html_entity_decode( \maybe_unserialize( $notification['notification_query_sql'] ) );

						$notification['settings'] = array();
						if ( isset( $notification['notification_settings'] ) ) {
							$notification['settings'] = \json_decode( $notification['notification_settings'], true );
						}

						$query_string = \esc_sql( $notification['notification_query_sql'] );

						$search_and_replace = Notification_Helper::map_alert_to_notifications( $type, $data );

						$query_string = \str_replace( $search_and_replace['search'], $search_and_replace['replace'], $query_string );

						$query = 'SELECT IF (' . ( $query_string ) . ', true, false) as `result`;';

						if ( false !== \strpos( $query_string, '___QWE_' ) ) {
							continue;
						}

						if ( false === self::$zone_set ) {
							$wpdb->query( 'SET time_zone = ' . "'" . Notification_Helper::get_mysql_time_zone() . "';" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							self::$zone_set = true;
						}

						$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

						$fire = false;

						if ( isset( $results ) && \is_array( $results ) && isset( $results[0] ) && isset( $results[0]['result'] ) ) {
							if ( true === (bool) $results[0]['result'] ) {
								$fire = true;
							}
						}

						if ( $fire ) {
							$sms_sent   = self::send_sms_notification( $type, $data, $date, null, $notification );
							$mail_sent  = self::send_mail_notification( $type, $data, $date, null, $notification );
							$slack_sent = self::send_slack_notification( $type, $data, $date, null, $notification );
							$fire_event = ( $sms_sent || $mail_sent || $slack_sent );
						}
					}
				}
			}

			if ( $fire_event ) {
				/**
				 * Fires immediately after an alert is logged.
				 *
				 * @since 5.1.1
				 */
				\do_action( 'wsal_notification_logged', null, $type, $data, $date, $site_id );
			}
			// phpcs:disable
			/* @premium:end */
			// phpcs:enable
		}
		// phpcs:disable
		/* @premium:start */
		// phpcs:enable
		/**
		 * Responsible for sending mail notifications (if set)
		 *
		 * @param int    $alert_id - The ID of the alert.
		 * @param array  $data - Collected alert data.
		 * @param string $date - Date passed (backward compatibility) may be null as this is legacy.
		 * @param int    $severity - If that is triggered because of severity notification settings - set that parameter with the integer value of the event severity, default to null otherwise.
		 * @param array  $custom_notification - The custom notification data (if that is triggered using custom notification).
		 *
		 * @return bool
		 *
		 * @since 5.1.1
		 */
		public static function send_mail_notification( $alert_id, array $data, $date, $severity = null, array $custom_notification = array() ) {
			$mail = Notification_Helper::get_notification_email( $alert_id, $severity );

			$uid = isset( $data['CurrentUserID'] ) ? $data['CurrentUserID'] : null;

			if ( ! empty( $custom_notification ) ) {
				$mail = Notification_Helper::get_custom_notification_email( $custom_notification, (int) $uid );
			}

			if ( $mail ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

				\remove_action( 'wp_mail_succeeded', array( WP_System_Sensor::class, 'mail_was_sent' ) );

				$blogname      = WP_Helper::get_blog_domain();
				$alert_message = Alert::get_message( (array) $data, null, $alert_id, 0, 'sms' );

				$username = \esc_html__( 'System', 'wp-security-audit-log' );

				if ( empty( $uid ) ) { // will happen "on login"
					// This will be populated.
					if ( ! empty( $data['Username'] ) ) {
						$username = $data['Username'];
					}
				} else {
					$user = \get_user_by( 'id', $uid );
					if ( false !== $user ) {
						$username = $user->user_login;
					}
				}

				// Get user first and last names from resulting username.
				$current_user = \get_user_by( 'login', $username );
				$first_name   = isset( $current_user->first_name ) ? $current_user->first_name : '';
				$last_name    = isset( $current_user->last_name ) ? $current_user->last_name : '';
				$user_email   = isset( $current_user->user_email ) ? $current_user->user_email : '';

				if ( Notification_Helper::get_correct_timestamp( $data, $date ) ) {
					$date = DateTime_Formatter_Helper::get_formatted_date_time( Notification_Helper::get_correct_timestamp( $data, $date ) );
				} else {
					$date = DateTime_Formatter_Helper::get_formatted_date_time(
						time(),
						'datetime',
						true,
						false,
						false
					);
				}

				$_user_roles = isset( $data['CurrentUserRoles'] ) ? $data['CurrentUserRoles'] : null;
				$user_role   = '';

				if ( isset( $_user_roles ) && isset( $_user_roles[0] ) && ! empty( $_user_roles[0] ) ) {
					if ( ! is_array( $_user_roles ) ) {
						$_user_roles = (array) $_user_roles;
					}
					if ( count( $_user_roles ) > 1 ) {
						$user_role = implode( ', ', $_user_roles );
					} else {
						$user_role = $_user_roles[0];
					}
				}

				$configuration     = Formatter_Factory::get_configuration( 'email' );
				$alert             = Alert::get_alert( $alert_id );
				$search_email_tags = array_keys( Notification_Helper::get_email_template_tags() );

				$replace_email_tags = array(
					$alert['desc'],
					$blogname,
					$username,
					$first_name,
					$last_name,
					$user_role,
					$user_email,
					$date,
					$alert_id,
					Constants::get_severity_by_code( Alert::get_alert( $alert_id )['severity'] )['name'],
					$alert_message,

					Alert::get_formatted_metadata( $configuration, $data, 0, $alert ),
					Alert::get_formatted_hyperlinks( $configuration, $data, 0, $alert ),
					( isset( $data['ClientIP'] ) ) ? $data['ClientIP'] : '',
					$data['Object'],
					$data['EventType'],
				);

				if ( ! empty( $custom_notification ) ) {
					$mail_template = Notification_Helper::get_custom_notification_mail_template( $custom_notification );
				} else {
					$mail_template = Notification_Helper::get_mail_template();
				}

				$subject = str_replace( $search_email_tags, $replace_email_tags, $mail_template['subject'] );

				if ( ! empty( $custom_notification ) ) {
					$subject = $custom_notification['notification_title'] . ' ' . $subject;
				}

				$content = str_replace( $search_email_tags, $replace_email_tags, stripslashes( $mail_template['body'] ) );

				// Send email notification.
				$bcc_field = '';

				if ( $bcc_mails = Notification_Helper::get_custom_notification_email_bcc( $custom_notification ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
					$bcc_field = 'Bcc: ' . $bcc_mails;
				}

				Email_Helper::send_email( Email_Helper::get_emails( $mail ), $subject, $content, $bcc_field );

				return \true;
			}

			return \false;
		}

		/**
		 * Responsible for sending SMS notifications (if set)
		 *
		 * @param int    $alert_id - The ID of the alert.
		 * @param array  $data - Collected alert data.
		 * @param string $date - Date passed (backward compatibility) may be null as this is legacy.
		 * @param int    $severity - If that is triggered because of severity notification settings - set that parameter with the integer value of the event severity, default to null otherwise.
		 * @param array  $custom_notification - The custom notification data (if that is triggered using custom notification).
		 * @return bool
		 *
		 * @since 5.1.1
		 */
		public static function send_sms_notification( $alert_id, array $data, $date, $severity = null, array $custom_notification = array() ) {
			$phone = Notification_Helper::get_notification_phone_number( $alert_id, $severity );

			if ( ! empty( $custom_notification ) ) {
				$phone = Notification_Helper::get_custom_notification_phone( $custom_notification );
			}

			if ( $phone && Twilio::is_set() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
				$blog_name     = WP_Helper::get_blog_name();
				$blog_url      = WP_Helper::get_blog_domain();
				$alert_message = Alert::get_message( (array) $data, null, $alert_id, 0, 'sms' );

				$uid      = isset( $data['CurrentUserID'] ) ? $data['CurrentUserID'] : null;
				$username = \esc_html__( 'System', 'wp-security-audit-log' );

				if ( empty( $uid ) ) { // will happen "on login"
					// This will be populated.
					if ( ! empty( $data['Username'] ) ) {
						$username = $data['Username'];
					}
				} else {
					$user = \get_user_by( 'id', $uid );
					if ( false !== $user ) {
						$username = $user->user_login;
					}
				}

				// Get user first and last names from resulting username.
				$current_user = \get_user_by( 'login', $username );
				$first_name   = isset( $current_user->first_name ) ? $current_user->first_name : '';
				$last_name    = isset( $current_user->last_name ) ? $current_user->last_name : '';
				$user_email   = isset( $current_user->user_email ) ? $current_user->user_email : '';

				if ( Notification_Helper::get_correct_timestamp( $data, $date ) ) {
					$date = DateTime_Formatter_Helper::get_formatted_date_time(
						Notification_Helper::get_correct_timestamp( $data, $date ),
						'datetime',
						true,
						false,
						false
					);
				} else {
					$date = DateTime_Formatter_Helper::get_formatted_date_time(
						time(),
						'datetime',
						true,
						false,
						false
					);
				}

				$_user_roles = isset( $data['CurrentUserRoles'] ) ? $data['CurrentUserRoles'] : null;
				$user_role   = '';

				if ( isset( $_user_roles ) && isset( $_user_roles[0] ) && ! empty( $_user_roles[0] ) ) {
					if ( ! is_array( $_user_roles ) ) {
						$_user_roles = (array) $_user_roles;
					}
					if ( count( $_user_roles ) > 1 ) {
						$user_role = implode( ', ', $_user_roles );
					} else {
						$user_role = $_user_roles[0];
					}
				}

				$search_sms_tags  = array_keys( Notification_Helper::get_sms_template_tags() );
				$replace_sms_tags = array(
					$blog_name,
					$username,
					$user_role,
					$user_email,
					$date,
					$alert_id,
					Constants::get_severity_by_code( Alert::get_alert( $alert_id )['severity'] )['name'],
					$alert_message,
					$data['ClientIP'],
					$data['Object'],
					$data['EventType'],
					$blog_url,
				);

				if ( ! empty( $custom_notification ) ) {
					$sms_template = Notification_Helper::get_custom_notification_sms_template( $custom_notification )['body'];
				} else {
					$sms_template = Notification_Helper::get_sms_template();
				}

				$sms_content = str_replace( $search_sms_tags, $replace_sms_tags, $sms_template );
				Twilio_API::send_sms( $phone, $sms_content );

				return \true;
			}

			return \false;
		}

		/**
		 * Responsible for sending Slack notifications (if set)
		 *
		 * @param int    $alert_id - The ID of the alert.
		 * @param array  $data - Collected alert data.
		 * @param string $date - Date passed (backward compatibility) may be null as this is legacy.
		 * @param int    $severity - If that is triggered because of severity notification settings - set that parameter with the integer value of the event severity, default to null otherwise.
		 * @param array  $custom_notification - The custom notification data (if that is triggered using custom notification).
		 * @return bool
		 *
		 * @since 5.3.4
		 */
		public static function send_slack_notification( $alert_id, array $data, $date, $severity = null, array $custom_notification = array() ) {
			$channel = Notification_Helper::get_notification_channel_name( $alert_id, $severity );

			if ( ! empty( $custom_notification ) ) {
				$channel = Notification_Helper::get_custom_notification_channel( $custom_notification );
			}

			if ( $channel && Slack::is_set() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

				$blogname      = WP_Helper::get_blog_domain();
				$alert_message = Alert::get_message( (array) $data, null, $alert_id, 0, 'slack' );

				$username = \esc_html__( 'System', 'wp-security-audit-log' );

				if ( empty( $uid ) ) { // will happen "on login"
					// This will be populated.
					if ( ! empty( $data['Username'] ) ) {
						$username = $data['Username'];
					}
				} else {
					$user = \get_user_by( 'id', $uid );
					if ( false !== $user ) {
						$username = $user->user_login;
					}
				}

				// Get user first and last names from resulting username.
				$current_user = \get_user_by( 'login', $username );
				$first_name   = isset( $current_user->first_name ) ? $current_user->first_name : '';
				$last_name    = isset( $current_user->last_name ) ? $current_user->last_name : '';
				$user_email   = isset( $current_user->user_email ) ? $current_user->user_email : '';

				if ( Notification_Helper::get_correct_timestamp( $data, $date ) ) {
					$date = DateTime_Formatter_Helper::get_formatted_date_time(
						Notification_Helper::get_correct_timestamp( $data, $date ),
						'datetime',
						true,
						false,
						false
					);
				} else {
					$date = DateTime_Formatter_Helper::get_formatted_date_time(
						time(),
						'datetime',
						true,
						false,
						false
					);
				}

				$_user_roles = isset( $data['CurrentUserRoles'] ) ? $data['CurrentUserRoles'] : null;
				$user_role   = '';

				if ( isset( $_user_roles ) && isset( $_user_roles[0] ) && ! empty( $_user_roles[0] ) ) {
					if ( ! is_array( $_user_roles ) ) {
						$_user_roles = (array) $_user_roles;
					}
					if ( count( $_user_roles ) > 1 ) {
						$user_role = implode( ', ', $_user_roles );
					} else {
						$user_role = $_user_roles[0];
					}
				}

				$configuration = Formatter_Factory::get_configuration( 'slack' );
				$alert         = Alert::get_alert( $alert_id );

				$search_email_tags = array_keys( Notification_Helper::get_email_template_tags() );

				$replace_email_tags = array(
					$alert['desc'],
					$blogname,
					$username,
					$first_name,
					$last_name,
					$user_role,
					$user_email,
					$date,
					$alert_id,
					Constants::get_severity_by_code( Alert::get_alert( $alert_id )['severity'] )['name'],
					$alert_message,

					Alert::get_formatted_metadata( $configuration, $data, 0, $alert ),
					Alert::get_formatted_hyperlinks( $configuration, $data, 0, $alert ),
					( isset( $data['ClientIP'] ) ) ? $data['ClientIP'] : '',
					$data['Object'],
					$data['EventType'],
				);

				if ( ! empty( $custom_notification ) ) {
					$sms_template = Notification_Helper::get_custom_notification_slack_template( $custom_notification )['body'];
				} else {
					$sms_template = Notification_Helper::get_slack_template()['body'];
				}

				$sms_content = str_replace( $search_email_tags, $replace_email_tags, $sms_template );
				Slack_API::send_slack_message_via_api( null, $channel, $sms_content );

				return \true;
			}

			return \false;
		}

		/**
		 * Determines what is the correct timestamp for the event.
		 *
		 * It uses the timestamp from metadata if available. This is needed because we introduced a possible delay by using
		 * action scheduler in 4.3.0. The $legacy_date attribute is only used for migration of legacy data. This should be
		 * removed in future releases.
		 *
		 * @param array $metadata    Event metadata.
		 * @param int   $legacy_date Legacy date only used when migrating old db event format to the new one.
		 *
		 * @return float GMT timestamp including microseconds.
		 *
		 * @since 4.6.0
		 */
		protected static function get_correct_timestamp( $metadata, $legacy_date ) {

			if ( is_null( $legacy_date ) ) {
				$timestamp = current_time( 'U.u', true );

				$timestamp = \apply_filters( 'wsal_database_timestamp_value', $timestamp, $metadata );

				return array_key_exists( 'Timestamp', $metadata ) ? $metadata['Timestamp'] : $timestamp;
			}

			return floatval( $legacy_date );
		}

		/**
		 * Count login failure and update the transient.
		 *
		 * @param string $ip - IP address.
		 * @param int    $site_id - Site ID.
		 * @param object $user_id - WPUser ID.
		 *
		 * @return int
		 *
		 * @since 5.2.1
		 */
		public static function count_login_failure( $ip, $site_id, $user_id = null ): int {
			$count = 0;
			// Get the current date.
			$date = new \DateTime();

			// Extract day, month, and year.
			$day   = $date->format( 'd' );
			$month = $date->format( 'm' );
			$year  = $date->format( 'Y' );
			if ( isset( $user_id ) ) {
				$occ   = Occurrences_Entity::build_multi_query(
					'   WHERE client_ip = %s '
					. ' AND user_id = %s '
					. ' AND alert_id = %d '
					. ' AND site_id = %d '
					. ' AND ( created_on BETWEEN %d AND %d );',
					array(
						$ip,
						$user_id,
						1002,
						$site_id,
						mktime( 0, 0, 0, $month, $day, $year ),
						mktime( 0, 0, 0, $month, $day + 1, $year ) - 1,
					)
				);
				$count = count( $occ ) ? count( $occ ) : 0;
			} else {
				$occ   = Occurrences_Entity::build_multi_query(
					'   WHERE client_ip = %s '
					. ' AND alert_id = %d '
					. ' AND site_id = %d '
					. ' AND ( created_on BETWEEN %d AND %d );',
					array(
						$ip,
						1003,
						$site_id,
						mktime( 0, 0, 0, $month, $day, $year ),
						mktime( 0, 0, 0, $month, $day + 1, $year ) - 1,
					)
				);
				$count = count( $occ ) ? count( $occ ) : 0;
			}

			return $count;
		}
// phpcs:disable
/* @premium:end */
// phpcs:enable
	}
}
