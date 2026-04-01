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
use WSAL\Extensions\Notifications\Custom_Notifications;
use WSAL\Views\Notifications;
use WSAL\Helpers\Settings\Settings_Builder;

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Custom notifications', 'wp-security-audit-log' ),
			'id'            => 'custom-notifications-tab',
			'type'          => 'tab-title',
			'settings_name' => Notifications::CUSTOM_NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'id'      => 'general-settings-tab',
			'type'    => 'html',
			'content' => '<p>' . \wp_sprintf(
				esc_html__( 'Use this section to create your own custom notifications based on specific activity log events. With the notification builder, you can define detailed criteria, such as the event type, object (e.g. user, post, plugin), usernames, roles, IP addresses, and more, to trigger notifications that are most relevant to your needs.', 'wp-security-audit-log' ) . '</p>' .

				'<p>' . esc_html__( 'Once a matching event is logged, the plugin will automatically send a notification via your chosen channel (email, SMS, or Slack). This feature gives you full control over what actions you’re alerted about, so you can tailor notifications to your specific workflows and security policies.', 'wp-security-audit-log' ) . '</p>' .
				// translators: %1$s, %3$s are a HTML tags. %2$s is the link title.
				'<p>' . esc_html__( 'For more information and guidance, refer to the %1$s%2$s%3$s.', 'wp-security-audit-log' ) . '</p>',
				'<a href="https://melapress.com/support/kb/wp-activity-log-sms-email-notification-wordpress-change/?utm_source=plugin&utm_medium=wsal&utm_campaign=notifications-builder-help-link-3" target="_blank">',
				esc_html__( 'Custom Notifications Guide', 'wp-security-audit-log' ),
				'</a>',
			),
		)
	);

	$custom_notifications_list = new Custom_Notifications( \WSAL_Views_AuditLog::get_page_arguments() );
	$custom_notifications_list->prepare_items();

	?>
		<style>
			#periodic-report-viewer-content {
				margin-left: 5px;
				margin-right: 5px;
			}
		</style>
		<div id="periodic-report-viewer-content">
			
			
				<?php
				echo '<div style="clear:both; float:right">';
				$custom_notifications_list->search_box(
					__( 'Search', 'wp-security-audit-log' ),
					strtolower( $custom_notifications_list::get_table_name() ) . '-find'
				);
				echo '</div>';
				// Display the audit log list.
				$custom_notifications_list->display();
				?>
		</div>
		
<script>
	jQuery('li.wsal-tabs:not(.wsal-not-tab)').click(function () {
		jQuery('.wsal-save-button').show();
		jQuery('.create_custom_notification').hide();
	});

	jQuery( ".wsal-options-tab-custom-notifications, #wsal-options-tab-custom-notifications" ).on( "activated", function() {
		jQuery('.wsal-save-button').hide();
		jQuery('.create_custom_notification').show();
	});
</script>
<?php
// phpcs:disable
/* @premium:end */
// phpcs:enable