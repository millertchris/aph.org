<?php
/**
 * Build-in notification settings of the plugin
 *
 * @package wsal
 *
 * @since 5.2.0
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
		'title'         => esc_html__( 'Content notfications', 'wp-security-audit-log' ),
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
			esc_html__( 'This section includes built-in notifications for content changes, such as new published content, content is changed, or a post is deleted. Simply enable the notifications you’d like to receive, and the plugin will alert you by email whenever these actions occur.', 'wp-security-audit-log' ) . '</p>' .

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
 * Content Notifications settings start
 */
Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Content notifications', 'wp-security-audit-log' ),
		'id'            => 'users-activity-notification-settings',
		'type'          => 'header',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

/**
 * Notification 2001 start
 */
Settings_Builder::build_option(
	array(
		'name'          => Notifications::get_notification_titles()[2001],
		'id'            => 'notification_event_2001_notification',
		'toggle'        => '#notification_event_2001-items',
		'type'          => 'checkbox',
		'pre_text'      => esc_html__( 'Event ID 2001', 'wp-security-audit-log' ),
		'default'       => false,
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

?>
<div id="notification_event_2001-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_2001_notification_custom_message',
			'toggle'        => '#notification_event_2001_notification_email_address-item, #notification_event_2001_notification_phone-item, #notification_event_2001_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_2001_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_2001_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_2001_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_2001_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_2001_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 2001 end
	 */

	/**
	 * Notification 2065 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[2065],
			'id'            => 'notification_event_2065_notification',
			'toggle'        => '#notification_event_2065-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 2065', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_2065-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_2065_notification_custom_message',
			'toggle'        => '#notification_event_2065_notification_email_address-item, #notification_event_2065_notification_phone-item, #notification_event_2065_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_2065_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_2065_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_2065_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_2065_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_2065_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 2065 end
	 */

	/**
	 * Notification 2008 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[2008],
			'id'            => 'notification_event_2008_notification',
			'toggle'        => '#notification_event_2008-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 2008', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_2008-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_2008_notification_custom_message',
			'toggle'        => '#notification_event_2008_notification_email_address-item, #notification_event_2008_notification_phone-item, #notification_event_2008_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_2008_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_2008_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_2008_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_2008_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_2008_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 2008 end
	 */
	/**
	 * Notification 2012 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[2012],
			'id'            => 'notification_event_2012_notification',
			'toggle'        => '#notification_event_2012-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 2012', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_2012-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_2012_notification_custom_message',
			'toggle'        => '#notification_event_2012_notification_email_address-item, #notification_event_2012_notification_phone-item, #notification_event_2012_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_2012_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_2012_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_2012_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_2012_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_2012_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 2012 end
	 */

	/**
	 * Notification '2002', '2016', '2017', '2019', '2021', '2025', '2027', '2047', '2048', '2049', '2050', '2053', '2054', '2055', '2062', '2086', '2119', '2120', '2131', '2132' start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[2002],
			'id'            => 'notification_event_2002_notification',
			'toggle'        => '#notification_event_2002-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Other content changes', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_2002-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_2002_notification_custom_message',
			'toggle'        => '#notification_event_2002_notification_email_address-item, #notification_event_2002_notification_phone-item, #notification_event_2002_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_2002_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_2002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_2002_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_2002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_2002_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification '2002', '2016', '2017', '2019', '2021', '2025', '2027', '2047', '2048', '2049', '2050', '2053', '2054', '2055', '2062', '2086', '2119', '2120', '2131', '2132' end
	 */
	/**
	 * Content Notifications settings end
	 */
// phpcs:disable
/* @premium:end */
// phpcs:enable