<?php

namespace SearchWP_Metrics;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DeletionJob is responsible for managing the state of metrics data deletion jobs.
 *
 * @since 1.5.0
 */
class DeletionJob {

	/**
	 * The unique identifier for this job.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $job_id;

	/**
	 * The timestamp when the job was started.
	 *
	 * @since 1.5.0
	 *
	 * @var int
	 */
	private $start_date;

	/**
	 * The date threshold for deletion (delete data before this date).
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $date_threshold;

	/**
	 * The tables to process and their progress.
	 *
	 * @since 1.5.0
	 *
	 * @var array
	 */
	private $tables;

	/**
	 * The current table being processed.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $current_table;

	/**
	 * The overall progress percentage.
	 *
	 * @since 1.5.0
	 *
	 * @var int
	 */
	private $overall_progress;

	/**
	 * The status of the job (queued, in_progress, complete, failed, error, canceled).
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $status;

	/**
	 * The timestamp of the last update to this job.
	 *
	 * @since 1.5.0
	 *
	 * @var int
	 */
	private $last_updated;

	/**
	 * The option name where the active job state is stored.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $active_job_option;

	/**
	 * The option name where the job queue is stored.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $job_queue_option;

	/**
	 * The job type (manual, automatic).
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $job_type;

	/**
	 * The security token for this job.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	private $security_token;

	/**
	 * Additional metadata for this job.
	 *
	 * @since 1.5.0
	 *
	 * @var array
	 */
	private $metadata = [];

	/**
	 * The maximum number of allowed queued jobs.
	 *
	 * @since 1.5.0
	 *
	 * @var int
	 */
	private $max_queue_size = 3;

	/**
	 * The batch size for processing chunks.
	 *
	 * @since 1.5.0
	 *
	 * @var int
	 */
	private $batch_size;

	/**
	 * DeletionJob constructor.
	 *
	 * @since 1.5.0
	 */
	public function __construct() {

		$this->active_job_option = SEARCHWP_METRICS_PREFIX . 'deletion_job';
		$this->job_queue_option  = SEARCHWP_METRICS_PREFIX . 'deletion_job_queue';
		/**
		 * Filters the maximum number of entries to delete in a single job.
		 *
		 * @param int $batch_size The batch size for processing chunks.
		 */
		$this->batch_size = absint( apply_filters( 'searchwp_metrics_deletion_job_batch_size', 1000 ) );

		// Try to load the active job state.
		$active_job = $this->get_active_job();
		if ( $active_job ) {
			$this->load_from_state( $active_job );
		}
	}

	/**
	 * Add a new deletion job.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date_threshold Delete data before this date.
	 * @param string $job_type       The type of job (manual or automatic).
	 * @param array  $metadata       Additional metadata for the job.
	 *
	 * @return bool True if job was added, false if queue is full.
	 */
	public function add_new_job( $date_threshold, $job_type = 'manual', $metadata = [] ) {

		// Check if an active job exists.
		$active_job = $this->get_active_job();

		// If there's no active job, create one and set it as active.
		if ( ! $active_job ) {
			$this->initialize_new_job( $date_threshold, $job_type, $metadata );
			$this->save_active_job();

			return true;
		}

		// Otherwise, add to the queue if there's space.
		$queue = $this->get_job_queue();

		if ( count( $queue ) >= $this->max_queue_size ) {
			return false; // The queue is full.
		}

		// Add a job to the queue.
		$new_job = [
			'job_id'         => uniqid( 'swpm_del_', true ),
			'date_threshold' => $date_threshold,
			'status'         => 'queued',
			'job_type'       => $job_type,
			'created'        => time(),
			'metadata'       => $metadata,
		];

		$queue[] = $new_job;
		$this->save_job_queue( $queue );

		return true;
	}

	/**
	 * Initialize a new job with the provided settings.
	 *
	 * @since 1.5.0
	 *
	 * @param string $date_threshold Delete data before this date.
	 * @param string $job_type       The type of job (manual or automatic).
	 * @param array  $metadata       Additional metadata for the job.
	 */
	private function initialize_new_job( $date_threshold, $job_type = 'manual', $metadata = [] ) {

		$this->job_id         = uniqid( 'swpm_del_', true );
		$this->start_date     = time();
		$this->last_updated   = time();
		$this->date_threshold = $date_threshold;
		$this->job_type       = $job_type;
		$this->metadata       = $metadata;
		$this->security_token = wp_generate_password( 64, true, true );
		// phpcs:disable WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		$this->tables = [
			'clicks'     => [ 'total' => null, 'processed' => 0, 'complete' => false ],
			'click_buoy' => [ 'total' => null, 'processed' => 0, 'complete' => false ],
			'queries'    => [ 'total' => null, 'processed' => 0, 'complete' => false ],
			'ids_uid'    => [ 'total' => null, 'processed' => 0, 'complete' => false ],
			'ids_hash'   => [ 'total' => null, 'processed' => 0, 'complete' => false ],
			'searches'   => [ 'total' => null, 'processed' => 0, 'complete' => false ],
		];
		// phpcs:enable
		$this->current_table    = 'clicks';
		$this->overall_progress = 0;
		$this->status           = 'in_progress';
	}

	/**
	 * Load job state from an existing state array.
	 *
	 * @since 1.5.0
	 *
	 * @param array $state The job state.
	 */
	private function load_from_state( $state ) {

		$this->job_id           = $state['job_id'];
		$this->start_date       = $state['start_date'];
		$this->date_threshold   = $state['date_threshold'];
		$this->tables           = $state['tables'];
		$this->current_table    = $state['current_table'];
		$this->overall_progress = $state['overall_progress'];
		$this->status           = $state['status'];
		$this->last_updated     = isset( $state['last_updated'] ) ? $state['last_updated'] : time();
		$this->job_type         = isset( $state['job_type'] ) ? $state['job_type'] : 'manual';
		$this->security_token   = isset( $state['security_token'] ) ? $state['security_token'] : null;
		$this->metadata         = isset( $state['metadata'] ) ? $state['metadata'] : [];
	}

	/**
	 * Get the active job from the database.
	 *
	 * @since 1.5.0
	 *
	 * @return array|false The job state or false if no active job exists.
	 */
	public function get_active_job() {

		$state = get_option( $this->active_job_option, false );

		if ( ! $state || ! is_array( $state ) ) {
			return false;
		}

		return $state;
	}

	/**
	 * Save the current job as the active job.
	 *
	 * @since 1.5.0
	 */
	public function save_active_job() {

		$this->last_updated = time();

		$state = [
			'job_id'           => $this->job_id,
			'start_date'       => $this->start_date,
			'date_threshold'   => $this->date_threshold,
			'tables'           => $this->tables,
			'current_table'    => $this->current_table,
			'overall_progress' => $this->overall_progress,
			'status'           => $this->status,
			'last_updated'     => $this->last_updated,
			'job_type'         => $this->job_type,
			'security_token'   => $this->security_token,
			'metadata'         => $this->metadata,
		];

		update_option( $this->active_job_option, $state, false );
	}

	/**
	 * Get the job queue from the database.
	 *
	 * @since 1.5.0
	 *
	 * @return array The job queue.
	 */
	public function get_job_queue() {

		$queue = get_option( $this->job_queue_option, [] );

		if ( ! is_array( $queue ) ) {
			return [];
		}

		return $queue;
	}

	/**
	 * Save the job queue to the database.
	 *
	 * @since 1.5.0
	 *
	 * @param array $queue The job queue to save.
	 */
	public function save_job_queue( $queue ) {

		update_option( $this->job_queue_option, $queue, false );
	}

	/**
	 * Get the current job state from the database.
	 * Alias for backward compatibility.
	 *
	 * @since 1.5.0
	 *
	 * @return array|false The job state or false if no job exists.
	 */
	public function get_job_state() {

		return $this->get_active_job();
	}

	/**
	 * Save the current job state to the database.
	 * Alias for backward compatibility.
	 *
	 * @since 1.5.0
	 */
	public function save_state() {

		$this->save_active_job();
	}

	/**
	 * Update the progress for the current table.
	 *
	 * @since 1.5.0
	 *
	 * @param int $processed The number of records processed.
	 */
	public function update_table_progress( $processed ) {

		if ( ! isset( $this->tables[ $this->current_table ] ) ) {
			return;
		}

		$this->tables[ $this->current_table ]['processed'] += $processed;

		// Mark as complete if processed count matches total.
		if ( $this->tables[ $this->current_table ]['processed'] >= $this->tables[ $this->current_table ]['total'] ) {
			$this->tables[ $this->current_table ]['complete'] = true;
			$this->move_to_next_table();
		}

		$this->calculate_overall_progress();
		$this->save_state();
	}

	/**
	 * Move to the next table for processing.
	 *
	 * @since 1.5.0
	 */
	private function move_to_next_table() {

		$tables        = array_keys( $this->tables );
		$current_index = array_search( $this->current_table, $tables, true );

		if ( $current_index !== false && isset( $tables[ $current_index + 1 ] ) ) {
			$this->current_table = $tables[ $current_index + 1 ];
		} else {
			// All tables are complete.
			$this->status = 'complete';
		}
	}

	/**
	 * Calculate the overall progress percentage based on entries processed.
	 *
	 * @since 1.5.0
	 */
	private function calculate_overall_progress() {

		$total_entries     = 0;
		$processed_entries = 0;

		foreach ( $this->tables as $table ) {
			if ( isset( $table['total'] ) && $table['total'] > 0 ) {
				$total_entries     += $table['total'];
				$processed_entries += $table['processed'];
			}
		}

		if ( $total_entries > 0 ) {
			$this->overall_progress = round( ( $processed_entries / $total_entries ) * 100 );
		} else {
			// Fall back to table-based calculation if no totals are available.
			$total_tables     = count( $this->tables );
			$completed_tables = 0;

			foreach ( $this->tables as $table ) {
				if ( $table['complete'] ) {
					++$completed_tables;
				}
			}

			$this->overall_progress = $total_tables > 0 ? round( ( $completed_tables / $total_tables ) * 100 ) : 0;
		}

		// Ensure we don't exceed 100%.
		$this->overall_progress = min( 100, $this->overall_progress );
	}

	/**
	 * Get the batch size for processing.
	 *
	 * @since 1.5.0
	 *
	 * @return int The batch size.
	 */
	public function get_batch_size() {

		return $this->batch_size;
	}

	/**
	 * Get the date threshold for deletion.
	 *
	 * @since 1.5.0
	 *
	 * @return string The date threshold.
	 */
	public function get_date_threshold() {

		return $this->date_threshold;
	}

	/**
	 * Get the current table being processed.
	 *
	 * @since 1.5.0
	 *
	 * @return string The current table.
	 */
	public function get_current_table() {

		return $this->current_table;
	}

	/**
	 * Get the overall progress percentage.
	 *
	 * @since 1.5.0
	 *
	 * @return int The overall progress percentage.
	 */
	public function get_overall_progress() {

		return $this->overall_progress;
	}

	/**
	 * Get the job status.
	 *
	 * @since 1.5.0
	 *
	 * @return string The job status.
	 */
	public function get_status() {

		return $this->status;
	}

	/**
	 * Get the security token for the job.
	 *
	 * @since 1.5.0
	 *
	 * @return string|null The security token or null if not set.
	 */
	public function get_security_token() {

		return $this->security_token;
	}

	/**
	 * Update the total count for a table.
	 *
	 * @since 1.5.0
	 *
	 * @param string $table The table name.
	 * @param int    $count The total count of entries to delete.
	 */
	public function update_table_total_count( $table, $count ) {

		if ( isset( $this->tables[ $table ] ) ) {
			$this->tables[ $table ]['total'] = $count;
			$this->save_state();
		}
	}

	/**
	 * Check if all tables have their total counts set.
	 *
	 * @since 1.5.0
	 *
	 * @return bool Whether all tables have their counts.
	 */
	public function all_tables_have_counts() {

		foreach ( $this->tables as $table ) {
			if ( ! isset( $table['total'] ) || $table['total'] === null ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the current table being prepared.
	 *
	 * @since 1.5.0
	 *
	 * @return string|null The current table or null if all tables are prepared.
	 */
	public function get_current_preparation_table() {
		// If we're not in preparation mode, return null.
		if ( $this->status !== 'preparing' ) {
			return null;
		}

		// Check if we have a preparation_table set.
		if ( isset( $this->metadata['preparation_table'] ) ) {
			return $this->metadata['preparation_table'];
		}

		// Start with the first table.
		$tables                              = array_keys( $this->tables );
		$this->metadata['preparation_table'] = $tables[0];
		$this->save_state();

		return $this->metadata['preparation_table'];
	}

	/**
	 * Move to the next table for preparation.
	 *
	 * @since 1.5.0
	 */
	public function move_to_next_preparation_table() {

		$tables        = array_keys( $this->tables );
		$current_table = $this->get_current_preparation_table();

		if ( ! $current_table ) {
			return;
		}

		$current_index = array_search( $current_table, $tables, true );

		if ( $current_index !== false && isset( $tables[ $current_index + 1 ] ) ) {
			// Move to the next table.
			$this->metadata['preparation_table'] = $tables[ $current_index + 1 ];
		} else {
			// All tables are prepared.
			unset( $this->metadata['preparation_table'] );
		}

		$this->save_state();
	}

	/**
	 * Set the job status.
	 *
	 * @since 1.5.0
	 *
	 * @param string $status The new status.
	 */
	public function set_status( $status ) {

		$this->status = $status;

		// Update last_update timestamp when changing status.
		$this->metadata['last_update'] = time();

		$this->save_state();
	}

	/**
	 * Check if the job is complete.
	 *
	 * @since 1.5.0
	 *
	 * @return bool Whether the job is complete.
	 */
	public function is_complete() {

		return $this->status === 'complete';
	}

	/**
	 * Check if a specific table is marked as complete.
	 *
	 * @since 1.5.0
	 *
	 * @param string $table The table name.
	 *
	 * @return bool Whether the table is complete.
	 */
	public function is_table_complete( $table ) {

		return isset( $this->tables[ $table ] ) && $this->tables[ $table ]['complete'];
	}

	/**
	 * Force mark a table as complete.
	 *
	 * @since 1.5.0
	 *
	 * @param string $table The table name.
	 */
	public function force_mark_table_complete( $table ) {

		if ( isset( $this->tables[ $table ] ) ) {
			$this->tables[ $table ]['complete'] = true;
			$this->move_to_next_table();
			$this->calculate_overall_progress();
			$this->save_state();
		}
	}

	/**
	 * Check if the job has failed or stalled.
	 *
	 * @since 1.5.0
	 *
	 * @return bool Whether the job has failed or stalled.
	 */
	public function is_failed() {
		// Check if job is already marked as failed.
		if ( $this->status === 'failed' ) {
			return true;
		}

		// Check if job is in progress but hasn't been updated for a while.
		$timeout = (int) ini_get( 'max_execution_time' );

		// If there's no time limit (0), we'll fall back to 30 minutes as a reasonable default for a stalled job.
		// We also add a buffer to the timeout value to prevent race conditions where the job is falsely marked as failed.
		if ( $timeout > 0 ) {
			// Add a 1-minute buffer to the execution time.
			$timeout += 60;
		} else {
			// Default to 30 minutes if execution time is unlimited.
			$timeout = 1800;
		}

		if ( in_array( $this->status, [ 'in_progress', 'preparing' ], true ) && ( time() - $this->last_updated ) > $timeout ) {
			$this->status = 'failed';
			$this->save_active_job();

			return true;
		}

		return false;
	}

	/**
	 * Cancel the current job.
	 *
	 * @since 1.5.0
	 */
	public function cancel() {

		$this->status = 'canceled';
		$this->save_active_job();
	}

	/**
	 * Clean up the current job and move to the next queued job if available.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if a new job was started, false otherwise.
	 */
	public function cleanup() {

		// Get the queue before deleting the current job.
		$queue = $this->get_job_queue();

		// Remove the current active job.
		delete_option( $this->active_job_option );

		// If there are queued jobs, start the next one.
		if ( ! empty( $queue ) ) {
			$next_job = array_shift( $queue );
			$this->save_job_queue( $queue );

			// Get metadata from the queued job if it exists.
			$metadata = isset( $next_job['metadata'] ) ? $next_job['metadata'] : [];

			// Initialize the next job.
			$this->initialize_new_job(
				$next_job['date_threshold'],
				$next_job['job_type'],
				$metadata
			);
			$this->save_active_job();

			return true;
		}

		return false;
	}

	/**
	 * Mark the active job as failed.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function mark_as_failed() {

		$this->status = 'failed';
		$this->save_active_job();
	}

	/**
	 * Check if the job preparation phase is stalled.
	 *
	 * @since 1.5.0
	 *
	 * @return bool Whether the job preparation is stalled.
	 */
	public function is_preparation_stalled() {

		if ( $this->status !== 'preparing' ) {
			return false;
		}

		// Check if the job has been in preparation for more than 5 minutes.
		$last_update = isset( $this->metadata['last_update'] ) ? $this->metadata['last_update'] : 0;

		return ( time() - $last_update ) > 300; // 5 minutes
	}

	/**
	 * Restart a failed job.
	 * This will reset the steps and start from the beginning.
	 *
	 * @since 1.5.0
	 *
	 * @return bool True if the job was restarted, false otherwise.
	 */
	public function restart() {

		if ( $this->status !== 'failed' && $this->status !== 'canceled' ) {
			return false;
		}

		// Set the job back to in_progress.
		$this->status       = 'in_progress';
		$this->last_updated = time();
		$this->save_active_job();

		return true;
	}

	/**
	 * Get the job data for AJAX responses.
	 *
	 * @since 1.5.0
	 *
	 * @return array The job data.
	 */
	public function get_job_data() {

		// Check if the job has failed due to timeout.
		$this->is_failed();

		// Get queue information.
		$queue       = $this->get_job_queue();
		$queue_count = count( $queue );

		// Prepare job data.
		$job_data = [
			'job_id'           => $this->job_id,
			'start_date'       => $this->start_date,
			'date_threshold'   => $this->date_threshold,
			'current_table'    => $this->current_table,
			'overall_progress' => $this->overall_progress,
			'status'           => $this->status,
			'tables'           => $this->tables,
			'last_updated'     => $this->last_updated,
			'job_type'         => $this->job_type,
			'queue_count'      => $queue_count,
			'max_queue_size'   => $this->max_queue_size,
			'security_token'   => $this->security_token,
		];

		// Add metadata if it exists.
		if ( ! empty( $this->metadata ) ) {
			// For automatic jobs, include interval_days in the main data structure.
			if ( isset( $this->metadata['interval_days'] ) ) {
				$job_data['interval_days'] = $this->metadata['interval_days'];
			}

			// Include all metadata.
			$job_data['metadata'] = $this->metadata;
		}

		return $job_data;
	}

	/**
	 * Get data about all jobs (active and queued).
	 *
	 * @since 1.5.0
	 *
	 * @return array Data about all jobs.
	 */
	public function get_all_jobs_data() {

		// Check if the active job has failed due to timeout.
		$this->is_failed();

		$queue      = $this->get_job_queue();
		$active_job = $this->get_job_data();

		return [
			'active_job'     => $active_job,
			'queued_jobs'    => $queue,
			'queue_count'    => count( $queue ),
			'max_queue_size' => $this->max_queue_size,
		];
	}

	/**
	 * Delete all jobs.
	 *
	 * @since 1.5.0
	 */
	public function delete_all_jobs() {

		delete_option( $this->active_job_option );
		delete_option( $this->job_queue_option );
	}
}
