<?php
/**
 * Class IndexNow_REST
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Instant_Indexing;

use SmartCrawl\Singleton;

class IndexNow_REST extends \WP_REST_Controller {

	use Singleton;

	/**
	 * Namespace for REST API.
	 *
	 * @var string
	 */
	protected $namespace = 'smartcrawl/v1';

	/**
	 * Boot the hooking part.
	 */
	public static function run() {
		self::get()->add_hooks();
	}

	/**
	 * Register hooks.
	 */
	private function add_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/instant-indexing',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'indexnow_submit_urls' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => $this->get_collection_params(),
			)
		);
	}

	/**
	 * Check if the user has permission to use the API.
	 *
	 * @return bool
	 */
	public function permissions_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle the IndexNow submit URLs AJAX request.
	 */
	public function indexnow_submit_urls( \WP_REST_Request $request ) {
		$urls = $request->get_param( 'urls' );

		if ( empty( $urls ) || ! is_array( $urls ) ) {
			return new \WP_REST_Response( array( 'message' => esc_html__( 'No valid URLs submitted.', 'wds' ) ), 400 );
		}

		// Validate URLs.
		$invalid_urls = array_filter( $urls, fn( $url ) => ! filter_var( $url, FILTER_VALIDATE_URL ) );
		if ( $invalid_urls ) {
			return new \WP_REST_Response( array( 'message' => esc_html__( 'Invalid URLs found: ', 'wds' ) . implode( ', ', $invalid_urls ) ), 400 );
		}

		$response = IndexNow_API::get()->submit_urls( $urls );
		if ( is_wp_error( $response ) ) {
			return new \WP_REST_Response( array( 'message' => esc_html__( 'Error submitting URLs: ', 'wds' ) . $response['message'] ), 500 );
		}

		$status_code = $response['code'] ?? 0;
		$is_success  = in_array( $status_code, array( 200, 202 ), true );

		if ( $is_success ) {
			$status        = 'success';
			$final_message = sprintf(
				__( '%1$sSubmission Completed:%2$s %3$d URLs have been processed successfully%4$s %5$s', 'wds' ),
				'<strong>', '</strong>', count( $urls ), '<br/>',
				sprintf(
					'<span><a href="%1$s" class="sui-button sui-button-ghost"><span class="sui-icon-eye" aria-hidden="true"></span> %2$s</a></span>',
					admin_url( 'admin.php?page=wds_instant_indexing&tab=tab_submission_history' ),
					esc_html__( 'View Submission History', 'wds' )
				)
			);
		} else {
			$status        = 'error';
			$final_message = sprintf(
				__( '%1$sSubmission Failed:%2$s %3$d URLs have not been processed%4$s %5$s', 'wds' ),
				'<strong>', '</strong>', count( $urls ), '<br/>',
				sprintf(
					'<span><a href="%1$s" class="sui-button sui-button-ghost"><span class="sui-icon-eye" aria-hidden="true"></span> %2$s</a></span>',
					admin_url( 'admin.php?page=wds_instant_indexing&tab=tab_submission_history' ),
					esc_html__( 'View Submission History', 'wds' )
				)
			);
		}

		return new \WP_REST_Response( array( 'success' => true, 'status' => $status, 'message' => $final_message ), 200 );
	}

	/**
	 * Get the parameters for submitting URLs.
	 *
	 * @return array
	 */
	public function get_collection_params(): array {
		return array(
			'urls' => array(
				'description' => esc_html__( 'Array of URLs to be submitted.', 'wds' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'string',
				),
				'required'    => true,
			),
		);
	}
}