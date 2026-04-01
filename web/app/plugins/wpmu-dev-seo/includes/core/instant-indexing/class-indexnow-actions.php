<?php
/**
 * Class IndexNow_API
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Instant_Indexing;

use SmartCrawl\Singleton;

class IndexNow_Actions {

	use Singleton;

	/**
	 * Submission type.
	 *
	 * @var string
	 */
	private $submission_type = 'Manual';

	/**
	 * Previous post submission.
	 *
	 * @var array
	 */
	private array $recent_submission = array();

	/**
	 * Store last post status for comparison.
	 *
	 * @var array
	 */
	private array $last_post_status = array();

	/**
	 * Store last post permalinks for comparison.
	 *
	 * @var array
	 */
	private array $last_post_url = array();

	/**
	 * Boot the hooking part.
	 */
	public static function run() {
		self::get()->add_hooks();
	}

	/**
	 * Submit URLs to IndexNow API.
	 *
	 * @return string
	 */
	public function get_submission_type() {
		/**
		 * Filter the Instant indexing submission type.
		 *
		 * @param string $submission_type Submission type.
		 */
		return apply_filters( 'smartcrawl_instant_indexing_submission_type', $this->submission_type );
	}

	/**
	 * Register hooks.
	 */
	private function add_hooks() {
		add_filter( 'wp_insert_post_data', array( $this, 'before_save_post' ), 10, 2 );

		$post_types = $this->indexing_post_type();
		foreach ( $post_types as $post_type ) {
			add_action( 'save_post_' . $post_type, array( $this, 'indexnow_on_save_post' ), 10, 2 );
			add_filter( 'bulk_actions-edit-' . $post_type, array( $this, 'bulk_actions' ), 11 );
			add_filter( 'handle_bulk_actions-edit-' . $post_type, array( $this, 'handle_bulk_actions' ), 10, 3 );
		}

		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
		add_filter( 'admin_init', array( $this, 'handle_post_row_actions' ) );

		add_action( 'wp', array( $this, 'indexnow_api_key_file' ) );
	}

	/**
	 * Handle the post data before saving.
	 *
	 * @param array $data Post data.
	 * @param array $postarr Raw post data.
	 */
	public function before_save_post( $data, $postarr ) {
		$postarrID     = $postarr['ID'];
		$postarrStatus = $postarr['post_status'];

		$this->last_post_status[ $postarrID ] = $postarrStatus;
		$this->last_post_url[ $postarrID ]    = str_replace( '__trashed', '', get_permalink( $postarrID ) );

		return $data;
	}

	/**
	 * Handle the IndexNow on post save.
	 *
	 * @param $post_id int post ID.
	 * @param $post object post object.
	 *
	 * @return void
	 */
	public function indexnow_on_save_post( $post_id, $post ) {
		// Check if already submitted.
		if ( in_array( $post_id, $this->recent_submission, true ) ) {
			return;
		}

		// Check if post status changed to publish or trash.
		if ( ! in_array( $post->post_status, array( 'publish', 'trash' ), true ) ) {
			return;
		}

		// If new status is trash, check if previous status was published.
		if ( 'trash' === $post->post_status && 'publish' !== $this->last_post_status[ $post_id ] ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Fetch selected post types from the settings option.
		$settings_options    = get_option( 'wds_instant_indexing_options', array() );
		$selected_post_types = $settings_options['indexnow_post_types'] ?? array();

		if ( empty( $selected_post_types ) || ! in_array( $post->post_type, $selected_post_types, true ) ) {
			return;
		}

		$this->submission_type = 'Automatic';

		$url = get_permalink( $post_id );
		if ( 'trash' === $post->post_status ) {
			$url = $this->last_post_url[ $post_id ];
		}

		// Submit the URL to IndexNow API.
		$this->submit_api( [ $url ] );

		$this->recent_submission[] = $post_id;
	}

	/**
	 * Add IndexNow action link to the post row.
	 *
	 * @param array $actions Action links.
	 * @param object $post Current post object.
	 *
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $actions;
		}

		if ( 'publish' !== $post->post_status ) {
			return $actions;
		}

		if ( ! in_array( $post->post_type, $this->indexing_post_type(), true ) ) {
			return $actions;
		}

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action'  => 'wds_instant_index_post',
					'post_id' => $post->ID,
				),
			),
			'wds_instant_index_post'
		);

		$actions['smartcrawl_indexnow_submit'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'SmartCrawl Instant Indexing: Submit Now', 'wds' ) . '</a>';

		return $actions;
	}

	/**
	 * Handle post row action link actions.
	 *
	 * @return void
	 */
	public function handle_post_row_actions() {
		if ( isset( $_GET['action'] ) && 'wds_instant_index_post' !== $_GET['action'] ) {
			return;
		}

		if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		$post_id = absint( $_GET['post_id'] );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wds_instant_index_post' ) ) {
			return;
		}

		$this->submit_api( [ get_permalink( $post_id ) ] );

		wp_safe_redirect( remove_query_arg( [ 'action', 'post_id', '_wpnonce' ] ) );

		exit;
	}

	/**
	 * Add IndexNow bulk actions to the post list.
	 *
	 * @param array $actions Actions.
	 *
	 * @return array New actions.
	 */
	public function bulk_actions( $actions ) {
		$actions['smartcrawl_indexnow_submit'] = esc_html__( 'SmartCrawl Instant Indexing: Submit Now', 'wds' );

		return $actions;
	}

	/**
	 * Handle the IndexNow bulk action.
	 *
	 * @param string $redirect Redirect URL.
	 * @param string $action Performed action.
	 * @param array $post_ids Post IDs.
	 *
	 * @return string New redirect URL.
	 */
	public function handle_bulk_actions( $redirect, $action, $post_ids ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $redirect;
		}

		if ( 'smartcrawl_indexnow_submit' !== $action || empty( $post_ids ) ) {
			return $redirect;
		}

		$urls = array();
		foreach ( $post_ids as $post_id ) {
			$urls[] = get_permalink( $post_id );
		}
		// Submit the URL to IndexNow API.
		$this->submit_api( $urls );

		return $redirect;
	}

	/**
	 * Submit APIs
	 *
	 * @param $urls array URLs to submit.
	 *
	 * @return void
	 */
	private function submit_api( $urls ) {
		$api = new IndexNow_API();
		$api->submit_urls( $urls, $this->submission_type );
	}

	/**
	 * Get the Indexing post types.
	 *
	 * @return array|mixed
	 */
	private function indexing_post_type() {
		$options             = get_option( 'wds_instant_indexing_options', array() );
		$indexnow_post_types = $options['indexnow_post_types'] ?? array();

		/**
		 * Filter the IndexNow post types.
		 *
		 * @param array $indexnow_post_types Post types.
		 */
		return apply_filters( 'smartcrawl_indexnow_post_types', $indexnow_post_types );
	}

	/**
	 * IndexNow API key file.
	 */
	public function indexnow_api_key_file() {
		global $wp;
		$api           = IndexNow_API::get();
		$key           = $api->get_key();
		$key_location  = $api->get_key_location();
		$request_path  = '/' . untrailingslashit( $wp->request );
		$expected_path = wp_parse_url( $key_location, PHP_URL_PATH );

		if ( $expected_path && $request_path === $expected_path ) {
			header( 'Content-Type: text/plain' );
			header( 'X-Robots-Tag: noindex' );
			header( 'Cache-Control: no-cache, no-store, must-revalidate' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			status_header( 200 );
			echo esc_html( $key );

			exit();
		}
	}
}