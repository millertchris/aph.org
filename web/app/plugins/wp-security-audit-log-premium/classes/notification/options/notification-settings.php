<?php
/**
 * Notification Settings of the plugin
 *
 * @package wsal
 *
 * @since 5.1.1
 */

// phpcs:disable
/* @premium:start */
// phpcs:enable

use WSAL\Controllers\Slack\Slack;
use WSAL\Controllers\Twilio\Twilio;
use WSAL\Views\Notifications;
use WSAL\Helpers\Settings\Settings_Builder;
use WSAL\Extensions\Helpers\Notification_Helper;

Settings_Builder::set_current_options( Notifications::get_global_notifications_setting() );

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Notification settings', 'wp-security-audit-log' ),
		'id'            => 'notification-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Default email address, SMS number, and Slack channel', 'wp-security-audit-log' ),
		'id'            => 'notification-default-settings',
		'type'          => 'header',
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	Notification_Helper::email_settings_array( 'notification_default_email_address', Notifications::NOTIFICATIONS_SETTINGS_NAME, esc_html__( 'Default Email address(es): ', 'wp-security-audit-log' ) )
);

if ( Twilio::is_set() ) {

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Default phone number', 'wp-security-audit-log' ),
			'type'          => 'text',
			'pattern'       => '\+\d+',
			'validate'      => 'tel',
			'id'            => 'notification_default_phone',
			'hint'          => esc_html__( 'By default SMS messages will be sent to this number.', 'wp-security-audit-log' ),
			'title_attr'    => esc_html__( 'Please use the following format: +16175551212', 'wp-security-audit-log' ),
			'max_chars'     => 20,
			'placeholder'   => esc_html__( '+16175551212', 'wp-security-audit-log' ),
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);
} else {
	Settings_Builder::build_option(
		Notification_Helper::phone_settings_error_array( 'notification_default_phone', Notifications::NOTIFICATIONS_SETTINGS_NAME )
	);
}

if ( Slack::is_set() ) {

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Default channel name', 'wp-security-audit-log' ),
			'type'          => 'text',
			'id'            => 'notification_default_slack_channel',
			'max_chars'     => 80,
			'hint'          => esc_html__( 'By default Slack messages will be sent to this channel.', 'wp-security-audit-log' ),
			'placeholder'   => esc_html__( 'WSAL notifications', 'wp-security-audit-log' ),
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);
} else {
	Settings_Builder::build_option(
		Notification_Helper::slack_settings_error_array( 'notification_default_slack_channel', Notifications::NOTIFICATIONS_SETTINGS_NAME )
	);
}

// Twilio settings part start.
Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Twilio SMS account', 'wp-security-audit-log' ),
		'id'            => 'twilio-notification-settings',
		'type'          => 'header',
		'hint'          => esc_html__( 'Refer to the ', 'wp-security-audit-log' ) . '<a href="https://melapress.com/support/kb/wp-activity-log-configure-sms-message-notification/#utm_source=plugin&amp;utm_medium=link&amp;utm_campaign=wsal" rel="nofollow" target="_blank">' . esc_html__( 'Twilio integration documentation', 'wp-security-audit-log' ) . '</a> ' . esc_html__( 'for a complete guide on how to set up an account and configure the integration.', 'wp-security-audit-log' ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Account SID: ', 'wp-security-audit-log' ),
		'id'            => 'twilio_notification_account_sid',
		'type'          => 'text',
		'hint'          => esc_html__( 'Get your Account SID from the ', 'wp-security-audit-log' ) . '<a href="https://www.twilio.com/console" target="_blank">' . esc_html__( 'Twilio console', 'wp-security-audit-log' ) . '</a>',
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Auth token: ', 'wp-security-audit-log' ),
		'id'            => 'twilio_notification_auth_token',
		'type'          => 'text',
		'hint'          => esc_html__( 'Get your Auth token from the ', 'wp-security-audit-log' ) . '<a href="https://www.twilio.com/console" target="_blank">' . esc_html__( 'Twilio console', 'wp-security-audit-log' ) . '</a>',
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Twilio number / Alphanumeric ID: ', 'wp-security-audit-log' ),
		'id'            => 'twilio_notification_phone_number',
		'type'          => 'text',
		'hint'          => esc_html__( 'Specify a Twilio phone number including the country code (e.g. +16175551212) or a valid Alphanumeric ID (e.g. WSAL)', 'wp-security-audit-log' ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'id'            => 'twilio_notification_nonce',
		'type'          => 'hidden',
		'default'       => \wp_create_nonce( Twilio::NONCE_NAME ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'add_label'     => true,
		'id'            => 'twilio_notification_store_settings_ajax',
		'type'          => 'button',
		'default'       => esc_html__( 'Save Twilio settings', 'wp-security-audit-log' ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);
// Twilio settings part end.

// Slack settings part start.
Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Slack account', 'wp-security-audit-log' ),
		'id'            => 'slack-notification-settings',
		'type'          => 'header',
		'hint'          => esc_html__( 'Refer to the ', 'wp-security-audit-log' ) . '<a href="https://api.slack.com/quickstart" rel="nofollow" target="_blank">' . esc_html__( 'Slack integration documentation', 'wp-security-audit-log' ) . '</a> ' . esc_html__( 'for a complete guide on how to set up an account and configure the integration.', 'wp-security-audit-log' ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Bot token: ', 'wp-security-audit-log' ),
		'id'            => 'slack_notification_auth_token',
		'type'          => 'text',
		'hint'          => esc_html__( 'Get your bot token from the application from the "OAuth & Permissions" section', 'wp-security-audit-log' ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'id'            => 'slack_notification_nonce',
		'type'          => 'hidden',
		'default'       => \wp_create_nonce( Slack::NONCE_NAME ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'add_label'     => true,
		'id'            => 'slack_notification_store_settings_ajax',
		'type'          => 'button',
		'default'       => esc_html__( 'Save Slack settings', 'wp-security-audit-log' ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);
// Slack settings part end.

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Default templates', 'wp-security-audit-log' ),
		'id'            => 'default-templates-notification-settings',
		'type'          => 'header',
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

?>

<div class="wsal-section-title wsal-section-tabs header-settings-tabs">
	<a href="#main-nav-settings" class="active"><?php esc_html_e( 'Email template', 'wp-security-audit-log' ); ?></a>
	<a href="#top-nav-settings"><?php esc_html_e( 'SMS template', 'wp-security-audit-log' ); ?></a>
	<a href="#slack-nav-settings"><?php esc_html_e( 'Slack template', 'wp-security-audit-log' ); ?></a>
</div>


<div id="slack-nav-settings" class="top-main-nav-settings">
	<?php

	$mail_template_tags = '';

	foreach ( Notification_Helper::get_email_template_tags() as $tag_name => $desc ) {
		$mail_template_tags .= '<li>' . esc_html( $tag_name ) . ' — ' . esc_html( $desc ) . '</li>';
	}

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Body', 'wp-security-audit-log' ),
			'id'            => 'slack_notifications_body',
			'type'          => 'textarea',
			'default'       => Notification_Helper::get_default_slack_body(),
			'hint'          => '<b>' . esc_html__( 'Available template tags:', 'wp-security-audit-log' ) . '</b><ul>' . $mail_template_tags . '</ul>',
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'add_label'     => true,
			'id'            => 'test_slack_notification_settings_ajax',
			'type'          => 'button',
			'default'       => esc_html__( 'Send test slack', 'wp-security-audit-log' ),
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
</div><!-- #slack-nav-settings /-->


<div id="main-nav-settings" class="top-main-nav-settings">
	<?php
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Subject: ', 'wp-security-audit-log' ),
			'id'            => 'email_notifications_subject',
			'type'          => 'text',
			'default'       => Notification_Helper::get_default_email_subject(),
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	$mail_template_tags = '';

	foreach ( Notification_Helper::get_email_template_tags() as $tag_name => $desc ) {
		$mail_template_tags .= '<li>' . esc_html( $tag_name ) . ' — ' . esc_html( $desc ) . '</li>';
	}

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Body', 'wp-security-audit-log' ),
			'id'            => 'email_notifications_body',
			'type'          => 'editor',
			'default'       => Notification_Helper::get_default_email_body(),
			'hint'          => '<b>' . esc_html__( 'HTML is accepted. Available template tags:', 'wp-security-audit-log' ) . '</b><ul>' . $mail_template_tags . '</ul>',
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'add_label'     => true,
			'id'            => 'test_email_notification_settings_ajax',
			'type'          => 'button',
			'default'       => esc_html__( 'Send test email', 'wp-security-audit-log' ),
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
</div><!-- #main-nav-settings /-->

<div id="top-nav-settings" class="top-main-nav-settings">

<?php


	$sms_template_tags = '';

foreach ( Notification_Helper::get_sms_template_tags() as $tag_name => $desc ) {
	$sms_template_tags .= '<li>' . esc_html( $tag_name ) . ' — ' . esc_html( $desc ) . '</li>';
}

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Body', 'wp-security-audit-log' ),
			'id'            => 'sms_notifications_body',
			'type'          => 'textarea',
			'default'       => Notification_Helper::get_default_sms_body(),
			'hint'          => '<b><i>' . esc_html__( 'One SMS message is 160 characters long. If your message contains more than 160 characters you will receive and be charged for multiple SMS messages. Therefore always double check the length of the fields you are using when updating the template.', 'wp-security-audit-log' ) . '</b></i><p><b>' . esc_html__( 'Available template tags: :', 'wp-security-audit-log' ) . '</b><ul>' . $sms_template_tags . '</ul></p>',
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Shorten URLs with Bit.ly', 'wp-security-audit-log' ),
			'id'            => 'shorten_notification_urls',
			'toggle'        => '#notification_bitly_shorten_key-item',
			'type'          => 'checkbox',
			'default'       => false,
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Bit.ly Access Token: ', 'wp-security-audit-log' ),
			'id'            => 'notification_bitly_shorten_key',
			'type'          => 'text',
			'hint'          => /* Translators: Bit.ly documentation hyperlink */
						sprintf( esc_html__( 'The URL shortener works for URLs in the {message} variable and will not shorten the URL of the website in the variable {site}. Shorten all URLs in the message using the %s.', 'wp-security-audit-log' ), '<a href="https://dev.bitly.com/v4_documentation.html" target="_blank">' . esc_html__( 'Bit.ly URL Shortener API', 'wp-security-audit-log' ) . '</a>' ),
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'add_label'     => true,
			'id'            => 'test_sms_notification_settings_ajax',
			'type'          => 'button',
			'default'       => esc_html__( 'Send test sms', 'wp-security-audit-log' ),
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
</div><!-- #top-nav-settings /-->

<?php

// phpcs:disable
/* @premium:end */
// phpcs:enable