<?php
/**
 * WP OAuth Server Actions
 *
 * @author  Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */

/**
 * Remove all OAuth access tokens when a user logs out of the WordPress
 *
 * @param $user_id
 */
function wo_example_destroy_authorizations( $user_id ) {
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user_id ) );
}

add_action( 'wp_logout', 'wo_example_destroy_authorizations' );

/**
 * Invalidate any token and refresh tokens during password reset
 *
 * @param object $user     WP_User Object
 * @param String $new_pass New Password
 *
 * @return Void
 *
 * @since 3.1.8
 */
function wo_password_reset_action( $user, $new_pass ) {
	 global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user->ID ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user->ID ) );
}

add_action( 'password_reset', 'wo_password_reset_action', 10, 2 );

/**
 * [wo_profile_update_action description]
 *
 * @param int $user_id WP User ID
 *
 * @return Void
 */
function wo_profile_update_action( $user_id ) {
	if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
		return;
	}
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user_id ) );
}

add_action( 'profile_update', 'wo_profile_update_action' );

/**
 * Only allow 1 acces_token at a time
 *
 * @param [type] $results [description]
 *
 * @return [type]          [description]
 */
function wo_only_allow_one_access_token( $object ) {
	if ( is_null( $object ) ) {
		return;
	}

	// Define the user ID
	$user_id = $object['user_id'];

	// Remove all other access tokens and refresh tokens from the system
	global $wpdb;
	$wpdb->delete( "{$wpdb->prefix}oauth_access_tokens", array( 'user_id' => $user_id ) );
	$wpdb->delete( "{$wpdb->prefix}oauth_refresh_tokens", array( 'user_id' => $user_id ) );

	return;
}

/**
 * Restrict users to only have a single access token
 *
 * @since 3.2.7
 */
$wo_restrict_single_access_token = apply_filters( 'wo_restrict_single_access_token', false );
if ( $wo_restrict_single_access_token ) {
	add_action( 'wo_set_access_token', 'wo_only_allow_one_access_token' );
}

/**
 * Handle non-invasive checks
 */
function wo_license_expiring_notice() {
	// Only run for admins in the admin
	if ( ! is_admin() ) {
		return;
	}

	// check if we need to display the message again. Lets do this daily.
	// @todo This will run every page load when we really don't need it to. It should be fine for now but this will need to change at some point.
	$notice = get_option( 'wp_30day_notice' );
	if ( $notice != false ) {

		// Show every 1 day
		$show_again_time = strtotime( '+1 day' );
		if ( $notice < $show_again_time ) {
			return;
		}
	}

	// Run the check
	$options = get_option( 'wo_license_information' );
	if ( isset( $options['expires'] ) ) {
		$expire  = strtotime( $options['expires'] );
		$current = strtotime( '+30 days' );

		if ( $expire < $current ) {
			function wo__license_expiring_notice() {
				?>
				<div class="wo_30day_notice notice notice-error is-dismissible">
					<p><?php _e( '<strong>Your license for WP OAuth Server is about to expire or has expired!</strong><br/><br/> To ensure there is no lapse in updates and support be sure to update your license. <a href="https://wp-oauth.com/my-account/">Visit Your Account</a> .', 'wp-oauth' ); ?></p>
				</div>
				<?php
			}

			add_action( 'admin_notices', 'wo__license_expiring_notice' );
		}
	}

	do_action( 'wo_after_license_expire_check' );
}

// add_action( 'init', 'wo_license_expiring_notice' );

/**
 * Define DOING_OAUTH if OAuth is present in the URL
 */
add_action( 'login_init', 'wp_oauth_setup_doing_oauth_login_page', 1 );
function wp_oauth_setup_doing_oauth_login_page() {
	/*
	* Check if there is an redirect_url parameter during the login page.
	*
	* If the script has made it this far for WP OAuth Server, there will be redirect URL exposed for the login redirect
	* required by WP OAuth Server. We can use this redirect as a flag to check for the path. If "oauth" is present, we
	* should assume that the request is an oauth request and should not be redirected.
	*/
	$redirect = isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : '';
	$url      = wp_parse_url( $redirect );

	if ( strpos( $url['path'], 'oauth' ) !== false ) {
		define( 'DOING_OAUTH', true );
	}
}

/**
 * Post Handler for regenerating certificates if needed and verify
 * that they have been creates.
 */
add_action( 'admin_post_wpoauth_regenerate_certificates', 'wp_oauth_regenerate_certificates', 10 );
function wp_oauth_regenerate_certificates() {
	/*
	* Security Check
	* This feature should only be left to the admin of WP and nothing else.
	*/
	if ( current_user_can( 'manage_options' ) && wp_verify_nonce( $_REQUEST['wo_nonce'], 'wo_nonce' ) ) {
		wp_oauth_generate_server_keys( true );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=wo_server_status' ) );
}

