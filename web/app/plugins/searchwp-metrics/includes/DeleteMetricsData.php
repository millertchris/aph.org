<?php

namespace SearchWP_Metrics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DeleteMetricsData is responsible for deleting old Metrics data.
 *
 * @since 1.4.2
 */
class DeleteMetricsData {

	/**
	 * Stores the Metrics database prefix.
	 *
	 * @since 1.4.2
	 *
	 * @var string
	 */
	private $db_prefix;

	/**
	 * The current deletion job.
	 *
	 * @since 1.5.0
	 *
	 * @var DeletionJob
	 */
	private $deletion_job;

	/**
	 * Flag to track if next processing step has been triggered in current request.
	 *
	 * @since 1.5.0
	 *
	 * @var bool
	 */
	private $next_step_triggered = false;

	/**
	 * DeleteMetricsData constructor.
	 *
	 * @since 1.4.2
	 *
	 * @param string $db_prefix WPDB + Metrics DB prefix.
	 */
	public function __construct( $db_prefix ) {

		$this->db_prefix = $db_prefix;
	}

	/**
	 * Init.
	 *
	 * @since 1.4.2
	 */
	public function init() {
		// AJAX endpoints.
		add_action( 'wp_ajax_searchwp_metrics_clear_metrics_data_before', [ $this, 'clear_metrics_data_before' ] );
		add_action( 'wp_ajax_searchwp_metrics_clear_data_process_status', [ $this, 'searchwp_metrics_clear_data_process_status' ] );
		add_action( 'wp_ajax_nopriv_searchwp_metrics_process_deletion_step', [ $this, 'process_deletion_step' ] );
		add_action( 'wp_ajax_searchwp_metrics_process_deletion_step', [ $this, 'process_deletion_step' ] );
		add_action( 'wp_ajax_searchwp_metrics_restart_failed_job', [ $this, 'restart_failed_job' ] );
		add_action( 'wp_ajax_searchwp_metrics_cancel_job', [ $this, 'cancel_job' ] );

		// Schedule automatic clearing of data at set intervals.
		if ( ! wp_next_scheduled( SEARCHWP_METRICS_PREFIX . 'maintenance' ) ) {
			wp_schedule_event( time(), 'daily', SEARCHWP_METRICS_PREFIX . 'maintenance' );
		}

		add_action( SEARCHWP_METRICS_PREFIX . 'maintenance', [ $this, 'maintenance' ] );

		// Add admin notice about failed jobs.
		add_action( 'admin_notices', [ $this, 'display_failed_job_notice' ] );
	}

	/**
	 * Callback for ajax endpoint to clear Metrics data before a specified date.
	 *
	 * @since 1.4.2
	 */
	public function clear_metrics_data_before() {

		check_ajax_referer( 'searchwp_metrics_ajax' );

		/**
		 * Filter the capability required to cancel a deletion job.
		 *
		 * @param string $settings_cap The capability required to cancel a deletion job.
		 */
		$settings_cap = apply_filters( 'searchwp_metrics_capability_settings', 'manage_options' );

		if ( ! current_user_can( $settings_cap ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
		}

		// Check if we have a date parameter.
		if ( ! isset( $_POST['date'] ) ) {
			wp_send_json_error( [ 'message' => 'Missing date parameter' ] );
		}

		$date     = sanitize_text_field( wp_unslash( $_POST['date'] ) );
		$job_type = isset( $_POST['job_type'] ) ? sanitize_text_field( wp_unslash( $_POST['job_type'] ) ) : 'manual';

		$this->deletion_job = new DeletionJob();
		$added              = $this->deletion_job->add_new_job( $date, $job_type );

		if ( ! $added ) {
			wp_send_json_error(
				[
					'message'   => 'Job queue is full. Please wait for current jobs to complete.',
					'jobs_data' => $this->deletion_job->get_all_jobs_data(),
				]
			);

			return;
		}

		// Start the HTTP daisy chain.
		$this->trigger_next_processing_step();

		wp_send_json_success(
			[
				'message'  => 'Job started successfully',
				'job_data' => $this->deletion_job->get_job_data(),
			]
		);
	}

	/**
	 * Maintenance routine to clear old data automatically and check for stalled jobs.
	 *
	 * @since 1.4.5
	 */
	public function maintenance() {
		// First, check if there's a failed job that needs to be restarted.
		$this->deletion_job = new DeletionJob();
		$job_state          = $this->deletion_job->get_active_job();

		if ( $job_state && $this->deletion_job->is_failed() ) {
			// Job has failed, mark it as failed without cleanup to allow for retry.
			$this->deletion_job->mark_as_failed();

			return;
		}

		// If there's an active job already, don't create a new one.
		if ( $job_state && ! $this->deletion_job->is_complete() ) {

			// Trigger the HTTP daisy chain.
			$this->trigger_next_processing_step();

			return;
		}

		// No active job or job is complete, check if we need to create a new automatic job.
		$clear_metrics_interval = get_option( SEARCHWP_METRICS_PREFIX . 'clear_data_interval' );

		if ( empty( $clear_metrics_interval ) || ! is_numeric( $clear_metrics_interval ) ) {
			return;
		}

		$clear_metrics_interval = absint( $clear_metrics_interval );

		// Get the date to delete data before.
		$date = gmdate( 'Y-m-d', strtotime( '-' . $clear_metrics_interval . ' days' ) );

		// Create a new automatic job.
		$this->deletion_job->add_new_job(
			$date,
			'automatic',
			[ 'interval_days' => $clear_metrics_interval ]
		);

		// Trigger the HTTP daisy chain to start the process.
		// The process_deletion_step method will handle preparation and deletion.
		$this->trigger_next_processing_step();
	}

	/**
	 * Trigger the next processing step via a non-blocking HTTP request.
	 * Will only trigger once per HTTP request to prevent duplicate processing.
	 *
	 * @since 1.5.0
	 */
	private function trigger_next_processing_step() {

		// If we've already triggered the next step in this request, don't do it again.
		if ( $this->next_step_triggered ) {
			return;
		}

		// Prepare the request arguments.
		$admin_url = admin_url( 'admin-ajax.php' );

		// Get the security token from the current job.
		$security_token = $this->deletion_job->get_security_token();

		$args = [
			'timeout'   => 0.1,
			'blocking'  => false,
			'cookies'   => $_COOKIE,
			'body'      => [
				'action'         => 'searchwp_metrics_process_deletion_step',
				'security_token' => $security_token,
			],
			'sslverify' => false,
		];

		// Make a non-blocking request.
		wp_remote_post( $admin_url, $args );

		// Mark that we've triggered the next step in this request.
		$this->next_step_triggered = true;
	}

	/**
	 * Process a deletion step via AJAX and continue the chain.
	 *
	 * @since 1.5.0
	 */
	public function process_deletion_step() {

		$this->deletion_job = new DeletionJob();

		// Check if the job exists and is not complete or failed.
		if ( ! $this->deletion_job->get_active_job() ||
			$this->deletion_job->is_complete() ||
			$this->deletion_job->is_failed() ) {
			return;
		}

		// Verify security token.
		if ( ! $this->verify_security_token() ) {
			wp_die( 'Security check failed', 'Security Error', [ 'response' => 403 ] );
		}

		// Handle job status and processing.
		$this->handle_job_processing();

		// Handle job completion and continuation.
		$this->handle_job_completion();
	}

	/**
	 * Verify the security token for the deletion job.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if token is valid, false otherwise.
	 */
	private function verify_security_token() {

		$job_token     = $this->deletion_job->get_security_token();
		$request_token = isset( $_POST['security_token'] ) ? wp_unslash( $_POST['security_token'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return ! empty( $job_token ) && hash_equals( $job_token, $request_token );
	}

	/**
	 * Handle job processing based on current status.
	 *
	 * @since 1.5.0
	 */
	private function handle_job_processing() {

		// Check job status and determine what to do.
		$status = $this->deletion_job->get_status();

		// Check if preparation is stalled.
		if ( $status === 'preparing' && $this->deletion_job->is_preparation_stalled() ) {
			// Force transition to in_progress if preparation is stalled.
			$this->deletion_job->set_status( 'in_progress' );
			$status = 'in_progress'; // Update status for subsequent checks.
		}

		if ( $status === 'in_progress' && ! $this->deletion_job->all_tables_have_counts() ) {
			// Job is new and needs preparation.
			$this->deletion_job->set_status( 'preparing' );
			$this->process_preparation_step();
			$this->trigger_next_processing_step();

			return;
		}

		if ( $status === 'preparing' ) {
			// Check if all tables already have counts.
			if ( $this->deletion_job->all_tables_have_counts() ) {
				// All tables have counts, skip to deletion phase.
				$this->deletion_job->set_status( 'in_progress' );
				$this->process_deletion_chunk();
			} else {
				// Continue with preparation.
				$this->process_preparation_step();
			}
			$this->trigger_next_processing_step();

			return;
		}

		if ( $status === 'in_progress' ) {
			// Job is in progress, process deletion.
			$this->process_deletion_chunk();
		}
	}

	/**
	 * Handle job completion and continuation.
	 *
	 * @since 1.5.0
	 */
	private function handle_job_completion() {

		// If the job is complete, clean up and check for queued jobs.
		if ( $this->deletion_job->is_complete() ) {
			$started_next = $this->deletion_job->cleanup();

			if ( $started_next ) {
				// A new job was started from the queue.
				$this->trigger_next_processing_step();
			}
		} else {
			// Continue processing this job.
			$this->trigger_next_processing_step();
		}
	}

	/**
	 * Get the status of current deletion process.
	 *
	 * @since 1.5.0
	 */
	public function searchwp_metrics_clear_data_process_status() {

		check_ajax_referer( 'searchwp_metrics_ajax' );

		$deletion_job = new DeletionJob();

		if ( ! $deletion_job->get_active_job() ) {
			wp_send_json_success( [ 'status' => 'no_job' ] );

			return;
		}

		wp_send_json_success(
			[
				'job_data' => $deletion_job->get_job_data(),
			]
		);
	}

	/**
	 * Process a preparation step to count entries for a single table.
	 *
	 * @since 1.5.0
	 */
	private function process_preparation_step() {

		if ( ! $this->deletion_job ) {
			return;
		}

		$date = $this->deletion_job->get_date_threshold();

		// Get the current table being prepared.
		$current_table = $this->deletion_job->get_current_preparation_table();

		if ( ! $current_table ) {
			// All tables have been prepared, set status to in_progress.
			$this->deletion_job->set_status( 'in_progress' );

			return;
		}

		// Count entries for the current table.
		$count_method = "count_metrics_{$current_table}";
		if ( method_exists( $this, $count_method ) ) {
			$count = $this->$count_method( $date );
			$this->deletion_job->update_table_total_count( $current_table, $count );
		}

		// Move to the next table for preparation.
		$this->deletion_job->move_to_next_preparation_table();

		// Check if all tables have counts after updating this one.
		if ( $this->deletion_job->all_tables_have_counts() ) {
			// All tables have counts, transition to in_progress.
			$this->deletion_job->set_status( 'in_progress' );
		}
	}

	/**
	 * Process a chunk of data for deletion.
	 *
	 * @since 1.5.0
	 */
	private function process_deletion_chunk() {

		if ( ! $this->deletion_job ) {
			return;
		}

		$current_table = $this->deletion_job->get_current_table();
		$date          = $this->deletion_job->get_date_threshold();
		$batch_size    = $this->deletion_job->get_batch_size();

		try {
			// Process the current table and get the number of processed items.
			$processed = $this->process_table_deletion( $current_table, $date, $batch_size );

			// Update progress.
			$this->deletion_job->update_table_progress( $processed );

			// Handle zero processed items case.
			$this->handle_zero_processed_items( $processed, $current_table, $date );

			// Check if job is complete.
			if ( $this->deletion_job->is_complete() ) {
				$this->deletion_job->cleanup();
			}
		} catch ( \Exception $e ) {
			// If an error occurs during deletion, mark the job as failed.
			$this->deletion_job->mark_as_failed();
		}
	}

	/**
	 * Process deletion for a specific table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $table      The table to process.
	 * @param string $date       The date threshold.
	 * @param int    $batch_size The batch size.
	 *
	 * @return int The number of processed items.
	 */
	private function process_table_deletion( $table, $date, $batch_size ) {

		// phpcs:disable WPForms.Formatting.EmptyLineBeforeReturn

		switch ( $table ) {
			case 'clicks':
				return $this->delete_metrics_clicks_chunk( $date, $batch_size );

			case 'click_buoy':
				return $this->delete_metrics_click_buoy_chunk( $date, $batch_size );

			case 'queries':
				return $this->delete_metrics_queries_chunk( $date, $batch_size );

			case 'ids_uid':
				return $this->delete_metrics_ids_uid_chunk( $date, $batch_size );

			case 'ids_hash':
				return $this->delete_metrics_ids_hash_chunk( $date, $batch_size );

			case 'searches':
				return $this->delete_metrics_searches_chunk( $date, $batch_size );

			default:
				return 0;
		}
		// phpcs:enable WPForms.Formatting.EmptyLineBeforeReturn
	}

	/**
	 * Handle the case when zero items were processed.
	 *
	 * @since 1.5.0
	 *
	 * @param int    $processed     The number of processed items.
	 * @param string $current_table The current table.
	 * @param string $date          The date threshold.
	 */
	private function handle_zero_processed_items( $processed, $current_table, $date ) {
		// Skip if items were processed or table is already complete.
		if ( $processed !== 0 || $this->deletion_job->is_table_complete( $current_table ) ) {
			return;
		}

		// Check if there are any more entries to delete.
		$count_method = "count_metrics_{$current_table}";
		if ( ! method_exists( $this, $count_method ) ) {
			return;
		}

		$remaining_count = $this->$count_method( $date );
		if ( $remaining_count === 0 ) {
			// No more entries to delete, force mark as complete.
			$this->deletion_job->force_mark_table_complete( $current_table );
		}
	}

	/**
	 * Count entries to be deleted from the clicks table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date Delete data before this date.
	 *
	 * @return int Number of entries to delete.
	 */
	private function count_metrics_clicks( $date ) {

		global $wpdb;
		$sql = "
			SELECT COUNT(*)
			FROM {$this->db_prefix}clicks
			WHERE tstamp <= %s;
		";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete a chunk of entries from the clicks table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date       Delete data before this date.
	 * @param int    $batch_size The number of records to process.
	 *
	 * @return int The number of records processed.
	 */
	private function delete_metrics_clicks_chunk( $date, $batch_size ) {

		global $wpdb;

		$sql = "
			DELETE FROM {$this->db_prefix}clicks
			WHERE tstamp <= %s
			LIMIT %d;
		";

		$wpdb->query( $wpdb->prepare( $sql, $date, $batch_size ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $wpdb->rows_affected;
	}

	/**
	 * Count entries to be deleted from the click_buoy table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date Delete data before this date.
	 *
	 * @return int Number of entries to delete.
	 */
	private function count_metrics_click_buoy( $date ) {

		global $wpdb;
		$sql = "
			SELECT COUNT(pm.meta_id)
			FROM wp_postmeta AS pm
			INNER JOIN {$this->db_prefix}queries AS q ON pm.meta_key = CONCAT('{$this->db_prefix}click_buoy_', MD5(q.query))
			WHERE NOT EXISTS (
				SELECT 1
				FROM {$this->db_prefix}searches s
				WHERE s.query = q.id AND s.tstamp >= %s
			);
		";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete a chunk of click_buoy entries from the postmeta table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date       Delete data before this date.
	 * @param int    $batch_size The number of records to process.
	 *
	 * @return int The number of records processed.
	 */
	private function delete_metrics_click_buoy_chunk( $date, $batch_size ) {

		global $wpdb;

		// First, get a list of meta_keys to delete.
		$sql = "
			SELECT pm.meta_id
			FROM wp_postmeta AS pm
			INNER JOIN {$this->db_prefix}queries AS q ON pm.meta_key = CONCAT('{$this->db_prefix}click_buoy_', MD5(q.query))
			WHERE NOT EXISTS (
				SELECT 1
				FROM {$this->db_prefix}searches s
				WHERE s.query = q.id AND s.tstamp >= %s
			)
			LIMIT %d;
		";

		$meta_ids = $wpdb->get_col( $wpdb->prepare( $sql, $date, $batch_size ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( empty( $meta_ids ) ) {
			return 0;
		}

		// Delete the found meta entries.
		$meta_ids_string = implode( ',', array_map( 'absint', $meta_ids ) );
		$delete_sql      = "DELETE FROM {$wpdb->postmeta} WHERE meta_id IN ($meta_ids_string)";

		$wpdb->query( $delete_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $wpdb->rows_affected;
	}

	/**
	 * Count entries to be deleted from the queries table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date Delete data before this date.
	 *
	 * @return int Number of entries to delete.
	 */
	private function count_metrics_queries( $date ) {

		global $wpdb;
		$sql = "
			SELECT COUNT(q.id)
			FROM {$this->db_prefix}queries AS q
			WHERE q.id NOT IN (
				SELECT s.query
				FROM {$this->db_prefix}searches AS s
				WHERE s.tstamp >= %s
			);
		";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete a chunk of entries from the queries table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date       Delete data before this date.
	 * @param int    $batch_size The number of records to process.
	 *
	 * @return int The number of records processed.
	 */
	private function delete_metrics_queries_chunk( $date, $batch_size ) {

		global $wpdb;

		// First, get a list of IDs to delete.
		$sql = "
			SELECT q.id
			FROM {$this->db_prefix}queries AS q
			WHERE q.id NOT IN (
				SELECT s.query
				FROM {$this->db_prefix}searches AS s
				WHERE s.tstamp >= %s
			)
			LIMIT %d;
		";

		$ids = $wpdb->get_col( $wpdb->prepare( $sql, $date, $batch_size ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( empty( $ids ) ) {
			return 0;
		}

		// Delete the found queries.
		$ids_string = implode( ',', array_map( 'absint', $ids ) );
		$delete_sql = "DELETE FROM {$this->db_prefix}queries WHERE id IN ($ids_string)";

		$wpdb->query( $delete_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $wpdb->rows_affected;
	}

	/**
	 * Count entries to be deleted from the ids table with type 'uid'.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date Delete data before this date.
	 *
	 * @return int Number of entries to delete.
	 */
	private function count_metrics_ids_uid( $date ) {

		global $wpdb;
		$sql = "
			SELECT COUNT(e.id)
			FROM {$this->db_prefix}ids AS e
            WHERE e.type = 'uid'
			AND NOT EXISTS (
                SELECT 1
                FROM {$this->db_prefix}searches s
                WHERE s.uid = e.id
                AND s.tstamp >= %s
            )
		";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete a chunk of entries from the ids table with type 'uid'.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date       Delete data before this date.
	 * @param int    $batch_size The number of records to process.
	 *
	 * @return int The number of records processed.
	 */
	private function delete_metrics_ids_uid_chunk( $date, $batch_size ) {

		global $wpdb;

		// Get a list of UIDs to delete.
		// This includes only 'uid' type IDs from the 'ids' table that are not associated with any searches after the given date.
		$sql = "
			SELECT e.id
			FROM {$this->db_prefix}ids AS e
            WHERE e.type = 'uid'
			AND NOT EXISTS (
                SELECT 1
                FROM {$this->db_prefix}searches s
                WHERE s.uid = e.id
                AND s.tstamp >= %s
            )
			LIMIT %d;
		";

		$ids = $wpdb->get_col( $wpdb->prepare( $sql, $date, $batch_size ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( empty( $ids ) ) {
			return 0;
		}

		// Delete the found UIDs.
		$ids_string = implode( "','", array_map( 'esc_sql', $ids ) );
		$delete_sql = "DELETE FROM {$this->db_prefix}ids WHERE type = 'uid' AND id IN ('$ids_string')";

		$wpdb->query( $delete_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $wpdb->rows_affected;
	}

	/**
	 * Count entries to be deleted from the ids table with type 'hash'.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date Delete data before this date.
	 *
	 * @return int Number of entries to delete.
	 */
	private function count_metrics_ids_hash( $date ) {

		global $wpdb;
		$sql = "
			SELECT COUNT(e.id)
			FROM {$this->db_prefix}ids AS e
            WHERE e.type = 'hash'
			AND NOT EXISTS (
                SELECT 1
                FROM {$this->db_prefix}searches s
                WHERE s.uid = e.id
                AND s.tstamp >= %s
            )
		";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete a chunk of entries from the ids table with type 'hash'.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date       Delete data before this date.
	 * @param int    $batch_size The number of records to process.
	 *
	 * @return int The number of records processed.
	 */
	private function delete_metrics_ids_hash_chunk( $date, $batch_size ) {

		global $wpdb;

		// Get a list of hashes to delete.
		// This includes only 'hash' type IDs from the 'ids' table that are not associated with any searches after the given date.
		$sql = "
			SELECT e.id
			FROM {$this->db_prefix}ids AS e
            WHERE e.type = 'hash'
			AND NOT EXISTS (
                SELECT 1
                FROM {$this->db_prefix}searches s
                WHERE s.uid = e.id
                AND s.tstamp >= %s
            )
			LIMIT %d;
		";

		$ids = $wpdb->get_col( $wpdb->prepare( $sql, $date, $batch_size ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( empty( $ids ) ) {
			return 0;
		}

		// Delete the found hashes.
		$ids_string = implode( "','", array_map( 'esc_sql', $ids ) );
		$delete_sql = "DELETE FROM {$this->db_prefix}ids WHERE type = 'hash' AND id IN ('$ids_string')";

		$wpdb->query( $delete_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $wpdb->rows_affected;
	}

	/**
	 * Count entries to be deleted from the searches table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date Delete data before this date.
	 *
	 * @return int Number of entries to delete.
	 */
	private function count_metrics_searches( $date ) {

		global $wpdb;
		$sql = "
			SELECT COUNT(*)
			FROM {$this->db_prefix}searches
			WHERE tstamp <= %s;
		";

		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Delete a chunk of entries from the searches table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date       Delete data before this date.
	 * @param int    $batch_size The number of records to process.
	 *
	 * @return int The number of records processed.
	 */
	private function delete_metrics_searches_chunk( $date, $batch_size ) {

		global $wpdb;

		$sql = "
			DELETE FROM {$this->db_prefix}searches
			WHERE tstamp <= %s
			LIMIT %d;
		";

		$wpdb->query( $wpdb->prepare( $sql, $date, $batch_size ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $wpdb->rows_affected;
	}

	/**
	 * Cancel the current deletion job.
	 *
	 * @since 1.5.0
	 */
	private function cancel_deletion_job() {

		$this->deletion_job = new DeletionJob(); // Date will be loaded from state.

		if ( $this->deletion_job->get_job_state() ) {
			$this->deletion_job->cancel();
			$this->deletion_job->cleanup();
		}
	}

	/**
	 * Restart a failed job via AJAX.
	 *
	 * @since 1.5.0
	 */
	public function restart_failed_job() {

		check_ajax_referer( 'searchwp_metrics_ajax' );

		/**
		 * Filter the capability required to cancel a deletion job.
		 *
		 * @param string $settings_cap The capability required to cancel a deletion job.
		 */
		$settings_cap = apply_filters( 'searchwp_metrics_capability_settings', 'manage_options' );

		if ( ! current_user_can( $settings_cap ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
		}

		$this->deletion_job = new DeletionJob();
		$restarted          = $this->deletion_job->restart();
		$this->trigger_next_processing_step();

		if ( $restarted ) {
			wp_send_json_success(
				[
					'message'  => 'Job restarted successfully',
					'job_data' => $this->deletion_job->get_job_data(),
				]
			);
		} else {
			wp_send_json_error( [ 'message' => 'No failed job to restart' ] );
		}
	}

	/**
	 * Cancel the current job via AJAX.
	 *
	 * @since 1.5.0
	 */
	public function cancel_job() {

		check_ajax_referer( 'searchwp_metrics_ajax' );

		/**
		 * Filter the capability required to cancel a deletion job.
		 *
		 * @param string $settings_cap The capability required to cancel a deletion job.
		 */
		$settings_cap = apply_filters( 'searchwp_metrics_capability_settings', 'manage_options' );

		if ( ! current_user_can( $settings_cap ) ) {
			wp_send_json_error( [ 'message' => 'Insufficient permissions' ] );
		}

		$this->deletion_job = new DeletionJob();
		$this->deletion_job->cancel();
		$this->deletion_job->cleanup();

		wp_send_json_success( [ 'message' => 'Job canceled successfully' ] );
	}

	/**
	 * Display an admin notice for failed deletion jobs.
	 *
	 * @since 1.5.0
	 */
	public function display_failed_job_notice() {

		// If this is the Metrics page, don't display the notice.
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'searchwp-metrics' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$metrics_page_url = admin_url( 'admin.php?page=searchwp-metrics' );
		$deletion_job     = new DeletionJob();
		$job_state        = $deletion_job->get_job_state();

		if ( empty( $job_state['status'] ) || $job_state['status'] !== 'failed' ) {
			return;
		}
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses(
					__( '<strong>SearchWP Metrics:</strong> A data deletion job was interrupted. Please visit the Metrics page to resume it or cancel it.', 'searchwp-metrics' ),
					[
						'strong' => [],
					]
				);
				?>
				<a href="<?php echo esc_url( $metrics_page_url ); ?>" class="button button-secondary">
					<?php esc_html_e( 'View Metrics', 'searchwp-metrics' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
