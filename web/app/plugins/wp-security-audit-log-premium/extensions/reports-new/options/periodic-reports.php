<?php
/**
 * Advanced settings of the plugin
 *
 * @package wsal
 *
 * @since 5.0.0
 */

use WSAL\Extensions\Views\Reports;
use WSAL\Reports\List_Periodic_Reports;
use WSAL\Helpers\Settings\Settings_Builder;

	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Configured periodic report', 'wp-security-audit-log' ),
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
				esc_html__( 'This section displays a list of all configured periodic reports.', 'wp-security-audit-log' ) . '</p>' .

				'<p>' . esc_html__( 'For each report, you can view key details such as the report name, the user who created it, the configured frequency (daily, weekly, monthly, or quarterly), the date it was last sent, its creation date, and the recipient email address.', 'wp-security-audit-log' ) . '</p>' .

				'<p>' . esc_html__( 'Use this overview to review, edit, duplicate, or audit your scheduled reports and ensure they are configured as expected.', 'wp-security-audit-log' ) . '</p>'
			),
		)
	);

	$events_list = new List_Periodic_Reports( \WSAL_Views_AuditLog::get_page_arguments() );
	$events_list->prepare_items();

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
				$events_list->search_box(
					__( 'Search', 'wp-security-audit-log' ),
					strtolower( $events_list::get_table_name() ) . '-find'
				);
				echo '</div>';
				// Display the audit log list.
				$events_list->display();
				?>
		</div>
		<script>
			
			jQuery( ".wsal-options-tab-periodic-reports-table, #wsal-options-tab-periodic-reports-table" ).on( "activated", function() {
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
