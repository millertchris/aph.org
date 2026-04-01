<?php
/**
 * Class: Notification Helper.
 *
 * Logger class for wsal.
 *
 * @since 5.1.1
 *
 * @package    wsal
 * @subpackage helpers
 */

namespace WSAL\Extensions\Helpers;

use WSAL\Controllers\Alert_Manager;
use WSAL\Controllers\Slack\Slack;
use WSAL\Controllers\Slack\Slack_API;
use WSAL\Controllers\Twilio\Twilio;
use WSAL\Controllers\Twilio\Twilio_API;
use WSAL\Entities\Metadata_Entity;
use WSAL\Entities\Occurrences_Entity;
use WSAL\Helpers\DateTime_Formatter_Helper;
use WSAL\Helpers\Email_Helper;
use WSAL\Helpers\Settings_Helper;
use WSAL\Helpers\WP_Helper;
use WSAL\Views\Notifications;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Notifications Helper class
 */
if ( ! class_exists( '\WSAL\Extensions\Helpers\Notification_Helper' ) ) {
	/**
	 * This class triggers notifications if set.
	 *
	 * @package    wsal
	 * @subpackage helpers
	 *
	 * @since 5.1.1
	 */
	class Notification_Helper {

		public const VALUE_QUERY_PREFIX          = '^GGG^';
		public const NUMBER_OF_EVENTS_TO_INCLUDE = 10;

		/**
		 * Daily Report Events.
		 *
		 * Events to be included in the daily report summary.
		 *
		 * @var array
		 *
		 * @since 5.2.2
		 */
		public static $daily_report_events;

		// phpcs:disable
		/* @premium:start */
		// phpcs:enable
		/**
		 * Returns the the mail notification possible template tags.
		 *
		 * @return array
		 *
		 * @since 5.1.1
		 */
		public static function get_email_template_tags() {
			return \apply_filters(
				'wsal_notification_email_template_tags',
				array(
					'{title}'          => esc_html__( 'Notification Title', 'wp-security-audit-log' ),
					'{site}'           => esc_html__( 'Website Name', 'wp-security-audit-log' ),
					'{username}'       => esc_html__( 'User Login Name', 'wp-security-audit-log' ),
					'{user_firstname}' => esc_html__( 'User First Name', 'wp-security-audit-log' ),
					'{user_lastname}'  => esc_html__( 'User Last Name', 'wp-security-audit-log' ),
					'{user_role}'      => esc_html__( 'Role(s) of the User', 'wp-security-audit-log' ),
					'{user_email}'     => esc_html__( 'Email of the User', 'wp-security-audit-log' ),
					'{date_time}'      => esc_html__( 'Event generated on Date and Time', 'wp-security-audit-log' ),
					'{alert_id}'       => esc_html__( 'Event Code', 'wp-security-audit-log' ),
					'{severity}'       => esc_html__( 'Event Severity', 'wp-security-audit-log' ),
					'{message}'        => esc_html__( 'Event Message', 'wp-security-audit-log' ),
					'{meta}'           => esc_html__( 'Event Metadata', 'wp-security-audit-log' ),
					'{links}'          => esc_html__( 'Event Links', 'wp-security-audit-log' ),
					'{source_ip}'      => esc_html__( 'Client IP Address', 'wp-security-audit-log' ),
					'{object}'         => esc_html__( 'Event Object', 'wp-security-audit-log' ),
					'{event_type}'     => esc_html__( 'Event Type', 'wp-security-audit-log' ),
				)
			);
		}

		/**
		 * Sends test notification email via AJAX request (used in Notification settings wp-admin page).
		 *
		 * @return void
		 *
		 * @since 5.3.0
		 * @since 5.6.0 - Fixed test email body to preserve HTML formatting using wp_kses_post and wpautop.
		 */
		public static function send_test_email() {
			if ( \wp_doing_ajax() ) {
				if ( isset( $_REQUEST['_wpnonce'] ) ) {

					$nonce_check = \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ), Twilio::NONCE_NAME );

					if ( ! $nonce_check ) {
						\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Nonce checking failed', 'wp-security-audit-log' ) ), 400 );
					}
				} else {
					\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Nonce not provided', 'wp-security-audit-log' ) ), 400 );
				}
			} else {
				\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Not allowed', 'wp-security-audit-log' ) ), 400 );
			}

			if ( \current_user_can( 'manage_options' ) ) {
				if ( isset( $_REQUEST['email_body'] ) && ! empty( $_REQUEST['email_body'] )
				&& isset( $_REQUEST['email_subject'] ) && ! empty( $_REQUEST['email_subject'] )
				&& isset( $_REQUEST['email_address'] ) && ! empty( $_REQUEST['email_address'] ) ) {
					$replace_email_tags = array(
						\esc_html__( 'Test description', 'wp-security-audit-log' ),
						WP_Helper::get_blog_domain(),
						\esc_html__( 'Test username', 'wp-security-audit-log' ),
						\esc_html__( 'Test first name', 'wp-security-audit-log' ),
						\esc_html__( 'Test last name', 'wp-security-audit-log' ),
						\esc_html__( 'Test user role', 'wp-security-audit-log' ),
						\esc_html__( 'Test email', 'wp-security-audit-log' ),
						DateTime_Formatter_Helper::get_formatted_date_time( time() ),
						'0000',
						__( 'Medium', 'wp-security-audit-log' ),
						\esc_html__( 'Test alert message', 'wp-security-audit-log' ),

						\esc_html__( 'Activated testing messaging', 'wp-security-audit-log' ),
						\esc_html__( 'test.com', 'wp-security-audit-log' ),
						'127.0.0.1',
						\esc_html__( 'Test object', 'wp-security-audit-log' ),
						\esc_html__( 'Test event type', 'wp-security-audit-log' ),
					);

					$search_email_tags = array_keys( self::get_email_template_tags() );

					$subject = str_replace( $search_email_tags, $replace_email_tags, \sanitize_text_field( \wp_unslash( $_REQUEST['email_subject'] ) ) );

					$content = str_replace( $search_email_tags, $replace_email_tags, \wpautop( \wp_kses_post( \wp_unslash( $_REQUEST['email_body'] ) ) ) );

					$result = Email_Helper::send_email( Email_Helper::get_emails( \sanitize_text_field( \wp_unslash( $_REQUEST['email_address'] ) ) ), $subject, $content );

					if ( $result ) {
						\wp_send_json_success();
						\wp_die();
					} else {
						if ( empty( trim( $subject ) ) ) {
							\wp_send_json_error( __( 'No subject is provided. Please check and provide the details again.', 'wp-security-audit-log' ) );
							\wp_die();
						}
						if ( empty( trim( $content ) ) ) {
							\wp_send_json_error( __( 'No content is provided. Please check and provide the details again.', 'wp-security-audit-log' ) );
							\wp_die();
						}
						if ( empty( trim( \sanitize_text_field( \wp_unslash( $_REQUEST['email_subject'] ) ) ) ) ) {
							\wp_send_json_error( __( 'No email is provided. Please specify a default email address in the settings to send a test email.', 'wp-security-audit-log' ) );
							\wp_die();
						}

						\wp_send_json_error( __( 'Something went wrong with sending test email!', 'wp-security-audit-log' ) );
						\wp_die();
					}
				}
			}
			\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'You are not allowed to do this', 'wp-security-audit-log' ) ), 400 );
		}

		/**
		 * Stores the Twilio Credentials key via AJAX request.
		 *
		 * @return void
		 *
		 * @since 5.3.0
		 */
		public static function send_test_sms() {
			if ( \wp_doing_ajax() ) {
				if ( isset( $_REQUEST['_wpnonce'] ) ) {
					$nonce_check = \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ), Twilio::NONCE_NAME );
					if ( ! $nonce_check ) {
						\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Nonce checking failed', 'wp-security-audit-log' ) ), 400 );
					}
				} else {
					\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Nonce is not provided', 'wp-security-audit-log' ) ), 400 );
				}
			} else {
				\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Not allowed', 'wp-security-audit-log' ) ), 400 );
			}

			if ( \current_user_can( 'manage_options' ) && Twilio::is_set() ) {
				if ( isset( $_REQUEST['email_body'] ) && ! empty( $_REQUEST['email_body'] )
				&& isset( $_REQUEST['number'] ) && ! empty( $_REQUEST['number'] ) ) {
					$replace_sms_tags = array(
						WP_Helper::get_blog_name(),
						\esc_html__( 'Test username', 'wp-security-audit-log' ),
						\esc_html__( 'Test user role', 'wp-security-audit-log' ),
						\esc_html__( 'Test email', 'wp-security-audit-log' ),
						DateTime_Formatter_Helper::get_formatted_date_time( time() ),
						'0000',
						__( 'Medium', 'wp-security-audit-log' ),
						\esc_html__( 'Test alert message', 'wp-security-audit-log' ),
						'127.0.0.1',
						\esc_html__( 'Test object', 'wp-security-audit-log' ),
						\esc_html__( 'Test event type', 'wp-security-audit-log' ),
						WP_Helper::get_blog_domain(),
					);

					$sms_template = \sanitize_text_field( \wp_unslash( $_REQUEST['email_body'] ) );
					$sms_content  = str_replace( array_keys( self::get_sms_template_tags() ), $replace_sms_tags, $sms_template );
					$result       = Twilio_API::send_sms( \sanitize_text_field( \wp_unslash( $_REQUEST['number'] ) ), $sms_content );

					if ( $result ) {
						\wp_send_json_success();
						\wp_die();
					} else {
						if ( empty( trim( $sms_content ) ) ) {
							\wp_send_json_error( __( 'No sms content is provided. Please check and provide the details again.', 'wp-security-audit-log' ) );
							\wp_die();
						}
						if ( empty( trim( \sanitize_text_field( \wp_unslash( $_REQUEST['number'] ) ) ) ) ) {
							\wp_send_json_error( __( 'No number is provided. Please specify a default phone number in the settings to send a test message.', 'wp-security-audit-log' ) );
							\wp_die();
						}

						$twilio_error = Twilio_API::get_twilio_error();
						if ( ! empty( $twilio_error ) ) {
							\wp_send_json_error( __( 'TWILIO:', 'wp-security-audit-log' ) . $twilio_error );
						}

						\wp_send_json_error( __( 'Something went wrong with sending test sms!', 'wp-security-audit-log' ) );
						\wp_die();
					}
				}
			}
			\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'You are not allowed to do this or Twilio is not set', 'wp-security-audit-log' ) ), 400 );
		}

		/**
		 * Sends the Slack test message via AJAX request.
		 *
		 * @return void
		 *
		 * @since 5.3.4
		 */
		public static function send_test_slack() {
			if ( \wp_doing_ajax() ) {
				if ( isset( $_REQUEST['_wpnonce'] ) ) {
					$nonce_check = \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) ), Slack::NONCE_NAME );
					if ( ! $nonce_check ) {
						\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Nonce checking failed', 'wp-security-audit-log' ) ), 400 );
					}
				} else {
					\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Nonce is not provided', 'wp-security-audit-log' ) ), 400 );
				}
			} else {
				\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'Not allowed', 'wp-security-audit-log' ) ), 400 );
			}

			if ( \current_user_can( 'manage_options' ) && Slack::is_set() ) {
				if ( isset( $_REQUEST['email_body'] ) && ! empty( $_REQUEST['email_body'] )
				&& isset( $_REQUEST['number'] ) && ! empty( $_REQUEST['number'] ) ) {
					$replace_slack_tags = array(
						\esc_html__( 'Test description', 'wp-security-audit-log' ),
						WP_Helper::get_blog_domain(),
						\esc_html__( 'Test username', 'wp-security-audit-log' ),
						\esc_html__( 'Test first name', 'wp-security-audit-log' ),
						\esc_html__( 'Test last name', 'wp-security-audit-log' ),
						\esc_html__( 'Test user role', 'wp-security-audit-log' ),
						\esc_html__( 'Test email', 'wp-security-audit-log' ),
						DateTime_Formatter_Helper::get_formatted_date_time( time() ),
						'0000',
						__( 'Medium', 'wp-security-audit-log' ),
						\esc_html__( 'Test alert message', 'wp-security-audit-log' ),

						\esc_html__( 'Activated testing messaging', 'wp-security-audit-log' ),
						\esc_html__( 'test.com', 'wp-security-audit-log' ),
						'127.0.0.1',
						\esc_html__( 'Test object', 'wp-security-audit-log' ),
						\esc_html__( 'Test event type', 'wp-security-audit-log' ),
					);

					$search_slack_tags = array_keys( self::get_email_template_tags() );

					$slack_template = \sanitize_textarea_field( ( $_REQUEST['email_body'] ) );

					$content = str_replace( $search_slack_tags, $replace_slack_tags, $slack_template );

					$result = Slack_API::send_slack_message_via_api( null, \sanitize_text_field( \wp_unslash( $_REQUEST['number'] ) ), $content );

					if ( $result ) {
						\wp_send_json_success();
						\wp_die();
					} else {
						if ( empty( trim( $content ) ) ) {
							\wp_send_json_error( __( 'No slack content is provided. Please check and provide the details again.', 'wp-security-audit-log' ) );
							\wp_die();
						}
						if ( empty( trim( \sanitize_text_field( \wp_unslash( $_REQUEST['number'] ) ) ) ) ) {
							\wp_send_json_error( __( 'No channel name is provided. Please specify a default channel name in the settings to send a test slack.', 'wp-security-audit-log' ) );
							\wp_die();
						}

						$slack_error = Slack_API::get_slack_error();
						if ( ! empty( $slack_error ) ) {
							\wp_send_json_error( __( 'SLACK:', 'wp-security-audit-log' ) . $slack_error );
						}

						\wp_send_json_error( __( 'Something went wrong with sending test message!', 'wp-security-audit-log' ) );
						\wp_die();
					}
				}
			}
			\wp_send_json_error( new \WP_Error( 500, \esc_html__( 'You are not allowed to do this or Slack is not set', 'wp-security-audit-log' ) ), 400 );
		}

		/**
		 * Get SMS Template Tags.
		 *
		 * Returns the tags supported by WSAL sms notification.
		 *
		 * @since 5.1.1
		 *
		 * @return array
		 */
		public static function get_sms_template_tags() {
			return \apply_filters(
				'wsal_notification_sms_template_tags',
				array(
					'{site_name}'  => 'Website Name',
					'{username}'   => 'User Login Name',
					'{user_role}'  => 'Role(s) of the User',
					'{user_email}' => 'Email of the User',
					'{date_time}'  => 'Event generated on Date and Time',
					'{alert_id}'   => 'Event Code',
					'{severity}'   => 'Event Severity',
					'{message}'    => 'Event Message',
					'{source_ip}'  => 'Client IP Address',
					'{object}'     => 'Event Object',
					'{event_type}' => 'Event Type',
					'{site}'       => 'Website Url',
				)
			);
		}

		/**
		 * Returns the default SMS body.
		 *
		 * @since 5.1.1
		 */
		public static function get_default_sms_body(): string {
			$default_sms_body  = esc_html__( 'Site Name', 'wp-security-audit-log' ) . ': {site_name}' . "\r\n";
			$default_sms_body .= esc_html__( 'User/Role', 'wp-security-audit-log' ) . ': {username} / {user_role}' . "\r\n";
			// $default_sms_body .= esc_html__( 'Email', 'wp-security-audit-log' ) . ': {user_email}' . "\r\n";
			$default_sms_body .= esc_html__( 'IP Address', 'wp-security-audit-log' ) . ': {source_ip}' . "\r\n";
			$default_sms_body .= esc_html__( 'Event ID', 'wp-security-audit-log' ) . ': {alert_id}' . "\r\n";
			// $default_sms_body .= esc_html__( 'Event type', 'wp-security-audit-log' ) . ': {event_type}' . "\r\n";
			// $default_sms_body .= esc_html__( 'Message', 'wp-security-audit-log' ) . ': {message}';

			return $default_sms_body;
		}

		/**
		 * Returns the email template.
		 *
		 * @since 5.1.1
		 */
		public static function get_mail_template(): array {
			$settings = Notifications::get_global_notifications_setting();

			$template = array();

			if ( ! empty( $settings ) && isset( $settings['email_notifications_body'] ) && '' !== trim( $settings['email_notifications_body'] ) ) {
				$template['body'] = $settings['email_notifications_body'];
			} else {
				$template['body'] = self::get_default_email_body();
			}

			if ( ! empty( $settings ) && isset( $settings['email_notifications_subject'] ) && '' !== trim( $settings['email_notifications_subject'] ) ) {
				$template['subject'] = $settings['email_notifications_subject'];
			} else {
				$template['subject'] = self::get_default_email_subject();
			}

			return $template;
		}

		/**
		 * Returns the email template.
		 *
		 * @since 5.1.1
		 */
		public static function get_slack_template(): array {
			$settings = Notifications::get_global_notifications_setting();

			$template = array();

			if ( ! empty( $settings ) && isset( $settings['slack_notifications_body'] ) && '' !== trim( $settings['slack_notifications_body'] ) ) {
				$template['body'] = $settings['slack_notifications_body'];
			} else {
				$template['body'] = self::get_default_slack_body();
			}

			return $template;
		}

		/**
		 * Returns the email template.
		 *
		 * @param array $notification - The notification data to check for.
		 *
		 * @since 5.2.1
		 */
		public static function get_custom_notification_mail_template( array $notification ): array {
			$template = array();

			$template = self::get_mail_template();

			if ( ! empty( $notification ) && isset( $notification['notification_template'] ) && '' !== trim( $notification['notification_template'] ) ) {
				$template_data = json_decode( $notification['notification_template'], true );
				if ( $template_data['custom_notification_template_enabled'] ) {
					$template['body'] = $template_data['email_custom_notifications_body'];

					$template['subject'] = $template_data['email_custom_notifications_subject'];
				}
			}

			return $template;
		}

		/**
		 * Returns the sms template.
		 *
		 * @param array $notification - The notification data to check for.
		 *
		 * @since 5.3.0
		 */
		public static function get_custom_notification_sms_template( array $notification ): array {
			$template = array();

			$template['body'] = self::get_sms_template();

			if ( ! empty( $notification ) && isset( $notification['notification_sms_template'] ) && '' !== trim( $notification['notification_sms_template'] ) ) {
				$template_data = json_decode( $notification['notification_sms_template'], true );
				if ( isset( $template_data['custom_notification_sms_template_enabled'] ) && $template_data['custom_notification_sms_template_enabled'] ) {
					$template['body'] = $template_data['sms_custom_notifications_body'];
				}
			}

			return $template;
		}

		/**
		 * Returns the slack template.
		 *
		 * @param array $notification - The notification data to check for.
		 *
		 * @since 5.3.4
		 */
		public static function get_custom_notification_slack_template( array $notification ): array {
			$template = array();

			$template['body'] = self::get_slack_template()['body'];

			if ( ! empty( $notification ) && isset( $notification['notification_slack_template'] ) && '' !== trim( $notification['notification_slack_template'] ) ) {
				$template_data = json_decode( $notification['notification_slack_template'], true );
				if ( isset( $template_data['custom_notification_slack_template_enabled'] ) && $template_data['custom_notification_slack_template_enabled'] ) {
					$template['body'] = $template_data['slack_custom_notifications_body'];
				}
			}

			return $template;
		}

		/**
		 * Returns the SMS template.
		 *
		 * @since 5.1.1
		 */
		public static function get_sms_template(): string {
			$settings = Notifications::get_global_notifications_setting();

			if ( ! empty( $settings ) && isset( $settings['sms_notifications_body'] ) && '' !== trim( $settings['sms_notifications_body'] ) ) {
				return $settings['sms_notifications_body'];
			}

			return self::get_default_sms_body();
		}

		/**
		 * Prepares the search and replace arrays - searches for placeholders and their representitive values. Returns array with 2 arrays - search and replace.
		 *
		 * @param int   $alert_id   - The ID of the alert.
		 * @param array $alert_data - The collected event data.
		 *
		 * @return array - Multidimensional array with search and replace arrays.
		 *
		 * @since 5.2.1
		 */
		public static function map_alert_to_notifications( $alert_id, array $alert_data ): array {
			$notification_fields = self::get_notifications_fields_name_mapping();
			$search              = array( self::VALUE_QUERY_PREFIX );
			$replace             = array( "'" );

			$processed_alert_data = \array_change_key_case( (array) $alert_data, \CASE_LOWER );

			foreach ( $notification_fields as $field_name => $field_key ) {
				if ( 'event_id' === $field_name ) {
					$search[]  = \esc_sql( $notification_fields[ $field_name ]['interpolate'] );
					$replace[] = $alert_id;

					continue;
				}
				if ( 'productstatus' === $field_name ) {
					if ( 'woocommerce-product' === $alert_data['Object']
					&& (
						isset( $alert_data['NewStatus'] ) ||
					isset( $alert_data['OldStatus'] ) ||
					isset( $alert_data['PostStatus'] ) ) ) {
						$status = '';
						if ( isset( $alert_data['NewStatus'] ) ) {
							$status    = $alert_data['NewStatus'];
							$search[]  = '___QWE_PRODUCT_STATUS_QWE___';
							$replace[] = "'" . $status . "'";
						}
						if ( isset( $alert_data['OldStatus'] ) ) {
							$status    = $alert_data['OldStatus'];
							$search[]  = '___QWE_PRODUCT_STATUS_QWE___';
							$replace[] = "'" . $status . "'";
						}
						if ( isset( $alert_data['PostStatus'] ) ) {
							$status    = $alert_data['PostStatus'];
							$search[]  = '___QWE_PRODUCT_STATUS_QWE___';
							$replace[] = "'" . $status . "'";
						}

						continue;
					}
				}

				if ( 'roles' === $field_name && isset( $processed_alert_data[ $field_name ] ) && ! empty( $processed_alert_data[ $field_name ] ) ) {
					if ( is_string( $processed_alert_data[ $field_name ] ) ) {
						$roles = "'" . \implode( "','", array_map( 'trim', \explode( ',', $processed_alert_data[ $field_name ] ) ) ) . "'";
					} else {
						$roles = "'" . \implode( "','", array_map( 'trim', $processed_alert_data[ $field_name ] ) ) . "'";
					}

					$search[]  = \esc_sql( $notification_fields[ $field_name ]['interpolate'] );
					$replace[] = $roles;

					continue;
				}
				if ( 'affected_user_role' === $field_name && ( isset( $processed_alert_data['targetuserdata'] ) && ! empty( $processed_alert_data['targetuserdata'] ) || isset( $processed_alert_data['newuserdata'] ) && ! empty( $processed_alert_data['newuserdata'] ) ) ) {
					$field_name_to_look_for = ( isset( $processed_alert_data['targetuserdata'] ) ) ? 'targetuserdata' : 'newuserdata';

					$roles = '';

					if ( \is_object( $processed_alert_data[ $field_name_to_look_for ] ) && property_exists( $processed_alert_data[ $field_name_to_look_for ], 'Roles' ) ) {
						if ( is_string( $processed_alert_data[ $field_name_to_look_for ]->Roles ) ) {
							$roles = "'" . \implode( "','", array_map( 'trim', \explode( ',', $processed_alert_data[ $field_name_to_look_for ]->Roles ) ) ) . "'";
						} else {
							$roles = "'" . \implode( "','", array_map( 'trim', $processed_alert_data[ $field_name_to_look_for ]->Roles ) ) . "'";
						}
					}

					if ( \is_array( $processed_alert_data[ $field_name_to_look_for ] ) && isset( $processed_alert_data[ $field_name_to_look_for ]['Roles'] ) ) {
						if ( is_string( $processed_alert_data[ $field_name_to_look_for ]['Roles'] ) ) {
							$roles = "'" . \implode( "','", array_map( 'trim', \explode( ',', $processed_alert_data[ $field_name_to_look_for ]['Roles'] ) ) ) . "'";
						} else {
							$roles = "'" . \implode( "','", array_map( 'trim', $processed_alert_data[ $field_name_to_look_for ]['Roles'] ) ) . "'";
						}
					}

					$search[]  = \esc_sql( $notification_fields[ $field_name ]['interpolate'] );
					$replace[] = $roles;

					continue;
				}
				if ( 'currentuserroles' === $field_name && isset( $processed_alert_data[ $field_name ] ) && ! empty( $processed_alert_data[ $field_name ] ) ) {
					if ( is_string( $processed_alert_data[ $field_name ] ) ) {
						$roles = "'" . \implode( "','", array_map( 'trim', \explode( ',', $processed_alert_data[ $field_name ] ) ) ) . "'";
					} else {
						$roles = "'" . \implode( "','", array_map( 'trim', $processed_alert_data[ $field_name ] ) ) . "'";
					}

					$search[]  = \esc_sql( $notification_fields[ $field_name ]['interpolate'] );
					$replace[] = $roles;

					continue;
				}
				if ( 'time' === $field_name ) {
					$search[]  = \esc_sql( $notification_fields[ $field_name ]['interpolate'] );
					$replace[] = "LOWER( DATE_FORMAT( NOW(), '%k:%i' ) )";

					continue;
				}
				if ( 'date' === $field_name ) {
					$search[]  = \esc_sql( $notification_fields[ $field_name ]['interpolate'] );
					$replace[] = 'DATE( NOW() )';

					continue;
				}
				if ( isset( $processed_alert_data[ $field_name ] ) && ! empty( $processed_alert_data[ $field_name ] ) ) {
					$search[] = \esc_sql( $notification_fields[ $field_name ]['interpolate'] );
					if ( 'string' === $notification_fields[ $field_name ]['type'] ) {
						$replace[] = "'" . $processed_alert_data[ $field_name ] . "'";
					} else {
						$replace[] = $processed_alert_data[ $field_name ];
					}
				}
			}

			return array(
				'search'  => $search,
				'replace' => $replace,
			);
		}

		/**
		 * Converts currently selected timezone into string which can be passed to MySQL server.
		 *
		 * @since 5.2.1
		 */
		public static function get_mysql_time_zone(): string {
			$timezone_string = \wp_timezone();

			$date = new \DateTime( 'now', $timezone_string );

			// create a new date offset by the timezone offset.
			// gets the interval as hours & minutes.
			$offset      = $timezone_string->getOffset( $date ) . ' seconds';
			$date_offset = clone $date;
			$date_offset->sub( \DateInterval::createFromDateString( $offset ) );

			$interval = $date_offset->diff( $date );
			$offset   = $interval->format( '%R%H:%I' );

			return $offset;
		}

		/**
		 * Normalizes the given query string before storing it in DB.
		 *
		 * @param string $query - The query string to be normalized.
		 *
		 * @since 5.2.1
		 */
		public static function normalize_query( string $query ): string {
			if ( false !== \strpos( $query, '___QWE_USER_ROLE_QWE___' ) ) {
				$re = '/(___QWE_USER_ROLE_QWE___) (\!?\=)+ (\^GGG\^.*?\^GGG\^)/m';

				preg_match_all( $re, $query, $matches, PREG_SET_ORDER, 0 );

				if ( ! empty( $matches ) ) {
					foreach ( $matches as $match ) {
						$new_string = $match[3] . ( ( '!=' === $match[2] ) ? ' not in ' : ' in ' ) . '(' . $match[1] . ')';
						$serch      = $match[0];
						$query      = \str_replace( $serch, $new_string, $query );
					}
				}
			}
			if ( false !== \strpos( $query, '___QWE_AFFECTED_USER_ROLE_QWE___' ) ) {
				$re = '/(___QWE_AFFECTED_USER_ROLE_QWE___) (\!?\=)+ (\^GGG\^.*?\^GGG\^)/m';

				preg_match_all( $re, $query, $matches, PREG_SET_ORDER, 0 );

				if ( ! empty( $matches ) ) {
					foreach ( $matches as $match ) {
						$new_string = $match[3] . ( ( '!=' === $match[2] ) ? ' not in ' : ' in ' ) . '(' . $match[1] . ')';
						$serch      = $match[0];
						$query      = \str_replace( $serch, $new_string, $query );
					}
				}
			}

			return $query;
		}

		/**
		 * Returns default email subject.
		 *
		 * @since 5.1.1
		 */
		public static function get_default_email_subject(): string {
			return esc_html__( 'Notification {title} on website {site} triggered', 'wp-security-audit-log' );
		}

		/**
		 * Returns the default email body.
		 *
		 * @since 5.1.1
		 */
		public static function get_default_email_body(): string {
			$default_email_body = '<p>' .
			sprintf(
			/* translators: Title of the notification */
				esc_html__( 'Notification %s was triggered. Below are the notification details:', 'wp-security-audit-log' ),
				'<strong>{title}</strong>'
			)
			. '</p>';
			$default_email_body .= '<ul>';
			$default_email_body .= '<li>' . esc_html__( 'Website', 'wp-security-audit-log' ) . ': {site}</li>';
			$default_email_body .= '<li>' . esc_html__( 'Event ID', 'wp-security-audit-log' ) . ': {alert_id}</li>';
			$default_email_body .= '<li>' . esc_html__( 'Username', 'wp-security-audit-log' ) . ': {username}</li>';
			$default_email_body .= '<li>' . esc_html__( 'User first name', 'wp-security-audit-log' ) . ': {user_firstname}</li>';
			$default_email_body .= '<li>' . esc_html__( 'User last name', 'wp-security-audit-log' ) . ': {user_lastname}</li>';
			$default_email_body .= '<li>' . esc_html__( 'User role', 'wp-security-audit-log' ) . ': {user_role}</li>';
			$default_email_body .= '<li>' . esc_html__( 'User email', 'wp-security-audit-log' ) . ': {user_email}</li>';
			$default_email_body .= '<li>' . esc_html__( 'IP address', 'wp-security-audit-log' ) . ': {source_ip}</li>';
			$default_email_body .= '<li>' . esc_html__( 'Object', 'wp-security-audit-log' ) . ': {object}</li>';
			$default_email_body .= '<li>' . esc_html__( 'Event Type', 'wp-security-audit-log' ) . ': {event_type}</li>';
			$default_email_body .= '<li>' . esc_html__( 'Event Message', 'wp-security-audit-log' ) . ': {message}</li>';
			$default_email_body .= '<li>' . esc_html__( 'Event generated on', 'wp-security-audit-log' ) . ': {date_time}</li>';
			$default_email_body .= '</ul>';
			$default_email_body .= '<p>' .
			sprintf(
			/* translators: Plugin webpage */
				esc_html__( 'Monitoring of WordPress and Email Notifications provided by %1$s%2$s%3$s', 'wp-security-audit-log' ),
				'<a href="https://melapress.com/wordpress-activity-log/?utm_source=plugin&utm_medium=link&utm_campaign=wsal">',
				esc_html__( 'WP Activity Log, WordPress most comprehensive audit trail plugin', 'wp-security-audit-log' ),
				'</a>'
			)
			. '.</p>';

			return $default_email_body;
		}

		/**
		 * Returns the default email body.
		 *
		 * @since 5.1.1
		 */
		public static function get_default_slack_body(): string {
			$default_slack_body = '' .
			sprintf(
			/* translators: Title of the notification */
				esc_html__( 'Notification %s was triggered. Below are the notification details:', 'wp-security-audit-log' ),
				'*{title}*'
			)
			. "\n";
			$default_slack_body .= "\n";
			$default_slack_body .= '-' . esc_html__( 'Website', 'wp-security-audit-log' ) . ': {site}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'Event ID', 'wp-security-audit-log' ) . ': {alert_id}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'Username', 'wp-security-audit-log' ) . ': {username}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'User first name', 'wp-security-audit-log' ) . ': {user_firstname}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'User last name', 'wp-security-audit-log' ) . ': {user_lastname}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'User role', 'wp-security-audit-log' ) . ': {user_role}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'User email', 'wp-security-audit-log' ) . ': {user_email}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'IP address', 'wp-security-audit-log' ) . ': {source_ip}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'Object', 'wp-security-audit-log' ) . ': {object}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'Event Type', 'wp-security-audit-log' ) . ': {event_type}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'Event Message', 'wp-security-audit-log' ) . ': {message}' . "\n";
			$default_slack_body .= '-' . esc_html__( 'Event generated on', 'wp-security-audit-log' ) . ': {date_time}' . "\n";

			return $default_slack_body;
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
		 * @since 4.3.0
		 */
		public static function get_correct_timestamp( $metadata, $legacy_date ) {
			if ( is_null( $legacy_date ) ) {
				$timestamp = current_time( 'U.u', true );

				$timestamp = \apply_filters( 'wsal_database_timestamp_value', $timestamp, $metadata );

				return array_key_exists( 'Timestamp', $metadata ) ? $metadata['Timestamp'] : current_time( 'U.u', true );
			}

			return floatval( $legacy_date );
		}

		/**
		 * Returns the email for the notification. Falls back to default global email if not set.
		 *
		 * @param int $alert_id - The ID of the alert.
		 * @param int $severity - The severity of the alert.
		 *
		 * @return string|false - The phone number for the notification or false if not set.
		 *
		 * @since 5.1.1
		 */
		public static function get_notification_email( $alert_id, $severity = null ) {
			$built_notifications = Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

			$mail    = false;
			$default = false;

			if ( null !== $severity && isset( $built_notifications[ 'event_' . $severity . '_notification_email_address' ] ) ) {
				$mail = isset( $built_notifications[ 'event_' . $severity . '_notification_email_address' ] ) ? $built_notifications[ 'event_' . $severity . '_notification_email_address' ] : false;
			} elseif ( isset( $built_notifications[ 'event_' . $alert_id . '_notification_email_address' ] ) ) {
				$mail = ( isset( $built_notifications[ 'event_' . $alert_id . '_notification_email_address' ] ) && ! empty( $built_notifications[ 'event_' . $alert_id . '_notification_email_address' ] ) ) ? $built_notifications[ 'event_' . $alert_id . '_notification_email_address' ] : false;
			} elseif ( false === $mail && Notifications::is_default_mail_set() ) {
				$mail    = Notifications::get_default_mail();
				$default = true;
			}

			// There is a chance that alert id to be part of sub-alert ids - lets try to find a parent if any exist.
			if ( false === $mail || $default ) {
				$search = $alert_id;
				$arr    = Notifications::$additional_events_to_store;
				$found  = \array_filter(
					$arr,
					function ( $v, $k ) use ( $search ) {
						return \in_array( $search, $v, true );
					},
					ARRAY_FILTER_USE_BOTH
				);

				$possible_to_search = \array_keys( $found );

				if ( ! empty( $possible_to_search ) && isset( $found[ $possible_to_search[0] ] ) ) {
					$alert_id = $possible_to_search[0];
					if ( isset( $built_notifications[ 'event_' . $alert_id . '_notification_email_address' ] ) ) {
						$mail = isset( $built_notifications[ 'event_' . $alert_id . '_notification_email_address' ] ) ? $built_notifications[ 'event_' . $alert_id . '_notification_email_address' ] : false;
					}
				}

				if ( false === $mail && Notifications::is_default_mail_set() ) {
					$mail    = Notifications::get_default_mail();
					$default = true;
				}
			}

			if ( false !== $mail ) {
				return $mail;
			}

			return false;
		}

		/**
		 * Returns the email for custom notification. Falls back to default global email if not set.
		 *
		 * @param array $notification - The custom notification data.
		 * @param int   $uid          - The user id (if present).
		 *
		 * @return string|false - The email for the notification or false if not set.
		 *
		 * @since 5.2.1
		 */
		public static function get_custom_notification_email( array $notification, int $uid ) {
			if ( isset( $notification['notification_email_user'] ) && true === (bool) $notification['notification_email_user'] && ! empty( $uid ) ) {
				$mail_user = \get_userdata( $uid )->user_email;
				if ( ! empty( $mail_user ) ) {
					$mail = $mail_user;
				}
			}

			$use_default = true;

			if ( ( isset( $notification['settings']['custom_notification_send_to_default_email'] ) && false === $notification['settings']['custom_notification_send_to_default_email'] ) || ! isset( $notification['settings']['custom_notification_send_to_default_email'] ) ) {
				$use_default = false;
			}

			if ( isset( $notification['notification_email'] ) && ! empty( $notification['notification_email'] ) && ! $use_default ) {

				$selected_mail = $notification['notification_email'];

				if ( isset( $mail ) && ! empty( $mail ) ) {
					$mail = $mail . ', ' . $selected_mail;
				} else {
					$mail = $selected_mail;
				}

				return $mail;
			}

			if ( Notifications::is_default_mail_set() && isset( $notification['settings']['custom_notification_send_to_default_email'] ) && $notification['settings']['custom_notification_send_to_default_email'] ) {
				$mail = Notifications::get_default_mail();

				return $mail;
			}

			if ( Notifications::is_default_mail_set() && ! isset( $notification['settings']['custom_notification_send_to_default_email'] ) ) {
				$mail = Notifications::get_default_mail();

				return $mail;
			}

			if ( isset( $mail ) && ! empty( $mail ) ) {
				return $mail;
			}

			return false;
		}

		/**
		 * Returns the BCC email for custom notification.
		 *
		 * @param array $notification - The custom notification data.
		 *
		 * @return string|false - The BCC email for the notification or false if not set.
		 *
		 * @since 5.2.2
		 */
		public static function get_custom_notification_email_bcc( array $notification ) {
			if ( isset( $notification['notification_email_bcc'] ) ) {
				$mail = ( isset( $notification['notification_email_bcc'] ) && ! empty( $notification['notification_email_bcc'] ) ) ? $notification['notification_email_bcc'] : false;

				return $mail;
			}

			return false;
		}

		/**
		 * Returns the phone number for the notification. Falls back to default Twilio phone number if not set.
		 *
		 * @param int $alert_id - The ID of the alert.
		 * @param int $severity - The severity of the alert.
		 *
		 * @return string|false - The phone number for the notification or false if not set.
		 *
		 * @since 5.1.1
		 */
		public static function get_notification_phone_number( $alert_id, $severity = null ) {
			$built_notifications = (array) Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

			if ( null !== $severity && isset( $built_notifications[ 'event_' . $severity . '_notification_phone' ] ) || Notifications::is_default_twilio_set() ) {
				return isset( $built_notifications[ 'event_' . $severity . '_notification_phone' ] ) ? $built_notifications[ 'event_' . $severity . '_notification_phone' ] : Notifications::get_default_twilio();
			} elseif ( isset( $built_notifications[ 'event_' . $alert_id . '_notification_phone' ] ) || Notifications::is_default_twilio_set() ) {
				return isset( $built_notifications[ 'event_' . $alert_id . '_notification_phone' ] ) ? $built_notifications[ 'event_' . $alert_id . '_notification_phone' ] : Notifications::get_default_twilio();
			}

			// There is a chance that alert id to be part of sub-alert ids - lets try to find a parent if any exist.

			$search = $alert_id;
			$arr    = Notifications::$additional_events_to_store;
			$found  = \array_filter(
				$arr,
				function ( $v, $k ) use ( $search ) {
					return \in_array( $search, $v, true );
				},
				ARRAY_FILTER_USE_BOTH
			);

			$possible_to_search = \array_keys( $found );

			if ( ! empty( $possible_to_search ) && isset( $found[ $possible_to_search[0] ] ) ) {
				$alert_id = $possible_to_search[0];
				if ( isset( $built_notifications[ 'event_' . $alert_id . '_notification_phone' ] ) || Notifications::is_default_twilio_set() ) {
					return isset( $built_notifications[ 'event_' . $alert_id . '_notification_phone' ] ) ? $built_notifications[ 'event_' . $alert_id . '_notification_phone' ] : Notifications::get_default_twilio();
				}
			}

			return false;
		}

		/**
		 * Returns the channel name for the notification. Falls back to default Slack channel name if not set.
		 *
		 * @param int $alert_id - The ID of the alert.
		 * @param int $severity - The severity of the alert.
		 *
		 * @return string|false - The slack channel for the notification or false if not set.
		 *
		 * @since 5.3.4
		 */
		public static function get_notification_channel_name( $alert_id, $severity = null ) {
			$built_notifications = (array) Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

			if ( null !== $severity && isset( $built_notifications[ 'event_' . $severity . '_notification_slack' ] ) || Notifications::is_default_slack_set() ) {
				return isset( $built_notifications[ 'event_' . $severity . '_notification_slack' ] ) ? $built_notifications[ 'event_' . $severity . '_notification_slack' ] : Notifications::get_default_slack();
			} elseif ( isset( $built_notifications[ 'event_' . $alert_id . '_notification_slack' ] ) || Notifications::is_default_slack_set() ) {
				return isset( $built_notifications[ 'event_' . $alert_id . '_notification_slack' ] ) ? $built_notifications[ 'event_' . $alert_id . '_notification_slack' ] : Notifications::get_default_slack();
			}

			// There is a chance that alert id to be part of sub-alert ids - lets try to find a parent if any exist.

			$search = $alert_id;
			$arr    = Notifications::$additional_events_to_store;
			$found  = \array_filter(
				$arr,
				function ( $v, $k ) use ( $search ) {
					return \in_array( $search, $v, true );
				},
				ARRAY_FILTER_USE_BOTH
			);

			$possible_to_search = \array_keys( $found );

			if ( ! empty( $possible_to_search ) && isset( $found[ $possible_to_search[0] ] ) ) {
				$alert_id = $possible_to_search[0];
				if ( isset( $built_notifications[ 'event_' . $alert_id . '_notification_slack' ] ) || Notifications::is_default_twilio_set() ) {
					return isset( $built_notifications[ 'event_' . $alert_id . '_notification_slack' ] ) ? $built_notifications[ 'event_' . $alert_id . '_notification_slack' ] : Notifications::get_default_slack();
				}
			}

			return false;
		}

		/**
		 * Returns the phone number for the custom notification. Falls back to default Twilio phone number if not set.
		 *
		 * @param array $notification - The custom notification data.
		 *
		 * @return string|false - The phone number for the notification or false if not set.
		 *
		 * @since 5.2.1
		 */
		public static function get_custom_notification_phone( array $notification ) {

			$use_default = true;

			if ( ( isset( $notification['settings']['custom_notification_send_to_default_phone'] ) && false === $notification['settings']['custom_notification_send_to_default_phone'] ) || ! isset( $notification['settings']['custom_notification_send_to_default_phone'] ) ) {
				$use_default = false;
			}

			if ( isset( $notification['notification_phone'] ) && ! empty( $notification['notification_phone'] ) && ! $use_default ) {
				$phone = $notification['notification_phone'];

				return $phone;
			}

			if ( Notifications::is_default_twilio_set() && isset( $notification['settings']['custom_notification_send_to_default_phone'] ) && $notification['settings']['custom_notification_send_to_default_phone'] ) {
				$phone = Notifications::get_default_twilio();

				return $phone;
			}

			if ( Notifications::is_default_twilio_set() && ! isset( $notification['settings']['custom_notification_send_to_default_phone'] ) ) {
				$phone = Notifications::get_default_twilio();

				return $phone;
			}

			return false;
		}

		/**
		 * Returns the slack channel for the custom notification. Falls back to default Slack channel if not set.
		 *
		 * @param array $notification - The custom notification data.
		 *
		 * @return string|false - The channel name for the notification or false if not set.
		 *
		 * @since 5.3.4
		 */
		public static function get_custom_notification_channel( array $notification ) {

			$use_default = true;

			if ( ( isset( $notification['settings']['custom_notification_send_to_default_slack'] ) && false === $notification['settings']['custom_notification_send_to_default_slack'] ) || ! isset( $notification['settings']['custom_notification_send_to_default_slack'] ) ) {
				$use_default = false;
			}

			if ( isset( $notification['notification_slack'] ) && ! empty( $notification['notification_slack'] ) && ! $use_default ) {
				$channel = $notification['notification_slack'];

				return $channel;
			}

			if ( Notifications::is_default_slack_set() && isset( $notification['settings']['custom_notification_send_to_default_slack'] ) && $notification['settings']['custom_notification_send_to_default_slack'] ) {
				$channel = Notifications::get_default_slack();

				return $channel;
			}

			if ( Notifications::is_default_slack_set() && ! isset( $notification['settings']['custom_notification_send_to_default_slack'] ) ) {
				$channel = Notifications::get_default_slack();

				return $channel;
			}

			return false;
		}
		// phpcs:disable
		/* @premium:end */
		// phpcs:enable

		/**
		 * Responsible for proper mapping between the event collected fields and the Query fields.
		 *
		 * @since 5.2.1
		 */
		public static function get_notifications_fields_name_mapping(): array {
			return array(
				'event_id'           => array(
					'interpolate' => '___QWE_EVENT_ID_QWE___',
					'type'        => 'integer',
					'label'       => esc_html__( 'Event ID', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'eventtype'          => array(
					'interpolate' => '___QWE_TYPE_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Type', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'severity'           => array(
					'interpolate' => '___QWE_SEVERITY_QWE___',
					'type'        => 'integer',
					'label'       => esc_html__( 'Severity', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'posttype'           => array(
					'interpolate' => '___QWE_POSTTYPE_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Post Type', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'poststatus'         => array(
					'interpolate' => '___QWE_POSTSTATUS_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Post Status', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'object'             => array(
					'interpolate' => '___QWE_OBJECT_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Object', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'roles'              => array(
					'interpolate' => '___QWE_USER_ROLE_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'User Role', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'currentuserroles'   => array(
					'interpolate' => '___QWE_USER_ROLE_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'User Role', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'username'           => array(
					'interpolate' => '___QWE_USER_NAME_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'User Name', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'postid'             => array(
					'interpolate' => '___QWE_POST_ID_QWE___',
					'type'        => 'integer',
					'label'       => esc_html__( 'Post ID', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'orderid'            => array(
					'interpolate' => '___QWE_ORDER_ID_QWE___',
					'type'        => 'integer',
					'label'       => esc_html__( 'Order ID', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'productid'          => array(
					'interpolate' => '___QWE_PRODUCT_ID_QWE___',
					'type'        => 'integer',
					'label'       => esc_html__( 'Product ID', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'couponid'           => array(
					'interpolate' => '___QWE_COUPON_ID_QWE___',
					'type'        => 'integer',
					'label'       => esc_html__( 'Coupon ID', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'productstatus'      => array(
					'interpolate' => '___QWE_PRODUCT_STATUS_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Product Status', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'sku'                => array(
					'interpolate' => '___QWE_SKU_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Product SKU', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'clientip'           => array(
					'interpolate' => '___QWE_SOURCE_IP_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Source IP', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'contains', 'not_contains', 'not_equal'",
				),
				'date'               => array(
					'interpolate' => '___QWE_DATE_QWE___',
					'type'        => 'date',
					'label'       => esc_html__( 'Date', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'less', 'greater', 'not_equal'",
				),
				'time'               => array(
					'interpolate' => '___QWE_TIME_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Time', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'less', 'greater'",
				),
				'siteid'             => array(
					'interpolate' => '___QWE_SITE_ID_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Site', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'custom_field_name'  => array(
					'interpolate' => '___QWE_CUSTOM_FIELD_NAME_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Custom User Field', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
				'affected_user_role' => array(
					'interpolate' => '___QWE_AFFECTED_USER_ROLE_QWE___',
					'type'        => 'string',
					'label'       => esc_html__( 'Affected User Role', 'wp-security-audit-log' ),
					'operators'   => "'equal', 'not_equal'",
				),
			);
		}

		/**
		 * Returns all the alert_ids that must be included in the daily report.
		 *
		 * @since 5.3.0
		 */
		private static function get_default_report_event_ids(): array {
			if ( null === self::$daily_report_events ) {
				self::$daily_report_events = array_merge( array( 1000, 1005, 1002, 1003, 2001, 2008, 2012, 2065, 4000, 4001, 4002, 4003, 4004, 4007, 4010, 4011, 5000, 5001, 5002, 5003, 5004, 6028, 6029, 6030, 7000, 7001, 7002, 7003, 7004, 7005, 2021 ), \array_keys( Alert_Manager::get_alerts_by_category( esc_html__( 'WordPress & System', 'wp-security-audit-log' ) ) ) );
			}

			return self::$daily_report_events;
		}

		/**
		 * Returns report email body.
		 *
		 * @param bool $test - Test report (Sends current date's report).
		 * @param bool $weekly - Is that weekly report or not.
		 *
		 * @since 5.2.2
		 */
		public static function get_report( $test = false, $weekly = false ): array {
			$date_format = Settings_Helper::get_date_format(); // Get date format.
			$date_obj    = new \DateTime();

			// Convert to WP timezone before getting midnight.
			$date_obj->setTimezone( \wp_timezone() );

			// Set time of the object to 00:00:00.
			$date_obj->setTime( 0, 0 );

			// Get the date in UNIX timestamp.
			$date_string = $date_obj->format( 'U' );

			$current_settings = Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

			if ( $weekly ) {
				$disable_if_empty = isset( $current_settings['weekly_send_empty_summary_emails'] ) ? ! (bool) $current_settings['weekly_send_empty_summary_emails'] : false; // Option to disable if no alerts found.
			} else {
				$disable_if_empty = isset( $current_settings['daily_send_empty_summary_emails'] ) ? ! (bool) $current_settings['daily_send_empty_summary_emails'] : false; // Option to disable if no alerts found.
			}

			if ( ! $test ) {
				if ( $weekly ) {
					$start = strtotime( '-7 day +1 second', $date_string ); // Get yesterday's starting timestamp.
				} else {
					$start = strtotime( '-1 day +1 second', $date_string ); // Get yesterday's starting timestamp.
				}
				$end = strtotime( '-1 second', $date_string ); // Get yesterday's ending timestamp.
			} else {
				// If test then set the start and end timestamps to today's date.
				$start = strtotime( '+1 second', $date_string );
				$end   = strtotime( '+1 day -1 second', $date_string );
			}

			if ( $test ) {
				$site_id = 0;
			} else {
				$site_id = WP_Helper::get_blog_id();
			}

			$query = array();
			// if we have a site ID then add it as condition.
			if ( $site_id ) {
				$query['AND'][] = array( ' site_id = %s ' => $site_id );
			}
			// add condition to check only alerts that are daily report events.
			$query['AND'][] = array( 'find_in_set( alert_id, %s ) > 0 ' => implode( ',', self::get_default_report_event_ids() ) );
			// from this time.
			$query['AND'][] = array( ' created_on >= %s ' => $start );
			// till this time.
			$query['AND'][] = array( ' created_on <= %s ' => $end ); // To the hour 23:59:59.

			$meta_table_name = Metadata_Entity::get_table_name();
			$join_clause     = array(
				$meta_table_name => array(
					'direction'   => 'LEFT',
					'join_fields' => array(
						array(
							'join_field_left'  => 'occurrence_id',
							'join_table_right' => Occurrences_Entity::get_table_name(),
							'join_field_right' => 'id',
						),
					),
				),
			);
			// order results by date and return the query.
			$meta_full_fields_array       = Metadata_Entity::prepare_full_select_statement();
			$occurrence_full_fields_array = Occurrences_Entity::prepare_full_select_statement();
			$events                       = Occurrences_Entity::build_query( array_merge( $meta_full_fields_array, $occurrence_full_fields_array ), $query, array( 'created_on' => 'ASC' ), array(), $join_clause );

			$events       = Occurrences_Entity::prepare_with_meta_data( $events );
			$total_events = count( $events );

			if ( ! $test && $disable_if_empty && empty( $events ) ) {
				return array();
			}

			$home_url = \home_url();
			$safe_url = str_replace( array( 'http://', 'https://' ), '', $home_url );

			// the date displayed in daily reports.
			$display_date    = \wp_date( $date_format, $start );
			$report_date     = \wp_date( 'Y-m-d', $start );
			$report_end_date = false;
			if ( $weekly ) {
				$report_end_date  = \wp_date( 'Y-m-d', $end );
				$display_end_date = \wp_date( $date_format, $end );
			}

			// Report object.
			$report            = array();
			$report['subject'] = 'Activity Log Highlight from ' . $safe_url . ' on ' . $display_date; // Email subject.
			if ( $weekly ) {
				$report['subject'] .= ' - ' . $display_end_date;
			}
			$report['body'] = Notification_Template::generate_report_body( $events, $display_date, $total_events, $report_date, $report_end_date ); // Email body.

			return $report;
		}

		/**
		 * Send notifications email.
		 *
		 * @param string $email_address - Email Address.
		 * @param string $subject       - Email subject.
		 * @param string $content       - Email content.
		 * @param int    $alert_id      - (Optional) Alert ID.
		 *
		 * @return bool
		 *
		 * @since 5.2.2
		 */
		public static function send_notification_email( $email_address, $subject, $content, $alert_id = 0 ) {
			if ( class_exists( '\WSAL\Helpers\Email_Helper' ) ) {
				// Get email addresses even when there is the Username.
				$email_address = Email_Helper::get_emails( $email_address );
				if ( WSAL_NOTIFICATIONS_DEBUG ) {
					error_log('WP Activity Log Notification'); // phpcs:ignore
					error_log('Email address: ' . $email_address); // phpcs:ignore
					error_log('Alert ID: ' . $alert_id); // phpcs:ignore
				}

				// Give variable a value.
				$result = false;

				// Get email template.
				$result = Email_Helper::send_email( $email_address, $subject, $content );
			}

			if ( WSAL_NOTIFICATIONS_DEBUG ) {
				error_log('Email success: ' . print_r($result, true)); // phpcs:ignore
			}

			return $result;
		}

		/**
		 * Returns no default email is set text.
		 *
		 * @since 5.3.0
		 */
		public static function no_default_email_is_set(): string {
			return '<span style="color:red">' . esc_html__( ' Currently no default email is set.', 'wp-security-audit-log' ) . '</span>';
		}

		/**
		 * Returns no default phone is set text.
		 *
		 * @since 5.3.0
		 */
		public static function no_default_phone_is_set(): string {
			return '<span style="color:red">' . esc_html__( ' Currently no default phone is set.', 'wp-security-audit-log' ) . '</span>';
		}

		/**
		 * Returns no default slack channel is set text.
		 *
		 * @since 5.3.4
		 */
		public static function no_default_slack_is_set(): string {
			return '<span style="color:red">' . esc_html__( ' Currently no default slack channel is set.', 'wp-security-audit-log' ) . '</span>';
		}

		/**
		 * Email settings array function.
		 *
		 * @param string $id            - The name of the id of the field.
		 * @param string $settings_name - The name of the setting to use.
		 * @param string $name          - The name (title) of the field.
		 *
		 * @since 5.3.0
		 */
		public static function email_settings_array( string $id, string $settings_name, string $name = '' ): array {
			$options = array(
				'id'            => $id,
				'type'          => 'text',
				'pattern'       => '([a-zA-Z0-9\._\%\+\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,20}[,]{0,}){0,}',
				'hint'          => esc_html__( 'Leave empty if you want to use default one. You can enter multiple email addresses separated by commas. Do not use a space in between the email addresses and commas. For example: support@melapress.com,info@melapress.com', 'wp-security-audit-log' ),
				'settings_name' => $settings_name,
			);
			if ( '' === $name ) {
				$name = esc_html__( 'Email address(es): ', 'wp-security-audit-log' );
			}

			$options['name'] = $name;

			return $options;
		}

		/**
		 * Phone settings default array function.
		 *
		 * @param string $id            - The name of the id of the field.
		 * @param string $settings_name - The name of the setting to use.
		 * @param string $name          - The name (title) of the field.
		 * @param string $hint          - The hint text to show under field.
		 *
		 * @since 5.3.0
		 */
		public static function phone_settings_array( string $id, string $settings_name, string $name = '', string $hint = '' ): array {
			$options = array(
				'id'            => $id,
				'type'          => 'text',
				'pattern'       => '\+\d+',
				'validate'      => 'tel',
				'title_attr'    => esc_html__( 'Please use the following format: +16175551212', 'wp-security-audit-log' ),
				'max_chars'     => 20,
				'placeholder'   => esc_html__( '+16175551212', 'wp-security-audit-log' ),
				'hint'          => ( empty( $hint ) ) ? esc_html__( 'Leave empty if you want to use default one. Format you must use is: +16175551212', 'wp-security-audit-log' ) : $hint,
				'settings_name' => $settings_name,
			);
			if ( '' === $name ) {
				$name = esc_html__( 'Phone number: ', 'wp-security-audit-log' );
			}

			$options['name'] = $name;

			return $options;
		}

		/**
		 * Returns the default phone settings error array.
		 *
		 * @param string $id            - The name of the id of the field.
		 * @param string $settings_name - The name of the setting to use.
		 *
		 * @since 5.3.0
		 */
		public static function phone_settings_error_array( string $id, string $settings_name ): array {
			$options = array(
				'name'          => esc_html__( 'Phone number: ', 'wp-security-audit-log' ),
				'id'            => $id,
				'type'          => 'error_text',
				'text'          => '<span>' . esc_html__( 'In order to send notifications via SMS messages please configure the Twilio integration in the ', 'wp-security-audit-log' ) . '<a class="inner_links" href="#" data-section="twilio-notification-settings" data-url="wsal-options-tab-notification-settings">' . esc_html__( 'settings.', 'wp-security-audit-log' ) . ' </a></span>',
				'settings_name' => $settings_name,
			);

			return $options;
		}


		/**
		 * Slack settings default array function.
		 *
		 * @param string $id            - The name of the id of the field.
		 * @param string $settings_name - The name of the setting to use.
		 * @param string $name          - The name (title) of the field.
		 * @param string $hint          - The hint text to show under field.
		 *
		 * @since 5.3.4
		 */
		public static function slack_settings_array( string $id, string $settings_name, string $name = '', string $hint = '' ): array {
			$options = array(
				'id'            => $id,
				'type'          => 'text',
				'max_chars'     => 80,
				'placeholder'   => esc_html__( 'WSAL notifications', 'wp-security-audit-log' ),
				'hint'          => ( empty( $hint ) ) ? esc_html__( 'Leave empty if you want to use default one.', 'wp-security-audit-log' ) : $hint,
				'settings_name' => $settings_name,
			);
			if ( '' === $name ) {
				$name = esc_html__( 'Slack channel: ', 'wp-security-audit-log' );
			}

			$options['name'] = $name;

			return $options;
		}

		/**
		 * Returns the default slack settings error array.
		 *
		 * @param string $id            - The name of the id of the field.
		 * @param string $settings_name - The name of the setting to use.
		 *
		 * @since 5.3.4
		 */
		public static function slack_settings_error_array( string $id, string $settings_name ): array {
			$options = array(
				'name'          => esc_html__( 'Slack channel: ', 'wp-security-audit-log' ),
				'id'            => $id,
				'type'          => 'error_text',
				'text'          => '<span>' . esc_html__( 'In order to send notifications via Slack messages please configure the Slack integration in the ', 'wp-security-audit-log' ) . '<a class="inner_links" href="#" data-section="slack-notification-settings" data-url="wsal-options-tab-notification-settings">' . esc_html__( 'settings.', 'wp-security-audit-log' ) . ' </a></span>',
				'settings_name' => $settings_name,
			);

			return $options;
		}

		/**
		 * Default channel hint for email, phone and slack.
		 *
		 * @return string
		 *
		 * @since 5.4.2
		 */
		public static function default_hint_channels_set() {

			$defaults = '';
			if ( Notifications::is_default_mail_set() ) {
				return '';
				$current_default_mail = Notifications::get_default_mail();
				$defaults            .= esc_html__( ' Currently default email is set to: ', 'wp-security-audit-log' ) . $current_default_mail;
			} else {
				$defaults .= self::no_default_email_is_set();
			}

			if ( Notifications::is_default_twilio_set() ) {
				$current_default_twilio = Notifications::get_default_twilio();
				$defaults              .= esc_html__( ' Currently default phone is set to: ', 'wp-security-audit-log' ) . $current_default_twilio;
			} else {
				$defaults .= self::no_default_phone_is_set();
			}

			if ( Notifications::is_default_slack_set() ) {
				$current_default_twilio = Notifications::get_default_slack();
				$defaults              .= esc_html__( ' Currently default slack channel is set to: ', 'wp-security-audit-log' ) . $current_default_twilio;
			} else {
				$defaults .= self::no_default_slack_is_set();
			}

			return \esc_html__( 'You can set default email / phone for all notifications in ', 'wp-security-audit-log' ) . '<a class="inner_links" href="#" data-section="notification-default-settings" data-url="wsal-options-tab-notification-settings">' . \esc_html__( 'settings', 'wp-security-audit-log' ) . '</a>, ' . \esc_html__( 'or check this and specify ones for this event', 'wp-security-audit-log' ) . $defaults;
		}
	}
}
