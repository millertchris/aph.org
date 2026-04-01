<?php
/**
 * Extension: External DB
 *
 * External DB extension for WSAL.
 *
 * @since 1.0.0
 * @package wsal
 */

use WSAL\Helpers\View_Manager;
use WSAL\Controllers\Connection;
use WSAL\Helpers\Settings_Helper;
use WSAL\Helpers\Formatters\Alert_Formatter_Configuration;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_Ext_Plugin
 *
 * @package wsal
 */
class WSAL_Ext_Plugin {

	const SCHEDULED_HOOK_ARCHIVING = 'wsal_run_archiving';

	/**
	 * Instance of WpSecurityAuditLog.
	 *
	 * @var WpSecurityAuditLog
	 */
	protected $wsal = null;

	/**
	 * Triggered when the main plugin is loaded.
	 *
	 * @see WpSecurityAuditLog::load()
	 */
	public function __construct() {
		new \WSAL_Ext_Ajax();

		\add_action(
			'wsal_init',
			function() {
				View_Manager::add_from_class( 'WSAL_Ext_Settings' );
			}
		);

		global $wsal_class;

		if ( null === $wsal_class ) {
			$wsal_class = \WpSecurityAuditLog::get_instance();
		}

		// Register alert formatter for Slack.
		\add_filter( 'wsal_alert_formatters', array( __CLASS__, 'register_alert_formatters' ), 10, 1 );

		self::check_schedules_setup();

		// Background job for the migration.
		new \WSAL_Ext_DataMigration();
	}

	/**
	 * Checks current schedules setup and does any necessary scheduling or job cancellation.
	 *
	 * @since 4.2.1
	 */
	private static function check_schedules_setup() {
		// Cron job archiving.
		if ( Settings_Helper::is_archiving_set_and_enabled() ) {
			//\add_action( self::SCHEDULED_HOOK_ARCHIVING, array( Connection::class, 'archiving_alerts' ) );

			\add_filter(
				'wsal_cron_hooks',
				function( $hooks ) {
					$archiving_frequency = strtolower( Settings_Helper::get_archiving_frequency() );

					$hooks[ self::SCHEDULED_HOOK_ARCHIVING ] = array(
						'time' => $archiving_frequency,
						'hook' => array( Connection::class, 'archiving_alerts' ),
						'args' => array(),
					);

					return $hooks;
				}
			);
			// if ( ! wp_next_scheduled( self::SCHEDULED_HOOK_ARCHIVING ) ) {
			// 	$archiving_frequency = strtolower( Settings_Helper::get_archiving_frequency() );
			// 	wp_schedule_event( time(), $archiving_frequency, self::SCHEDULED_HOOK_ARCHIVING );
			// }
		}
	}

	/**
	 * Remove External DB config and recreate DB tables on WP.
	 */
	public function remove_config() {
		Connection::remove_external_storage_config();
		Connection::recreate_tables();
	}

	/**
	 * Registers plain text alert formatter.
	 *
	 * @param array $formatters Formatter definition arrays.
	 *
	 * @return array
	 *
	 * @since 4.2.1
	 */
	public static function register_alert_formatters( $formatters ) {
		$formatters['plain'] = Alert_Formatter_Configuration::get_default_configuration();

		return $formatters;
	}
}
