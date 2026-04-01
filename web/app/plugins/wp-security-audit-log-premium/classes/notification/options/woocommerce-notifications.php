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

$built_in_notifications = Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

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
		'title'         => esc_html__( 'WooCommerce changes', 'wp-security-audit-log' ),
		'id'            => 'user-notification-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

/**
 * Woocommerce Notifications settings start
 */
Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'WooCommerce notifications', 'wp-security-audit-log' ),
		'id'            => 'users-activity-notification-settings',
		'type'          => 'header',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

/**
 * Notification '9000', '9001', '9003', '9004', '9005', '9006', '9008', '9009', '9010', '9011', '9012', '9013', '9014', '9015', '9072', '9073', '9077', '9007', '9016', '9017', '9018', '9019', '9020', '9021', '9022', '9023', '9024', '9025', '9026', '9042', '9043', '9044', '9045', '9046', '9047', '9048', '9049', '9050', '9051' start
 */
Settings_Builder::build_option(
	array(
		'name'          => Notifications::get_notification_titles()[9000],
		'id'            => 'notification_event_9000_notification',
		'toggle'        => '#notification_event_9000-items',
		'type'          => 'checkbox',
		'pre_text'      => esc_html__( 'All product changes', 'wp-security-audit-log' ),
		'default'       => false,
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

?>
	<div id="notification_event_9000-items">
		<?php

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
				'id'            => 'notification_event_9000_notification_custom_message',
				'toggle'        => '#notification_event_9000_notification_email_address-item, #notification_event_9000_notification_phone-item, #notification_event_9000_notification_slack-item',
				'type'          => 'checkbox',
				'hint'          => Notification_Helper::default_hint_channels_set(),
				'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			Notification_Helper::email_settings_array( 'notification_event_9000_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);

		if ( Twilio::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::phone_settings_array( 'notification_event_9000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::phone_settings_error_array( 'notification_event_9000_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}

		if ( Slack::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::slack_settings_array( 'notification_event_9000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::slack_settings_error_array( 'notification_event_9000_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}
		?>
	</div>
<?php
	/**
	 * Notification '9000', '9001', '9003', '9004', '9005', '9006', '9008', '9009', '9010', '9011', '9012', '9013', '9014', '9015', '9072', '9073', '9077', '9007', '9016', '9017', '9018', '9019', '9020', '9021', '9022', '9023', '9024', '9025', '9026', '9042', '9043', '9044', '9045', '9046', '9047', '9048', '9049', '9050', '9051' end
	 */

	/**
	 * Notification '9027', '9028', '9029', '9030', '9031', '9032', '9033', '9034', '9074', '9075', '9076', '9159' start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[9027],
			'id'            => 'notification_event_9027_notification',
			'toggle'        => '#notification_event_9027-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'All store changes', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
	<div id="notification_event_9027-items">
	<?php

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
				'id'            => 'notification_event_9027_notification_custom_message',
				'toggle'        => '#notification_event_9027_notification_email_address-item, #notification_event_9027_notification_phone-item, #notification_event_9027_notification_slack-item',
				'type'          => 'checkbox',
				'hint'          => Notification_Helper::default_hint_channels_set(),
				'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			Notification_Helper::email_settings_array( 'notification_event_9027_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);

		if ( Twilio::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::phone_settings_array( 'notification_event_9027_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::phone_settings_error_array( 'notification_event_9027_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}

		if ( Slack::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::slack_settings_array( 'notification_event_9027_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::slack_settings_error_array( 'notification_event_9027_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}
		?>
	</div>
	<?php
	/**
	 * Notification '9027', '9028', '9029', '9030', '9031', '9032', '9033', '9034', '9074', '9075', '9076', '9159' end
	 */

	/**
	 * Notification '9063', '9064', '9065', '9066', '9067', '9068', '9069', '9070', '9071' start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[9063],
			'id'            => 'notification_event_9063_notification',
			'toggle'        => '#notification_event_9063-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'All coupon changes', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>

	<div id="notification_event_9063-items">
		<?php

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
				'id'            => 'notification_event_9063_notification_custom_message',
				'toggle'        => '#notification_event_9063_notification_email_address-item, #notification_event_9063_notification_phone-item, #notification_event_9063_notification_slack-item',
				'type'          => 'checkbox',
				'hint'          => Notification_Helper::default_hint_channels_set(),
				'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
				'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			Notification_Helper::email_settings_array( 'notification_event_9063_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
		);

		if ( Twilio::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::phone_settings_array( 'notification_event_9063_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::phone_settings_error_array( 'notification_event_9063_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}

		if ( Slack::is_set() ) {

			Settings_Builder::build_option(
				Notification_Helper::slack_settings_array( 'notification_event_9063_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		} else {
			Settings_Builder::build_option(
				Notification_Helper::slack_settings_error_array( 'notification_event_9063_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);
		}
		?>
	</div>
	<?php
	/**
	 * Notification '9063', '9064', '9065', '9066', '9067', '9068', '9069', '9070', '9071' end
	 */

	/**
	 * Notification '9035', '9036', '9037', '9038', '9039', '9040', '9041' start
	 */
	Settings_Builder::build_option(
		array(
			'name'          => Notifications::get_notification_titles()[9035],
			'id'            => 'notification_event_9035_notification',
			'toggle'        => '#notification_event_9035-items',
			'type'          => 'checkbox',
			'pre_text'      => esc_html__( 'All orders changes', 'wp-security-audit-log' ),
			'default'       => false,
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	?>
	<div id="notification_event_9035-items">
			<?php

			Settings_Builder::build_option(
				array(
					'name'          => esc_html__( 'Use different email address / SMS number / Slack channel', 'wp-security-audit-log' ),
					'id'            => 'notification_event_9035_notification_custom_message',
					'toggle'        => '#notification_event_9035_notification_email_address-item, #notification_event_9035_notification_phone-item, #notification_event_9035_notification_slack-item',
					'type'          => 'checkbox',
					'hint'          => Notification_Helper::default_hint_channels_set(),
					'default'       => ( Notifications::is_default_mail_set() || Notifications::is_default_twilio_set() ) ? false : true,
					'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
				)
			);

			Settings_Builder::build_option(
				Notification_Helper::email_settings_array( 'notification_event_9035_notification_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
			);

			if ( Twilio::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::phone_settings_array( 'notification_event_9035_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::phone_settings_error_array( 'notification_event_9035_notification_phone', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}

			if ( Slack::is_set() ) {

				Settings_Builder::build_option(
					Notification_Helper::slack_settings_array( 'notification_event_9035_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			} else {
				Settings_Builder::build_option(
					Notification_Helper::slack_settings_error_array( 'notification_event_9035_notification_slack', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
				);
			}
			?>
	</div>
	<?php
	/**
	 * Notification '9035', '9036', '9037', '9038', '9039', '9040', '9041' end
	 */
	/**
	 * Woocommerce Notifications settings end
	 */

// phpcs:disable
/* @premium:start */
// phpcs:enable