<?php
/**
 * Advanced settings of the plugin
 *
 * @package wsal
 *
 * @since 5.0.0
 */

use WSAL\Helpers\WP_Helper;
use WSAL\MainWP\MainWP_Addon;
use WSAL\Controllers\Constants;
use WSAL\Helpers\Settings_Helper;
use WSAL\Extensions\Views\Reports;
use WSAL\Controllers\Alert_Manager;
use WSAL\Helpers\Settings\Settings_Builder;

if ( ! isset( Settings_Builder::get_current_options()['periodic_report'] ) ) {
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Generate a report', 'wp-security-audit-log' ),
			'id'            => 'general-settings-tab',
			'type'          => 'tab-title',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);
} else {
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Edit report', 'wp-security-audit-log' ),
			'id'            => 'general-settings-tab',
			'type'          => 'tab-title',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);
}


$settings_url = \add_query_arg(
	array(
		'page' => Reports::get_safe_view_name(),
	),
	\network_admin_url( 'admin.php' )
) . '#wsal-options-tab-column-settings';


$url = \add_query_arg(
	array(
		'page' => Reports::get_safe_view_name(),
	),
	\network_admin_url( 'admin.php' )
) . '#wsal-options-tab-saved-reports';

Settings_Builder::build_option(
	array(
		'id'      => 'general-settings-tab',
		'type'    => 'html',
		'content' => '<p>' . \wp_sprintf(
			esc_html__( 'Use this section to generate custom reports based on your website’s activity log data.', 'wp-security-audit-log' ) . '</p>' .

			'<p>' . esc_html__( 'To create a report, configure the criteria using the dropdown menus below, select the desired date range, specify a report title and tag, then click Generate Report. All reports are generated in CSV and HTML format.', 'wp-security-audit-log' ) . '</p>' .

			'<p>' . esc_html__( 'Enable the Periodic report option if you would like this report to be automatically generated and delivered via email on a daily, weekly, monthly, or quarterly basis.', 'wp-security-audit-log' ) . '</p>' .
			// translators: %1$s, %3$s are a HTML tags. %2$s is the link title.
			'<p>' . esc_html__( 'For step-by-step instructions and more details, refer to the %1$s%2$s%3$s.', 'wp-security-audit-log' ) . '</p>' .
			// translators: %4$s, %3$s are a HTML tags. %5$s is the link title.
			'<p>' . esc_html__( 'The generated report and previoulsy generated ones can downloaded from the %4$s%5$s%3$s section.', 'wp-security-audit-log' ) . '</p>',
			'<a href="https://melapress.com/support/kb/wp-activity-log-wordpress-activity-log-reports-getting-started/?utm_source=plugin&utm_medium=wsal&utm_campaign=reports-page-link-1" target="_blank">',
			esc_html__( 'Reports Getting Started Guide', 'wp-security-audit-log' ),
			'</a>',
			'<a href="javascript:void(0);" onclick="window.location.href=(\'' . \esc_url( $url ) . '\');  location.reload(); return false">',
			esc_html__( 'Generated & saved reports', 'wp-security-audit-log' ),
		),
	)
);

if ( WP_Helper::is_multisite() || MainWP_Addon::check_mainwp_plugin_active() ) {
	// Sites select.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Sites', 'wp-security-audit-log' ),
			'id'            => 'sites-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Site(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_sites',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All sites', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific sites', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All sites except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_sites-item',
				'all_except' => '#except_these_sites-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific sites', 'wp-security-audit-log' ),
			'id'            => 'only_these_sites',
			'class'         => 'report_type_sites',
			'type'          => 'sites',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All sites except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_sites',
			'class'         => 'report_type_sites',
			'type'          => 'sites',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);
}
	// Users select.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Users', 'wp-security-audit-log' ),
			'id'            => 'users-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By User(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_users',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All users', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific users', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All users except these', 'wp-security-audit-log' ),
				'all_domain' => esc_html__( 'All users with this domain in their email address', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_users-item',
				'all_except' => '#except_these_users-item',
				'all_domain' => '#all_users_domain-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific users', 'wp-security-audit-log' ),
			'id'            => 'only_these_users',
			'class'         => 'report_type_users',
			'type'          => 'users',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All users except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_users',
			'class'         => 'report_type_users',
			'type'          => 'users',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All users with this domain in their email address', 'wp-security-audit-log' ),
			'id'            => 'all_users_domain',
			'class'         => 'report_type_users',
			'type'          => 'text',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Roles select.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Roles', 'wp-security-audit-log' ),
			'id'            => 'roles-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Role(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_roles',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All roles', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific roles', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All roles except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_roles-item',
				'all_except' => '#except_these_roles-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific roles', 'wp-security-audit-log' ),
			'id'            => 'only_these_roles',
			'class'         => 'report_type_roles',
			'type'          => 'roles',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All roles except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_roles',
			'class'         => 'report_type_roles',
			'type'          => 'roles',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Ips select.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'IPs', 'wp-security-audit-log' ),
			'id'            => 'ips-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By IP(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_ips',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All IPs', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific IPs', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All IPs except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_ips-item',
				'all_except' => '#except_these_ips-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific IP(s)', 'wp-security-audit-log' ),
			'id'            => 'only_these_ips',
			'class'         => 'report_type_ips',
			'type'          => 'ips',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All IP(s) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_ips',
			'class'         => 'report_type_ips',
			'type'          => 'ips',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Objects select.
	$event_objects = Alert_Manager::get_event_objects_data();

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Objects', 'wp-security-audit-log' ),
			'id'            => 'objects-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Object(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_objects',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Objects', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Objects', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Objects except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_objects-item',
				'all_except' => '#except_these_objects-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Object(s)', 'wp-security-audit-log' ),
			'id'            => 'only_these_objects',
			'class'         => 'report_type_objects',
			'type'          => 'select2-multiple',
			'options'       => $event_objects,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All Objects(s) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_objects',
			'class'         => 'report_type_objects',
			'type'          => 'select2-multiple',
			'options'       => $event_objects,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Event types select.
	$event_types = Alert_Manager::get_event_type_data();

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Event types', 'wp-security-audit-log' ),
			'id'            => 'event-types-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Event type(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_event_types',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Event Types', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Event types', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Event types except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_event_types-item',
				'all_except' => '#except_these_event_types-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Event type(s)', 'wp-security-audit-log' ),
			'id'            => 'only_these_event_types',
			'class'         => 'report_type_event_types',
			'type'          => 'select2-multiple',
			'options'       => $event_types,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All Event type(s) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_event_types',
			'class'         => 'report_type_event_types',
			'type'          => 'select2-multiple',
			'options'       => $event_types,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Post titles select.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Post titles', 'wp-security-audit-log' ),
			'id'            => 'post-titles-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Post title(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_post_titles',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Post titles', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Post titles', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Post titles except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_post_titles-item',
				'all_except' => '#except_these_post_titles-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Post title(s)', 'wp-security-audit-log' ),
			'id'            => 'only_these_post_titles',
			'class'         => 'report_type_post_titles',
			'type'          => 'post_titles',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All Post title(s) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_post_titles',
			'class'         => 'report_type_post_titles',
			'type'          => 'post_titles',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Post types select.
	$post_types = \get_post_types( array(), 'names' );

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Post types', 'wp-security-audit-log' ),
			'id'            => 'post-types-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Post type(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_post_types',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Post Types', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Post types', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Post types except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_post_types-item',
				'all_except' => '#except_these_post_types-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Post type(s)', 'wp-security-audit-log' ),
			'id'            => 'only_these_post_types',
			'class'         => 'report_type_post_types',
			'type'          => 'select2-multiple',
			'options'       => $post_types,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All Post type(s) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_post_types',
			'class'         => 'report_type_post_types',
			'type'          => 'select2-multiple',
			'options'       => $post_types,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Post statuses select.
	$post_statuses = \get_post_statuses();

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Post statuses', 'wp-security-audit-log' ),
			'id'            => 'post-statuses-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Post status(es)', 'wp-security-audit-log' ),
			'id'            => 'report_type_post_statuses',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Post Statuses', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Post statuses', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Post statuses except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_post_statuses-item',
				'all_except' => '#except_these_post_statuses-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Post status(es)', 'wp-security-audit-log' ),
			'id'            => 'only_these_post_statuses',
			'class'         => 'report_type_post_statuses',
			'type'          => 'select2-multiple',
			'options'       => $post_statuses,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All Post status(es) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_post_statuses',
			'class'         => 'report_type_post_statuses',
			'type'          => 'select2-multiple',
			'options'       => $post_statuses,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Post ids select.

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Post IDs', 'wp-security-audit-log' ),
			'id'            => 'post-ids-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Post IDs', 'wp-security-audit-log' ),
			'id'            => 'report_type_post_ids',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Post IDs', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Post IDs', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Post IDs except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_post_ids-item',
				'all_except' => '#except_these_post_ids-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Post IDs', 'wp-security-audit-log' ),
			'id'            => 'only_these_post_ids',
			'class'         => 'report_type_post_ids',
			'type'          => 'post_ids',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All Post IDs except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_post_ids',
			'class'         => 'report_type_post_ids',
			'type'          => 'post_ids',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Event IDs select.
	$alert_ids = array_column( Alert_Manager::get_alerts(), 'desc', 'code' );
	foreach ( $alert_ids as $alert_key => &$alert_desc ) {
		$alert_desc = $alert_key . ' (' . \html_entity_decode( $alert_desc ) . ')';
	}
	unset( $alert_desc );

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Alert IDs', 'wp-security-audit-log' ),
			'id'            => 'alert-ids-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Alert IDs', 'wp-security-audit-log' ),
			'id'            => 'report_type_alert_ids',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Alert IDs', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Alert IDs', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Alert IDs except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_alert_ids-item',
				'all_except' => '#except_these_alert_ids-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Alert IDs', 'wp-security-audit-log' ),
			'id'            => 'only_these_alert_ids',
			'class'         => 'report_type_alert_ids',
			'type'          => 'select2-multiple',
			'options'       => $alert_ids,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All Alert IDs except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_alert_ids',
			'class'         => 'report_type_alert_ids',
			'type'          => 'select2-multiple',
			'options'       => $alert_ids,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Event Groups select.
	$alert_groups_raw = array_column( Alert_Manager::get_alerts(), 'category' );
	$alert_groups_raw = array_unique( $alert_groups_raw );

	$alert_groups = array();

	foreach ( $alert_groups_raw as &$alert_category ) {
		$alert_groups[ \sanitize_title( $alert_category ) ] = \html_entity_decode( $alert_category );
	}
	unset( $alert_category );

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Alert group(s)', 'wp-security-audit-log' ),
			'id'            => 'alert-groups-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By alert group(s)', 'wp-security-audit-log' ),
			'id'            => 'report_type_alert_groups',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All alert group(s)', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Alert Ggoup(s)', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All alert group(s) except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_alert_groups-item',
				'all_except' => '#except_these_alert_groups-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific alert group(s)', 'wp-security-audit-log' ),
			'id'            => 'only_these_alert_groups',
			'class'         => 'report_type_alert_groups',
			'type'          => 'select2-multiple',
			'options'       => $alert_groups,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All alert group(s) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_alert_groups',
			'class'         => 'report_type_alert_groups',
			'type'          => 'select2-multiple',
			'options'       => $alert_groups,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Severities select.
	$severities = array_column( Constants::get_wsal_constants( true ), 'description', 'value' );

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Severity(ies)', 'wp-security-audit-log' ),
			'id'            => 'severities-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'By Severity(ies)', 'wp-security-audit-log' ),
			'id'            => 'report_type_severities',
			'type'          => 'select',
			'options'       => array(
				''           => esc_html__( 'All Severity(ies)', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific Severity(ies)', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All Severity(ies) except these', 'wp-security-audit-log' ),
			),
			'toggle'        => array(
				''           => '',
				'only_these' => '#only_these_severities-item',
				'all_except' => '#except_these_severities-item',
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'These specific Severity(ies)', 'wp-security-audit-log' ),
			'id'            => 'only_these_severities',
			'class'         => 'report_type_severities',
			'type'          => 'select2-multiple',
			'options'       => $severities,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'All alert group(s) except these', 'wp-security-audit-log' ),
			'id'            => 'except_these_severities',
			'class'         => 'report_type_severities',
			'type'          => 'select2-multiple',
			'options'       => $severities,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);
	?>
<div id="date-range" <?php echo ( isset( Settings_Builder::get_current_options()['periodic_report'] ) ? 'class="wsal-hide"' : '' ); ?>>
	<?php
	// Date range.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Select date range of the report', 'wp-security-audit-log' ),
			'id'            => 'severities-select-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Start date', 'wp-security-audit-log' ),
			'id'            => 'report_start_date',
			'type'          => 'date',
			'max'           => gmdate( 'Y-m-d' ),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'End date', 'wp-security-audit-log' ),
			'id'            => 'report_end_date',
			'type'          => 'date',
			'max'           => gmdate( 'Y-m-d' ),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);
	?>
</div>
<?php
	// Report tag.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Set a tag for the report', 'wp-security-audit-log' ),
			'id'            => 'report-tag-settings',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Report tag', 'wp-security-audit-log' ),
			'id'            => 'report_tag',
			'type'          => 'text',
			'max_chars'     => 100,
			'pattern'       => '[A-Za-z0-9_]{0,}',
			'hint'          => esc_html__( 'Tags are like "user friendly names" for your reports to help you sort and find the reports you need in the Saved Reports section. Report tags are limited to 100 characters and you can only use letters, numbers and underscores.', 'wp-security-audit-log' ),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	if ( Settings_Helper::is_archiving_enabled() ) {
		// Report adding archiving.
		Settings_Builder::build_option(
			array(
				'title'         => esc_html__( 'Archive table', 'wp-security-audit-log' ),
				'id'            => 'report-archive-add-settings',
				'type'          => 'header',
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Include records from the archive table', 'wp-security-audit-log' ),
				'id'            => 'report_include_archive',
				'type'          => 'checkbox',
				'default'       => false,
				'toggle'        => '#report_only_archive-item',
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Use ONLY records from the archive table', 'wp-security-audit-log' ),
				'id'            => 'report_only_archive',
				'type'          => 'checkbox',
				'default'       => false,
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);
	}

	// Report additional fields.
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Report title, comment & metadata settings', 'wp-security-audit-log' ),
			'id'            => 'report-additional-data-section',
			'type'          => 'header',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Title', 'wp-security-audit-log' ),
			'id'            => 'report_title',
			'max_chars'     => 100,
			'type'          => 'text',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Comment', 'wp-security-audit-log' ),
			'id'            => 'report_comment',
			'type'          => 'textarea',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Metadata', 'wp-security-audit-log' ),
			'id'            => 'report_metadata',
			'type'          => 'checkbox',
			'default'       => true,
			'hint'          => esc_html__( 'Do not add report metadata to the report.', 'wp-security-audit-log' ),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	// Make a periodic report.
	if ( ! isset( Settings_Builder::get_current_options()['periodic_report'] ) ) {

		Settings_Builder::build_option(
			array(
				'title'         => esc_html__( 'Configure the report as an automated periodic report', 'wp-security-audit-log' ),
				'type'          => 'header',
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'text'          => '<span style="display:block; margin-bottom:1em;">' . esc_html__( 'Periodic reports are automatically generated reports sent via email on a daily, weekly, monthly, or quarterly basis, depending on your selected schedule. The content of each periodic report is defined by the criteria you configure in the dropdown menus above.', 'wp-security-audit-log' ) . '</span>' . esc_html__( 'Once configured, the report will continue to be generated automatically using the same criteria, unless you modify or delete it.', 'wp-security-audit-log' ),
				'type'          => 'hint',
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Periodic report', 'wp-security-audit-log' ),
				'id'            => 'periodic_report',
				'toggle'        => '#generic_report_name-item, #generic_report_email-item, #generic_report_period-item, #generic_report_disabled-item',
				'type'          => 'checkbox',
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);
	} else {

		Settings_Builder::build_option(
			array(
				'title'         => esc_html__( 'Periodic report options', 'wp-security-audit-log' ),
				'type'          => 'header',
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Periodic report', 'wp-security-audit-log' ),
				'id'            => 'periodic_report',
				'type'          => 'hidden',
				'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
			)
		);

	}

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Report name', 'wp-security-audit-log' ),
			'id'            => 'generic_report_name',
			'type'          => 'text',
			'pattern'       => '[A-Za-z0-9_\-]{0,}',
			// 'required'      => true,
			'hint'          => esc_html__( 'Report names can only include numbers, letters, underscore, and hyphens.', 'wp-security-audit-log' ),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Send report to email', 'wp-security-audit-log' ),
			'id'            => 'generic_report_email',
			'type'          => 'text',
			'pattern'       => '^$|^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,20}(,[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,20})*$',
			'hint'          => esc_html__( 'You can add more than one address, separated with a comma, don\'t use spaces.', 'wp-security-audit-log' ),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Period of the report', 'wp-security-audit-log' ),
			'id'            => 'generic_report_period',
			'type'          => 'radio',
			'options'       => array(
				'0' => esc_html__( 'Daily', 'wp-security-audit-log' ),
				'1' => esc_html__( 'Weekly', 'wp-security-audit-log' ),
				'2' => esc_html__( 'Monthly', 'wp-security-audit-log' ),
				'3' => esc_html__( 'Quarterly', 'wp-security-audit-log' ),
			),
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	/*
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Disabled', 'wp-security-audit-log' ),
			'id'            => 'generic_report_disabled',
			'type'          => 'checkbox',
			'default'       => false,
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);
	*/
	?>
	<input type="hidden" id="generate_report_tab_selected" name="generate_report_tab_selected" value="1" />
	<script>
		jQuery("input[id='periodic_report']").change(function(){
			jQuery('#date-range').toggleClass( 'wsal-hide' );
		});
		jQuery( ".wsal-options-tab-generate, #wsal-options-tab-generate" ).on( "activated", function() {
			jQuery( ".wsal-save-button").css('display', 'block');
			if (jQuery('#periodic_report').is(':checked')){
				jQuery('.wsal-save-button').text('Save Report');
			} else {
				jQuery('.wsal-save-button').text('Generate Report');
			}
			if (jQuery('#generate_report_tab_selected').length) {
				jQuery('#generate_report_tab_selected').val(1);
			}

			if (jQuery('#generate_statistic_report_tab_selected').length) {
				jQuery('#generate_statistic_report_tab_selected').val(0);
			}
		});

		jQuery( "#periodic_report" ).on('change', function( event ) {
			
			if (jQuery(this).is(':checked')){
				jQuery('.wsal-save-button').text('Save Report');
			} else {
				jQuery('.wsal-save-button').text('Generate Report');
			}

		});

		if (jQuery('#generate_statistic_report_tab_selected').length) {
			jQuery('#generate_statistic_report_tab_selected').val(0);
		}

	</script>
