<?php
/**
 * Instant Indexing settings
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Admin\Settings;

use SmartCrawl\Controllers\Assets;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;

/**
 * Class Instant_Indexing
 */
class Instant_Indexing extends Admin_Settings {

	use Singleton;

	/**
	 * Validate.
	 *
	 * @param array $input Input.
	 *
	 * @return array
	 */
	public function validate( $input ) {
		$result  = array();
		if ( ! empty( $input['indexnow_api_key'] ) ) {
			$result['indexnow_api_key'] = sanitize_text_field( $input['indexnow_api_key'] );
		}
		if ( ! empty( $input['indexnow_post_types'] ) ) {
			$result['indexnow_post_types'] = array_map( 'sanitize_key', $input['indexnow_post_types'] );
		}

		return $result;
	}

	/**
	 * Get the title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Instant Indexing', 'wds' );
	}

	/**
	 * Init the module.
	 *
	 * @return void
	 */
	public function init() {
		$this->option_name = 'wds_instant_indexing_options';
		$this->name        = Settings::COMP_INSTANT_INDEXING;
		$this->slug        = Settings::TAB_INSTANT_INDEXING;
		$this->action_url  = admin_url( 'options.php' );
		$this->page_title  = sprintf(
		/* translators: %s: plugin title */
			__( '%s Wizard: Instant Indexing', 'wds' ),
			\smartcrawl_get_plugin_title()
		);

		parent::init();

		add_action( 'wp_ajax_wds_change_instant_indexing_status', array(
			$this,
			'change_instant_indexing_component_status'
		) );
		add_action( 'wp_ajax_wds_generate_indexnow_key', array( $this, 'generate_indexnow_key' ) );
		add_action( 'wp_ajax_wds_fetch_submission_history', array( $this, 'fetch_submission_history' ) );
		add_action( 'wp_ajax_wds_clear_submissions', array( $this, 'clear_submissions' ) );

		remove_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_menu', array( $this, 'add_page' ), 96 );
		add_action( 'wds_plugin_update', array( $this, 'update_blog_settings' ) );
	}

	/**
	 * Get the API key location URL.
	 *
	 * @return string
	 */
	public function get_key_location( $api_key ) {
		return trailingslashit( home_url() ) . $api_key . '.txt';
	}

	/**
	 * Gets default options set and their initial values
	 *
	 * @return array
	 */
	public function get_default_options() {
		$api_key = $this->generate_api_key();

		return array(
			'indexnow_api_key'    => $api_key,
			'indexnow_post_types' => array( 'post', 'page' ),
		);
	}

	/**
	 * Default settings
	 */
	public function defaults() {
		$options = Settings::get_component_options( $this->name );

		if ( empty( $options ) ) {
			foreach ( $this->get_default_options() as $opt => $default ) {
				if ( ! isset( $options[ $opt ] ) ) {
					$options[ $opt ] = $default;
				}
			}
		}

		update_option( $this->option_name, $options );
	}

	/**
	 * Update blog settings to ensure Instant Indexing tab is enabled by default.
	 *
	 * @return void
	 */
	public function update_blog_settings(): void {
		$modules = get_site_option( 'wds_blog_tabs' );
		if ( $modules && ! isset( $modules[ Settings::TAB_INSTANT_INDEXING ] ) ) {
			$modules[ Settings::TAB_INSTANT_INDEXING ] = 1;
			update_site_option( 'wds_blog_tabs', $modules );
		}
	}

	/**
	 * Add admin settings page
	 */
	public function options_page() {
		parent::options_page();

		$options = Settings::get_component_options( $this->name );
		$options = wp_parse_args( $options, $this->get_default_options() );

		$arguments = array(
			'options'    => $options,
			'active_tab' => $this->get_active_tab( 'tab_submit_url' ),
		);

		wp_enqueue_script( Assets::INSTANT_INDEXING_PAGE_JS );
		wp_enqueue_media();

		$this->render_page( 'instant-indexing/instant-indexing-content', $arguments );
	}

	/**
	 * Change Instant Indexing component status.
	 */
	public function change_instant_indexing_component_status() {
		$request_data = $this->get_request_data();

		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}

		$status                                 = (bool) \smartcrawl_get_array_value( $request_data, 'status' );
		$options                                = self::get_specific_options( 'wds_settings_options' );
		$options[ self::COMP_INSTANT_INDEXING ] = ! ! $status;

		self::update_specific_options( 'wds_settings_options', $options );

		wp_send_json_success();
	}

	/**
	 * Generate IndexNow key.
	 */
	public function generate_indexnow_key() {
		$request_data = $this->get_request_data();

		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}

		$api_key = $this->generate_api_key();

		wp_send_json_success(
			array(
				'api_key'          => esc_html( $api_key ),
				'api_key_location' => esc_url( $this->get_key_location( $api_key ) ),
			)
		);
	}

	/**
	 * Generate new random API key.
	 */
	private function generate_api_key() {
		$api_key = wp_generate_uuid4();

		return preg_replace( '[-]', '', $api_key );
	}

	/**
	 * Fetch submissions.
	 *
	 * @return void
	 */
	public function fetch_submission_history() {
		$request_data = $this->get_request_data();

		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}

		$page             = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$results_per_page = isset( $_POST['results_per_page'] ) ? absint( $_POST['results_per_page'] ) : 10;
		$offset           = ( $page - 1 ) * $results_per_page;

		$submissions = get_option( 'wds_instant_indexing_history', array() );
		/**
		 * Filter the submission history.
		 *
		 * @param array $submissions Submissions.
		 */
		$submissions = apply_filters( 'smartcrawl_indexnow_submission_history', $submissions );

		usort( $submissions, function ( $a, $b ) {
			return $b['time'] <=> $a['time'];
		} );

		$total_submissions = count( $submissions );
		$total_pages       = ceil( $total_submissions / $results_per_page );
		$paged_submissions = array_slice( $submissions, $offset, $results_per_page );

		ob_start();
		// Render the template part for the table rows.
		$this->render_view( 'instant-indexing/submission-history-rows', array(
			'paged_submissions' => $paged_submissions
		) );
		$html = ob_get_clean();

		ob_start();
		// Include the template part for the pagination.
		$this->render_view( 'instant-indexing/submission-history-pagination', array(
			'total_pages' => $total_pages,
			'page'        => $page
		) );
		$pagination = ob_get_clean();

		wp_send_json_success( array(
			'html'       => $html,
			'pagination' => $pagination,
		) );
	}

	function clear_submissions() {
		$request_data = $this->get_request_data();

		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}

		delete_option( 'wds_instant_indexing_history' );

		wp_send_json_success( array( 'message' => 'Submission history cleared successfully.' ) );
	}

	/**
	 * Get request data.
	 *
	 * @return array
	 */
	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-instant-indexing-nonce' )
			? $_POST
			: array();
	}
}