<?php
/**
 * Template: Instant Indexing Submission History.
 *
 * @package Smartcrwal
 */

$submissions = get_option( 'wds_instant_indexing_history', array() );
/**
 * Filter the submission history.
 *
 * @param array $submissions Submissions.
 */
$submissions = apply_filters( 'smartcrawl_indexnow_submission_history', $submissions );
if ( count( $submissions ) > 0 ) {
	$this->render_view(
		'instant-indexing/submission-history',
		array(
			'submissions' => $submissions,
		)
	);
} else {
	$this->render_view(
		'disabled-component-inner',
		array(
			'content'      => sprintf(
			/* translators: 1,2: Head tag */
				esc_html__( '%1$sNo URLs submitted yet%2$sYour most recent submissions will appear here.', 'wds' ),
				'<h2>',
				'</h2>'
			),
			'button_url'   => '#',
			'button_text'  => esc_html__( 'Submit URLs', 'wds' ),
			'button_color' => 'ghost',
			'button_class' => 'wds-submit-urls',
		)
	);
}