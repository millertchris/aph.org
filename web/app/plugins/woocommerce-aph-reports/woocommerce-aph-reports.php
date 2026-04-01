<?php
/**
 * Plugin Name: Woocommerce APH Reports
 *
 * @package WooCommerce\Admin
 */

/**
 * Register the JS.
 */
function add_extension_register_script() {
	// if ( ! class_exists( 'Automattic\WooCommerce\Admin\PageController' ) || ! \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page() ) {
	// 	return;
	// }

	$script_path       = '/build/index.js';
	$script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
	$script_asset      = file_exists( $script_asset_path )
		? require( $script_asset_path )
		: array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
	$script_url = plugins_url( $script_path, __FILE__ );

	wp_register_script(
		'woocommerce-louis-reports',
		$script_url,
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_register_style(
		'woocommerce-louis-reports',
		plugins_url( '/build/index.css', __FILE__ ),
		// Add any dependencies styles may have, such as wp-components.
		array(),
		filemtime( dirname( __FILE__ ) . '/build/index.css' )
	);

	wp_enqueue_script( 'woocommerce-louis-reports' );
	wp_enqueue_style( 'woocommerce-louis-reports' );
}

add_action( 'admin_enqueue_scripts', 'add_extension_register_script' );


function add_product_group_settings() {

	if(isset($_GET['page']) && $_GET['page'] == 'wc-admin'){

		// $louis_zero_args = array(
		// 	'post_type' => 'product',
		// 	'posts_per_page' => -1,
		// 	'post_status' => 'louis',
		// 	'meta_query' => array(
		// 		array(
		// 			'key'     => '_price',
		// 			'value'   => ['', '0'],
		// 			'compare' => 'IN'
		// 		)
		// 		),
		// 	'fields' => 'ids'
		// );		
		// $louis_zero_posts = get_posts($louis_zero_args);
		// $louis_zero_posts = implode(',', $louis_zero_posts);
		
		// $aph_zero_args = array(
		// 	'post_type' => 'product',
		// 	'posts_per_page' => -1,
		// 	'post_status' => 'publish',
		// 	'meta_query' => array(
		// 		'relation' => 'AND',
		// 		array(
		// 		'key'     => '_price',
		// 		'value'   => ['', '0'],
		// 		'compare' => 'IN'
		// 		),
		// 		array(
		// 			'key'     => 'discontinued',
		// 			'value'   => '1',
		// 			'compare' => '!='
		// 			)				   
		// 		),
		// 	'fields' => 'ids'				
		// );
		// $aph_zero_posts = get_posts($aph_zero_args);
		// $aph_zero_posts = implode(',', $aph_zero_posts);

		$product_groups = array(
			array(
				'label' => __( 'No Product Group', 'woocommerce-aph-reports' ),
				'value' => 'default',
			),
			array(
				'label' => __( 'APH Zero Dollar Products', 'woocommerce-aph-reports' ),
				'value' => 'aphZero',
			),
			array(
				'label' => __( 'Louis Zero Dollar Products', 'woocommerce-aph-reports' ),
				'value' => 'louisZero',
			),
		);

		$data_registry = Automattic\WooCommerce\Blocks\Package::container()->get(
			Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class
		);
	
		$data_registry->add( 'productGroups', $product_groups );

	}	

}
 
add_action( 'admin_init', 'add_product_group_settings' );

function apply_product_group_arg( $args ) {

    $product_group = 'default';
 
    if (isset($_GET['product_group'])){
        $product_group = sanitize_text_field( wp_unslash( $_GET['product_group'] ) );
    }
	if($product_group == 'louisZero'){
		// Add array of louis products with 0 price $args['product_includes'] = ['889'];
		$louis_zero_args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'post_status' => 'louis',
			'meta_query' => array(
				   array(
					'key'     => '_price',
					'value'   => ['', '0'],
					'compare' => 'IN'
				   )
				),
			'fields' => 'ids'
		);
		// var_dump($custom_product_query);		
		// die();
		$args['match'] = 'all';
		$args['product_includes'] = get_posts($louis_zero_args);

	}
	if($product_group == 'aphZero'){
		// Add array of non-louis products with 0 price $args['product_includes'] = ['889'];
		$aph_zero_args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_query' => array(
				   'relation' => 'AND',
				   array(
				   'key'     => '_price',
				   'value'   => ['', '0'],
				   'compare' => 'IN'
				   ),
				   array(
					'key'     => 'discontinued',
					'value'   => '1',
					'compare' => '!='
					)				   
				),
			'fields' => 'ids'				
		);
	
		// var_dump($custom_product_query);		
		// die();	
		$args['match'] = 'all';	
		$args['product_includes'] = get_posts($aph_zero_args);

	}	
    // $args['product_includes'] = ['889'];

    return $args;
} 
add_filter( 'woocommerce_analytics_orders_query_args', 'apply_product_group_arg' );
add_filter( 'woocommerce_analytics_orders_stats_query_args', 'apply_product_group_arg' );


function append_report_arg_data($report_args) {
	if(isset($report_args['product_group']) && $report_args['product_group'] == 'louisZero'){
		$louis_zero_args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'post_status' => 'louis',
			'meta_query' => array(
				   array(
					'key'     => '_price',
					'value'   => ['', '0'],
					'compare' => 'IN'
				   )
				),
			'fields' => 'ids'
		);		
		$louis_zero_posts = get_posts($louis_zero_args);
		$report_args['match'] = 'all';
		$report_args['product_includes'] = implode(',', $louis_zero_posts);
	}
	if(isset($report_args['product_group']) && $report_args['product_group'] == 'aphZero'){
		$aph_zero_args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'meta_query' => array(
				   'relation' => 'AND',
				   array(
				   'key'     => '_price',
				   'value'   => ['', '0'],
				   'compare' => 'IN'
				   ),
				   array(
					'key'     => 'discontinued',
					'value'   => '1',
					'compare' => '!='
					)				   
				),
			'fields' => 'ids'				
		);
		$aph_zero_posts = get_posts($aph_zero_args);
		$report_args['match'] = 'all';
		$report_args['product_includes'] = implode(',', $aph_zero_posts);	
	}
	unset($report_args['product_group']);	
	return $report_args;
}
add_filter('woocommerce_aph_export_request_reports_args', 'append_report_arg_data');
// add_filter('woocommerce_aph_queue_export_report_args', 'append_report_arg_data');
// add_filter('woocommerce_aph_export_report_args', 'append_report_arg_data');

function append_report_product_data($results, $args){
	foreach($results->data as $rindex => $row){
		if(isset($row['extended_info']) && isset($row['extended_info']['products'])){
			foreach($row['extended_info']['products'] as $pindex => $product){
				$product_sku = get_post_meta($product['id'], '_sku');
				$product_sku = is_array($product_sku) ? $product_sku[0] : '';
				$results->data[$rindex]['extended_info']['products'][$pindex]['name'] = $product['name'] . ' ~ ' . $product_sku;
			}
		}
	}
	return $results;
}
add_filter('woocommerce_analytics_orders_select_query', 'append_report_product_data', 10, 2);