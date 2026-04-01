<?php
/**
 * Advanced settings of the plugin
 *
 * @package wsal
 *
 * @since 5.0.0
 */

// phpcs:disable
/* @premium:start */
// phpcs:enable
use WSAL\Helpers\Validator;
use WSAL\Views\Notifications;
use WSAL\Controllers\Slack\Slack;
use WSAL\Controllers\Twilio\Twilio;
use WSAL\Helpers\Settings\Settings_Builder;
use WSAL\Entities\Custom_Notifications_Entity;
use WSAL\Extensions\Helpers\Notification_Helper;

if ( isset( $_REQUEST['action'] ) && 'edit' === $_REQUEST['action'] && isset( $_REQUEST['_wpnonce'] ) && isset( $_REQUEST[ Custom_Notifications_Entity::get_table_name() ] ) && 0 < \absint( ( (array) $_REQUEST[ Custom_Notifications_Entity::get_table_name() ] )[0] ) ) {
	$notification_id = absint( ( (array) $_REQUEST[ Custom_Notifications_Entity::get_table_name() ] )[0] );

	$generated_reports_data = Custom_Notifications_Entity::load_array( 'id=%d', array( $notification_id ) );

	$settings                                   = array();
	$settings['custom_notification_title']      = $generated_reports_data[0]['notification_title'];
	$settings['custom_notification_email']      = $generated_reports_data[0]['notification_email'];
	$settings['custom_notification_email_bcc']  = $generated_reports_data[0]['notification_email_bcc'];
	$settings['custom_notification_phone']      = $generated_reports_data[0]['notification_phone'];
	$settings['custom_notification_slack']      = $generated_reports_data[0]['notification_slack'] ?? '';
	$settings['custom_notification_email_user'] = (bool) $generated_reports_data[0]['notification_email_user'];
	$settings['custom_notification_enabled']    = (bool) $generated_reports_data[0]['notification_status'];
	$settings['custom_notification_query']      = $generated_reports_data[0]['notification_query'];

	if ( Validator::validate_json( (string) $generated_reports_data[0]['notification_template'] ) ) {
		$generated_reports_data[0]['notification_template'] = json_decode( $generated_reports_data[0]['notification_template'], true );

		if ( isset( $generated_reports_data[0]['notification_template'] ) && is_array( $generated_reports_data[0]['notification_template'] ) && ! empty( $generated_reports_data[0]['notification_template'] ) ) {
			$settings['custom_notification_template_enabled'] = (bool) $generated_reports_data[0]['notification_template']['custom_notification_template_enabled'];
			$settings['email_custom_notifications_subject']   = ( isset( $generated_reports_data[0]['notification_template']['email_custom_notifications_subject'] ) ? $generated_reports_data[0]['notification_template']['email_custom_notifications_subject'] : Notification_Helper::get_default_email_subject() );
			$settings['email_custom_notifications_body']      = ( isset( $generated_reports_data[0]['notification_template']['email_custom_notifications_body'] ) ? $generated_reports_data[0]['notification_template']['email_custom_notifications_body'] : Notification_Helper::get_default_email_body() );
		}
	}

	if ( Validator::validate_json( (string) $generated_reports_data[0]['notification_sms_template'] ) ) {
		$generated_reports_data[0]['notification_sms_template'] = json_decode( $generated_reports_data[0]['notification_sms_template'], true );

		if ( isset( $generated_reports_data[0]['notification_sms_template'] ) && is_array( $generated_reports_data[0]['notification_sms_template'] ) && ! empty( $generated_reports_data[0]['notification_sms_template'] ) ) {
			$settings['custom_notification_sms_template_enabled'] = (bool) $generated_reports_data[0]['notification_sms_template']['custom_notification_sms_template_enabled'];
			$settings['sms_custom_notifications_body']            = ( isset( $generated_reports_data[0]['notification_sms_template']['sms_custom_notifications_body'] ) ? $generated_reports_data[0]['notification_sms_template']['sms_custom_notifications_body'] : Notification_Helper::get_default_sms_body() );
		}
	}

	if ( isset( $generated_reports_data[0]['notification_slack_template'] ) && Validator::validate_json( (string) $generated_reports_data[0]['notification_slack_template'] ) ) {
		$generated_reports_data[0]['notification_slack_template'] = json_decode( $generated_reports_data[0]['notification_slack_template'], true );

		if ( isset( $generated_reports_data[0]['notification_slack_template'] ) && is_array( $generated_reports_data[0]['notification_slack_template'] ) && ! empty( $generated_reports_data[0]['notification_slack_template'] ) ) {
			$settings['custom_notification_slack_template_enabled'] = (bool) $generated_reports_data[0]['notification_slack_template']['custom_notification_slack_template_enabled'];
			$settings['slack_custom_notifications_body']            = ( isset( $generated_reports_data[0]['notification_slack_template']['slack_custom_notifications_body'] ) ? $generated_reports_data[0]['notification_slack_template']['slack_custom_notifications_body'] : Notification_Helper::get_default_slack_body() );
		}
	}

	$settings['custom_notification_send_to_default_email'] = true;

	$settings['custom_notification_send_to_default_slack'] = true;

	$settings['custom_notification_send_to_default_phone'] = true;

	if ( isset( $generated_reports_data[0]['notification_settings'] ) && Validator::validate_json( (string) $generated_reports_data[0]['notification_settings'] ) ) {
		$generated_reports_data[0]['notification_settings'] = json_decode( $generated_reports_data[0]['notification_settings'], true );

		if ( isset( $generated_reports_data[0]['notification_settings'] ) && is_array( $generated_reports_data[0]['notification_settings'] ) && ! empty( $generated_reports_data[0]['notification_settings'] ) ) {
			$settings['custom_notification_send_to_default_email'] = (bool) $generated_reports_data[0]['notification_settings']['custom_notification_send_to_default_email'];

			$settings['custom_notification_send_to_default_slack'] = (bool) $generated_reports_data[0]['notification_settings']['custom_notification_send_to_default_slack'];

			$settings['custom_notification_send_to_default_phone'] = (bool) $generated_reports_data[0]['notification_settings']['custom_notification_send_to_default_phone'];

		}
	} else {
		if ( isset( $settings['custom_notification_email'] ) && ! empty( $settings['custom_notification_email'] ) ) {
			$settings['custom_notification_send_to_default_email'] = false;
		}
		if ( isset( $settings['custom_notification_phone'] ) && ! empty( $settings['custom_notification_phone'] ) ) {
			$settings['custom_notification_send_to_default_phone'] = false;
		}
		if ( isset( $settings['custom_notification_slack'] ) && ! empty( $settings['custom_notification_slack'] ) ) {
			$settings['custom_notification_send_to_default_slack'] = false;
		}
	}

	Settings_Builder::set_current_options( $settings );

	?>
	<input type="hidden" id="custom_notifications_id" name="custom-notifications[custom_notifications_id]" value="<?php echo \esc_attr( $notification_id ); ?>" />
	<?php
}

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Custom notification', 'wp-security-audit-log' ),
		'id'            => 'user-notification-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Title: ', 'wp-security-audit-log' ),
		'id'            => 'custom_notification_title',
		'type'          => 'text',
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);
?>
<div><?php echo esc_html__( ' Refer to the ', 'wp-security-audit-log' ) . '<a href="https://melapress.com/support/kb/wp-activity-log-getting-started-sms-email-notifications/#utm_source=plugin&amp;utm_medium=link&amp;utm_campaign=wsal" rel="nofollow" target="_blank">' . esc_html__( 'Notifications getting started documentation.', 'wp-security-audit-log' ) . '</a> ' . esc_html__( 'for a detailed guide on how to build your own notification triggers.', 'wp-security-audit-log' ); ?></div>
<?php
Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Query builder: ', 'wp-security-audit-log' ),
		'id'            => 'custom_notification_query',
		'type'          => 'builder',
		'default'       => '',
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Notification enabled: ', 'wp-security-audit-log' ),
		'id'            => 'custom_notification_enabled',
		'type'          => 'checkbox',
		'default'       => true,
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);

if ( Notifications::is_default_mail_set() ) {

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Send to default email address: ', 'wp-security-audit-log' ),
			'id'            => 'custom_notification_send_to_default_email',
			'type'          => 'checkbox',
			'default'       => ( isset( $settings['custom_notification_send_to_default_email'] ) ? $settings['custom_notification_send_to_default_email'] : true ),
			'untoggle'      => '#custom_notification_email-item-container',
			'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

}
?>
	<div id="custom_notification_email-item-container">
	<?php
	Settings_Builder::build_option(
		Notification_Helper::email_settings_array( 'custom_notification_email', Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME )
	);
	?>
		<div class="buttons-container">
			<style>
				.buttons-container {
					display: flex;
					flex-direction: row;
					justify-content: space-between;
					width: 100%;
				}
			</style>
		<?php
		// Settings_Builder::build_option(
		// array(
		// 'add_label'     => true,
		// 'id'            => 'notification_store_settings',
		// 'type'          => 'button',
		// 'default'       => esc_html__( 'Save and use this email address for this notification only', 'wp-security-audit-log' ),
		// 'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
		// )
		// );

		// Settings_Builder::build_option(
		// array(
		// 'add_label'     => true,
		// 'id'            => 'notification_store_settings_and_set_global',
		// 'type'          => 'button',
		// 'default'       => esc_html__( 'Save and use this email address as the default notifications address', 'wp-security-audit-log' ),
		// 'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
		// )
		// );

		// Settings_Builder::build_option(
		// array(
		// 'add_label'     => true,
		// 'id'            => 'email_settings_cancel',
		// 'type'          => 'button',
		// 'default'       => esc_html__( 'Cancel', 'wp-security-audit-log' ),
		// 'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
		// )
		// );
		?>
		<input type="hidden" id="store_global_mail" name="custom-notifications[store_global_mail]" value="" />
		<script>
			jQuery( document ).ready( function( $ ) {
				jQuery( '#email_settings_cancel' ).on( 'click', function() {
					location.href = '<?php echo \esc_url( \add_query_arg( 'page', self::get_safe_view_name(), \network_admin_url( 'admin.php' ) ) ) . '#wsal-options-tab-custom-notifications'; ?>';
				} );

				jQuery( '#notification_store_settings_and_set_global' ).on( 'click', function( e ) {
					e.preventDefault();
					jQuery("#store_global_mail").val('yes');
					jQuery(".wsal-save-button")[7].click();
				});
				jQuery( '#notification_store_settings' ).on( 'click', function( e ) {
					e.preventDefault();
					jQuery(".wsal-save-button")[7].click();
				});
			} );
		</script>
		</div>
	</div><!-- #custom_notification_email-item /-->
<?php
if ( Notifications::is_default_slack_set() ) {

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Send to default slack channel: ', 'wp-security-audit-log' ),
			'id'            => 'custom_notification_send_to_default_slack',
			'type'          => 'checkbox',
			'untoggle'      => '#custom_notification_slack-item',
			'default'       => true,
			'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

}

if ( Slack::is_set() ) {

	Settings_Builder::build_option(
		Notification_Helper::slack_settings_array( 'custom_notification_slack', Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME, '', esc_html__( 'Leave empty if you do not want to send a Slack notfication.', 'wp-security-audit-log' ) )
	);
} else {
	Settings_Builder::build_option(
		Notification_Helper::slack_settings_error_array( 'custom_notification_slack', Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME )
	);
}

if ( Notifications::is_default_twilio_set() ) {

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Send to default phone number: ', 'wp-security-audit-log' ),
			'id'            => 'custom_notification_send_to_default_phone',
			'type'          => 'checkbox',
			'untoggle'      => '#custom_notification_phone-item',
			'default'       => true,
			'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

}

if ( Twilio::is_set() ) {
	Settings_Builder::build_option(
		Notification_Helper::phone_settings_array( 'custom_notification_phone', Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME, '', esc_html__( 'Leave empty if you you do not want to send a SMS notfication. Format you must use is: +16175551212', 'wp-security-audit-log' ) )
	);
} else {
	$exclude_objects_link = add_query_arg(
		array(
			'page' => 'wsal-notifications',
			'tab'  => 'exclude-objects',
		),
		\network_admin_url( 'admin.php' )
	) . '#wsal-options-tab-notification-settings';
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Phone number: ', 'wp-security-audit-log' ),
			'type'          => 'error_text',
			'id'            => 'custom_notification_phone',
			'text'          => '<span>' . esc_html__( 'In order to use Phone numbers you have to enable and set your Twilio credentials in ', 'wp-security-audit-log' ) . '<a  href="' . $exclude_objects_link . '">' . esc_html__( 'settings.', 'wp-security-audit-log' ) . ' </a></span>',
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
}

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Send email to user in the event.', 'wp-security-audit-log' ),
		'id'            => 'custom_notification_email_user',
		'type'          => 'checkbox',
		'default'       => false,
		'hint'          => esc_html__( 'Send the notification to user carrying out the activity.', 'wp-security-audit-log' ),
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	Notification_Helper::email_settings_array( 'custom_notification_email_bcc', Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME, esc_html__( 'Email BCC address: ', 'wp-security-audit-log' ) )
);

?>
<div class="wsal-section-title wsal-section-tabs header-settings-tabs">
	<a href="#custom-main-nav-settings" class="active"><?php esc_html_e( 'Email template', 'wp-security-audit-log' ); ?></a>
	<a href="#custom-top-nav-settings"><?php esc_html_e( 'SMS template', 'wp-security-audit-log' ); ?></a>
	<a href="#custom-slack-nav-settings"><?php esc_html_e( 'Slack template', 'wp-security-audit-log' ); ?></a>
</div>

<div id="custom-slack-nav-settings" class="top-main-nav-settings">
<?php

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Custom Slack template: ', 'wp-security-audit-log' ),
		'id'            => 'custom_notification_slack_template_enabled',
		'type'          => 'checkbox',
		'default'       => false,
		'toggle'        => '#custom-notification-slack-template',
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);
?>

	<div id="custom-notification-slack-template">	
		<?php

		$mail_template_tags = '';

		foreach ( Notification_Helper::get_email_template_tags() as $tag_name => $desc ) {
			$mail_template_tags .= '<li>' . esc_html( $tag_name ) . ' — ' . esc_html( $desc ) . '</li>';
		}

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Body', 'wp-security-audit-log' ),
				'id'            => 'slack_custom_notifications_body',
				'type'          => 'textarea',
				'default'       => Notification_Helper::get_default_slack_body(),
				'hint'          => '<b>' . esc_html__( 'Available template tags:', 'wp-security-audit-log' ) . '</b><ul>' . $mail_template_tags . '</ul>',
				'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		?>
	</div><!-- #custom-notification-email-template /-->
</div><!-- #slack-nav-settings /-->


<div id="custom-main-nav-settings" class="top-main-nav-settings">
<?php

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Custom email template: ', 'wp-security-audit-log' ),
		'id'            => 'custom_notification_template_enabled',
		'type'          => 'checkbox',
		'default'       => false,
		'toggle'        => '#custom-notification-email-template',
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);
?>

	<div id="custom-notification-email-template">
		<?php
		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Subject: ', 'wp-security-audit-log' ),
				'id'            => 'email_custom_notifications_subject',
				'type'          => 'text',
				'default'       => Notification_Helper::get_default_email_subject(),
				'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		$mail_template_tags = '';

		foreach ( Notification_Helper::get_email_template_tags() as $tag_name => $desc ) {
			$mail_template_tags .= '<li>' . esc_html( $tag_name ) . ' — ' . esc_html( $desc ) . '</li>';
		}

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Body', 'wp-security-audit-log' ),
				'id'            => 'email_custom_notifications_body',
				'type'          => 'editor',
				'default'       => Notification_Helper::get_default_email_body(),
				'hint'          => '<b>' . esc_html__( 'HTML is accepted. Available template tags:', 'wp-security-audit-log' ) . '</b><ul>' . $mail_template_tags . '</ul>',
				'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		?>
	</div><!-- #custom-notification-email-template /-->

</div>

<div id="custom-top-nav-settings" class="top-main-nav-settings">
<?php

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Custom sms template: ', 'wp-security-audit-log' ),
		'id'            => 'custom_notification_sms_template_enabled',
		'type'          => 'checkbox',
		'default'       => false,
		'toggle'        => '#custom-notification-sms-template',
		'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
	)
);
?>
	<div id="custom-notification-sms-template">
		<?php
		$sms_template_tags = '';

		foreach ( Notification_Helper::get_sms_template_tags() as $tag_name => $desc ) {
			$sms_template_tags .= '<li>' . esc_html( $tag_name ) . ' — ' . esc_html( $desc ) . '</li>';
		}

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Body', 'wp-security-audit-log' ),
				'id'            => 'sms_custom_notifications_body',
				'type'          => 'textarea',
				'default'       => Notification_Helper::get_default_sms_body(),
				'hint'          => '<b>' . esc_html__( 'Available template tags: :', 'wp-security-audit-log' ) . '</b><ul>' . $sms_template_tags . '</ul>',
				'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
	</div><!-- #custom-notification-sms-template /-->
</div>
<?php
// phpcs:disable
/* @premium:end */
// phpcs:enable