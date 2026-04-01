<?php
/**
 * The import functionality class.
 *
 * This is a background processing class.
 * NOTE: Original Background Process package supports
 * parallel queuing. But we don't support parallel queueing
 * yet to support counts without any complex changes.
 *
 * @link    https://wpmudev.com
 * @since   1.8.1
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Controllers
 */

namespace WPMUDEV_Videos\Core\Tasks;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Helpers\Cache;
use WPMUDEV_Videos\Core\External\Queue\Background_Process;

/**
 * Class Import
 *
 * @package WPMUDEV_Videos\Core\Controllers
 */
class Import extends Background_Process {

	/**
	 * Current action name.
	 *
	 * @since 1.8.1
	 *
	 * @var string $action
	 */
	protected $action = 'ivt_import_process';

	/**
	 * Perform the single item import action.
	 *
	 * Import a video or playlist item to the database and
	 * return false to process next item.
	 *
	 * @param mixed $item Queue item.
	 *
	 * @since 1.8.1
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		if ( isset( $item['type'] ) ) {
			if ( 'video' === $item['type'] ) {
				// Import video.
				Import\Video::get()->import( $item );
			} elseif ( 'playlist' === $item['type'] ) {
				// Import playlist.
				Import\Playlist::get()->import( $item );
			}
		}

		// Increase the completed count.
		$this->increment_completed_count();

		return false;
	}

	/**
	 * Save the current queue.
	 *
	 * NOTE: Currently total count is supported only for a single
	 * queue. If you save multiple queues, you need to change how
	 * the count is calculated.
	 *
	 * @since 1.8.1
	 *
	 * @return $this
	 */
	public function save() {
		// First cleanup previous items.
		$this->cleanup();

		// Save the batch.
		parent::save();

		if ( ! empty( $this->data ) ) {
			// Update the count.
			$this->set_total_count();

			// Update the completed count.
			update_site_option( $this->identifier . '_completed_count', 0 );
		}

		return $this;
	}

	/**
	 * Perform some actions when queue is complete.
	 *
	 * Clear counts after queue is finished.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	protected function complete() {
		parent::complete();

		// Delete the total and completed count.
		delete_site_option( $this->identifier . '_total_count' );
		delete_site_option( $this->identifier . '_completed_count' );

		// Refresh entire cache.
		Cache::refresh_cache();
	}

	/**
	 * Get the queue status of the current process.
	 *
	 * To show the progress bar, get the current process
	 * queue status.
	 *
	 * @since 1.8.1
	 *
	 * @return array $status
	 */
	public function get_status() {
		$status = array(
			'completed' => 0,
			'total'     => 0,
		);

		// Only if process is running.
		if ( $this->is_process_running() ) {
			// Get the no. of remaining items.
			$status['completed'] = $this->get_completed_count();

			// Get the no. of remaining items.
			$status['total'] = $this->get_total_count();
		}

		return $status;
	}

	/**
	 * Get the total items count.
	 *
	 * This count includes all queue items processed.
	 *
	 * @since 1.8.1
	 *
	 * @return int
	 */
	public function get_total_count() {
		return (int) get_site_option( $this->identifier . '_total_count', 0 );
	}

	/**
	 * Get the completed items count.
	 *
	 * This count includes all queue items processed.
	 *
	 * @since 1.8.1
	 *
	 * @return int
	 */
	public function get_completed_count() {
		return (int) get_site_option( $this->identifier . '_completed_count', 0 );
	}

	/**
	 * Increment the completed items count in queue.
	 *
	 * Please note that you need to clear this once
	 * the queue is finished.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	private function increment_completed_count() {
		// Get the existing count.
		$count = $this->get_completed_count() + 1;

		// Update the count.
		update_site_option( $this->identifier . '_completed_count', $count );
	}

	/**
	 * Set the total count of the queue.
	 *
	 * Multiple queues are not supported, so we will
	 * overwrite every time when you update.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	private function set_total_count() {
		// Add the current queue count.
		$count = count( $this->data );

		// Update the count.
		update_site_option( $this->identifier . '_total_count', $count );
	}

	/**
	 * Before saving new queue, cleanup existing ones.
	 *
	 * We don't support multiple queueing to get the API working
	 * with the import status.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	private function cleanup() {
		global $wpdb;

		if ( is_multisite() ) {
			$table  = $wpdb->sitemeta;
			$column = 'meta_key';
		} else {
			$table  = $wpdb->options;
			$column = 'option_name';
		}

		// phpcs:ignore
		$keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT {$column} FROM {$table} WHERE {$column} LIKE %s", // phpcs:ignore
				$wpdb->esc_like( $this->identifier . '_batch_' ) . '%'
			)
		);

		// Delete all existing queues.
		if ( ! empty( $keys ) ) {
			foreach ( $keys as $key ) {
				delete_site_option( $key );
			}
		}
	}
}