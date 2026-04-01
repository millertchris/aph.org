<?php
/**
 * Advanced settings of the plugin
 *
 * @package wsal
 *
 * @since 5.0.0
 */

use WSAL\Extensions\Views\Reports;
use WSAL\Helpers\Settings\Settings_Builder;
use WSAL\Helpers\Settings_Helper;

Settings_Builder::set_current_options( Settings_Helper::get_option_value( Reports::REPORT_WHITE_LABEL_SETTINGS_NAME, array() ) );

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'White labelling settings', 'wp-security-audit-log' ),
		'id'            => 'logo-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'id'      => 'general-settings-tab',
		'type'    => 'html',
		'content' => '<p>' . \wp_sprintf(
			esc_html__( 'Use this section to customize the branding of your reports by adding your business information.', 'wp-security-audit-log' ) . '</p>' .

			'<p>' . esc_html__( 'You can include your company name, contact details (such as name, surname, and email address), and upload a business logo to be displayed in the report header. You can also link the logo to a specific URL—ideal for directing report recipients to your website or support portal.', 'wp-security-audit-log' ) . '</p>' .
			// translators: %1$s, %3$s are a HTML tags. %2$s is the link title.
			'<p>' . esc_html__( 'For step-by-step instructions and more details, refer to the %1$s%2$s%3$s.', 'wp-security-audit-log' ) . '</p>',
			'<a href="https://melapress.com/support/kb/wp-activity-log-customize-wp-activity-log-reports/?utm_source=plugin&utm_medium=wsal&utm_campaign=reports-page-link-2" target="_blank">',
			esc_html__( 'Reports\' white labelling', 'wp-security-audit-log' ),
			'</a>',
		),
	)
);

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'White labelling', 'wp-security-audit-log' ),
		'id'            => 'white-labeling-settings-section',
		'type'          => 'header',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Business name', 'wp-security-audit-log' ),
		'id'            => 'business_name',
		'type'          => 'text',
		'default'       => get_bloginfo(),
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Contact details', 'wp-security-audit-log' ),
		'id'            => 'contact-details-settings-section',
		'type'          => 'header',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Name and surname', 'wp-security-audit-log' ),
		'id'            => 'name_surname',
		'type'          => 'text',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Email', 'wp-security-audit-log' ),
		'id'            => 'email',
		'type'          => 'text',
		'validate'      => 'email',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Phone', 'wp-security-audit-log' ),
		'id'            => 'phone_number',
		'type'          => 'text',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Logo', 'wp-security-audit-log' ),
		'id'            => 'logo-settings-section',
		'type'          => 'header',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'text'          => esc_html__( 'By default the HTML reports have a logo of the WP Activity Log plugin in them. Use the settings below to change this logo and also specify a URL that this logo should link to. ', 'wp-security-audit-log' ),
		'type'          => 'message',
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

echo '<div id="logo-image-settings" class="logo_setting-options">';

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Logo Image', 'wp-security-audit-log' ),
		'id'            => 'logo',
		'type'          => 'upload',
		'hint'          => esc_html__( 'The logo size should be 360px in width.', 'wp-security-audit-log' ),
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);

echo '</div>';

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Custom logo URL', 'wp-security-audit-log' ),
		'id'            => 'logo_url',
		'type'          => 'text',
		'hint'          => esc_html__( 'Specify the URL that the logo should link to, so when users click on the logo in the report, they are redirected to that URL.', 'wp-security-audit-log' ),
		'settings_name' => Reports::REPORT_WHITE_LABEL_SETTINGS_NAME,
	)
);
?>
<script>
	
jQuery( ".wsal-options-tab-white-label, #wsal-options-tab-white-label" ).on( "activated", function() {
	jQuery( ".wsal-save-button").css('display', 'block');
	jQuery('.wsal-save-button').text('Save changes');

	if (jQuery('#generate_report_tab_selected').length) {
		jQuery('#generate_report_tab_selected').val(0);
	}

	if (jQuery('#generate_statistic_report_tab_selected').length) {
		jQuery('#generate_statistic_report_tab_selected').val(0);
	}
});
</script>
