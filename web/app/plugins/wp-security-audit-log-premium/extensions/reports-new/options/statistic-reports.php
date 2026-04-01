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
		'title'         => esc_html__( 'Generate statistics report', 'wp-security-audit-log' ),
		'id'            => 'logo-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'id'      => 'general-settings-tab',
		'type'    => 'html',
		'content' => '<p>' . \wp_sprintf(
			esc_html__( 'Use this section to generate statistical reports from your website’s activity log data.', 'wp-security-audit-log' ) . '</p>' .

			'<p>' . esc_html__( 'To create report, define the date range, select how the data should be grouped (by day, week, or month), and choose the report type (e.g., number of logins, content published) from the drop down menu. You can also specify a tag. Once ready, click Generate Report.s.', 'wp-security-audit-log' ) . '</p>' .
			// translators: %4$s, %3$s are a HTML tags. %5$s is the link title.
			'<p>' . esc_html__( 'The generated report and previoulsy generated ones can downloaded from the %1$s%2$s%3$s section.', 'wp-security-audit-log' ) . '</p>',
			'<a href="javascript:void(0);" onclick="window.location.href=(\'' . \esc_url( $url ) . '\');  location.reload(); return false">',
			esc_html__( 'Generated & saved reports', 'wp-security-audit-log' ),
			'</a>',
		),
	)
);
	// Date range.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Select date range of the report', 'wp-security-audit-log' ),
			'id'            => 'daterange-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Start date', 'wp-security-audit-log' ),
			'id'            => 'report_start_date',
			'type'          => 'date',
			'max'           => gmdate( 'Y-m-d' ),
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'End date', 'wp-security-audit-log' ),
			'id'            => 'report_end_date',
			'type'          => 'date',
			'max'           => gmdate( 'Y-m-d' ),
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	// Grouping type.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Select data sorting type', 'wp-security-audit-log' ),
			'id'            => 'data-sorting-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Time period', 'wp-security-audit-log' ),
			'id'            => 'time_format',
			'type'          => 'radio',
			'options'       => array(
				'day'   => esc_html__( 'Per day', 'wp-security-audit-log' ),
				'week'  => esc_html__( 'Per week', 'wp-security-audit-log' ),
				'month' => esc_html__( 'Per month', 'wp-security-audit-log' ),
			),
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	// Types report.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Choose report type', 'wp-security-audit-log' ),
			'id'            => 'statistic-report-type-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Report type', 'wp-security-audit-log' ),
			'id'            => 'statistic_report_type',
			'type'          => 'select',
			'options'       => array(
				'logins_all_users'               => esc_html__( 'Number of logins for all users', 'wp-security-audit-log' ),
				'newly_registered_users'         => esc_html__( 'Number of newly registered users', 'wp-security-audit-log' ),
				'logins_for_users'               => esc_html__( 'Number of logins for user(s)', 'wp-security-audit-log' ),
				'logins_for_roles'               => esc_html__( 'Number of logins for users with the role(s) of', 'wp-security-audit-log' ),
				'profile_changes'                => esc_html__( 'Number of profile changes for all users', 'wp-security-audit-log' ),
				'profile_changes_users'          => esc_html__( 'Number of profile changes for user(s)', 'wp-security-audit-log' ),
				'profile_changes_roles'          => esc_html__( 'Number of profile changes for users with the role(s) of', 'wp-security-audit-log' ),
				'views_posts'                    => esc_html__( 'Number of views for all posts', 'wp-security-audit-log' ),
				'views_posts_users'              => esc_html__( 'Number of views for user(s)', 'wp-security-audit-log' ),
				'views_posts_roles'              => esc_html__( 'Number of views for users with the role(s) of', 'wp-security-audit-log' ),
				'views_specific_post'            => esc_html__( 'Number of views for a specific post', 'wp-security-audit-log' ),
				'published_by_all_users'         => esc_html__( 'Number of published content for all users', 'wp-security-audit-log' ),
				'published_by_users'             => esc_html__( 'Number of published content for user(s)', 'wp-security-audit-log' ),
				'published_by_roles'             => esc_html__( 'Number of published content for users with the role(s) of', 'wp-security-audit-log' ),
				'password_changes_and_resets'    => esc_html__( 'User password changes and password resets', 'wp-security-audit-log' ),
				'ips_for_users'                  => esc_html__( 'Different IP addresses for Usernames', 'wp-security-audit-log' ),
				'ips_accessed'                   => esc_html__( 'List of IP addresses that accessed the website', 'wp-security-audit-log' ),
				'users_accessed'                 => esc_html__( 'List of users who accessed the website', 'wp-security-audit-log' ),
				'last_login_date_time_all_users' => esc_html__( 'Last login date and time for all users', 'wp-security-audit-log' ),
				'last_login_date_time_roles'     => esc_html__( 'Last login date and time for users with the role(s) of', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''                           => '',
				'logins_for_users'           => '#statistic_users_select-item',
				'profile_changes_users'      => '#statistic_users_select-item',
				'views_posts_users'          => '#statistic_users_select-item',
				'published_by_users'         => '#statistic_users_select-item',
				'logins_for_roles'           => '#statistic_roles_select-item',
				'profile_changes_roles'      => '#statistic_roles_select-item',
				'views_posts_roles'          => '#statistic_roles_select-item',
				'published_by_roles'         => '#statistic_roles_select-item',
				'views_specific_post'        => '#statistic_posts_select-item',
				'ips_for_users'              => '#statistic_report_login_ips_only-item',
				'last_login_date_time_roles' => '#statistic_roles_select-item',
			),
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Select users', 'wp-security-audit-log' ),
			'id'            => 'statistic_users_select',
			'class'         => 'statistic_report_type',
			'type'          => 'users',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Select roles', 'wp-security-audit-log' ),
			'id'            => 'statistic_roles_select',
			'class'         => 'statistic_report_type',
			'type'          => 'roles',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Select posts', 'wp-security-audit-log' ),
			'id'            => 'statistic_posts_select',
			'class'         => 'statistic_report_type',
			'type'          => 'posts',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'List only IP addresses used during login', 'wp-security-audit-log' ),
			'id'            => 'statistic_report_login_ips_only',
			'type'          => 'checkbox',
			'class'         => 'statistic_report_type',
			'default'       => false,
			'hint'          => esc_html__( 'If the above option is enabled the report will only include the IP addresses from where the user logged in. If it is disabled it will list all the IP addresses from where the plugin recorded activity originating from the user.', 'wp-security-audit-log' ),
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	// Report tag.

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Set a tag for the report', 'wp-security-audit-log' ),
			'id'            => 'statistic-report-tag-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);
	Settings_Builder::build_option(
		array(
			'text'          => '<span style="display:block; margin-bottom:1em;">' . esc_html__( 'Tags are like "user friendly names" for your reports to help you sort and find the reports you need in the Saved Reports section. Report tags are limited to 100 characters and you can only use letters, numbers and underscores.', 'wp-security-audit-log' ) . '</span>',
			'type'          => 'hint',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Report tag', 'wp-security-audit-log' ),
			'id'            => 'statistic_report_tag',
			'type'          => 'text',
			'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
		)
	);

	if ( Settings_Helper::is_archiving_enabled() ) {
		// Report adding archiving.
		Settings_Builder::build_option(
			array(
				'title'         => esc_html__( 'Archive table', 'wp-security-audit-log' ),
				'id'            => 'statistic-report-archive-add-settings',
				'type'          => 'header',
				'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Include records from the archive table', 'wp-security-audit-log' ),
				'id'            => 'statistic_report_include_archive',
				'type'          => 'checkbox',
				'default'       => false,
				'toggle'        => '#statistic_report_only_archive-item',
				'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Use ONLY records from the archive table', 'wp-security-audit-log' ),
				'id'            => 'statistic_report_only_archive',
				'type'          => 'checkbox',
				'default'       => false,
				'settings_name' => Reports::GENERATE_STATISTIC_REPORT_SETTINGS_NAME,
			)
		);
	}
	?>
<input type="hidden" id="generate_statistic_report_tab_selected" name="generate_statistic_report_tab_selected" value="0" />
<script>
	
jQuery( ".wsal-options-tab-statistic-reports, #wsal-options-tab-statistic-reports" ).on( "activated", function() {
	jQuery( ".wsal-save-button").css('display', 'block');
	jQuery('.wsal-save-button').text('Generate Report');

	if (jQuery('#generate_statistic_report_tab_selected').length) {
		jQuery('#generate_statistic_report_tab_selected').val(1);
	}

	if (jQuery('#generate_report_tab_selected').length) {
		jQuery('#generate_report_tab_selected').val(0);
	}
});
</script>
