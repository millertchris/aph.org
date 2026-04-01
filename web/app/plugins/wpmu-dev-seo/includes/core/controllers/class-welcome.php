<?php
/**
 * Welcome modal functionality.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Controllers;

use SmartCrawl\Services\Service;
use SmartCrawl\SmartCrawl;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Simple_Renderer;


/**
 * Class Welcome
 */
class Welcome extends Controller {

	use Singleton;

	const WELCOME_MODAL_DISMISSED_OPTION = 'wds-welcome-modal-dismissed';

	/**
	 * Version of the welcome modal feature.
	 *
	 * @since 3.14.0
	 */
	const WELCOME_MODAL_FEATURE_VERSION = '3.14.0';

	/**
	 * Initialize the modal.
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'wp_ajax_wds-close-welcome-modal', array( $this, 'close_modal' ) );
		add_action( 'wp_ajax_wds_save_welcome_modal', array( $this, 'save_modal' ) );
		add_action( 'wds-dshboard-after_settings', array( $this, 'show_modal' ) );
	}

	/**
	 * Close the welcome modal and set the flag.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function close_modal() {
		$request_data = $this->get_request_data();
		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}

		// Set flag for dismissal.
		Settings::update_specific_options(
			self::WELCOME_MODAL_DISMISSED_OPTION,
			SMARTCRAWL_VERSION
		);

		wp_send_json_success();
	}

	/**
	 * Show welcome modal when required.
	 *
	 * @since 3.4.0 Renamed method.
	 *
	 * @return void
	 */
	public function show_modal() {
		$dismissed_version = Settings::get_specific_options( self::WELCOME_MODAL_DISMISSED_OPTION, '1.0.0' );
		$not_dismissed     = version_compare( $dismissed_version, self::WELCOME_MODAL_FEATURE_VERSION, '<' );
		$onboarding_done   = Settings::get_specific_options( Onboard::ONBOARDING_DONE_OPTION );
		$is_fresh_install  = ! SmartCrawl::get_last_version();

		if ( $onboarding_done && $not_dismissed && ! $is_fresh_install && ! White_Label::get()->is_hide_wpmudev_doc_link() ) {
			Simple_Renderer::render( 'dashboard/dashboard-welcome-modal' );
		}
	}

	/**
	 * Save the welcome modal and set the flag.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function save_modal() {
		$request_data = $this->get_request_data();

		if ( empty( $request_data ) ) {
			wp_send_json_error();
		}

		$admin_url = 'https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_instant-indexing_upgrade-modal_upgrade-button';

		// Check if the user is a member of WPMU DEV.
		$service = Service::get( Service::SERVICE_SITE );
		if ( $service->is_member() ) {
			$options                                    = Settings::get_specific_options( 'wds_settings_options' );
			$options[ Settings::COMP_INSTANT_INDEXING ] = 1;
			Settings::update_specific_options( 'wds_settings_options', $options );
			$admin_url = admin_url( 'admin.php?page=wds_instant_indexing' );
		}

		// Set flag for dismissal.
		Settings::update_specific_options(
			self::WELCOME_MODAL_DISMISSED_OPTION,
			SMARTCRAWL_VERSION
		);

		wp_send_json_success( array(
			'redirect_url' => esc_url( $admin_url ),
		) );
	}

	/**
	 * Get request data from ajax.
	 *
	 * @return array|mixed
	 */
	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wds_nonce'] ) ), 'wds-nonce' ) ? stripslashes_deep( $_POST ) : array();
	}
}