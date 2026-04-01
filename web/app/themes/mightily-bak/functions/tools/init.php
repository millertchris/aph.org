<?php
/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-08-20
 * Time: 15:29
 */

// This selects a specific environment
if (defined('WP_HOME') && (constant('WP_HOME') == 'http://aph5.local') ) {
	define( 'DEBUGGING', 100 );
	define( 'ENVIRONMENT', 'DEV');
	define( 'APH_LOGDIR', 'logs');
}

/* Debugging logs */

if (defined('DEBUGGING') && constant('DEBUGGING') > 0) {

	// Redirect mail to logs
	include "local_email.php";

	// create logging directory
	if ( ! file_exists(  APH_LOGDIR ) ) {
		mkdir( APH_LOGDIR, 0777, true );
	}

	/**
	 * Write to the debug logs
	 *
	 * @param mixed $data if not a scalar, will be jasonified
	 * @param string $label - optional prefix
	 * @param string $fname - filename to use
	 * @param int $level - level must be less than DEBUGGING level to be displayed (lower is higher priority)
	 */

	function aph_write_log( $data, $label = '', $fname = "debug", $level = 10 ) {

		if (DEBUGGING < $level) return;

		if ( ! is_scalar( $data ) ) {
			$data = json_encode( $data, JSON_PRETTY_PRINT );
		}
		if ( $label ) {
			$label .= ": ";
		}
		file_put_contents( APH_LOGDIR . "/$fname.log", "$label $data \n", FILE_APPEND | LOCK_EX );
	}

} else {
	// don't log anything
	function aph_write_log( $data, $label = '', $fname = "debug", $level = 10 ) {
		return;
	}
}


