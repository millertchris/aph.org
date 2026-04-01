<?php
/**
 * Core_Request class for handling HTTP API requests related to posts.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl;

/**
 * Class Core_Request
 *
 * Handles HTTP API requests for fetching and processing post content.
 */
class Core_Request {

	/**
	 * Gets post from front-end, via HTTP API
	 *
	 * @param int $post_id ID of the post to fetch content.
	 *
	 * @return string|\WP_Error
	 */
	public function get_rendered_post( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return new \WP_Error( __CLASS__, 'Unknown post ID' );
		}

		$post_parent      = wp_is_post_revision( $post_id );
		$is_post_revision = ! empty( $post_parent );
		$permalink        = $is_post_revision
			? get_permalink( $post_parent )
			: get_permalink( $post_id );
		if ( empty( $permalink ) ) {
			return new \WP_Error( __CLASS__, 'Error figuring out post permalink' );
		}

		$url = add_query_arg( 'preview', 'true', $permalink );
		if ( $is_post_revision ) {
			$url = add_query_arg( 'preview_id', $post_id, $url );
		}
		$url = add_query_arg( 'preview_nonce', wp_create_nonce( 'post_preview_' . $post_id ), $url );
		$url = add_query_arg( 'wds-frontend-check', md5( microtime() ), $url );

		$post_status = get_post_status( $post_id );
		if ( 'auto-draft' === $post_status ) {
			return '';
		}

		$params = array();

		// Let's copy over the current cookies to apply to the request.
		$cookies = array();
		$source  = ! empty( $_COOKIE )
			? $_COOKIE
			: array();
		foreach ( $source as $cname => $cvalue ) {
			if ( ! preg_match( '/^(wp-|wordpress_)/', $cname ) ) {
				continue;
			} // Only WP cookies, pl0x.
			$cookies[] = new \WP_Http_Cookie(
				array(
					'name'  => $cname,
					'value' => $cvalue,
				)
			);
		}

		// Post password cookie.
		$post = $is_post_revision
			? get_post( $post_parent )
			: get_post( $post_id );
		if ( ! empty( $post->post_password ) ) {
			if ( ! class_exists( '\PasswordHash' ) ) {
				require_once ABSPATH . WPINC . '/class-phpass.php';
			}
			$hasher    = new \PasswordHash( 8, true );
			$cookies[] = new \WP_Http_Cookie(
				array(
					'name'  => 'wp-postpass_' . COOKIEHASH,
					'value' => $hasher->HashPassword( $post->post_password ),
				)
			);
		}

		if ( ! empty( $cookies ) ) {
			$params['cookies'] = $cookies;
		}
		$params['timeout'] = $this->get_timeout();
		// Remove response size limit for large content pages.
		$params['limit_response_size'] = 0; // 0 means no limit.

		$response = wp_remote_get( $url, $params );

		if ( is_wp_error( $response ) ) {
			$error_code = $response->get_error_code();
			$error_message = $response->get_error_message();

			// Check for timeout errors specifically.
			if ( 'http_request_failed' === $error_code &&
			     ( strpos( $error_message, 'timeout' ) !== false ||
			       strpos( $error_message, 'timed out' ) !== false ) ) {
				return new \WP_Error(
					__CLASS__,
					sprintf(
						'Request timed out after %d seconds. The page content may be too large. Consider reducing the amount of content or increasing SMARTCRAWL_ANALYSIS_REQUEST_TIMEOUT.',
						$this->get_timeout()
					)
				);
			}

			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new \WP_Error(
				__CLASS__,
				sprintf( 'Non-200 response: %d', $response_code )
			);
		}

		$content = wp_remote_retrieve_body( $response );

		// Check if content is empty, which might indicate an issue.
		if ( empty( $content ) ) {
			return new \WP_Error(
				__CLASS__,
				'Empty response body received. The page may have failed to render properly.'
			);
		}

		$bits = Html::find( 'body', $content );

		return apply_filters(
			'wds-analysis-content',
			(string) trim( join( "\n", $bits ) ),
			$post_id
		);
	}

	/**
	 * Gets the timeout value for HTTP requests.
	 *
	 * @return int Timeout value in seconds.
	 */
	private function get_timeout() {
		return defined( 'SMARTCRAWL_ANALYSIS_REQUEST_TIMEOUT' )
			? SMARTCRAWL_ANALYSIS_REQUEST_TIMEOUT
			: 30;
	}
}