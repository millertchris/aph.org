<?php
/**
 * Template: Instant Indexing Sidenav.
 *
 * @package Smartcrwal
 */

$active_tab = empty( $active_tab ) ? '' : $active_tab;

$this->render_view(
	'vertical-tabs-side-nav',
	array(
		'active_tab' => $active_tab,
		'tabs'       => array(
			array(
				'id'   => 'tab_submit_url',
				'name' => esc_html__( 'Submit URL', 'wds' ),
			),
			array(
				'id'   => 'tab_submission_history',
				'name' => esc_html__( 'Submission History', 'wds' ),
			),
			array(
				'id'   => 'tab_settings',
				'name' => esc_html__( 'Settings', 'wds' ),
			),
		),
	)
);