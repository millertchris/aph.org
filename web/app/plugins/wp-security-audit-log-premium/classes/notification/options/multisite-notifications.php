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
		'title'         => esc_html__( 'Multisite changes', 'wp-security-audit-log' ),
		'id'            => 'multisite-notification-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

/**
 * Multisite Notifications settings start
 */
Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Multisite notifications', 'wp-security-audit-log' ),
		'id'            => 'users-activity-notification-settings',
		'type'          => 'header',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

/**
 * Notification 4008 start
 */
Settings_Builder::build_option(
	array(
		'name'          => Notifications::get_notification_titles()[4008],
		'id'            => 'notification_event_4008_notification',
		'toggle'        => '#notification_event_4008-items',
		'type'          => 'checkbox',
		'pre_text'      => esc_html__( 'Event ID 4008', 'wp-security-audit-log' ),
		'default'       => false,
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

?>
<div id="notification_event_4008-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4008_notification_custom_message',
			'toggle'        => '#notification_event_4008_notification_email_address-item, #notification_event_4008_notification_phone-item, #notification_event_4008_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4008_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4008_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4008_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4008_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4008_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4008 end
	 */

	/**
	 * Notification 4009 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4009],
			'id'            => 'notification_event_4009_notification',
			'toggle'        => '#notification_event_4009-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4009', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_4009-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4009_notification_custom_message',
			'toggle'        => '#notification_event_4009_notification_email_address-item, #notification_event_4009_notification_phone-item, #notification_event_4009_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4009_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4009_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4009_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4009_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4009_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4009 end
	 */

	/**
	 * Notification '4010' start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4010],
			'id'            => 'notification_event_4010_notification',
			'toggle'        => '#notification_event_4010-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4010', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_4010-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4010_notification_custom_message',
			'toggle'        => '#notification_event_4010_notification_email_address-item, #notification_event_4010_notification_phone-item, #notification_event_4010_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4010_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4010_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4010_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4010_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4010_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification '4010' end
	 */

	/**
	 * Notification 4011 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[4011],
			'id'            => 'notification_event_4011_notification',
			'toggle'        => '#notification_event_4011-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 4011', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_4011-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_4011_notification_custom_message',
			'toggle'        => '#notification_event_4011_notification_email_address-item, #notification_event_4011_notification_phone-item, #notification_event_4011_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_4011_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_4011_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_4011_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_4011_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_4011_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 4011 end
	 */

	/**
	 * Notification '7000', '7001', '7002', '7003', '7004', '7005' start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[7000],
			'id'            => 'notification_event_7000_notification',
			'toggle'        => '#notification_event_7000-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Other site changes', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_7000-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_7000_notification_custom_message',
			'toggle'        => '#notification_event_7000_notification_email_address-item, #notification_event_7000_notification_phone-item, #notification_event_7000_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_7000_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_7000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_7000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_7000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_7000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification '7000', '7001', '7002', '7003', '7004', '7005' end
	 */
	/**
	 * Notification 5008 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[5008],
			'id'            => 'notification_event_5008_notification',
			'toggle'        => '#notification_event_5008-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 5008', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_5008-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_5008_notification_custom_message',
			'toggle'        => '#notification_event_5008_notification_email_address-item, #notification_event_5008_notification_phone-item, #notification_event_5008_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_5008_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_5008_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_5008_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_5008_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_5008_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 5008 end
	 */

	/**
	 * Notification 5009 start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[5009],
			'id'            => 'notification_event_5009_notification',
			'toggle'        => '#notification_event_5009-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'Event ID 5009', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
<div id="notification_event_5009-items">
	<?php

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
			'id'            => 'notification_event_5009_notification_custom_message',
			'toggle'        => '#notification_event_5009_notification_email_address-item, #notification_event_5009_notification_phone-item, #notification_event_5009_notification_slack-item',
			'type'          => 'checkbox',
			'hint'          => Notification_Helper::default_hint_channels_set(),
			'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'notification_event_5009_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
	);

	if ( Twilio::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::phone_settings_array( 'notification_event_5009_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::phone_settings_error_array( 'notification_event_5009_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}

	if ( Slack::is_set() ) {

		Settings_Builder::build_option(
			Notification_Helper::slack_settings_array( 'notification_event_5009_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	} else {
		Settings_Builder::build_option(
			Notification_Helper::slack_settings_error_array( 'notification_event_5009_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);
	}
	?>
</div>
	<?php
	/**
	 * Notification 5009 end
	 */

	/**
	 * Multisite Notifications settings end
	 */
// phpcs:disable
/* @premium:end */
// phpcs:enable