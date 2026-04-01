<?php
/**
 * Build-in notification settings of the plugin
 *
 * @package wsal
 *
 * @since 5.1.1
 */

// phpcs:disable
/* @premium:start */
// phpcs:enable
use WSAL\Views\Notifications;
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
		'title'         => esc_html__( 'Users notfications', 'wp-security-audit-log' ),
		'id'            => 'user-notification-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'id'      => 'general-settings-tab',
		'type'    => 'html',
		'content' => '<p>' . \wp_sprintf(
			esc_html__( 'This section includes built-in notifications for important user-related activity and user profile changes, such as user logins, password changes, and other profile updates. Simply enable the notifications you’d like to receive, and the plugin will alert you by email whenever these actions occur.', 'wp-security-audit-log' ) . '</p>' .

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
 * User Activity Notifications settings start
 */
Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'User activity notifications', 'wp-security-audit-log' ),
		'id'            => 'users-activity-notification-settings',
		'type'          => 'header',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

/**
 * Notification 1000 start
 */
Settings_Builder::build_option(
	array(
		'name'          => Notifications::get_notification_titles()[1000],
		'id'            => 'notification_event_1000_notification',
		'toggle'        => '#notification_event_1000-items',
		'type'          => 'checkbox',
		'pre_text'      => esc_html__( 'Event ID 1000', 'wp-security-audit-log' ),
		'default'       => false,
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);
?>
<div id="notification_event_1000-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_1000_notification_custom_message',
			'toggle'        => '#notification_event_1000_notification_email_address-item, #notification_event_1000_notification_phone-item, #notification_event_1000_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_1000_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_1000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_1000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_1000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_1000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 1000 end
	 */

	/**
	 * Notification 4003 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4003],
			'id'            => 'notification_event_4003_notification',
			'toggle'        => '#notification_event_4003-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4003', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
<div id="notification_event_4003-items">
	<?php
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4003_notification_custom_message',
			'toggle'        => '#notification_event_4003_notification_email_address-item, #notification_event_4003_notification_phone-item, #notification_event_4003_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4003_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4003_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4003_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4003_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4003_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4003 end
	 */
	/**
	 * User Activity Notifications settings end
	 */

	/**
	 * User Profile Notifications settings start
	 */
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'User profiles notifications', 'wp-security-audit-log' ),
			'id'            => 'users-profile-notification-settings',
			'type'          => 'header',
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	/**
	* Notification 4005, 4006 start
	*/
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4005],
			'id'            => 'notification_event_4005_notification',
			'toggle'        => '#notification_event_4005-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4005, 4006', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
<div id="notification_event_4005-items">
	<?php
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4005_notification_custom_message',
			'toggle'        => '#notification_event_4005_notification_email_address-item, #notification_event_4005_notification_phone-item, #notification_event_4005_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4005_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4005_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4005_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4005_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4005_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4005, 4006 end
	 */

	/**
	 * Notification 4002 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4002],
			'id'            => 'notification_event_4002_notification',
			'toggle'        => '#notification_event_4002-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4002', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
<div id="notification_event_4002-items">
	<?php
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4002_notification_custom_message',
			'toggle'        => '#notification_event_4002_notification_email_address-item, #notification_event_4002_notification_phone-item, #notification_event_4002_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4002_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4002 end
	 */

	/**
	 * Notification 4004 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4004],
			'id'            => 'notification_event_4004_notification',
			'toggle'        => '#notification_event_4004-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4004', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
<div id="notification_event_4004-items">
	<?php
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4004_notification_custom_message',
			'toggle'        => '#notification_event_4004_notification_email_address-item, #notification_event_4004_notification_phone-item, #notification_event_4004_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4004_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4004_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4004_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4004_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4004_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4004 end
	 */

	/**
	 * Notification 4000, 4001, 4012 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4000],
			'id'            => 'notification_event_4000_notification',
			'toggle'        => '#notification_event_4000-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4000, 4001, 4012', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
<div id="notification_event_4000-items">
	<?php
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4000_notification_custom_message',
			'toggle'        => '#notification_event_4000_notification_email_address-item, #notification_event_4000_notification_phone-item, #notification_event_4000_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4000_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4000, 4001, 4012 end
	 */
	/**
	 * User Profile Notifications settings end
	 */
// phpcs:disable
/* @premium:start */
// phpcs:enable