<?php
/**
 * EXAMPLES
 *
 * 1. WP REST API Route with authentication
 * 2. Custom Login Form for Auth Code authentication
 *
 * @author Justin Greer <justin@justin-greer.com>
 *
 * @package WP OAuth Server
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'myplugin/v1',
			'/author/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => 'my_awesome_func',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}
);

function my_awesome_func() {
	$user_id = get_current_user_id();

	return array(
		'status'  => true,
		'message' => 'Congrats! You successfully made an authenticated request to a protected endpoint',
		'user_id' => $user_id,
	);
}


/**
 * CUSTOM LOGIN REDIRECT
 *
 * Redirect a user to a custom login page for authentication
 */
add_action( 'wo_before_authorize_method', 'custom_login_redirect' );
function custom_login_redirect() {
	if ( ! is_user_logged_in() ) {
		wp_redirect( site_url() . '/custom-login?redirect_to=' . urlencode( site_url() . $_SERVER['REQUEST_URI'] ) );
		exit;
	}
}


/**
 * Extend OpenID Discovery API
 *
 * @param $return
 *
 * @return mixed
 */
function modify_openid_discovery_api( $return ) {
	$return['new_discovery_value'] = array(
		'new_value_key' => 'Some value',
	);

	return $return;
}

add_filter( 'wo_openid_discovery', 'modify_openid_discovery_api' );

/**
 * START MANUALLY INSERT ACCESS TOKEN
 */
$client_id = 'XXXX';
function generateAccessToken() {
	$token_length = wo_setting( 'token_length' );

	return strtolower( wp_generate_password( $token_length, false, $extra_special_chars = false ) );
}

function setAccessToken( $access_token, $client_id, $user_id, $expires, $scope = null ) {
	global $wpdb;

	do_action(
		'wo_set_access_token',
		array(
			'access_token' => $access_token,
			'client_id'    => $client_id,
			'user_id'      => $user_id,
		)
	);

	$expires = date( 'Y-m-d H:i:s', $expires );
	if ( $this->getAccessToken( $access_token ) ) {
		$stmt = $this->db->prepare(
			"UPDATE {$wpdb->prefix}oauth_access_tokens SET client_id=%s, expires=%s, user_id=%s, scope=%s where access_token=%s",
			array(
				$client_id,
				$expires,
				$user_id,
				$scope,
				$access_token,
			)
		);
	} else {
		$stmt = $this->db->prepare(
			"INSERT INTO {$wpdb->prefix}oauth_access_tokens (access_token, client_id, expires, user_id, scope) VALUES (%s, %s, %s, %s, %s)",
			array(
				$access_token,
				$client_id,
				$expires,
				$user_id,
				$scope,
			)
		);
	}

	// Give return a value
	$results = $wpdb->query( $stmt );

	// Return Results
	return $results;
}

/**
 * Add first name and last name to return for /oauth/me
 */
add_filter( 'wo_me_resource_return', 'wo_example_extend_me', 99 );
function wo_example_extend_me( $data ) {
	// Grab a custom user meta field
	$first_name = get_user_meta( $data['ID'], 'first_name', true );
	$last_name  = get_user_meta( $data['ID'], 'last_name', true );

	$data['first_name'] = $first_name;
	$data['last_name']  = $last_name;

	$data['name']  = $first_name . ' ' . $last_name;
	$data['email'] = $data['user_email'];

	// $data['id'] = $data['ID'];

	return $data;
}

/**
 * UPDATES AN ACCESS TOKEN'S EXPIRE TIME BY X AMOUNT OF TIME WHEN SUCCESSFULLY USED FOR AUTHENTICATION
 *
 * The token parameter can be token[0] or token array.
 *
 * NOTE: This will override any token settings used within the plugin but does allow for active tokens to
 * stay active instead of expiring. This is useful for auto deauthentication tht is traditionally handled on the
 * client side.
 */
add_action( 'wo_endpoint_user_authenticated', 'wp_oauth_successful_authentication_action' );
function wp_oauth_successful_authentication_action( $token ) {
	// $current_expires = $token[0]['expires'];
	$access_token = $token[0]['access_token'];
	$minutes      = 30;
	$new_expires  = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + ( $minutes * 60 ) );

	global $wpdb;
	$update = $wpdb->update(
		$wpdb->prefix . 'oauth_access_tokens',
		array(
			'expires' => $new_expires,
		),
		array( 'access_token' => $access_token )
	);
}
