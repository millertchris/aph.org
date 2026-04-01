<?php
/**
 * Class Controller
 *
 * @package SmartCrawl
 */

namespace SmartCrawl\Instant_Indexing;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Services\Service;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Controllers;

/**
 * Class Controller
 */
class Controller extends Controllers\Controller {

	use Singleton;

	/**
	 * Should this module run?.
	 *
	 * @return bool
	 */
	public function should_run() {
		$service   = Service::get( Service::SERVICE_SITE );
		$is_member = $service->is_member();

		return (
			Settings::get_setting( 'instant_indexing' ) &&
			Admin_Settings::is_tab_allowed( Settings::TAB_INSTANT_INDEXING ) &&
			$is_member
		);
	}

	/**
	 * Initialize the module.
	 *
	 * @return void
	 */
	protected function init() {
		if ( ! $this->should_run() ) {
			return;
		}
		IndexNow_Actions::get()->run();
		IndexNow_API::get()->run();
		IndexNow_REST::get()->run();
	}
}