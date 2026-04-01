<?php
/**
 * Class IndexNow_API
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Instant_Indexing;

use SmartCrawl\Singleton;
use WP_Error;

class IndexNow_API {

	use Singleton;

	/**
	 * IndexNow API key.
	 *
	 * @var string
	 */
	protected string $api_key = '';

	/**
	 * Endpoint URL.
	 *
	 * @var string
	 */
	private string $endpoint = 'https://api.indexnow.org/indexnow';

	/**
	 * Boot the hooking part.
	 */
	public static function run() {
		self::get()->add_hooks();
	}

	/**
	 * Register hooks.
	 */
	private function add_hooks() {}

	/**
	 * Get the host for the API request.
	 *
	 * @return string
	 */
	public function get_host() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';

		/**
		 * Filter the IndexNow host.
		 *
		 * @param string $host Host.
		 */
		return apply_filters( 'SmartCrawl_indexnow_host', $host );
	}

	/**
	 * Get the API key from options.
	 *
	 * @return string
	 */
	public function get_key() {
		if ( ! empty( $this->api_key ) ) {
			return $this->api_key;
		}
		$options = get_option( 'wds_instant_indexing_options', array() );
		$api_key = sanitize_text_field( $options['indexnow_api_key'] ?? '' );

		/**
		 * Filter the IndexNow API key.
		 *
		 * @param string $api_key API key.
		 */
		$this->api_key = apply_filters( 'smartcrawl_indexnow_api_key', $api_key );

		return $this->api_key;
	}

	/**
	 * Get the API key location URL.
	 *
	 * @return string
	 */
	public function get_key_location() {
		$parsed_home    = wp_parse_url( home_url() );
		$clean_home_url = $parsed_home['scheme'] . '://' . $parsed_home['host'];

		if ( isset( $parsed_home['port'] ) ) {
			$clean_home_url .= ':' . $parsed_home['port'];
		}
		if ( isset( $parsed_home['path'] ) ) {
			$clean_home_url .= rtrim( $parsed_home['path'], '/' );
		}

		$key_location = trailingslashit( $clean_home_url ) . $this->get_key() . '.txt';

		/**
		 * Filter the IndexNow API key location.
		 *
		 * @param string $key_location API key location.
		 */
		return apply_filters( 'smartcrawl_indexnow_key_location', $key_location );
	}

	/**
	 * Submit URLs to the IndexNow API.
	 *
	 * @param array $urls URLs to submit.
	 * @param string $submission_type Submission type (e.g., 'Manual', 'Automatic').
	 *
	 * @return array
	 */
	public function submit_urls( array $urls, string $submission_type = 'Manual' ) {
		$payload  = $this->get_payload( $urls );
		$response = wp_remote_post( $this->endpoint, array(
			'body'    => $payload,
			'headers' => array(
				'Content-Type'  => 'application/json',
				'User-Agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
				'X-Source-Info' => 'https://wpmudev.com/project/smartcrawl-wordpress-seo/' . SMARTCRAWL_VERSION . '/' . $submission_type,
			),
		) );

		return $this->process_response( $response, $urls );
	}

	/**
	 * Prepare the request payload.
	 *
	 * @param array $urls URLs to submit.
	 *
	 * @return string
	 */
	private function get_payload( array $urls ) {
		return wp_json_encode( array(
			'host'        => $this->get_host(),
			'key'         => $this->get_key(),
			'keyLocation' => $this->get_key_location(),
			'urlList'     => $urls,
		) );
	}

	/**
	 * Process the API response.
	 *
	 * @param WP_Error|array $response API response.
	 * @param array $urls URLs submitted.
	 *
	 * @return array
	 */
	private function process_response( $response, array $urls ) {
		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
			$this->log_submission( $urls, 0, 'Failed' );

			return array(
				'status'  => 'Failed',
				'message' => $message
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$message     = $this->get_error_message( $status_code ) ?? esc_html__( 'Submission successful!', 'wds' );
		$status      = in_array( $status_code, [ 200, 202 ], true ) ? 'Success' : 'Failed';

		$this->log_submission( $urls, $status_code, $status );

		return array(
			'status'  => $status,
			'code'    => $status_code,
			'message' => $message
		);
	}

	/**
	 * Get the error message for a response code.
	 *
	 * @param int $code Response code.
	 *
	 * @return string
	 */
	public function get_error_message( $code ) {
		$messages = array(
			200 => esc_html__( 'Success. The URL has been submitted.', 'wds' ),
			202 => esc_html__( 'Accepted. The URL submission is being processed.', 'wds' ),
			400 => esc_html__( 'Bad request. Check your input.', 'wds' ),
			403 => esc_html__( 'Unauthorized. Invalid API key.', 'wds' ),
			404 => esc_html__( 'Resource not found. Verify URL.', 'wds' ),
			422 => esc_html__( 'Unprocessable entity. Invalid URL format.', 'wds' ),
			429 => esc_html__( 'Rate limit exceeded.', 'wds' ),
			500 => esc_html__( 'Server error. Try again later.', 'wds' ),
			503 => esc_html__( 'Service unavailable. Server busy.', 'wds' ),
		);

		/**
		 * Filter the IndexNow error messages.
		 *
		 * @param array $messages Error messages.
		 */
		$messages = apply_filters( 'smartcrawl_indexnow_error_messages', $messages );

		return $messages[ $code ] ?? esc_html__( 'Unexpected error occurred.', 'wds' );
	}

	/**
	 * Log the URL submission.
	 *
	 * @param array $urls URLs submitted.
	 * @param int $status HTTP status code.
	 * @param string $message Response message.
	 */
	private function log_submission( array $urls, int $status, string $message ) {
		$indexing_history = get_option( 'wds_instant_indexing_history', array() );
		$submissions_type = IndexNow_Actions::get()->get_submission_type();

		$recent_urls = array();
		if ( 'Manual' !== $submissions_type && ! empty( $indexing_history ) ) {
			foreach ( array_slice( array_reverse( $indexing_history ), 0, 20 ) as $history ) {
				if ( empty( $history['url'] ) || time() - (int) $history['time'] >= 10 ) {
					continue;
				}
				$recent_urls = array_merge( $recent_urls, $history['url'] );
			}
		}
		$unique_urls = array_diff( $urls, $recent_urls );

		if ( empty( $unique_urls ) ) {
			return;
		}

		$indexing_history[] = array(
			'url'     => $urls,
			'status'  => $status,
			'message' => $message,
			'time'    => time(),
			'type'    => $submissions_type,
		);

		/**
		 * Filter the Update IndexNow submission history.
		 *
		 * @param array $indexing_history Submission history.
		 */
		$indexing_history = apply_filters( 'smartcrawl_indexnow_update_submission_history', $indexing_history );

		update_option( 'wds_instant_indexing_history', array_slice( $indexing_history, - 100 ), false );
	}
}