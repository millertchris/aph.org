<?php
/**
 * Class WSAL_Ext_Ajax.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_Ext_Plugin' ) ) {
	exit( esc_html__( 'You are not allowed to view this page.', 'wp-security-audit-log' ) );
}

/**
 * Ajax handler for the External DB extension.
 *
 * @package    wsal
 * @subpackage external-db
 * @since      4.3.2
 */
final class WSAL_Ext_Ajax {

	/**
	 * Constructor.
	 *
	 */
	public function __construct() {
		new WSAL_Ext_StorageSwitchToLocal();
		new WSAL_Ext_StorageSwitchToExternal();
		new WSAL_Ext_MigrationCancellation();
	}
}
