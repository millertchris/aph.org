<?php
/**
 * Template: Onpage Meta Robots.
 *
 * @package Smartcrwal
 */

$items    = empty( $items ) ? array() : $items;
$for_type = empty( $for_type ) ? '' : $for_type;

if ( ! $items ) {
	return;
}

$this->render_view(
	'toggle-group',
	array(
		'id'         => 'wds-onpage-indexing-' . esc_attr( $for_type ),
		'label'       => esc_html__( 'Indexing', 'wds' ),
		'description' => esc_html__( 'Choose whether you want your website to appear in search results.', 'wds' ),
		'separator'   => true,
		'items'       => $items,
	)
);