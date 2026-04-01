<?php
/**
 * Build-in notification settings of the plugin
 *
 * @package wsal
 *
 * @since 5.1.1
 */

use WSAL\Views\Notifications;
use WSAL\Controllers\Constants;
use WSAL\Controllers\Slack\Slack;
use WSAL\Helpers\Settings_Helper;
use WSAL\Controllers\Twilio\Twilio;
use WSAL\Helpers\Settings\Settings_Builder;
use WSAL\Extensions\Helpers\Notification_Helper;

$built_in_notifications = (array) Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

$defaults = '';
if ( Notifications::is_default_mail_set() ) {
	$current_default_mail = Notifications::get_default_mail();
	$defaults            .= esc_html__( ' Currently default email is set to: ', 'wp-security-audit-log' ) . $current_default_mail;
} else {
	$defaults .= Notification_Helper::no_default_email_is_set();
}

if ( Notifications::is_default_twilio_set() ) {
	$current_default_twilio = Notifications::get_default_twilio();
	$defaults              .= esc_html__( ' Currently default phone is set to: ', 'wp-security-audit-log' ) . $current_default_twilio;
} else {
	$defaults .= Notification_Helper::no_default_phone_is_set();
}

if ( Notifications::is_default_slack_set() ) {
	$current_default_twilio = Notifications::get_default_slack();
	$defaults              .= esc_html__( ' Currently default slack channel is set to: ', 'wp-security-audit-log' ) . $current_default_twilio;
} else {
	$defaults .= Notification_Helper::no_default_slack_is_set();
}

$notifications = array();
foreach ( $built_in_notifications as $name => $value ) {
	$notifications[ 'notification_' . $name ] = $value;
}
unset( $built_in_notifications );

Settings_Builder::set_current_options( $notifications );

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Built-in WordPress notifications', 'wp-security-audit-log' ),
		'id'            => 'built-in-notification-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

// phpcs:disable
/* @premium:start */
// phpcs:enable



$url = \add_query_arg(
	array(
		'page' => Notifications::get_safe_view_name(),
	),
	\network_admin_url( 'admin.php' )
) . '#wsal-options-tab-custom-notifications';

Settings_Builder::build_option(
	array(
		'id'      => 'general-settings-tab',
		'type'    => 'html',
		'content' => '<p>' . \wp_sprintf(
			esc_html__( 'This section includes built-in notifications for key system-level changes on your website, such as plugin installations or updates, theme changes, and WordPress core updates. Simply enable the notifications you’d like to receive, and the plugin will alert you by email whenever those changes occur.', 'wp-security-audit-log' ) . '</p>' .

			// translators: %1$s, %3$s are a HTML tags. %2$s is the link title.
			'<p>' . esc_html__( 'If you want to set up a notification for something that you cannot find here, you can %1$s%2$s%3$s.', 'wp-security-audit-log' ) . '</p>',
			'<a href="javascript:void(0);" onclick="window.location.href=(\'' . \esc_url( $url ) . '\');  location.reload(); return false">',
			esc_html__( 'build your own custom notification criteria', 'wp-security-audit-log' ),
			'</a>',
			esc_html__( 'Generated & saved reports', 'wp-security-audit-log' ),
		),
	)
);

	/**
	 * Suspicious activity settings start
	 */
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Suspicious activity notifications', 'wp-security-audit-log' ),
			'id'            => 'suspicious-activity-notification-settings',
			'type'          => 'header',
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

		/**
		 * Notification 1002 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[1002],
				'id'            => 'notification_event_1002_notification',
				'toggle'        => '#notification_event_1002-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 1002', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_1002-items">
			<?php

				Settings_Builder::build_option(
					array(
						'name'          => esc_html__( 'Notify if the failed WordPress logins for a WordPress user exceed', 'wp-security-audit-log' ),
						'id'            => 'notification_event_1002_failed_more_than',
						'type'          => 'number',
						'min'           => 2,
						'max'           => 30,
						'default'       => (int) 10,
						'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
					)
				);

				Settings_Builder::build_option(
					array(
						'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
						'id'            => 'notification_event_1002_notification_custom_message',
						'toggle'        => '#notification_event_1002_notification_email_address-item, #notification_event_1002_notification_phone-item, #notification_event_1002_notification_slack-item',
						'type'          => 'checkbox',
						'hint'          => Notification_Helper::default_hint_channels_set(),
						'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
						'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
					)
				);

				Settings_Builder::build_option(
					Notification_Helper::email_settings_array( 'notification_event_1002_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);

				if ( Twilio::is_set() ) {

					Settings_Builder::build_option(
						Notification_Helper::phone_settings_array( 'notification_event_1002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				} else {
					Settings_Builder::build_option(
						Notification_Helper::phone_settings_error_array( 'notification_event_1002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				}

				if ( Slack::is_set() ) {

					Settings_Builder::build_option(
						Notification_Helper::slack_settings_array( 'notification_event_1002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				} else {
					Settings_Builder::build_option(
						Notification_Helper::slack_settings_error_array( 'notification_event_1002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				}
				?>
		</div>
		<?php
		/**
		 * Notification 1002 end
		 */

		/**
		 * Notification 1003 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[1003],
				'id'            => 'notification_event_1003_notification',
				'toggle'        => '#notification_event_1003-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 1003', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_1003-items">
			<?php

			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Notify if the failed WordPress logins of non existing users exceed', 'wp-security-audit-log' ),
					'id'            => 'notification_event_1003_failed_more_than',
					'type'          => 'number',
					'min'           => 2,
					'max'           => 30,
					'default'       => (int) 10,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_1003_notification_custom_message',
					'toggle'        => '#notification_event_1003_notification_email_address-item, #notification_event_1003_notification_phone-item, #notification_event_1003_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_1003_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_1003_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_1003_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_1003_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_1003_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
	<?php
		/**
		 * Notification 1003 end
		 */

	/**
	 * Suspicious activity settings end
	 */

	/**
	 * WordPress Install Changes settings start
	 */
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'WordPress install changes notifications', 'wp-security-audit-log' ),
			'id'            => 'suspicious-activity-notification-settings',
			'type'          => 'header',
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

		/**
		 * Notification 6004 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[6004],
				'id'            => 'notification_event_6004_notification',
				'toggle'        => '#notification_event_6004-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 6004', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_6004-items">
			<?php

				Settings_Builder::build_option(
					array(
						'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
						'id'            => 'notification_event_6004_notification_custom_message',
						'toggle'        => '#notification_event_6004_notification_email_address-item, #notification_event_6004_notification_phone-item, #notification_event_6004_notification_slack-item',
						'type'          => 'checkbox',
						'hint'          => Notification_Helper::default_hint_channels_set(),
						'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
						'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
					)
				);

				Settings_Builder::build_option(
					Notification_Helper::email_settings_array( 'notification_event_6004_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);

				if ( Twilio::is_set() ) {

					Settings_Builder::build_option(
						Notification_Helper::phone_settings_array( 'notification_event_6004_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				} else {
					Settings_Builder::build_option(
						Notification_Helper::phone_settings_error_array( 'notification_event_6004_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				}

				if ( Slack::is_set() ) {

					Settings_Builder::build_option(
						Notification_Helper::slack_settings_array( 'notification_event_6004_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				} else {
					Settings_Builder::build_option(
						Notification_Helper::slack_settings_error_array( 'notification_event_6004_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				}
				?>
		</div>
	<?php
		/**
		 * Notification 6004 end
		 */

	/**
	 * WordPress Install Changes settings end
	 */

	/**
	 * Plugin Changes Notifications settings start
	 */
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Plugin changes notifications', 'wp-security-audit-log' ),
			'id'            => 'plugin-changes-notification-settings',
			'type'          => 'header',
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

		/**
		 * Notification 5000 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5000],
				'id'            => 'notification_event_5000_notification',
				'toggle'        => '#notification_event_5000-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5000', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5000-items">
			<?php

				Settings_Builder::build_option(
					array(
						'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
						'id'            => 'notification_event_5000_notification_custom_message',
						'toggle'        => '#notification_event_5000_notification_email_address-item, #notification_event_5000_notification_phone-item, #notification_event_5000_notification_slack-item',
						'type'          => 'checkbox',
						'hint'          => Notification_Helper::default_hint_channels_set(),
						'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
						'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
					)
				);

				Settings_Builder::build_option(
					Notification_Helper::email_settings_array( 'notification_event_5000_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);

				if ( Twilio::is_set() ) {

					Settings_Builder::build_option(
						Notification_Helper::phone_settings_array( 'notification_event_5000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				} else {
					Settings_Builder::build_option(
						Notification_Helper::phone_settings_error_array( 'notification_event_5000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				}

				if ( Slack::is_set() ) {

					Settings_Builder::build_option(
						Notification_Helper::slack_settings_array( 'notification_event_5000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				} else {
					Settings_Builder::build_option(
						Notification_Helper::slack_settings_error_array( 'notification_event_5000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
					);
				}
				?>
		</div>
		<?php
		/**
		 * Notification 5000 end
		 */

		/**
		 * Notification 5001 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5001],
				'id'            => 'notification_event_5001_notification',
				'toggle'        => '#notification_event_5001-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5001', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5001-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5001_notification_custom_message',
					'toggle'        => '#notification_event_5001_notification_email_address-item, #notification_event_5001_notification_phone-item, #notification_event_5001_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5001_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5001_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5001_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5001_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5001_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 5001 end
		 */

		/**
		 * Notification 2051 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[2051],
				'id'            => 'notification_event_2051_notification',
				'toggle'        => '#notification_event_2051-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 2051', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_2051-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_2051_notification_custom_message',
					'toggle'        => '#notification_event_2051_notification_email_address-item, #notification_event_2051_notification_phone-item, #notification_event_2051_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_2051_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_2051_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_2051_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_2051_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_2051_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 2051 end
		 */

		/**
		 * Notification 5002 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5002],
				'id'            => 'notification_event_5002_notification',
				'toggle'        => '#notification_event_5002-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5002', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5002-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5002_notification_custom_message',
					'toggle'        => '#notification_event_5002_notification_email_address-item, #notification_event_5002_notification_phone-item, #notification_event_5002_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5002_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 5002 end
		 */

		/**
		 * Notification 5003 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5003],
				'id'            => 'notification_event_5003_notification',
				'toggle'        => '#notification_event_5003-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5003', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5003-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5003_notification_custom_message',
					'toggle'        => '#notification_event_5003_notification_email_address-item, #notification_event_5003_notification_phone-item, #notification_event_5003_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5003_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5003_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5003_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5003_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5003_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 5003 end
		 */

		/**
		 * Notification 5004 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5004],
				'id'            => 'notification_event_5004_notification',
				'toggle'        => '#notification_event_5004-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5004', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5004-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5004_notification_custom_message',
					'toggle'        => '#notification_event_5004_notification_email_address-item, #notification_event_5004_notification_phone-item, #notification_event_5004_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5004_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5004_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5004_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5004_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5004_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
	<?php
		/**
		 * Notification 5004 end
		 */
	/**
	 * Plugin Changes Notifications settings end
	 */

	/**
	 * Theme Changes Notifications settings start
	 */
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Theme changes notifications', 'wp-security-audit-log' ),
			'id'            => 'theme-changes-notification-settings',
			'type'          => 'header',
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

		/**
		 * Notification 5005 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5005],
				'id'            => 'notification_event_5005_notification',
				'toggle'        => '#notification_event_5005-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5005', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5005-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5005_notification_custom_message',
					'toggle'        => '#notification_event_5005_notification_email_address-item, #notification_event_5005_notification_phone-item, #notification_event_5005_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5005_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5005_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5005_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5005_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5005_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 5005 end
		 */

		/**
		 * Notification 5006 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5006],
				'id'            => 'notification_event_5006_notification',
				'toggle'        => '#notification_event_5006-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5006', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5006-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5006_notification_custom_message',
					'toggle'        => '#notification_event_5006_notification_email_address-item, #notification_event_5006_notification_phone-item, #notification_event_5006_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5006_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5006_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5006_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5006_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5006_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 5006 end
		 */

		/**
		 * Notification 2046 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[2046],
				'id'            => 'notification_event_2046_notification',
				'toggle'        => '#notification_event_2046-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 2046', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_2046-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_2046_notification_custom_message',
					'toggle'        => '#notification_event_2046_notification_email_address-item, #notification_event_2046_notification_phone-item, #notification_event_2046_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_2046_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_2046_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_2046_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_3046_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_3046_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 2046 end
		 */

		/**
		 * Notification 5007 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5007],
				'id'            => 'notification_event_5007_notification',
				'toggle'        => '#notification_event_5007-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5007', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5007-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5007_notification_custom_message',
					'toggle'        => '#notification_event_5007_notification_email_address-item, #notification_event_5007_notification_phone-item, #notification_event_5007_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5007_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5007_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5007_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5007_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5007_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
		<?php
		/**
		 * Notification 5007 end
		 */

		/**
		 * Notification 5031 start
		 */
		Settings_Builder::build_option(
			array(
				'name'          => Notifications::get_notification_titles()[5031],
				'id'            => 'notification_event_5031_notification',
				'toggle'        => '#notification_event_5031-items',
				'type'          => 'checkbox',
				'pre_text'      => esc_html__( 'Event ID 5031', 'wp-security-audit-log' ),
				'default'       => false,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
		<div id="notification_event_5031-items">
			<?php
			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_5031_notification_custom_message',
					'toggle'        => '#notification_event_5031_notification_email_address-item, #notification_event_5031_notification_phone-item, #notification_event_5031_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_5031_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_5031_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_5031_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_5031_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_5031_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
		</div>
	<?php
		/**
		 * Notification 5031 end
		 */
	/**
	 * Theme Changes Notifications settings end
	 */

	/**
	 * Critical Notifications settings start
	 */
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Critical events notifications', 'wp-security-audit-log' ),
			'id'            => 'critical-notification-settings',
			'type'          => 'header',
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	/**
	 * Notification Critical start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[500],
			'id'            => 'notification_event_500_notification',
			'toggle'        => '#notification_event_500-items',
			'type'          => 'checkbox',
			'pre_text'      => Constants::get_constant_name( 500 ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
	<div id="notification_event_500-items">
		<?php
		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
				'id'            => 'notification_event_500_notification_custom_message',
				'toggle'        => '#notification_event_500_notification_email_address-item, #notification_event_500_notification_phone-item, #notification_event_500_notification_slack-item',
				'type'          => 'checkbox',
				'hint'          => Notification_Helper::default_hint_channels_set(),
				'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			Notification_Helper::email_settings_array( 'notification_event_500_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);

		if ( Twilio::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::phone_settings_array( 'notification_event_500_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::phone_settings_error_array( 'notification_event_500_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}

		if ( Slack::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::slack_settings_array( 'notification_event_500_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::slack_settings_error_array( 'notification_event_500_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}
		?>
	</div>
	<?php
		/**
		 * Notification Critical end
		 */

	/**
	 * Critical Notifications settings end
	 */

// phpcs:disable
/* @premium:end */
// phpcs:enable