<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;
$condition_groups = get_post_meta( $package->ID, '_conditions', true );

?><div class='wpc-conditions wpc-conditions-meta-box'>
	<div class='wpc-condition-groups'>

		<p>
			<strong><?php esc_html_e( 'Match one of the conditions groups to create a new package', 'advanced-shipping-packages-for-woocommerce' ); ?></strong><?php
			echo wc_help_tip( __( 'The order will only attempt to split into packages when one of the condition groups below is matched.', 'advanced-shipping-packages-for-woocommerce' ) );
		?></p><?php

		if ( ! empty( $condition_groups ) ) :

			foreach ( $condition_groups as $condition_group => $conditions ) :
				include 'html-admin-page-package-conditions-group.php';
			endforeach;

		else :

			$condition_group = '0';
			include 'html-admin-page-package-conditions-group.php';

		endif;

	?></div>

	<div class='wpc-condition-group-template hidden' style='display: none'><?php
		$condition_group = '9999';
		$conditions      = array();
		include 'html-admin-page-package-conditions-group.php';
	?></div>
	<a class='button wpc-condition-group-add' href='javascript:void(0);'><?php esc_html_e( 'Add \'Or\' group', 'advanced-shipping-packages-for-woocommerce' ); ?></a>
</div>
