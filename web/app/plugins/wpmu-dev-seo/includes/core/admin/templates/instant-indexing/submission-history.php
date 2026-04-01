<?php
/**
 * Template: Instant Indexing History.
 *
 * @package Smartcrwal
 */

$submissions = $submissions ?: array();
$submissions = array_slice( $submissions, 0, 100 );
usort( $submissions, function ( $a, $b ) {
	return $b['time'] <=> $a['time'];
} );

// Set pagination variables.
$results_per_page  = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 10;
$current_page      = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$total_submissions = count( $submissions );
$total_pages       = ceil( $total_submissions / $results_per_page );

// Get current page submissions.
$offset            = ( $current_page - 1 ) * $results_per_page;
$paged_submissions = array_slice( $submissions, $offset, $results_per_page );
?>
<div>
	<p><?php esc_html_e( 'Below are the logs of your 100 most recent submissions.', 'wds' ); ?></p>
</div>

<div class="sui-box-settings-row sui-flushed">
	<div>
		<span class="sui-field-prefix">
			<?php esc_html_e( 'Display', 'wds' ); ?>
		</span>
		<span>
			<label>
				<select class="sui-select sui-select-inline sui-select-sm wds-indexing-per-page">
					<?php foreach ( range( 10, 100, 10 ) as $per_page ) : ?>
						<option value="<?php echo esc_attr( $per_page ); ?>" <?php selected( $results_per_page, $per_page ); ?>>
							<?php echo esc_html( $per_page ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
		</span>
		<span class="sui-field-suffix"><?php esc_html_e( 'results per page', 'wds' ); ?></span>
	</div>
	<div class="sui-actions-right">
		<button id="wds-clear-submissions" class="sui-button sui-button-ghost">
			<?php esc_html_e( 'Clear Submissions', 'wds' ); ?>
		</button>
	</div>
</div>

<table class="sui-table sui-table-flushed">
	<thead>
	<tr>
		<th colspan="2"><?php esc_html_e( 'Date', 'wds' ); ?></th>
		<th colspan="4"><?php esc_html_e( 'URL', 'wds' ); ?></th>
		<th><?php esc_html_e( 'Type', 'wds' ); ?></th>
		<th><?php esc_html_e( 'Response', 'wds' ); ?></th>
		<th><?php esc_html_e( 'Status', 'wds' ); ?></th>
	</tr>
	</thead>
	<tbody class="wds-submission-history">
	<?php $this->render_view( 'instant-indexing/submission-history-rows', array(
		'paged_submissions' => $paged_submissions
	) ); ?>
	</tbody>

</table>
<div class="sui-box-footer">
	<div>
		<span class="sui-field-prefix"><?php esc_html_e( 'Display', 'wds' ); ?></span>
		<span>
			<label>
				<select class="sui-select sui-select-inline sui-select-sm wds-indexing-per-page">
					<?php foreach ( range( 10, 100, 10 ) as $per_page ) : ?>
						<option value="<?php echo esc_attr( $per_page ); ?>" <?php selected( $results_per_page, $per_page ); ?>><?php echo esc_html( $per_page ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</span>
		<span class="sui-field-suffix"><?php esc_html_e( 'results per page', 'wds' ); ?></span>
	</div>

	<div class="sui-actions-right">
		<div class="sui-pagination-wrap">
			<span class="sui-pagination-results">
				<?php echo esc_html( $total_submissions ); ?> <?php esc_html_e( 'results', 'wds' ); ?>
			</span>
			<?php $this->render_view( 'instant-indexing/submission-history-pagination', array(
				'total_pages' => $total_pages,
				'page'        => $current_page
			) ); ?>
		</div>
	</div>
</div>