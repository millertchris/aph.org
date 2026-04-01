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
use WSAL\Reports\List_Generated_Reports;


$settings_url = \add_query_arg(
	array(
		'page' => Reports::get_safe_view_name(),
	),
	\network_admin_url( 'admin.php' )
) . '#wsal-options-tab-column-settings';


	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Generated & saved reports', 'wp-security-audit-log' ),
			'id'            => 'general-settings-tab',
			'type'          => 'tab-title',
			'settings_name' => Reports::GENERATE_REPORT_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'id'      => 'general-settings-tab',
			'type'    => 'html',
			'content' => '<p>' . \wp_sprintf(
				esc_html__( 'This section lists all previously generated reports.', 'wp-security-audit-log' ) . '</p>' .

				'<p>' . esc_html__( 'From here, you can download any report in CSV or HTML format, track the progress of reports that are currently being generated, or delete reports you no longer need.', 'wp-security-audit-log' ) . '</p>' .
				// translators: %1$s, %3$s are a HTML tags. %2$s is the link title.
				'<p>' . esc_html__( 'By default, the plugin retains reports for 30 days. Older reports are automatically deleted. To keep reports for a longer period, adjust the report retention setting in the %1$s%2$s%3$s.', 'wp-security-audit-log' ) . '</p>',
				'<a href="javascript:void(0);" onclick="window.location.href=(\'' . \esc_url( $settings_url ) . '\');  location.reload(); return false">',
				esc_html__( 'Report Settings', 'wp-security-audit-log' ),
				'</a>',
			),
		)
	);

	$events_list = new List_Generated_Reports( \WSAL_Views_AuditLog::get_page_arguments() );
	$events_list->prepare_items();
	?>
		<style>
			#saved-reports-viewer-content {
				margin-left: 5px;
				margin-right: 5px;
			}
		</style>
		<div id="saved-reports-viewer-content">
			<?php
			echo '<div style="clear:both; float:right">';
			$events_list->search_box(
				__( 'Search', 'wp-security-audit-log' ),
				strtolower( $events_list::get_table_name() ) . '-find'
			);
			echo '</div>';
			$events_list->display();
			?>
		</div>
		<script>
			
			jQuery( ".wsal-options-tab-saved-reports, #wsal-options-tab-saved-reports" ).on( "activated", function() {
				jQuery( ".wsal-save-button").css('display', 'none');
				jQuery('.wsal-save-button').text('Save changes');

				if (jQuery('#generate_report_tab_selected').length) {
					jQuery('#generate_report_tab_selected').val(0);
				}

				if (jQuery('#generate_statistic_report_tab_selected').length) {
					jQuery('#generate_statistic_report_tab_selected').val(0);
				}
			});
		</script>
