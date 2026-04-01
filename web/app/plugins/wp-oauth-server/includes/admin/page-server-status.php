<?php
/**
 * Server Status
 */
global $license_error;
$license_error = null;
function wo_server_status_page() {
	/*
	* OAUTH LICENCE CHECK
	*
	* THE IDEA BEHIND THIS IS TO ALLOW PEOPLE TO USE OAUTH TO PICK THIER LICENSE AND ACTIVATE INSTEAD OF HAVING TO
	* MANUALLY ENTER.
	*/
	/*
	if ( isset( $_REQUEST['oauth_license_check'] ) ) {
	wp_redirect( 'https://wp-oauth.com/oauth/authorize?client_id=onpXQbtaWgOCO5OlzLXLis8Xht4f4s&response_type=code&redirect_uri=' . admin_url( 'admin.php?page=wo_server_status&tab=license&oauth_license_check' ) . '&state=oauth_license_check' );
	exit;
	}

	if ( isset( $_REQUEST['code'] ) && isset( $_REQUEST['state'] ) && $_REQUEST['state'] == 'oauth_license_check' ) {

	// Get an access token from the code given
	$api_params = array(
	'client_id'    => 'onpXQbtaWgOCO5OlzLXLis8Xht4f4s',
	'grant_type'   => 'authorization_code',
	'code'         => $_REQUEST['code'],
	'redirect_uri' => admin_url( 'admin.php?page=wo_server_status' )
	);

	$response     = wp_remote_post( 'https://wp-oauth.com/oauth/token/', array( 'body' => $api_params ) );
	$access_token = json_decode( wp_remote_retrieve_body( $response ) );

	$access_token = $access_token->access_token;

	// CALL THE LICENSE API
	$api_params = array(
	'access_token' => $access_token
	);

	$api_args = array(
	'sslverify' => false
	);

	$licenses_return = wp_remote_get( add_query_arg( $api_params, 'https://wp-oauth.com/oauth/edd_license_check/' ), $api_args );
	$license_list    = json_decode( wp_remote_retrieve_body( $licenses_return ) );

	?>

	<div class="wrap">
	<h2>Select the license for WP OAuth Server</h2>
	<div style="background: #FFF; padding: 1em;">
				<ul>
					<?php foreach ( $license_list as $license ): ?>
						<li>
							<?php echo $license->download_name; ?> - <a
									href="<?php echo admin_url( '/admin.php?page=wo_server_status&tab=license&license_auth=' . $license->license_key ); ?>">
								<?php echo $license->license_key; ?>
								- <?php echo $license->status; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
	</div>
	</div>
	<?php
	exit;
	}*/

	if ( isset( $_REQUEST['activate_license'] ) || ! empty( $_REQUEST['license_auth'] ) ) {

		if ( empty( $_REQUEST['license_auth'] ) ) {
			$wo_license_key = sanitize_text_field( $_REQUEST['wo_license_key'] );
		} elseif ( ! empty( $_REQUEST['license_auth'] ) ) {
			$wo_license_key = sanitize_text_field( $_REQUEST['license_auth'] );
		}

		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $wo_license_key,
			'item_name'  => urlencode( 'WP OAuth Server' ),
			'url'        => home_url(),
		);

		$api_args = array(
			'sslverify' => false,
		);

		$response = wp_remote_get( add_query_arg( $api_params, 'https://wp-oauth.com' ), $api_args );
		if ( ! is_wp_error( $response ) ) {
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// Check for errors in the JSON.
			if ( $license_data === null || json_last_error() != JSON_ERROR_NONE ) {
				$json_errors = array(
					JSON_ERROR_NONE           => __( 'No error', 'wp-oauth' ),
					JSON_ERROR_DEPTH          => __( 'Maximum stack depth exceeded', 'wp-oauth' ),
					JSON_ERROR_STATE_MISMATCH => __( 'State mismatch (invalid or malformed JSON)', 'wp-oauth' ),
					JSON_ERROR_CTRL_CHAR      => __( 'Control character error, possibly incorrectly encoded', 'wp-oauth' ),
					JSON_ERROR_SYNTAX         => __( 'Syntax error', 'wp-oauth' ),
					JSON_ERROR_UTF8           => __( 'Malformed UTF-8 characters, possibly incorrectly encoded', 'wp-oauth' ),
				);

				global $license_error;
				$last_error    = json_last_error();
				$license_error = __( 'JSON ERROR: ', 'wp-oauth' ) . $json_errors[ $last_error ];
			}

			$body_return = json_decode( wp_remote_retrieve_body( $response ) );

			update_option( 'wo_license_key', $wo_license_key );
			update_option( 'wo_license_information', (array) $license_data );
			update_option( 'wo_license_license_valid', $body_return->license );

		} else {

			global $license_error;
			$license_error = $response->get_error_message();
		}
	}

	/*
	* LICENSE DEACTIVATE
	*/
	if ( isset( $_REQUEST['deactivate_license'] ) ) {

		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => wo_license_key(),
			'item_name'  => urlencode( 'WP OAuth Server' ),
			'url'        => home_url(),
		);

		$body_params = array(
			'body'      => $api_params,
			'timeout'   => 15,
			'sslverify' => false,
		);

		$deactivate_response = wp_remote_post( 'https://wp-oauth.com', $body_params );
		if ( ! is_wp_error( $deactivate_response ) ) {
			$license_data = json_decode( wp_remote_retrieve_body( $deactivate_response ) );

			// Check for errors in the JSON.
			if ( $license_data === null || json_last_error() != JSON_ERROR_NONE ) {
				$json_errors = array(
					JSON_ERROR_NONE           => __( 'No error', 'wp-oauth' ),
					JSON_ERROR_DEPTH          => __( 'Maximum stack depth exceeded', 'wp-oauth' ),
					JSON_ERROR_STATE_MISMATCH => __( 'State mismatch (invalid or malformed JSON)', 'wp-oauth' ),
					JSON_ERROR_CTRL_CHAR      => __( 'Control character error, possibly incorrectly encoded', 'wp-oauth' ),
					JSON_ERROR_SYNTAX         => __( 'Syntax error', 'wp-oauth' ),
					JSON_ERROR_UTF8           => __( 'Malformed UTF-8 characters, possibly incorrectly encoded', 'wp-oauth' ),
				);

				global $license_error;
				$last_error    = json_last_error();
				$license_error = __( 'JSON ERROR: ', 'wp-oauth' ) . $json_errors[ $last_error ];
			}

			if ( $license_data->success ) {
				update_option( 'wo_license_key', '' );
				update_option( 'wo_license_information', '' );
				update_option( 'wo_license_license_valid', '' );
			}
		} else {

			global $license_error;
			$license_error = $deactivate_response->get_error_message();
		}
	}

	wp_enqueue_style( 'wo_admin' );
	wp_enqueue_script( 'wo_admin' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	?>
	<div class="wrap">
		<h2><?php _e( 'Server Status', 'wp-oauth' ); ?></h2>
		<div class="section group">
			<div class="col span_6_of_6">
				<?php wo_display_settings_tabs(); ?>
			</div>
		</div>

	</div>
	<?php
}
