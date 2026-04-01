<?php
/**
 * View: WSAL Reports
 *
 * WSAL setup class file.
 *
 * @since 5.1.0
 * @package    wsal
 * @subpackage views
 */

declare(strict_types=1);

namespace WSAL\Extensions\Views;

use Tools\Select2_WPWS;
use WSAL\Helpers\WP_Helper;
use WSAL\Writers\CSV_Writer;
use WSAL\Helpers\File_Helper;
use WSAL\MainWP\MainWP_Addon;
use WSAL\Writers\HTML_Writer;
use WSAL\Entities\Base_Fields;
use WSAL\Helpers\Email_Helper;
use WSAL\Helpers\View_Manager;
use WSAL\MainWP\MainWP_Helper;
use WSAL\Controllers\Constants;
use WSAL\Controllers\Connection;
use WSAL\Entities\Reports_Entity;
use WSAL\Helpers\Settings_Helper;
use WSAL\Controllers\Alert_Manager;
use WSAL\Entities\Occurrences_Entity;
use WSAL\Entities\Generated_Reports_Entity;
use WSAL\Helpers\Settings\Settings_Builder;
use WSAL\Extensions\Helpers\Download_Helper;
use WSAL\Reports\Controllers\Statistic_Reports;
use WSAL\Extensions\Helpers\Reports_Data_Format;
use WSAL\Extensions\Helpers\Helper_Modifiers_Formatters;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( '\WSAL\Extensions\Views\Reports' ) ) {
	/**
	 * Class: WSAL reports.
	 *
	 * @package    wsal
	 * @subpackage views
	 *
	 * @since 5.0.0
	 */
	class Reports {
		public const REPORT_LIMIT                            = 500;
		public const GENERATE_REPORT_SETTINGS_NAME           = 'generate-report';
		public const GENERATE_STATISTIC_REPORT_SETTINGS_NAME = 'generate-statistic-report';
		public const REPORT_WHITE_LABEL_SETTINGS_NAME        = 'report-white-label-settings';
		public const REPORT_GENERATE_COLUMNS_SETTINGS_NAME   = 'report-generate-columns-settings';

		/**
		 * Pointer to the hook suffix
		 *
		 * @var string
		 *
		 * @since 5.0.0
		 */
		private static $hook_suffix = null;

		/**
		 * Holds the normalized header columns for the report generator.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $header_columns = array();

		/**
		 * Holds report columns and all the data related to them for the report generator.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $report_columns = array();

		/**
		 * Keeps status of the report (empty or not). To be used only when first wave is in place, can not be relied otherwise.
		 *
		 * @var array
		 *
		 * @since 5.0.0
		 */
		private static $empty_report = false;

		/**
		 * Initialize the report class
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function init() {
			if ( is_admin() ) {
				\add_action( 'wsal_init', array( __CLASS__, 'wsal_init' ), 20 );

				Settings_Builder::init();

				Download_Helper::init();

				/**
				 * Save Options
				 */
				\add_action( 'wp_ajax_generate_report_data_save', array( __CLASS__, 'save_settings_ajax' ) );

				Select2_WPWS::init( WSAL_BASE_URL . 'classes/Select2' );
			}

			if ( ( defined( '\DOING_CRON' ) && \DOING_CRON ) || ( isset( $_POST['action'] ) && 'acm/event/run' === $_POST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				\add_action( 'wsal_generate_reports', array( __CLASS__, 'generate_reports' ) );

				\add_action( 'wsal_generate_reports_daily', array( __CLASS__, 'generate_reports_daily' ) );
				\add_action( 'wsal_generate_reports_weekly', array( __CLASS__, 'generate_reports_weekly' ) );
				\add_action( 'wsal_generate_reports_monthly', array( __CLASS__, 'generate_reports_monthly' ) );
				\add_action( 'wsal_generate_reports_quarterly', array( __CLASS__, 'generate_reports_quarterly' ) );

				\add_action( 'wsal_clear_reports', array( __CLASS__, 'clear_the_reports' ) );
			}
		}

		/**
		 * Collects all the submitted reports data and saves them or generates a new report.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function save_settings_ajax() {
			if ( \check_ajax_referer( 'generate-report-data', 'wsal-security' ) ) {

				if ( ! \current_user_can( 'manage_options' ) ) {
					\wp_die( \esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
				}

				if ( isset( $_POST[ self::REPORT_WHITE_LABEL_SETTINGS_NAME ] ) && ! empty( $_POST[ self::REPORT_WHITE_LABEL_SETTINGS_NAME ] ) && \is_array( $_POST[ self::REPORT_WHITE_LABEL_SETTINGS_NAME ] ) ) {

					$data = \stripslashes_deep( $_POST[ self::REPORT_WHITE_LABEL_SETTINGS_NAME ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					self::white_label_check_and_save( $data );
				}

				if ( isset( $_POST[ self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME ] ) && ! empty( $_POST[ self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME ] ) && \is_array( $_POST[ self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME ] ) ) {

					if ( ! isset( $_POST['generate_statistic_report_tab_selected'] ) || 1 !== (int) $_POST['generate_statistic_report_tab_selected'] ) {
						$data = \stripslashes_deep( $_POST[ self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						self::generate_columns_check_and_save( $data );
					}
				}

				if ( isset( $_POST[ self::GENERATE_REPORT_SETTINGS_NAME ] ) && ! empty( $_POST[ self::GENERATE_REPORT_SETTINGS_NAME ] ) && \is_array( $_POST[ self::GENERATE_REPORT_SETTINGS_NAME ] ) ) {

					// We are on the general tab - so proceed with the report generating logic - otherwise - bounce.
					if ( isset( $_POST['generate_report_tab_selected'] ) && 1 === (int) $_POST['generate_report_tab_selected'] ) {

						$data         = \stripslashes_deep( $_POST[ self::GENERATE_REPORT_SETTINGS_NAME ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$return_value = self::generate_report_check_and_save( $data );

						if ( isset( $return_value['redirect'] ) && '' !== trim( (string) $return_value['redirect'] ) ) {
							\wp_send_json_success( array( 'redirect' => $return_value['redirect'] ) );
							exit;
						}

						if ( isset( $return_value['no_data'] ) ) {
							$error = new \WP_Error( '001', \esc_html__( 'Your criteria returned no data', 'wp-security-audit-log' ), 'Some information' );
							\wp_send_json_error( $error, 400 );
							exit;
						}
					}
				}

				if ( isset( $_POST[ self::GENERATE_STATISTIC_REPORT_SETTINGS_NAME ] ) && ! empty( $_POST[ self::GENERATE_STATISTIC_REPORT_SETTINGS_NAME ] ) && \is_array( $_POST[ self::GENERATE_STATISTIC_REPORT_SETTINGS_NAME ] ) ) {
					// We are on the statistic tab - so proceed with the statistic report generating logic - otherwise - bounce.
					if ( isset( $_POST['generate_statistic_report_tab_selected'] ) && 1 === (int) $_POST['generate_statistic_report_tab_selected'] ) {

						$data         = \stripslashes_deep( $_POST[ self::GENERATE_STATISTIC_REPORT_SETTINGS_NAME ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$return_value = self::generate_statistic_report_check_and_save( $data );

						if ( isset( $return_value['redirect'] ) && '' !== trim( (string) $return_value['redirect'] ) ) {
							\wp_send_json_success( array( 'redirect' => $return_value['redirect'] ) );
							exit;
						}

						if ( isset( $return_value['no_data'] ) ) {
							$error = new \WP_Error( '001', \esc_html__( 'Your criteria(es) returned no data', 'wp - security - audit - log' ), 'Some information' );
							\wp_send_json_error( $error, 400 );
							exit;
						}
					}
				}

				\wp_send_json_success( 2 );
			}
		}

		/**
		 * Checks and validates the data for the generate reports columns.
		 *
		 * @param array $post_array - The array with all the data provided.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		private static function generate_columns_check_and_save( array $post_array ) {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
			}

			$report_options = array();

			$enabled_columns = array();

			foreach ( \array_keys( self::get_report_columns() ) as $column_name ) {
				$report_options[ $column_name ]  = ( array_key_exists( 'column_' . $column_name . '_enabled', $post_array ) ) ? (bool) $post_array[ 'column_' . $column_name . '_enabled' ] : '';
				$enabled_columns[ $column_name ] = $report_options[ $column_name ];
			}

			$enabled_columns = array_filter( $enabled_columns );
			if ( empty( $enabled_columns ) ) {
				foreach ( self::get_report_columns() as $column_name => $column_values ) {
					if ( isset( $column_values['default'] ) ) {
						$report_options[ $column_name ] = $column_values['default'];
					}
				}
			}

			if ( isset( $post_array['sort_order'] ) ) {
				$selected_sort_order = \sanitize_text_field( \wp_unslash( $post_array['sort_order'] ) );

				$selected_sort_order = explode( ',', $post_array['sort_order'] );

				$selected_sort_order = (array) $selected_sort_order;

				$selected_sort_order = array_map( 'trim', $selected_sort_order );

				$selected_sort_order = \array_flip( $selected_sort_order );

				$sort_order = array();

				foreach ( \array_keys( self::get_report_columns() ) as $column_name ) {
					foreach ( $selected_sort_order as $key => $value ) {
						if ( \str_starts_with( $key, 'column_' . $column_name . '_' ) || $key === $column_name ) {
							$sort_order[ $column_name ] = array( 'order' => $value );
							break;
						}
					}
				}

				$report_options['sort_order'] = $sort_order;

			} else {
				$report_options['sort_order'] = array();
			}

			$report_options['cron_records_to_process'] = ( array_key_exists( 'cron_records_to_process', $post_array ) ) ? (int) $post_array['cron_records_to_process'] : self::REPORT_LIMIT;

			$report_options['reports_auto_purge_enabled'] = ( array_key_exists( 'reports_auto_purge_enabled', $post_array ) ) ? filter_var( $post_array['reports_auto_purge_enabled'], FILTER_VALIDATE_BOOLEAN ) : false;

			if ( $report_options['reports_auto_purge_enabled'] ) {
				$report_options['reports_auto_purge_older_than_days'] = ( array_key_exists( 'reports_auto_purge_older_than_days', $post_array ) ) ? (int) $post_array['reports_auto_purge_older_than_days'] : 30;
			}

			$report_options['report_send_time'] = ( array_key_exists( 'report_send_time', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['report_send_time'] ) ) : '08:00';

			$report_options['reports_send_empty_summary_emails'] = ( array_key_exists( 'reports_send_empty_summary_emails', $post_array ) ) ? filter_var( $post_array['reports_send_empty_summary_emails'], FILTER_VALIDATE_BOOLEAN ) : false;

			$report_options['reports_send_reports_attachments_emails'] = ( array_key_exists( 'reports_send_reports_attachments_emails', $post_array ) ) ? filter_var( $post_array['reports_send_reports_attachments_emails'], FILTER_VALIDATE_BOOLEAN ) : false;

			if ( ! empty( $post_array ) ) {
				self::clear_reps();
			}

			if ( empty( $report_options ) ) {
				Settings_Helper::delete_option_value( self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME );
			} else {
				Settings_Helper::set_option_value( self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME, $report_options );
			}

			return $report_options;
		}

		/**
		 * Checks and validates the data for the white labeling.
		 *
		 * @param array $post_array - The array with all the data provided.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		private static function white_label_check_and_save( array $post_array ) {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
			}

			$report_options = array();

			$report_options['business_name'] = ( array_key_exists( 'business_name', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['business_name'] ) ) : '';
			$report_options['name_surname']  = ( array_key_exists( 'name_surname', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['name_surname'] ) ) : '';
			$report_options['email']         = ( array_key_exists( 'email', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['email'] ) ) : '';
			$report_options['phone_number']  = ( array_key_exists( 'phone_number', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['phone_number'] ) ) : '';
			$report_options['logo']          = ( array_key_exists( 'logo', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['logo'] ) ) : '';
			$report_options['logo_url']      = ( array_key_exists( 'logo_url', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['logo_url'] ) ) : '';

			if ( empty( $report_options ) ) {
				Settings_Helper::delete_option_value( self::REPORT_WHITE_LABEL_SETTINGS_NAME );
			} else {
				Settings_Helper::set_option_value( self::REPORT_WHITE_LABEL_SETTINGS_NAME, $report_options );
			}
		}

		/**
		 * Checks and validates the data for the reports provided and stores or creates the report.
		 *
		 * @param array $post_array - The array with all the data provided.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		private static function generate_statistic_report_check_and_save( array $post_array ) {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
			}

			Statistic_Reports::prepare_and_generate_report( $post_array );

			if ( Statistic_Reports::get_empty_report() ) {

				return array( 'no_data' => true );
			} else {
				$query_args_view_data = array(
					'page'     => self::get_safe_view_name(),
					'act'      => \wp_rand(), // Forces URL reloading via JS.
					'_wpnonce' => \wp_create_nonce( 'view_data_nonce' ),
				);
				$admin_page_url       = \network_admin_url( 'admin.php' );
				$view_data_link       = add_query_arg( $query_args_view_data, $admin_page_url );

				return array( 'redirect' => $view_data_link . '#wsal-options-tab-saved-reports' );
			}
		}

		/**
		 * Checks and validates the data for the reports provided and stores or creates the report.
		 *
		 * @param array $post_array - The array with all the data provided.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		private static function generate_report_check_and_save( array $post_array ) {
			if ( ! \current_user_can( 'manage_options' ) ) {
				\wp_die( \esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-security-audit-log' ) );
			}

			$report_options = array();

			if ( WP_Helper::is_multisite() || MainWP_Addon::check_mainwp_plugin_active() ) {
				/**
				 * Sites part collecting
				 */
				$report_options['report_type_sites'] = ( array_key_exists( 'report_type_sites', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['report_type_sites'] ) ) : '';

				if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_sites'] ] ) ) {
					$report_options['report_type_sites'] = '';
				} elseif ( 'only_these' === $report_options['report_type_sites'] ) {
					$report_options['only_these_sites'] = ( array_key_exists( 'only_these_sites', $post_array ) ) ? array_map( 'intval', (array) $post_array['only_these_sites'] ) : array();

					if ( empty( $report_options['only_these_sites'] ) ) {
						// The type is "only_these" but there are no sites selected - remove the type also.
						$report_options['report_type_sites'] = '';
						unset( $report_options['only_these_sites'] );
					}
				} elseif ( 'all_except' === $report_options['report_type_sites'] ) {
					$report_options['except_these_sites'] = ( array_key_exists( 'except_these_sites', $post_array ) ) ? array_map( 'intval', (array) $post_array['except_these_sites'] ) : array();

					if ( empty( $report_options['except_these_sites'] ) ) {
						// The type is "all_except" but there are no sites selected - remove the type also.
						$report_options['report_type_sites'] = '';
						unset( $report_options['except_these_sites'] );
					}
				}
			}

			/**
			 * Users part collecting
			 */
			$report_options['report_type_users'] = ( array_key_exists( 'report_type_users', $post_array ) ) ? $post_array['report_type_users'] : '';

			$possible_types = array_merge(
				self::get_generate_report_types_selector(),
				array(
					'all_domain' => \esc_html__( 'All users with this domain in their email address', 'wp-security-audit-log' ),
				)
			);

			if ( ! isset( $possible_types[ $report_options['report_type_users'] ] ) ) {
				$report_options['report_type_users'] = '';
			} elseif ( 'only_these' === $report_options['report_type_users'] ) {
				$report_options['only_these_users'] = ( array_key_exists( 'only_these_users', $post_array ) ) ? array_map( 'intval', (array) $post_array['only_these_users'] ) : array();

				if ( empty( $report_options['only_these_users'] ) ) {
					// The type is "only_these" but there are no users selected - remove the type also.
					$report_options['report_type_users'] = '';
					unset( $report_options['only_these_users'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_users'] ) {
				$report_options['except_these_users'] = ( array_key_exists( 'except_these_users', $post_array ) ) ? array_map( 'intval', (array) $post_array['except_these_users'] ) : array();

				if ( empty( $report_options['except_these_users'] ) ) {
					// The type is "all_except" but there are no users selected - remove the type also.
					$report_options['report_type_users'] = '';
					unset( $report_options['except_these_users'] );
				}
			} elseif ( 'all_domain' === $report_options['report_type_users'] ) {
				$report_options['all_users_domain'] = ( array_key_exists( 'all_users_domain', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['all_users_domain'] ) ) : '';

				if ( empty( $report_options['all_users_domain'] ) ) {
					// The type is "all_except" but there are no users selected - remove the type also.
					$report_options['report_type_users'] = '';
					unset( $report_options['all_users_domain'] );
				}
			}

			/**
			 * Roles part collecting
			 */
			$report_options['report_type_roles'] = ( array_key_exists( 'report_type_roles', $post_array ) ) ? $post_array['report_type_roles'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_roles'] ] ) ) {
				$report_options['report_type_roles'] = '';
			} elseif ( 'only_these' === $report_options['report_type_roles'] ) {
				$report_options['only_these_roles'] = ( array_key_exists( 'only_these_roles', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_roles'] ) : array();

				if ( empty( $report_options['only_these_roles'] ) ) {
					// The type is "only_these" but there are no roles selected - remove the type also.
					$report_options['report_type_roles'] = '';
					unset( $report_options['only_these_roles'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_roles'] ) {
				$report_options['except_these_roles'] = ( array_key_exists( 'except_these_roles', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_roles'] ) : array();

				if ( empty( $report_options['except_these_roles'] ) ) {
					// The type is "all_except" but there are no roles selected - remove the type also.
					$report_options['report_type_roles'] = '';
					unset( $report_options['except_these_roles'] );
				}
			}

			/**
			 * IPs part collecting
			 */
			$report_options['report_type_ips'] = ( array_key_exists( 'report_type_ips', $post_array ) ) ? $post_array['report_type_ips'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_ips'] ] ) ) {
				$report_options['report_type_ips'] = '';
			} elseif ( 'only_these' === $report_options['report_type_ips'] ) {
				$report_options['only_these_ips'] = ( array_key_exists( 'only_these_ips', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_ips'] ) : array();

				if ( empty( $report_options['only_these_ips'] ) ) {
					// The type is "only_these" but there are no ips selected - remove the type also.
					$report_options['report_type_ips'] = '';
					unset( $report_options['only_these_ips'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_ips'] ) {
				$report_options['except_these_ips'] = ( array_key_exists( 'except_these_ips', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_ips'] ) : array();

				if ( empty( $report_options['except_these_ips'] ) ) {
					// The type is "all_except" but there are no ips selected - remove the type also.
					$report_options['report_type_ips'] = '';
					unset( $report_options['except_these_ips'] );
				}
			}

			/**
			 * Objects part collecting
			 */
			$report_options['report_type_objects'] = ( array_key_exists( 'report_type_objects', $post_array ) ) ? $post_array['report_type_objects'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_objects'] ] ) ) {
				$report_options['report_type_objects'] = '';
			} elseif ( 'only_these' === $report_options['report_type_objects'] ) {
				$report_options['only_these_objects'] = ( array_key_exists( 'only_these_objects', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_objects'] ) : array();

				if ( empty( $report_options['only_these_objects'] ) ) {
					// The type is "only_these" but there are no objects selected - remove the type also.
					$report_options['report_type_objects'] = '';
					unset( $report_options['only_these_objects'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_objects'] ) {
				$report_options['except_these_objects'] = ( array_key_exists( 'except_these_objects', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_objects'] ) : array();

				if ( empty( $report_options['except_these_objects'] ) ) {
					// The type is "all_except" but there are no objects selected - remove the type also.
					$report_options['report_type_objects'] = '';
					unset( $report_options['except_these_objects'] );
				}
			}

			/**
			 * Event type part collecting
			 */
			$report_options['report_type_event_types'] = ( array_key_exists( 'report_type_event_types', $post_array ) ) ? $post_array['report_type_event_types'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_event_types'] ] ) ) {
				$report_options['report_type_event_types'] = '';
			} elseif ( 'only_these' === $report_options['report_type_event_types'] ) {
				$report_options['only_these_event_types'] = ( array_key_exists( 'only_these_event_types', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_event_types'] ) : array();

				if ( empty( $report_options['only_these_event_types'] ) ) {
					// The type is "only_these" but there are no event_types selected - remove the type also.
					$report_options['report_type_event_types'] = '';
					unset( $report_options['only_these_event_types'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_event_types'] ) {
				$report_options['except_these_event_types'] = ( array_key_exists( 'except_these_event_types', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_event_types'] ) : array();

				if ( empty( $report_options['except_these_event_types'] ) ) {
					// The type is "all_except" but there are no event_types selected - remove the type also.
					$report_options['report_type_event_types'] = '';
					unset( $report_options['except_these_event_types'] );
				}
			}

			/**
			 * Post titles type part collecting
			 */
			$report_options['report_type_post_titles'] = ( array_key_exists( 'report_type_post_titles', $post_array ) ) ? $post_array['report_type_post_titles'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_post_titles'] ] ) ) {
				$report_options['report_type_post_titles'] = '';
			} elseif ( 'only_these' === $report_options['report_type_post_titles'] ) {
				$report_options['only_these_post_titles'] = ( array_key_exists( 'only_these_post_titles', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_post_titles'] ) : array();

				if ( empty( $report_options['only_these_post_titles'] ) ) {
					// The type is "only_these" but there are no post_titles selected - remove the type also.
					$report_options['report_type_post_titles'] = '';
					unset( $report_options['only_these_post_titles'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_post_titles'] ) {
				$report_options['except_these_post_titles'] = ( array_key_exists( 'except_these_post_titles', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_post_titles'] ) : array();

				if ( empty( $report_options['except_these_post_titles'] ) ) {
					// The type is "all_except" but there are no post_titles selected - remove the type also.
					$report_options['report_type_post_titles'] = '';
					unset( $report_options['except_these_post_titles'] );
				}
			}

			/**
			 * Post type part collecting
			 */
			$report_options['report_type_post_types'] = ( array_key_exists( 'report_type_post_types', $post_array ) ) ? $post_array['report_type_post_types'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_post_types'] ] ) ) {
				$report_options['report_type_post_types'] = '';
			} elseif ( 'only_these' === $report_options['report_type_post_types'] ) {
				$report_options['only_these_post_types'] = ( array_key_exists( 'only_these_post_types', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_post_types'] ) : array();

				if ( empty( $report_options['only_these_post_types'] ) ) {
					// The type is "only_these" but there are no post_types selected - remove the type also.
					$report_options['report_type_post_types'] = '';
					unset( $report_options['only_these_post_types'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_post_types'] ) {
				$report_options['except_these_post_types'] = ( array_key_exists( 'except_these_post_types', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_post_types'] ) : array();

				if ( empty( $report_options['except_these_post_types'] ) ) {
					// The type is "all_except" but there are no post_types selected - remove the type also.
					$report_options['report_type_post_types'] = '';
					unset( $report_options['except_these_post_types'] );
				}
			}

			/**
			 * Post statuses part collecting
			 */
			$report_options['report_type_post_statuses'] = ( array_key_exists( 'report_type_post_statuses', $post_array ) ) ? $post_array['report_type_post_statuses'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_post_statuses'] ] ) ) {
				$report_options['report_type_post_statuses'] = '';
			} elseif ( 'only_these' === $report_options['report_type_post_statuses'] ) {
				$report_options['only_these_post_statuses'] = ( array_key_exists( 'only_these_post_statuses', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_post_statuses'] ) : array();

				if ( empty( $report_options['only_these_post_statuses'] ) ) {
					// The type is "only_these" but there are no post_statuses selected - remove the type also.
					$report_options['report_type_post_statuses'] = '';
					unset( $report_options['only_these_post_statuses'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_post_statuses'] ) {
				$report_options['except_these_post_statuses'] = ( array_key_exists( 'except_these_post_statuses', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_post_statuses'] ) : array();

				if ( empty( $report_options['except_these_post_statuses'] ) ) {
					// The type is "all_except" but there are no post_statuses selected - remove the type also.
					$report_options['report_type_post_statuses'] = '';
					unset( $report_options['except_these_post_statuses'] );
				}
			}

			/**
			 * Post ids part collecting
			 */
			$report_options['report_type_post_ids'] = ( array_key_exists( 'report_type_post_ids', $post_array ) ) ? $post_array['report_type_post_ids'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_post_ids'] ] ) ) {
				$report_options['report_type_post_ids'] = '';
			} elseif ( 'only_these' === $report_options['report_type_post_ids'] ) {
				$report_options['only_these_post_ids'] = ( array_key_exists( 'only_these_post_ids', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_post_ids'] ) : array();

				if ( empty( $report_options['only_these_post_ids'] ) ) {
					// The type is "only_these" but there are no post_ids selected - remove the type also.
					$report_options['report_type_post_ids'] = '';
					unset( $report_options['only_these_post_ids'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_post_ids'] ) {
				$report_options['except_these_post_ids'] = ( array_key_exists( 'except_these_post_ids', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_post_ids'] ) : array();

				if ( empty( $report_options['except_these_post_ids'] ) ) {
					// The type is "all_except" but there are no post_ids selected - remove the type also.
					$report_options['report_type_post_ids'] = '';
					unset( $report_options['except_these_post_ids'] );
				}
			}

			/**
			 * Alert IDs part collecting
			 */
			$report_options['report_type_alert_ids'] = ( array_key_exists( 'report_type_alert_ids', $post_array ) ) ? $post_array['report_type_alert_ids'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_alert_ids'] ] ) ) {
				$report_options['report_type_alert_ids'] = '';
			} elseif ( 'only_these' === $report_options['report_type_alert_ids'] ) {
				$report_options['only_these_alert_ids'] = ( array_key_exists( 'only_these_alert_ids', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_alert_ids'] ) : array();

				if ( empty( $report_options['only_these_alert_ids'] ) ) {
					// The type is "only_these" but there are no alert_ids selected - remove the type also.
					$report_options['report_type_alert_ids'] = '';
					unset( $report_options['only_these_alert_ids'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_alert_ids'] ) {
				$report_options['except_these_alert_ids'] = ( array_key_exists( 'except_these_alert_ids', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_alert_ids'] ) : array();

				if ( empty( $report_options['except_these_alert_ids'] ) ) {
					// The type is "all_except" but there are no alert_ids selected - remove the type also.
					$report_options['report_type_alert_ids'] = '';
					unset( $report_options['except_these_alert_ids'] );
				}
			}

			/**
			 * Alert Groups part collecting
			 */
			$report_options['report_type_alert_groups'] = ( array_key_exists( 'report_type_alert_groups', $post_array ) ) ? $post_array['report_type_alert_groups'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_alert_groups'] ] ) ) {
				$report_options['report_type_alert_groups'] = '';
			} elseif ( 'only_these' === $report_options['report_type_alert_groups'] ) {
				$report_options['only_these_alert_groups'] = ( array_key_exists( 'only_these_alert_groups', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_alert_groups'] ) : array();

				if ( empty( $report_options['only_these_alert_groups'] ) ) {
					// The type is "only_these" but there are no alert_groups selected - remove the type also.
					$report_options['report_type_alert_groups'] = '';
					unset( $report_options['only_these_alert_groups'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_alert_groups'] ) {
				$report_options['except_these_alert_groups'] = ( array_key_exists( 'except_these_alert_groups', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_alert_groups'] ) : array();

				if ( empty( $report_options['except_these_alert_groups'] ) ) {
					// The type is "all_except" but there are no alert_groups selected - remove the type also.
					$report_options['report_type_alert_groups'] = '';
					unset( $report_options['except_these_alert_groups'] );
				}
			}

			/**
			 * Severities part collecting
			 */
			$report_options['report_type_severities'] = ( array_key_exists( 'report_type_severities', $post_array ) ) ? $post_array['report_type_severities'] : '';

			if ( ! isset( self::get_generate_report_types_selector()[ $report_options['report_type_severities'] ] ) ) {
				$report_options['report_type_severities'] = '';
			} elseif ( 'only_these' === $report_options['report_type_severities'] ) {
				$report_options['only_these_severities'] = ( array_key_exists( 'only_these_severities', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['only_these_severities'] ) : array();

				if ( empty( $report_options['only_these_severities'] ) ) {
					// The type is "only_these" but there are no severities selected - remove the type also.
					$report_options['report_type_severities'] = '';
					unset( $report_options['only_these_severities'] );
				}
			} elseif ( 'all_except' === $report_options['report_type_severities'] ) {
				$report_options['except_these_severities'] = ( array_key_exists( 'except_these_severities', $post_array ) ) ? array_map( 'sanitize_text_field', (array) $post_array['except_these_severities'] ) : array();

				if ( empty( $report_options['except_these_severities'] ) ) {
					// The type is "all_except" but there are no severities selected - remove the type also.
					$report_options['report_type_severities'] = '';
					unset( $report_options['except_these_severities'] );
				}
			}

			// Dates grabbing.
			$report_options['report_start_date'] = ( array_key_exists( 'report_start_date', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['report_start_date'] ) ) : '';
			$report_options['report_end_date']   = ( array_key_exists( 'report_end_date', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['report_end_date'] ) ) : '';

			// Report tag.
			$report_options['report_tag'] = ( array_key_exists( 'report_tag', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['report_tag'] ) ) : '';

			// Include archive.
			$report_options['report_include_archive'] = ( array_key_exists( 'report_include_archive', $post_array ) ) ? filter_var( $post_array['report_include_archive'], FILTER_VALIDATE_BOOLEAN ) : false;

			if ( $report_options['report_include_archive'] ) {
				$report_options['report_only_archive'] = ( array_key_exists( 'report_only_archive', $post_array ) ) ? filter_var( $post_array['report_only_archive'], FILTER_VALIDATE_BOOLEAN ) : false;
			} else {
				$report_options['report_only_archive'] = false;
			}

			// Report additional information.
			$report_options['report_title']    = ( array_key_exists( 'report_title', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['report_title'] ) ) : '';
			$report_options['report_title']    = ( mb_strlen( $report_options['report_title'] ) > 100 ) ? mb_substr( $report_options['report_title'], 0, 100 ) . '...' : $report_options['report_title'];
			$report_options['report_comment']  = ( array_key_exists( 'report_comment', $post_array ) ) ? \sanitize_text_field( \wp_unslash( $post_array['report_comment'] ) ) : '';
			$report_options['report_metadata'] = ( array_key_exists( 'report_metadata', $post_array ) ) ? filter_var( $post_array['report_metadata'], FILTER_VALIDATE_BOOLEAN ) : false;

			$save_report = false;
			// If we have to save the report - should we also redirect the user to the newly saved report one or it is already on the edit page?
			$redirect = true;

			if ( array_key_exists( 'periodic_report', $post_array ) ) {
				$save_report = true;
				$report_data = array(
					'report_name'      => ( ( isset( $post_array['generic_report_name'] ) ) ? \sanitize_text_field( \wp_unslash( $post_array['generic_report_name'] ) ) : 'No_name' ),
					'report_tag'       => ( ( isset( $report_options['report_tag'] ) ) ? mb_substr( \sanitize_text_field( \wp_unslash( $report_options['report_tag'] ) ), 0, 100 ) : '' ),
					'report_frequency' => ( ( isset( $post_array['generic_report_period'] ) ) ? (int) $post_array['generic_report_period'] : 1 ),
					'report_email'     => ( ( isset( $post_array['generic_report_email'] ) ) ? \sanitize_text_field( \wp_unslash( $post_array['generic_report_email'] ) ) : Email_Helper::get_default_email_to() ),
					'report_data'      => $report_options,
					'report_disabled'  => ( ( isset( $post_array['generic_report_disabled'] ) ) ? filter_var( $post_array['generic_report_disabled'], FILTER_VALIDATE_BOOLEAN ) : false ),
				);
			}

			if ( array_key_exists( 'generated_report_id', $post_array ) ) {
				$save_report       = true;
				$redirect          = false;
				$report_data['id'] = $post_array['generated_report_id'];
			}

			if ( $save_report ) {

				$user_id = \get_current_user_id();
				if ( ! $user_id ) {
					$username = \__( 'System', 'wp-security-audit-log' );
				} else {
					$username = \get_userdata( $user_id )->user_login;
				}

				$report_data['report_user_id']  = $user_id;
				$report_data['report_username'] = $username;

				$last_id = Reports_Entity::save( $report_data );
				if ( $redirect ) {

					$query_args_view_data = array(
						'page'     => self::get_safe_view_name(),
						'action'   => 'view_data',
						Reports_Entity::get_table_name() . '_id' => absint( $last_id ),
						'_wpnonce' => \wp_create_nonce( 'view_data_nonce' ),
					);

					$admin_page_url = \network_admin_url( 'admin.php' );
					$view_data_link = add_query_arg( $query_args_view_data, $admin_page_url );

					return array( 'redirect' => $view_data_link );
				}
			} else {
				self::generate_report( $report_options );

				if ( self::$empty_report ) {

					return array( 'no_data' => true );
				} else {
					$query_args_view_data = array(
						'page'     => self::get_safe_view_name(),
						'act'      => \wp_rand(), // Forces URL reloading via JS.
						'_wpnonce' => \wp_create_nonce( 'view_data_nonce' ),
					);
					$admin_page_url       = \network_admin_url( 'admin.php' );
					$view_data_link       = \add_query_arg( $query_args_view_data, $admin_page_url );

					return array( 'redirect' => $view_data_link . '#wsal-options-tab-saved-reports' );
				}
			}

			return array( 'redirect' => false );
		}

		/**
		 * Hooks on the main plugin init process
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function wsal_init() {
			View_Manager::add_from_class( __CLASS__ );
		}

		/**
		 * Override this and make it return true to create a shortcut link in plugin page to the view.
		 *
		 * @return boolean
		 *
		 * @since 5.0.0
		 */
		public static function has_plugin_shortcut_link() {
			return false;
		}

		/**
		 * Stores the view weight (where is should be positioned in the menu).
		 *
		 * @since 5.0.0
		 */
		public static function get_weight() {
			return 3;
		}

		/**
		 * Method: Whether page should be accessible or not.
		 *
		 * @return boolean
		 *
		 * @since 5.0.0
		 */
		public static function is_accessible() {
			return true;
		}

		/**
		 * Method: Safe view menu name.
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_safe_view_name() {
			return 'wsal-reports-new';
		}

		/**
		 * Returns the view name
		 *
		 * @since 5.0.0
		 */
		public static function get_title() {
			return \esc_html__( 'Reports', 'wp-security-audit-log' );
		}

		/**
		 * Returns the view name
		 *
		 * @since 5.0.0
		 */
		public static function get_name() {
			return \esc_html__( 'Reports', 'wp-security-audit-log' );
		}

		/**
		 * Method: Whether page should appear in menu or not.
		 *
		 * @return boolean
		 *
		 * @since 5.0.0
		 */
		public static function is_visible() {
			return true;
		}

		/**
		 * Sets the hook suffix
		 *
		 * @param string $suffix - The hook suffix.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function set_hook_suffix( $suffix ) {
			self::$hook_suffix = $suffix;
		}

		/**
		 * Returns the hook suffix
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function get_hook_suffix() {
			return self::$hook_suffix;
		}

		/**
		 * Draws the header
		 *
		 * @since 5.0.0
		 */
		public static function header() {
			Select2_WPWS::enqueue_scripts();

			\wp_enqueue_script(
				'wsal-reports-admin-scripts',
				WSAL_BASE_URL . 'classes/Helpers/settings/admin/wsal-settings.js',
				array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'wp-color-picker', 'jquery-ui-autocomplete', 'wpw-select2' ),
				WSAL_VERSION,
				false
			);

			\wp_enqueue_style(
				'wsal-reports-admin-style',
				WSAL_BASE_URL . 'classes/Helpers/settings/admin/style.css',
				array(),
				WSAL_VERSION,
				'all'
			);
		}

		/**
		 * Draws the footer
		 *
		 * @since 5.0.0
		 */
		public static function footer() {
		}

		/**
		 * Renders the view icon (this has been deprecated in newer WP versions).
		 *
		 * @since 5.0.0
		 */
		public static function render_icon() {
			?>
			<div id="icon-plugins" class="icon32"><br></div>
			<?php
		}

		/**
		 * Renders the view title.
		 *
		 * @since 5.0.0
		 */
		public static function render_title() {
			echo '<h2>' . esc_html( self::get_title() ) . '</h2>';
		}

		/**
		 * Method: Render content of the view.
		 *
		 * @since 5.0.0
		 */
		public static function render_content() {
			if ( ! Settings_Helper::current_user_can( 'edit' ) ) {
				$network_admin = get_site_option( 'admin_email' );
				$message       = esc_html__( 'To generate a report or configure automated scheduled report please contact the administrator of this multisite network on ', 'wp-security-audit-log' );
				$message      .= '<a href="mailto:' . esc_attr( $network_admin ) . '" target="_blank">' . esc_html( $network_admin ) . '</a>';
				\wp_die( $message ); // phpcs:ignore
			}

			// Verify the uploads directory.
			$reports_working_dir = Settings_Helper::get_working_dir_path_static( 'reports' );

			if ( ! \is_wp_error( $reports_working_dir ) && self::check_directory( $reports_working_dir ) ) {
				$plugin_dir = realpath( __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR );

				self::content_show();

				return;
			}

			// Skip creation this time to get the path for the prompt even if the folder cannot be created.
			$reports_working_dir = Settings_Helper::get_working_dir_path_static( 'reports', true );
			?>
			<div class="error">
				<p><?php printf( __( 'The %s directory which the Reports plugin uses to create reports in was either not found or is not accessible.', 'wp-security-audit-log' ), 'uploads' ); // phpcs:ignore ?></p>
				<p>
			<?php
			printf(
				// translators: 1: directory name, 2: contact support link
				\esc_html__( 'In order for the plugin to function, the directory %1$s must be created and the plugin should have access to write to this directory, so please configure the following permissions: 0755. If you have any questions or need further assistance please %2$s', 'wp-security-audit-log' ),
				'<strong>' . $reports_working_dir . '</strong>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'<a href="mailto:support@melapress.com">contact us</a>'
			);
			?>
				</p>
			</div>
			<?php
		}

		/**
		 * Shows the view of the selected options
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function content_show() {

			\wp_enqueue_script( 'jquery-ui-sortable' );

			\wp_enqueue_media();

			$settings_tabs = array(
				'generate'          => array(
					'icon'  => 'admin-generic',
					'title' => esc_html__( 'Generate reports / configure periodic reports', 'wp-security-audit-log' ),
				),

				'statistic-reports' => array(
					'icon'  => 'admin-generic',
					'title' => esc_html__( 'Generate statistics reports', 'wp-security-audit-log' ),
				),

				'white-label'       => array(
					'icon'  => 'lightbulb',
					'title' => esc_html__( 'White labelling settings', 'wp-security-audit-log' ),
				),

				'column-settings'   => array(
					'icon'  => 'welcome-widgets-menus ',
					'title' => esc_html__( 'Reports settings', 'wp-security-audit-log' ),
				),
			);

			$generated_report_id_hidden_field = '';

			if ( isset( $_REQUEST['action'] ) && 'view_data' === $_REQUEST['action'] && isset( $_REQUEST['_wpnonce'] ) && isset( $_REQUEST[ Reports_Entity::get_table_name() . '_id' ] ) ) {

				$nonce = \sanitize_text_field( \wp_unslash( $_REQUEST['_wpnonce'] ) );
				// verify the nonce.
				if ( wp_verify_nonce( $nonce, 'view_data_nonce' ) ) {

					$generated_reports_data = Reports_Entity::load_array( 'id=%d', array( absint( $_REQUEST[ Reports_Entity::get_table_name() . '_id' ] ) ) );

					$settings                            = \json_decode( $generated_reports_data[0]['report_data'], true );
					$settings['generic_report_name']     = $generated_reports_data[0]['report_name'];
					$settings['generic_report_email']    = $generated_reports_data[0]['report_email'];
					$settings['generic_report_period']   = (int) $generated_reports_data[0]['report_frequency'];
					$settings['periodic_report']         = true;
					$settings['generic_report_disabled'] = (bool) $generated_reports_data[0]['report_disabled'];

					Settings_Builder::set_current_options( $settings );

					$generated_report_id_hidden_field = '<input type="hidden" name="' . self::GENERATE_REPORT_SETTINGS_NAME . '[generated_report_id]" value="' . absint( $_REQUEST[ Reports_Entity::get_table_name() . '_id' ] ) . '">';

				}
			}

			?>
			<div id="wsal-page-overlay"></div>

			<div id="wsal-saving-settings">
				<svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
					<circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
					<path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
					<path class="checkmark__error_1" d="M38 38 L16 16 Z" />
					<path class="checkmark__error_2" d="M16 38 38 16 Z" />
				</svg>
			</div>

			<div class="wsal-panel wrap">

				<div class="wsal-panel-tabs">
					
					<ul>
			<?php

			if ( empty( $generated_report_id_hidden_field ) ) {

				foreach ( $settings_tabs as $tab => $settings ) {

					$icon  = $settings['icon'];
					$title = $settings['title'];
					?>

							<li class="wsal-tabs wsal-options-tab-<?php echo \esc_attr( $tab ); ?>">
								<a href="#wsal-options-tab-<?php echo \esc_attr( $tab ); ?>">
									<span class="dashicons-before dashicons-<?php echo \esc_html( $icon ); ?> wsal-icon-menu"></span>
					<?php echo \esc_html( $title ); ?>
								</a>
							</li>
					<?php
				}
				?>

							<li class="wsal-tabs wsal-options-tab-periodic-reports-table">
								<a href="#wsal-options-tab-periodic-reports-table">
									<span class="dashicons-before dashicons-admin-page wsal-icon-menu"></span>
					<?php echo \esc_html__( 'Configured periodic reports', 'wp-security-audit-log' ); ?>
								</a>
							</li>

							<li class="wsal-tabs wsal-options-tab-saved-reports">
								<a href="#wsal-options-tab-saved-reports">
									<span class="dashicons-before dashicons-text-page wsal-icon-menu"></span>
					<?php echo \esc_html__( 'Generated & saved reports', 'wp-security-audit-log' ); ?>
								</a>
							</li>
				<?php
			} else {
				self::get_report_back_link();
			}
			?>
					</ul>
					<div class="clear"></div>
				</div> <!-- .wsal-panel-tabs -->

				<div class="wsal-panel-content">

					<form method="post" name="wsal_form" id="wsal_form" enctype="multipart/form-data">

			<?php
			echo $generated_report_id_hidden_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			foreach ( $settings_tabs as $tab => $settings ) {

				?>
						<!-- <?php echo \esc_attr( $tab ); ?> Settings -->
						<div id="wsal-options-tab-<?php echo \esc_attr( $tab ); ?>" class="tabs-wrap">

					<?php
					include_once __DIR__ . '/options/' . $tab . '.php';
					?>

						</div>
					<?php
			}
			?>

			<?php \wp_nonce_field( 'generate-report-data', 'wsal-security' ); ?>
						<input type="hidden" name="action" value="generate_report_data_save" />

						<div class="wsal-footer">
				<?php \do_action( 'wsal_settings_save_button' ); ?>
						</div>
					</form>
					<form id="periodic-report-viewer" method="get">
						<input type="hidden" name="page" value="<?php echo esc_attr( \WSAL_Views_AuditLog::get_page_arguments()['page'] ); ?>" />

						<div id="wsal-options-tab-periodic-reports-table" class="tabs-wrap">
 
				<?php
				include_once __DIR__ . '/options/periodic-reports.php';
				?>
						</div>
					</form>
					<form id="saved-reports-viewer" method="get">
						<input type="hidden" name="page" value="<?php echo esc_attr( \WSAL_Views_AuditLog::get_page_arguments()['page'] ); ?>" />
						<div id="wsal-options-tab-saved-reports" class="tabs-wrap">

					<?php
					include_once __DIR__ . '/options/saved-reports.php';
					?>

						</div>
					</form>
				</div><!-- .wsal-panel-content -->
				<div class="clear"></div>

			</div><!-- .wsal-panel -->
				<?php if ( ! isset( $_GET['action'] ) || ( isset( $_GET['action'] ) && 'view_data' !== $_GET['action'] ) ) { ?>
			<script>
				jQuery('.wsal-save-button').text('Generate Report');
			</script>
					<?php
				}
		}

		/**
		 * Check to see whether the specified directory is accessible.
		 *
		 * @param string $dir_path - Directory Path.
		 *
		 * @return bool
		 *
		 * @since 5.0.0
		 */
		private static function check_directory( $dir_path ) {
			if ( ! is_dir( $dir_path ) ) {
				return false;
			}
			if ( ! is_readable( $dir_path ) ) {
				return false;
			}
			if ( ! is_writable( $dir_path ) ) {
				return false;
			}
			// Create the index.php file if not already there.
			File_Helper::create_index_file( $dir_path );

			return true;
		}

		/**
		 * Names of all the users types
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_generate_report_types_selector(): array {
			return array(
				''           => esc_html__( 'All', 'wp-security-audit-log' ),
				'only_these' => esc_html__( 'These specific', 'wp-security-audit-log' ),
				'all_except' => esc_html__( 'All except these', 'wp-security-audit-log' ),
			);
		}

		/**
		 * Reports get back link
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function get_report_back_link() {
			?>
			<li>
			<?php
			echo '<a href="' . \esc_url( \add_query_arg( 'page', self::get_safe_view_name(), \network_admin_url( 'admin.php' ) ) ) . '#wsal-options-tab-periodic-reports-table-target"><span class="dashicons-before dashicons-controls-back wsal-icon-menu"></span>' . \esc_html__( 'Back to the reports', 'wp-security-audit-log' ) . '</a>';
			?>
			</li>
			<?php
		}

		/**
		 * Generates the report based on the data passed.
		 *
		 * @param array $report_options - All the data needed to generate the report.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function generate_report( array $report_options ) {

			$search_string  = '';
			$report_filters = array();

			if ( isset( $report_options['report_title'] ) ) {
				$report_filters['custom_title'] = $report_options['report_title'];
			}

			if ( isset( $report_options['report_comment'] ) ) {
				$report_filters['comment'] = $report_options['report_comment'];
			}

			if ( isset( $report_options['report_metadata'] ) && true === $report_options['report_metadata'] ) {
				$report_filters['no_meta'] = false;
			} else {
				$report_filters['no_meta'] = true;
			}

			if ( isset( $report_options['only_these_sites'] ) ) {
				$sites_label                    = esc_html__( 'Site(s)', 'wp-security-audit-log' );
				$report_filters[ $sites_label ] = array();

				foreach ( $report_options['only_these_sites'] as $site_id ) {
					$search_string                   .= ' site_id:' . (int) $site_id;
					$report_filters[ $sites_label ][] = WP_Helper::get_blog_info( $site_id )['name'];
				}
			}
			if ( isset( $report_options['except_these_sites'] ) ) {
				$sites_label                    = esc_html__( 'Excluded site(s)', 'wp-security-audit-log' );
				$report_filters[ $sites_label ] = array();

				foreach ( $report_options['except_these_sites'] as $site_id ) {
					$search_string                   .= ' -site_id:' . (int) $site_id;
					$report_filters[ $sites_label ][] = WP_Helper::get_blog_info( $site_id )['name'];
				}
			}

			if ( isset( $report_options['only_these_users'] ) ) {
				$users_label                    = esc_html__( 'User(s)', 'wp-security-audit-log' );
				$report_filters[ $users_label ] = array();

				foreach ( $report_options['only_these_users'] as $user_id ) {
					$search_string .= ' user_id:' . (int) $user_id;
					$user           = \get_user_by( 'id', $user_id );
					if ( empty( $user ) && ( MainWP_Addon::check_mainwp_plugin_active() ) ) {
						$mainwp_users = MainWP_Helper::find_users_by( array( 'ID' ), array( $user_id ) );
						$user         = reset( $mainwp_users );
					}
					$report_filters[ $users_label ][] = $user->user_login . ' — ' . $user->user_email;
				}
			}
			if ( isset( $report_options['except_these_users'] ) ) {
				$users_label                    = esc_html__( 'Excluded user(s)', 'wp-security-audit-log' );
				$report_filters[ $users_label ] = array();

				foreach ( $report_options['except_these_users'] as $user_id ) {
					$search_string .= ' -user_id:' . (int) $user_id;
					$user           = \get_user_by( 'id', $user_id );
					if ( empty( $user ) && ( MainWP_Addon::check_mainwp_plugin_active() ) ) {
						$user = reset( MainWP_Helper::find_users_by( array( 'ID' ), array( $user_id ) ) );
					}
					$report_filters[ $users_label ][] = $user->user_login . ' — ' . $user->user_email;
				}
			}
			if ( isset( $report_options['all_users_domain'] ) ) {
				$users_label                    = esc_html__( 'Users with that domain e-mail', 'wp-security-audit-log' );
				$report_filters[ $users_label ] = array();

				$domains = \explode( ',', $report_options['all_users_domain'] );

				foreach ( $domains as $domain ) {
					$search_string .= ' user_email:%' . trim( $domain );

					$report_filters[ $users_label ][] = $domain;
				}
			}

			if ( isset( $report_options['only_these_roles'] ) ) {
				$roles_label                    = esc_html__( 'Role(s)', 'wp-security-audit-log' );
				$report_filters[ $roles_label ] = array();

				foreach ( $report_options['only_these_roles'] as $user_role ) {
					$search_string                   .= ' user_role:' . $user_role;
					$report_filters[ $roles_label ][] = $user_role;
				}
			}
			if ( isset( $report_options['except_these_roles'] ) ) {
				$roles_label                    = esc_html__( 'Excluded role(s)', 'wp-security-audit-log' );
				$report_filters[ $roles_label ] = array();

				foreach ( $report_options['except_these_roles'] as $user_role ) {
					$search_string                   .= ' -user_role:' . $user_role;
					$report_filters[ $roles_label ][] = $user_role;
				}
			}

			if ( isset( $report_options['only_these_ips'] ) ) {
				$ips_label                    = esc_html__( 'IP address(es)', 'wp-security-audit-log' );
				$report_filters[ $ips_label ] = array();

				foreach ( $report_options['only_these_ips'] as $ip ) {
					$search_string                 .= ' client_ip:' . $ip;
					$report_filters[ $ips_label ][] = $ip;
				}
			}
			if ( isset( $report_options['except_these_ips'] ) ) {
				$ips_label                    = esc_html__( 'Excluded IP address(es)', 'wp-security-audit-log' );
				$report_filters[ $ips_label ] = array();

				foreach ( $report_options['except_these_ips'] as $ip ) {
					$search_string                 .= ' -client_ip:' . $ip;
					$report_filters[ $ips_label ][] = $ip;
				}
			}

			if ( isset( $report_options['only_these_objects'] ) ) {
				$objects_label                    = esc_html__( 'Object(s)', 'wp-security-audit-log' );
				$report_filters[ $objects_label ] = array();

				foreach ( $report_options['only_these_objects'] as $object ) {
					$search_string                     .= ' object:' . $object;
					$report_filters[ $objects_label ][] = $object;
				}
			}
			if ( isset( $report_options['except_these_objects'] ) ) {
				$objects_label                    = esc_html__( 'Excluded object(s)', 'wp-security-audit-log' );
				$report_filters[ $objects_label ] = array();

				foreach ( $report_options['except_these_objects'] as $object ) {
					$search_string                     .= ' -object:' . $object;
					$report_filters[ $objects_label ][] = $object;
				}
			}

			if ( isset( $report_options['only_these_event_types'] ) ) {
				$events_label                    = esc_html__( 'Event type(s)', 'wp-security-audit-log' );
				$report_filters[ $events_label ] = array();

				foreach ( $report_options['only_these_event_types'] as $event_types ) {
					$search_string                    .= ' event_type:' . $event_types;
					$report_filters[ $events_label ][] = $event_types;
				}
			}
			if ( isset( $report_options['except_these_event_types'] ) ) {
				$events_label                    = esc_html__( 'Excluded event type(s)', 'wp-security-audit-log' );
				$report_filters[ $events_label ] = array();

				foreach ( $report_options['except_these_event_types'] as $event_types ) {
					$search_string                    .= ' -event_type:' . $event_types;
					$report_filters[ $events_label ][] = $event_types;
				}
			}

			if ( isset( $report_options['only_these_post_titles'] ) ) {
				$post_ids_label                    = esc_html__( 'Post(s)', 'wp-security-audit-log' );
				$report_filters[ $post_ids_label ] = array();

				foreach ( $report_options['only_these_post_titles'] as $post_id ) {
					$search_string                      .= ' post_id:' . $post_id;
					$report_filters[ $post_ids_label ][] = '(' . $post_id . ') ' . \get_the_title( $post_id );
				}
			}
			if ( isset( $report_options['except_these_post_titles'] ) ) {
				$post_ids_label                    = esc_html__( 'Excluded post(s)', 'wp-security-audit-log' );
				$report_filters[ $post_ids_label ] = array();

				foreach ( $report_options['except_these_post_titles'] as $post_id ) {
					$search_string                      .= ' -post_id:' . $post_id;
					$report_filters[ $post_ids_label ][] = '(' . $post_id . ') ' . \get_the_title( $post_id );
				}
			}

			if ( isset( $report_options['only_these_post_types'] ) ) {
				$post_types_label                    = esc_html__( 'Post type(s)', 'wp-security-audit-log' );
				$report_filters[ $post_types_label ] = array();

				foreach ( $report_options['only_these_post_types'] as $post_type ) {
					$search_string                        .= ' post_type:' . $post_type;
					$report_filters[ $post_types_label ][] = $post_type;
				}
			}
			if ( isset( $report_options['except_these_post_types'] ) ) {
				$post_types_label                    = esc_html__( 'Excluded post type(s)', 'wp-security-audit-log' );
				$report_filters[ $post_types_label ] = array();

				foreach ( $report_options['except_these_post_types'] as $post_type ) {
					$search_string                        .= ' -post_type:' . $post_type;
					$report_filters[ $post_types_label ][] = $post_type;
				}
			}

			if ( isset( $report_options['only_these_post_statuses'] ) ) {
				$post_statuses_label                    = esc_html__( 'Post status(es)', 'wp-security-audit-log' );
				$report_filters[ $post_statuses_label ] = array();

				foreach ( $report_options['only_these_post_statuses'] as $post_status ) {
					$search_string                           .= ' post_status:' . $post_status;
					$report_filters[ $post_statuses_label ][] = \get_post_statuses()[ $post_status ];
				}
			}
			if ( isset( $report_options['except_these_post_statuses'] ) ) {
				$post_statuses_label                    = esc_html__( 'Excluded post status(es)', 'wp-security-audit-log' );
				$report_filters[ $post_statuses_label ] = array();

				foreach ( $report_options['except_these_post_statuses'] as $post_status ) {
					$search_string                           .= ' -post_status:' . $post_status;
					$report_filters[ $post_statuses_label ][] = \get_post_statuses()[ $post_status ];
				}
			}

			if ( isset( $report_options['only_these_post_ids'] ) ) {
				$post_ids_label                    = esc_html__( 'Post IDs', 'wp-security-audit-log' );
				$report_filters[ $post_ids_label ] = array();

				foreach ( $report_options['only_these_post_ids'] as $post_id ) {
					$search_string                      .= ' post_id:' . $post_id;
					$report_filters[ $post_ids_label ][] = '(' . $post_id . ') ' . \get_the_title( $post_id );
				}
			}
			if ( isset( $report_options['except_these_post_ids'] ) ) {
				$post_ids_label                    = esc_html__( 'Excluded post IDs', 'wp-security-audit-log' );
				$report_filters[ $post_ids_label ] = array();

				foreach ( $report_options['except_these_post_ids'] as $post_id ) {
					$search_string                      .= ' -post_id:' . $post_id;
					$report_filters[ $post_ids_label ][] = '(' . $post_id . ') ' . \get_the_title( $post_id );
				}
			}

			if ( isset( $report_options['only_these_alert_ids'] ) ) {
				$alerts_label                    = esc_html__( 'Alert code(s)', 'wp-security-audit-log' );
				$report_filters[ $alerts_label ] = array();

				foreach ( $report_options['only_these_alert_ids'] as $alert_id ) {
					$search_string                    .= ' alert_id:' . $alert_id;
					$report_filters[ $alerts_label ][] = $alert_id;
				}
			}
			if ( isset( $report_options['except_these_alert_ids'] ) ) {
				$alerts_label                    = esc_html__( 'Excluded alert code(s)', 'wp-security-audit-log' );
				$report_filters[ $alerts_label ] = array();

				foreach ( $report_options['except_these_alert_ids'] as $alert_id ) {
					$search_string                    .= ' -alert_id:' . $alert_id;
					$report_filters[ $alerts_label ][] = $alert_id;
				}
			}

			/**
			 * Alert categories requires different logic
			 */
			if ( isset( $report_options['only_these_alert_groups'] ) || isset( $report_options['except_these_alert_groups'] ) ) {
				$alert_groups_raw      = array_column( Alert_Manager::get_alerts(), 'category', 'code' );
				$alerts_category_array = array();

				foreach ( $alert_groups_raw as $alert_id => $alert_category ) {
					$alerts_category_array[ \sanitize_title( $alert_category ) ][] = $alert_id;
				}

				if ( isset( $report_options['only_these_alert_groups'] ) ) {
					$alert_groups_label                    = esc_html__( 'Alert group(s)', 'wp-security-audit-log' );
					$report_filters[ $alert_groups_label ] = array();

					foreach ( $report_options['only_these_alert_groups'] as $alert_group ) {
						foreach ( $alerts_category_array[ $alert_group ] as $alert_id ) {
							$search_string .= ' alert_id:' . $alert_id;
						}
						$report_filters[ $alert_groups_label ][] = $alert_group;
					}
				}
				if ( isset( $report_options['except_these_alert_groups'] ) ) {
					$alert_groups_label                    = esc_html__( 'Excluded alert group(s)', 'wp-security-audit-log' );
					$report_filters[ $alert_groups_label ] = array();

					foreach ( $report_options['except_these_alert_groups'] as $alert_group ) {
						foreach ( $alerts_category_array[ $alert_group ] as $alert_id ) {
							$search_string .= ' -alert_id:' . $alert_id;
						}
						$report_filters[ $alert_groups_label ][] = $alert_group;
					}
				}
			}

			if ( isset( $report_options['only_these_severities'] ) ) {
				$severities_label                    = esc_html__( 'Severity(ies)', 'wp-security-audit-log' );
				$report_filters[ $severities_label ] = array();

				foreach ( $report_options['only_these_severities'] as $severity ) {
					$search_string                        .= ' severity:' . $severity;
					$report_filters[ $severities_label ][] = $severity . ' ' . Constants::get_severity_name_by_code( $severity );
				}
			}
			if ( isset( $report_options['except_these_severities'] ) ) {
				$severities_label                    = esc_html__( 'Excluded severity(ies)', 'wp-security-audit-log' );
				$report_filters[ $severities_label ] = array();

				foreach ( $report_options['except_these_severities'] as $severity ) {
					$search_string                        .= ' -severity:' . $severity;
					$report_filters[ $severities_label ][] = $severity . ' ' . Constants::get_severity_name_by_code( $severity );
				}
			}

			$filters_check = $report_filters;
			/**
			 * Checking is the $report_filters is empty.
			 * That logic is stupid and must be optimized but it will work for now.
			 * The common case - $report_filters is prefilled with custom_title, comment and meta keys - remove that and check the $report_filters ($filters_check) for emptiness.
			 */
			unset( $filters_check['custom_title'] );
			unset( $filters_check['comment'] );
			unset( $filters_check['no_meta'] );
			if ( empty( $filters_check ) ) {
				$report_filters[ esc_html__( 'ALL', 'wp-security-audit-log' ) ] = esc_html__( 'All', 'wp-security-audit-log' );
			}

			// Date processing.
			if ( isset( $report_options['report_start_date'] ) && ! empty( $report_options['report_start_date'] ) ) {
				$search_string .= ' start_date:' . $report_options['report_start_date'];
				$report_filters[ esc_html__( 'Start date', 'wp-security-audit-log' ) ] = $report_options['report_start_date'];
			}

			if ( isset( $report_options['report_end_date'] ) && ! empty( $report_options['report_end_date'] ) ) {
				$search_string .= ' end_date:' . $report_options['report_end_date'];
				$report_filters[ esc_html__( 'End date', 'wp-security-audit-log' ) ] = $report_options['report_end_date'];
			}

			self::report_data_processing( $search_string, $report_filters, $report_options );
		}

		/**
		 * Generates a report filename in format "YYYYMMDD-{01234567}".
		 *
		 * @return string
		 *
		 * @since 5.0.0
		 */
		public static function generate_report_filename() {
			$random_number = wp_rand( 1, 99999999 );
			$number_padded = str_pad( (string) $random_number, 8, '0', STR_PAD_LEFT );

			return gmdate( 'Ymd' ) . '-' . str_shuffle( $number_padded );
		}

		/**
		 * Generates a report header columns (along with the column names used for the report)
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function generate_report_columns_info_collecting(): array {
			if ( empty( self::$header_columns ) ) {
				foreach ( self::get_report_columns() as $table_column => $column ) {
					if ( isset( $column['enabled'] ) && $column['enabled'] ) {

						if ( isset( $column['table_column'] ) && ! empty( $column['table_column'] ) ) {
							self::$header_columns[ $column['table_column'] ] = $column['name'];
						} else {
							self::$header_columns[ $table_column ] = $column['name'];
						}
					}
				}
			}

			return self::$header_columns;
		}

		/**
		 * Generates header columns using the provided sorting (in order column)
		 *
		 * @param boolean $settings - If the call is from settings, we have to return enabled and disabled fields, if it is for the report header we need simplified version without the disabled fields.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function generate_report_columns_header( bool $settings = false ): array {
			$report_columns = self::get_report_columns();

			if ( ! $settings ) {
				foreach ( $report_columns as $key => $values ) {
					if ( isset( $values['enabled'] ) && false === $values['enabled'] ) {
						unset( $report_columns[ $key ] );
					}
				}
			}

			$column_settings = Settings_Helper::get_option_value( self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME, array() );

			if ( \is_array( $column_settings ) && ! empty( $column_settings ) ) {
				if ( isset( $column_settings['sort_order'] ) && \is_array( $column_settings['sort_order'] ) && ! empty( $column_settings['sort_order'] ) ) {

					$sorting = $column_settings['sort_order'];

					foreach ( $report_columns as $key => &$val ) {
						if ( isset( $sorting[ $key ] ) ) {
							$val = array_merge( $val, $sorting[ $key ] );
						}
					}
					unset( $val );

					// $report_columns = \array_replace_recursive( $report_columns, $sorting );
				}
			}

			$col = array_column( $report_columns, 'order' );
			array_multisort( $col, SORT_ASC, $report_columns );

			if ( ! $settings ) {
				$sorted_columns = array();

				foreach ( $report_columns as $table_column => $column ) {
					if ( isset( $column['table_column'] ) && ! empty( $column['table_column'] ) ) {
						$sorted_columns[ $column['table_column'] ] = $column['name'];
					} else {
						$sorted_columns[ $table_column ] = $column['name'];
					}
				}
			} else {
				$sorted_columns = $report_columns;
			}

			return $sorted_columns;
		}

		/**
		 * The default columns for the generated reports.
		 *
		 * @return array
		 *
		 * @since 5.0.0
		 */
		public static function get_report_columns(): array {
			if ( empty( self::$report_columns ) ) {
				self::$report_columns = array(
					'code'           => array(
						'name'         => esc_html__( 'Code', 'wp-security-audit-log' ),
						'default'      => true,
						'table_column' => 'alert_id',
						'order'        => 1,
					),
					'type'           => array(
						'name'          => esc_html__( 'Type', 'wp-security-audit-log' ),
						'default'       => true,
						'table_column'  => 'severity',
						'normalization' => array(
							'\WSAL\Controllers\Constants',
							'get_severity_name_by_code',
						),
						'order'         => 2,
					),
					// Order is extremely important here because once created_on is processed, there is a lot of possibility that data will be lost, so, process created_on last and everything date related before that. For ordering, use the order key.
					'time'           => array(
						'name'                 => esc_html__( 'Time', 'wp-security-audit-log' ),
						'default'              => true,
						'table_column'         => '',
						'normalization'        => array(
							'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
							'get_date_info',
						),
						'normalization_extras' => array(
							'variable' => 'row',
							'type'     => 'time',
						),
						'order'                => 3,
					),
					'date'           => array(
						'name'                     => esc_html__( 'Date', 'wp-security-audit-log' ),
						'default'                  => true,
						'table_column'             => 'created_on',
						'normalization'            => array(
							'\WSAL\Helpers\DateTime_Formatter_Helper',
							'get_formatted_date_time',
						),
						'normalization_parameters' => array(
							'date',
						),
						'order'                    => 4,
					),
					'first_and_last' => array(
						'name'                 => esc_html__( 'First and last name', 'wp-security-audit-log' ),
						'default'              => true,
						'table_column'         => '',
						'normalization'        => array(
							'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
							'get_user_info',
						),
						'normalization_extras' => array(
							'variable' => 'row',
							'type'     => 'first_and_last',
						),
						'order'                => 5,
					),
					'username'       => array(
						'name'                 => esc_html__( 'Username', 'wp-security-audit-log' ),
						'default'              => true,
						'table_column'         => '',
						'normalization'        => array(
							'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
							'get_user_info',
						),
						'normalization_extras' => array(
							'variable' => 'row',
							'type'     => 'username',
						),
						'order'                => 6,
					),
					'user_email'     => array(
						'name'                 => esc_html__( 'User e-mail', 'wp-security-audit-log' ),
						'default'              => false,
						'table_column'         => '',
						'normalization'        => array(
							'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
							'get_user_info',
						),
						'normalization_extras' => array(
							'variable' => 'row',
							'type'     => 'user_email',
						),
						'order'                => 7,
					),
					// These columns are part of the statistic reports only - thats why they don't have default values.
					'display_name'   => array(
						'name'                 => esc_html__( 'Display name', 'wp-security-audit-log' ),
						'table_column'         => '',
						'normalization'        => array(
							Helper_Modifiers_Formatters::class,
							'get_user_info',
						),
						'normalization_extras' => array(
							'variable' => 'row',
							'type'     => 'display_name',
						),
						'order'                => 8,
					),
					'post'           => array(
						'name'          => esc_html__( 'Post title', 'wp-security-audit-log' ),
						'table_column'  => 'post',
						'normalization' => array(
							'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
							'get_post_info',
						),
						'order'         => 9,
					),
					'post_id'        => array(
						'name'          => esc_html__( 'Post title', 'wp-security-audit-log' ),
						'table_column'  => 'post_id',
						'normalization' => array(
							'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
							'get_post_info',
						),
						'order'         => 10,
					),
					'events'         => array(
						'name'          => esc_html__( 'Events IDs', 'wp-security-audit-log' ),
						'table_column'  => 'events',
						'normalization' => array(
							'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
							'get_events',
						),
						'order'         => 11,
					),
					// End of the statistic reports only columns.
					'session'        => array(
						'name'         => esc_html__( 'Session ID', 'wp-security-audit-log' ),
						'default'      => false,
						'table_column' => 'session_id',
						'order'        => 12,
					),
					'agent'          => array(
						'name'         => esc_html__( 'User agent', 'wp-security-audit-log' ),
						'default'      => false,
						'table_column' => 'user_agent',
						'order'        => 13,
					),
					'role'           => array(
						'name'         => esc_html__( 'Role', 'wp-security-audit-log' ),
						'default'      => true,
						'table_column' => 'user_roles',
						'order'        => 14,
					),
					'client_ip'      => array(
						'name'         => esc_html__( 'Source IP', 'wp-security-audit-log' ),
						'default'      => true,
						'table_column' => 'client_ip',
						'order'        => 15,
					),
					'object'         => array(
						'name'         => esc_html__( 'Object type', 'wp-security-audit-log' ),
						'default'      => true,
						'table_column' => 'object',
						'order'        => 16,
					),
					'type_event'     => array(
						'name'         => esc_html__( 'Event type', 'wp-security-audit-log' ),
						'default'      => true,
						'table_column' => 'event_type',
						'order'        => 17,
					),
					'message'        => array(
						'name'                 => esc_html__( 'Message', 'wp-security-audit-log' ),
						'default'              => true,
						'table_column'         => '',
						'normalization'        => array(
							'\WSAL\Entities\Occurrences_Entity',
							'get_alert_message',
						),
						'normalization_extras' => array(
							'variable' => 'row',
						),
						'order'                => 18,
					),
					'meta'           => array(
						'name'                 => esc_html__( 'Meta values', 'wp-security-audit-log' ),
						'default'              => false,
						'table_column'         => '',
						'normalization'        => array(
							'\WSAL\Entities\Occurrences_Entity',
							'get_alert_meta',
						),
						'normalization_extras' => array(
							'variable' => 'row',
						),
						'order'                => 19,
					),
				);

				// if ( WP_Helper::is_multisite() || MainWP_Addon::check_mainwp_plugin_active() ) {
				self::$report_columns =
					array(
						'site_id' => array(
							'name'          => esc_html__( 'Blog name', 'wp-security-audit-log' ),
							'default'       => ( WP_Helper::is_multisite() || MainWP_Addon::check_mainwp_plugin_active() ) ? true : false,
							'table_column'  => 'site_id',
							'normalization' => array(
								'\WSAL\Extensions\Helpers\Helper_Modifiers_Formatters',
								'get_blog_name_name_by_id',
							),
							'order'         => 0,
						),
					) + self::$report_columns;
				// }

				/**
				 * Merge with the values stored in the settings (if there are any).
				 */
				$columns_settings = Settings_Helper::get_option_value( self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME, array() );
				if ( empty( $columns_settings ) ) {
					$columns_settings = self::generate_columns_check_and_save( array() );
				}

				$columns_settings = \apply_filters( 'wsal_reports_columns_settings', $columns_settings );

				if ( ! empty( $columns_settings ) ) {
					foreach ( self::$report_columns as $column_name => $column_values ) {
						if ( isset( $columns_settings[ $column_name ] ) ) {
							self::$report_columns[ $column_name ]['enabled'] = (bool) $columns_settings[ $column_name ];
						}
					}
				}
			}

			return self::$report_columns;
		}

		/**
		 * Removes links from alert message if marked for exclusion in reports.
		 *
		 * Selectively removes only links that have 'exclude_from_reports' => true in their
		 * definition, preserving other links in the alert.
		 *
		 * @param string $message - The alert message.
		 * @param int    $alert_id - The alert ID.
		 * @param array  $all_alerts - Array of all alert definitions.
		 *
		 * @return string - The message of this specific alert, with excluded links removed, original message otherwise.
		 *
		 * @since 5.6.0
		 */
		public static function maybe_exclude_alert_message_from_report( $message, $alert_id, $all_alerts ): string {

			$message = (string) $message;

			// Validate input parameters to prevent warnings/errors.
			if ( ! \is_array( $all_alerts ) ) {
				return $message;
			}

			$current_alert = $all_alerts[ $alert_id ] ?? null;

			if ( ! \is_array( $current_alert ) ) {
				return $message;
			}

			$links = $current_alert['links'] ?? null;

			if ( ! \is_array( $links ) || empty( $links ) ) {
				return $message;
			}

			// Collect URLs that should be excluded from reports.
			$urls_to_exclude = array();

			foreach ( $links as $link_data ) {
				// Skip if link_data is not an array.
				if ( ! \is_array( $link_data ) ) {
					continue;
				}

				$exclude_from_reports = $link_data['exclude_from_reports'] ?? false;
				$url                  = $link_data['url'] ?? null;

				// Validate URL is a non-empty string.
				if ( true !== $exclude_from_reports || ! \is_string( $url ) || '' === $url ) {
					continue;
				}

				// Store both the original URL and other versions of it to make sure we find possible matches.
				$urls_to_exclude[] = $url;
				$urls_to_exclude[] = \htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
				$urls_to_exclude[] = \esc_url( $url );
			}

			// If no URLs to exclude, return original message.
			if ( empty( $urls_to_exclude ) ) {
				return $message;
			}

			// Remove duplicates from URLs to exclude.
			$urls_to_exclude = \array_unique( $urls_to_exclude );

			// Split message by <br /> or <br/> (case insensitive).
			$segments = preg_split( '/<br\s*\/?>/i', $message );

			if ( false === $segments || ! \is_array( $segments ) ) {
				return $message;
			}

			// Filter out segments containing excluded URLs.
			$filtered_segments = array();

			// Iterate over each segment of the alert message, which could contain multiple links.
			foreach ( $segments as $segment ) {
				$should_exclude = false;

				foreach ( $urls_to_exclude as $url_to_remove ) {
					if ( false !== \strpos( $segment, $url_to_remove ) ) {
						$should_exclude = true;
						break;
					}
				}

				// If there's no url to exclude, keep this segment.
				if ( ! $should_exclude ) {
					$filtered_segments[] = $segment;
				}
			}

			// Rejoin segments with <br />.
			return implode( '<br />', $filtered_segments );
		}

		/**
		 * Normalizes the column data (and format) based on the column name. Extracts extra data if necessary.
		 *
		 * @param array $columns - The current columns.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function normalize_columns( array &$columns ) {
			$all_alerts       = Alert_Manager::get_alerts();
			$columns_included = self::generate_report_columns_info_collecting();

			/**
			 * Prepare temporary array (indexed) for the self::$report_columns, because the array_search returns index and not key name (because it is search in multidimensional array)
			 */
			$headers_columns_indexed = \array_keys( self::get_report_columns() );

			foreach ( $columns as &$row ) {
				$row['inner_site_id'] = $row['site_id'];
				foreach ( \array_keys( $columns_included ) as $key_column ) {
					if ( isset( $row[ $key_column ] ) ) {
						$key = array_search( $key_column, array_column( self::get_report_columns(), 'table_column' ) );
						if ( false !== $key ) {
							$column_info = self::get_report_columns()[ $headers_columns_indexed[ $key ] ];

							if ( isset( $column_info['normalization'] ) ) {
								$params = array( $row[ $key_column ] );
								if ( isset( $column_info['normalization_parameters'] ) ) {
									$params = \array_merge( $params, (array) $column_info['normalization_parameters'] );
								}
								$row[ $key_column ] = \call_user_func_array( $column_info['normalization'], $params );
							}
						}
					} elseif ( isset( self::get_report_columns()[ $key_column ] ) && isset( self::get_report_columns()[ $key_column ]['normalization'] ) ) {
						if ( isset( self::get_report_columns()[ $key_column ]['normalization_extras'] ) ) {
							$row[ $key_column ] = \call_user_func_array(
								self::get_report_columns()[ $key_column ]['normalization'],
								array(
									${self::get_report_columns()[ $key_column ]['normalization_extras']['variable']},
									( isset( self::get_report_columns()[ $key_column ]['normalization_extras']['type'] ) ) ? self::get_report_columns()[ $key_column ]['normalization_extras']['type'] : '',
								)
							);
						}
					}

					if ( 'message' === $key_column && isset( $row['message'] ) && isset( $row['alert_id'] ) ) {
						$alert_id       = (int) $row['alert_id'];
						$row['message'] = self::maybe_exclude_alert_message_from_report( $row['message'], $alert_id, $all_alerts );
					}
				}
				unset( $row );
			}
		}

		/**
		 * Builds the full list of possible filters. Ideally this would be used by the rest of the plugin, but for now we
		 * only use if for functionality introduced in version 4.4.0 to prevent further duplication.
		 *
		 * @return array[]
		 *
		 * @since 4.4.0
		 */
		public static function get_possible_filters() {
			return array(
				'sites'         => array(
					'property_name'  => 'sites',
					'criteria_label' => esc_html__( 'Site(s)', 'wp-security-audit-log' ),
				),
				'sites-exclude' => array(
					'property_name'    => 'sites_excluded',
					'is_exclusion_for' => 'sites',
					'criteria_label'   => esc_html__( 'Excluded site(s)', 'wp-security-audit-log' ),
				),
			);
		}

		/**
		 * Cron call to finish / continue processing the reports generation
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function generate_reports() {
			// Extract all of the not finished reports.
			$not_finished = Generated_Reports_Entity::get_all_not_finished_reports();

			foreach ( $not_finished as $report ) {
				if ( 999 === (int) $report['generated_report_format'] ) {
					Statistic_Reports::cron_report( $report );
				} else {
					self::report_data_processing( $report['generated_report_where_clause'], \json_decode( $report['generated_report_filters_normalized'], true ), \json_decode( $report['generated_report_filters'], true ), (int) $report['id'], $report );
				}
			}
		}

		/**
		 * Process the reports collected data and generates them. Called from the cron jobs as well.
		 *
		 * @param string  $search_string - The parsed where clause of the report.
		 * @param array   $report_filters - The normalized filters of the report, if that comes from cron - the already stored value is used.
		 * @param array   $report_options - The collected report options, if that comes from cron - the already stored value is used.
		 * @param integer $report_id - The ID of the report to process (from generated reports table) empty if that is not a cron call.
		 * @param array   $report - The array with the report (from generated reports table) - whole data (possible optimizations here). Empty if that is not a cron call.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		private static function report_data_processing( string $search_string, array $report_filters, array $report_options = array(), int $report_id = 0, $report = null ) {

			$report_settings = Settings_Helper::get_option_value( self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME, array() );

			if ( ! isset( $report_options['use_archive_db'] ) ) {
				$report_options['use_archive_db'] = false;
			}

			$records = self::REPORT_LIMIT;

			if ( isset( $report_settings['cron_records_to_process'] ) ) {
				$records = (int) $report_settings['cron_records_to_process'];
				if ( 10 > $records || 10000 < $records ) {
					$records = self::REPORT_LIMIT;
				}
			}

			if ( 0 === $report_id ) {
				if ( false === self::is_there_enabled_columns() ) {
					// No columns associated with this report - bounce.

					return;
				}
				$where_clause = Base_Fields::string_to_search( $search_string );
			} else {
				$where_clause = $search_string;
			}

			$extra = '';

			if ( '' !== trim( $where_clause ) ) {
				$extra = ' AND ' . $where_clause;
			}

			$wsal_db = null;

			if ( isset( $report_options['report_include_archive'] ) ) {
				if ( ( true === (bool) $report_options['report_include_archive'] && Settings_Helper::is_archiving_enabled() && $report_options['report_only_archive'] ) || ( true === (bool) $report_options['report_include_archive'] && Settings_Helper::is_archiving_enabled() && $report_options['use_archive_db'] ) ) {
					$connection_name = Settings_Helper::get_option_value( 'archive-connection' );

					$wsal_db = Connection::get_connection( $connection_name );
				}
			}

			$first_date = 0;

			if ( $report_id ) {
				$first_date = $report['generated_report_to_date'];
			} else {

				$first_event = Occurrences_Entity::build_query(
					array(),
					array(),
					array( 'created_on' => 'DESC' ),
					array( 1 ),
					array(),
					$wsal_db
				);

				if ( ! empty( $first_event ) && isset( $first_event[0] ) && isset( $first_event[0]['created_on'] ) ) {
					$first_date = $first_event[0]['created_on'];
				}
			}

			$extra = ' AND created_on <= ' . $first_date . $extra;

			$limit_start = 0;

			if ( $report_id ) {
				$limit_start = $report_options['limit_to_start'];
				// $limit_start = $report['generated_report_number_of_records'];
			}

			$occurrences = Occurrences_Entity::load_array( '%d', array( 1 ), $wsal_db, $extra . ' ORDER BY site_id, created_on DESC LIMIT ' . $limit_start . ', ' . $records );

			if ( ( isset( self::get_report_columns()['message']['enabled'] ) && self::get_report_columns()['message']['enabled'] ) || isset( self::get_report_columns()['meta']['enabled'] ) && self::get_report_columns()['meta']['enabled'] ) {
				$occurrences = Occurrences_Entity::get_multi_meta_array( $occurrences, $wsal_db );
			}

			self::normalize_columns( $occurrences );

			$finished = false;

			$report_options['limit_to_start'] = count( $occurrences );

			if ( $report_id ) {
				$report_options['limit_to_start'] = (int) ( $report['generated_report_number_of_records'] ) + count( $occurrences );
			}

			if ( $records > count( $occurrences ) ) {
				$finished                         = true;
				$report_options['limit_to_start'] = 0;
				if ( isset( $report_options['report_include_archive'] ) ) {
					if ( true === (bool) $report_options['report_include_archive'] && Settings_Helper::is_archiving_enabled() && ! $report_options['use_archive_db'] ) {
						if ( ! $report_options['report_only_archive'] ) {
							$report_options['use_archive_db'] = true;
							$finished                         = false;
						} else {
							$report_options['archive_db_only'] = true;
						}
					}
				}
			}

			if ( $report_id ) {
				$header_columns = \json_decode( $report['generated_report_header_columns'], true );

				$file_name = $report['generated_report_name'];

				CSV_Writer::set_header_columns( $header_columns );
				CSV_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.csv' );
				CSV_Writer::write_csv( 2, $occurrences );

				HTML_Writer::set_header_columns( $header_columns );
				HTML_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.html' );
				HTML_Writer::write_html( 2, $occurrences, $report_filters );

				$data = array(
					'generated_report_user_id'            => (int) ( $report['generated_report_user_id'] ),
					'generated_report_username'           => $report['generated_report_username'],
					'generated_report_filters'            => $report_options,
					'generated_report_filters_normalized' => $report_filters,
					'generated_report_header_columns'     => $header_columns,
					'generated_report_where_clause'       => $where_clause,
					'generated_report_finished'           => $finished,
					'generated_report_number_of_records'  => (int) ( $report['generated_report_number_of_records'] ) + count( $occurrences ),
					'generated_report_name'               => $file_name,
					'generated_report_to_date'            => $first_date,
					'generated_report_tag'                => $report_options['report_tag'],
					'id'                                  => $report_id,
				);
			} else {
				$file_name = self::generate_report_filename();

				$header_columns = self::generate_report_columns_header();

				$user_id = \get_current_user_id();
				if ( ! $user_id ) {
					$username = __( 'System', 'wp-security-audit-log' );
				} else {
					$username = \get_userdata( $user_id )->user_login;
				}

				if ( 0 === $report_id ) {
					$data = array(
						'generated_report_user_id'        => $user_id,
						'generated_report_username'       => $username,
						'generated_report_filters'        => $report_options,
						'generated_report_filters_normalized' => $report_filters,
						'generated_report_header_columns' => $header_columns,
						'generated_report_where_clause'   => $where_clause,
						'generated_report_finished'       => $finished,
						'generated_report_number_of_records' => (int) count( $occurrences ),
						'generated_report_name'           => $file_name,
						'generated_report_to_date'        => $first_date,
						'generated_report_tag'            => $report_options['report_tag'],
					);
				}

				CSV_Writer::set_header_columns( $header_columns );

				CSV_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.csv' );

				CSV_Writer::write_csv( 1, $occurrences );

				HTML_Writer::set_header_columns( $header_columns );

				HTML_Writer::set_file( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.html' );

				HTML_Writer::write_html( 1, $occurrences, $report_filters );
			}

			if ( 0 === (int) $data['generated_report_number_of_records'] && $data['generated_report_finished'] ) {
				self::$empty_report = true;
			}

			if ( $finished ) {
				// Thats the end of the report - close the HTML file.

				HTML_Writer::set_footer();

				if ( isset( $report_options['from_periodic'] ) && (bool) $report_options['from_periodic'] ) {
					$report = Reports_Entity::load_array( 'id = %d', array( (int) $report_options['from_periodic'] ) );

					if ( ! empty( $report ) && class_exists( '\WSAL\Helpers\Email_Helper' ) ) {
						$date_format = Settings_Helper::get_date_format( true );
						$pre_subject = '';

						$content       = '<p>';
						$period_string = '';

						switch ( $report[0]['report_frequency'] ) {
							case 0:
							default:
								$pre_subject = sprintf(
								// translators: time number, website name.
									esc_html__( '%1$s - Website %2$s', 'wp-security-audit-log' ),
									gmdate( $date_format, time() ),
									\get_bloginfo( 'name' )
								);
								$period_string = gmdate( $date_format, time() );
								break;
							case 1:
								$pre_subject = sprintf(
								// translators: time number, website name.
									\esc_html__( 'Week number %1$s - Website %2$s', 'wp-security-audit-log' ),
									gmdate( 'W', strtotime( '-1 week' ) ),
									\get_bloginfo( 'name' )
								);
								$period_string = 'week ' . gmdate( 'W', strtotime( '-1 week' ) );
								break;
							case 2:
								$last_month  = strtotime( '-1 month' );
								$pre_subject = sprintf(
								// translators: time number, website name.
									\esc_html__( 'Month %1$s - Website %2$s', 'wp-security-audit-log' ),
									gmdate( 'F', $last_month ) . ' ' . gmdate( 'Y', $last_month ),
									\get_bloginfo( 'name' )
								);
								$period_string = 'the month of ' . gmdate( 'F', $last_month ) . ' ' . gmdate( 'Y', $last_month );
								break;
							case 3:
								$month  = gmdate( 'n', time() );
								$quoter = '';
								if ( $month >= 1 && $month <= 3 ) {
									$quoter = 'Q1';
								} elseif ( $month >= 4 && $month <= 6 ) {
									$quoter = 'Q2';
								} elseif ( $month >= 7 && $month <= 9 ) {
									$quoter = 'Q3';
								} elseif ( $month >= 10 && $month <= 12 ) {
									$quoter = 'Q4';
								}
								$pre_subject = sprintf(
								// translators: time number, website name.
									\esc_html__( 'Quarter %1$s - Website %2$s', 'wp-security-audit-log' ),
									$quoter,
									\get_bloginfo( 'name' )
								);
								$period_string = 'the quarter ' . $quoter;
								break;
						}

						$title   = $report[0]['report_name'];
						$subject = $pre_subject . sprintf(
						// translators: report title collected from user input.
							\esc_html__( ' - %s Email Report', 'wp-security-audit-log' ),
							$title
						);

						$attachments = array(
							'CSV'  =>
							\add_query_arg(
								array(
									'action' => 'wsal_file_download',
									'f'      => base64_encode( \basename( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.csv' ) ),
									'ctype'  => 'csv',
									'nonce'  => \wp_create_nonce( 'wpsal_reporting_nonce_action' ),
								),
								\admin_url( 'admin-ajax.php' )
							),
							'HTML' =>
							\add_query_arg(
								array(
									'action' => 'wsal_file_download',
									'f'      => base64_encode( \basename( Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.html' ) ),
									'ctype'  => 'html',
									'nonce'  => \wp_create_nonce( 'wpsal_reporting_nonce_action' ),
								),
								\admin_url( 'admin-ajax.php' )
							),
						);

						$attachments_files = array();
						if ( $report_settings['reports_send_reports_attachments_emails'] ) {
							$attachments_files = array(
								$title . '.csv'  => Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.csv',
								$title . '.html' => Settings_Helper::get_working_dir_path_static( 'reports', true ) . $file_name . '.html',
							);
						}

						$report_description = '<strong>' . $title . '</strong> from website <strong>' . get_bloginfo( 'name' ) . '</strong> for <strong>' . $period_string . '</strong>';
						if ( empty( $attachments ) ) {
							$content .= \esc_html__( 'No event IDs matched the criteria of the configured report ', 'wp-security-audit-log' ) . $report_description . '.';
						} else {
							$content .= \esc_html__( 'The report ', 'wp-security-audit-log' ) . $report_description . esc_html__( ' can be downloaded from the following links:', 'wp-security-audit-log' );

							$content .= '<p><i>' . \esc_html__( 'Because of the security, these links wont be available after the maximum of 24 hours. You can still download the reports from the reports section of the plugin', 'wp-security-audit-log' ) . '</i></p>';

							$content .= '<div>';
							foreach ( $attachments as $key => $attachment ) {
								$content .= '<a href="' . $attachment . '">' . $key . '</a><br>';
							}
							$content .= '</div>';
						}

						$content .= '</p>';

						$email = $report[0]['report_email'];

						$send_mail = true;

						if ( self::$empty_report && $report_settings['reports_send_empty_summary_emails'] ) {
							$send_mail = true;
						} elseif ( self::$empty_report ) {
							$send_mail = false;
						}

						if ( $send_mail ) {
							Email_Helper::send_email( $email, $subject, $content, '', $attachments_files );
						}

						Reports_Entity::save( array_merge( $report[0], array( 'last_sent' => microtime( true ) ) ) );
					}
				}
			}

			Generated_Reports_Entity::save( $data );
		}

		/**
		 * Generates daily reports from the periodic reports.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function clear_the_reports() {
			$report_settings = Settings_Helper::get_option_value( self::REPORT_GENERATE_COLUMNS_SETTINGS_NAME, array() );

			if ( isset( $report_settings['reports_auto_purge_enabled'] ) && false !== $report_settings['reports_auto_purge_enabled'] ) {

				$days = ( isset( $report_settings['reports_auto_purge_older_than_days'] ) ? $report_settings['reports_auto_purge_older_than_days'] : 30 );

				$reports = Generated_Reports_Entity::get_all_reports_older_than_days( (int) $days );

				if ( \is_array( $reports ) && ! empty( $reports ) ) {
					foreach ( $reports as $report ) {
						Generated_Reports_Entity::delete_by_id( (int) $report['id'] );
					}
				}
			}
		}

		/**
		 * Generates daily reports from the periodic reports.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function generate_reports_daily() {
			self::generate_periodic_reports( 0 );
		}

		/**
		 * Generates weekly reports from the periodic reports.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function generate_reports_weekly() {
			self::generate_periodic_reports( 1 );
		}

		/**
		 * Generates monthly reports from the periodic reports.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function generate_reports_monthly() {
			self::generate_periodic_reports( 2 );
		}

		/**
		 * Generates quarterly reports from the periodic reports.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function generate_reports_quarterly() {
			self::generate_periodic_reports( 3 );
		}

		/**
		 * Starts a period generation manually.
		 *
		 * @param integer $report_id - The id of the report to run.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function generate_report_manually( int $report_id ) {
			$report_data = Reports_Entity::load( 'id=%d', $report_id );

			if ( isset( $report_data ) && ! empty( $report_data ) && isset( $report_data['report_frequency'] ) ) {
				self::generate_periodic_reports( (int) $report_data['report_frequency'], array( $report_data ) );
			}
		}

		/**
		 * Generates periodic reports by given parameter
		 *
		 * @param integer $period - The period for which the report should be generated.
		 * @param array   $reports - Array with preselected reports - if present it will be used for generating the reports, if not - the DB will be asked for proper report IDs.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		private static function generate_periodic_reports( int $period, $reports = array() ) {

			if ( ! empty( $reports ) ) {
				$get_reports = $reports;
			} else {
				// Extract all of the not finished reports.
				$get_reports = Reports_Entity::get_all_reports_for_period( $period );
			}

			$date_format = Settings_Helper::get_date_format( true );

			$yesterday = gmdate( $date_format, strtotime( 'yesterday' ) );

			switch ( $period ) {
				case 0:
				default:
					// Fallback for any other time period would go here.
					// get YESTERDAYS date.
					$start_date = $yesterday;
					$end_date   = $yesterday;
					break;
				case 1:
					$start_date = gmdate( $date_format, strtotime( 'last week' ) );
					$end_date   = gmdate( $date_format, strtotime( 'last week + 6 days' ) );
					break;
				case 2:
					$start_date = gmdate( $date_format, strtotime( 'last month' ) );
					$end_date   = gmdate( $date_format, strtotime( 'this month - 1 day' ) );
					break;
				case 3:
					$month = gmdate( 'n', time() );
					$year  = gmdate( 'Y', time() );
					if ( $month >= 1 && $month <= 3 ) {
						$start_date = gmdate( $date_format, strtotime( $year . '-01-01' ) );
					} elseif ( $month >= 4 && $month <= 6 ) {
						$start_date = gmdate( $date_format, strtotime( $year . '-04-01' ) );
					} elseif ( $month >= 7 && $month <= 9 ) {
						$start_date = gmdate( $date_format, strtotime( $year . '-07-01' ) );
					} elseif ( $month >= 10 && $month <= 12 ) {
						$start_date = gmdate( $date_format, strtotime( $year . '-10-01' ) );
					}
					$end_date = $yesterday;

					break;
			}

			foreach ( $get_reports as $report ) {
				$report_options = \json_decode( $report['report_data'], true );

				$report_options['report_start_date'] = $start_date;
				$report_options['report_end_date']   = $end_date;

				$report_options['from_periodic'] = $report['id'];

				self::generate_report( $report_options );
			}
		}

		/**
		 * Checks if there is an enabled columns in the given report data. If there are no columns enabled it returns false.
		 *
		 * @return boolean
		 *
		 * @since 5.0.0
		 */
		private static function is_there_enabled_columns(): bool {
			foreach ( self::get_report_columns() as $values ) {
				if ( isset( $values['enabled'] ) && true === $values['enabled'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Handles AJAX call that triggers report file download.
		 *
		 * @since 5.0.0
		 */
		public static function process_report_download() {
			// #! No  cache
			if ( ! headers_sent() ) {
				header( 'Expires: Mon, 26 Jul 1990 05:00:00 GMT' );
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				header( 'Cache-Control: no-store, no-cache, must-revalidate' );
				header( 'Cache-Control: post-check=0, pre-check=0', false );
				header( 'Pragma: no-cache' );
			}

			$strm = '[WSAL Reporting Plugin] Requesting download';

			// Validate nonce.
			if ( ! isset( $_GET['nonce'] ) || ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_GET['nonce'] ) ), 'wpsal_reporting_nonce_action' ) ) {
				\wp_die( \esc_attr( $strm ) . ' with a missing or invalid nonce [code: 1000]' );
			}

			// Missing f param from url.
			if ( ! isset( $_GET['f'] ) ) {
				\wp_die( \esc_attr( $strm ) . ' without the "f" parameter [code: 2000]' );
			}

			// Missing ctype param from url.
			if ( ! isset( $_GET['ctype'] ) ) {
				wp_die( \esc_attr( $strm ) . ' without the "ctype" parameter [code: 3000]' );
			}

			// Invalid fn provided in the url.
			$fn = base64_decode( \sanitize_text_field( \wp_unslash( $_GET['f'] ) ) );
			if ( false === $fn ) {
				wp_die( \esc_attr( $strm ) . ' without a valid base64 encoded file name [code: 4000]' );
			}

			$sub_dir = 'reports';
			// Extract subdir.
			if ( isset( $_GET['dir'] ) ) {
				$sub_dir = (string) \sanitize_text_field( \wp_unslash( $_GET['dir'] ) );
			}

			$dir       = Settings_Helper::get_working_dir_path_static( $sub_dir, true );
			$file_path = $dir . $fn;

			// Directory traversal attacks won't work here.
			if ( preg_match( '/\.\./', $file_path ) ) {
				\wp_die( \esc_attr( $strm ) . ' with an invalid file name (' . \esc_attr( $fn ) . ') [code: 6000]' );
			}
			if ( ! is_file( $file_path ) ) {
				\wp_die( \esc_attr( $strm ) . ' with an invalid file name (' . \esc_attr( $fn ) . ') [code: 7000]' );
			}

			$data_format = intval( \wp_unslash( $_GET['ctype'] ) );
			if ( ! Reports_Data_Format::is_valid( $data_format ) ) {
				// Content type is not valid.
				\wp_die( \esc_attr( $strm ) . ' with an invalid content type [code: 7000]' );
			}

			$content_type = Reports_Data_Format::get_content_type( $data_format );
			$file_size    = filesize( $file_path );
			$file         = fopen( $file_path, 'rb' );

			// - turn off compression on the server - that is, if we can...
			ini_set( 'zlib.output_compression', 'Off' );
			// set the headers, prevent caching + IE fixes.
			header( 'Pragma: public' );
			header( 'Expires: -1' );
			header( 'Cache-Control: public, must-revalidate, post-check=0, pre-check=0' );
			if ( 'text/html' !== $content_type ) {
				header( 'Content-Disposition: attachment; filename="' . $fn . '"' );
			}
			header( "Content-Length: $file_size" );
			header( "Content-Type: {$content_type}" );
			set_time_limit( 0 );
			while ( ! feof( $file ) ) {
				print( fread( $file, 1024 * 8 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_system_operations_fread
				ob_flush();
				flush();
				if ( connection_status() != 0 ) {
					fclose( $file );
					exit;
				}
			}
			// File save was a success.
			fclose( $file );

			exit;
		}

		/**
		 * Clears the report columns caching for the class.
		 *
		 * @return void
		 *
		 * @since 5.0.0
		 */
		public static function clear_reps() {
			self::$report_columns = array();
		}

		/**
		 * Returns the inner class status variable - it is set to true if the report is empty
		 *
		 * @return bool
		 *
		 * @since 5.1.0
		 */
		public static function get_report_empty_status() {
			return self::$empty_report;
		}
	}
}
