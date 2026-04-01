<?php

// =========================================================================
// DEVELOPER TOOLS
// =========================================================================
require_once dirname(__FILE__) . '/functions/tools/init.php';
require_once dirname(__FILE__) . '/classes/autoload.php';

// =========================================================================
// HELPER FUNCTIONS
// =========================================================================
require_once dirname(__FILE__) . '/functions/helpers.php'; //Functions that are used throughout the theme

// =========================================================================
// WORDPRESS HOOKS AND FUNCTIONS
// =========================================================================

require_once dirname(__FILE__) . '/functions/wp/base.php'; //Pulls in all of the functions that we create
require_once dirname(__FILE__) . '/functions/wp-hooks.php'; //Action and filter hooks that use our functions

// =========================================================================
// CUSTOM POST TYPES
// =========================================================================
require_once dirname(__FILE__) . '/functions/cpt/cpt-documents.php';
require_once dirname(__FILE__) . '/functions/cpt/cpt-people.php';
require_once dirname(__FILE__) . '/functions/cpt/cpt-addresses.php';
// Disabled until approved
require_once dirname(__FILE__) . '/functions/cpt/cpt-events.php';
// require_once dirname(__FILE__) . '/functions/cpt/cpt-faq.php';

// =========================================================================
// WOOCOMMERCE HOOKS AND FUNCTIONS
// =========================================================================
require_once dirname(__FILE__) . '/functions/wc/base.php';
require_once dirname(__FILE__) . '/functions/wc-hooks.php';

require_once dirname(__FILE__) . '/functions/wc/class-wc-simple-registration.php';

function custom_login_redirect()
{
	if (!is_user_logged_in()) {
		wp_redirect(site_url() . '/my-account?redirect_to=' . urlencode(site_url() . $_SERVER['REQUEST_URI']));
		exit;
	}
}

add_action('wo_before_authorize_method', 'custom_login_redirect');

function woocommerce_button_proceed_to_checkout()
{ ?>
	<a role="button" href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-button button alt wc-forward">
		<?php esc_html_e('Proceed to checkout', 'woocommerce'); ?>
	</a>
<?php
}

//function wp_maintenance_mode() {
//if (!current_user_can('edit_themes') || !is_user_logged_in()) {
//wp_die('<h1>Under Maintenance</h1><br />The website will be down for maintenance from 4pm EDT Saturday, September 30 until mid-day on Monday October 2.  We appreciate your patience.');
//}
//}
//add_action('get_header', 'wp_maintenance_mode');

add_filter('woocommerce_product_backorders_allowed', 'products_backorders_allowed', 10, 3);
function products_backorders_allowed($backorder_allowed, $product_id, $product)
{
	$user       = wp_get_current_user();
	$user_roles = (array) $user->roles;
	if (in_array('eot', $user_roles)) {
		$backorder_allowed = true;
	}
	return $backorder_allowed;
}

add_action('product_database_usimac_cron', 'product_database_usimac');
function product_database_usimac()
{
	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$products = new WP_Query($args);
	$totalProducts = $products->found_posts;
	$totalPages = floor($totalProducts / 100);
	$allProducts = [];
	for ($page = 1; $page <= $totalPages; $page++) {
		$curl_url = "https://www.aph.org/wp-json/wc/v2/products?per_page=100&page=$page&consumer_secret=cs_5c972ecf3bebde97ec452d8b6add353be5b9e7b4&consumer_key=ck_c5d665deaf7c8a4e48534c1c66c1dd24a338c4b3";

		$ch = curl_init($curl_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json'
		));
		# Return response instead of printing.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		# Send request.
		$result = curl_exec($ch);
		$allProducts = [...$allProducts, ...json_decode($result, TRUE)];
		curl_close($ch);
	}
	//FTP server credentials
	$ftp_server = 'Greymine.com';
	$ftp_user_name = 'APH.Upload@greymine.com';
	$ftp_user_pass = 'ZwTv;vh6S~aF#uZ=uI';
	//1st requirements
	// Create temporary file
	$local_file = fopen('php://temp', 'r+');
	fwrite($local_file, json_encode($allProducts));
	rewind($local_file);
	// FTP connection
	$ftp_conn = ftp_connect($ftp_server);
	// FTP login
	@$login_result = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
	// FTP upload

	if ($login_result) {
		//code for deleting files before 60 days.
		$contents = ftp_nlist($ftp_conn, ".");
		$days_back_date = date('Y-m-d', time() - 86400 * 60);
		foreach ($contents as &$entry) {
			$fileDate = substr(substr($entry . '/', 0, strpos($entry, '.json')), -10);
			if ($fileDate && (strtotime($fileDate) < strtotime($days_back_date))) {
				ftp_delete($ftp_conn, $entry);
			}
		}

		ftp_delete($ftp_conn, 'aph-full-product-inventory-extract-' . date('Y-m-d') . '.json');
		$upload_result = ftp_fput($ftp_conn, 'aph-full-product-inventory-extract-' . date('Y-m-d') . '.json', $local_file, FTP_ASCII);
	}
	// Close file handle
	fclose($local_file);
	//2nd requirements
	//$results_arr = json_decode($allProducts, TRUE);
	$diffArr = [];
	foreach ($allProducts as $arr) {
		$diff = date_diff(date_create(substr($arr['date_modified'], 0, 10)), date_create(date('Y-m-d')));
		if ($diff->format("%R%a days") == 0) {
			array_push($diffArr, $arr);
		}
	}
	$diffJson = json_encode($diffArr);
	// Create temporary file
	$local_file = fopen('php://temp', 'r+');
	fwrite($local_file, $diffJson);
	rewind($local_file);
	// FTP upload
	if ($login_result) {
		ftp_delete($ftp_conn, 'aph-delta-product-inventory-extract-' . date('Y-m-d') . '.json');
		$upload_result = ftp_fput($ftp_conn, 'aph-delta-product-inventory-extract-' . date('Y-m-d') . '.json', $local_file, FTP_ASCII);
	}
	// Error handling
	if (!$login_result or !$upload_result) {
		echo ('<p>FTP error: The file could not be written to the FTP server.</p>');
	}
	// Close FTP connection
	ftp_close($ftp_conn);
	// Close file handle
	fclose($local_file);
}

// Add metadata to WooCommerce customer API response
add_action('woocommerce_rest_prepare_customer', 'add_customer_meta_data_to_api', 10, 3);

function add_customer_meta_data_to_api($response, $customer, $request)
{
	// Get the customer's user ID
	$user_id = $customer->ID;
	global $wpdb;
	$table_name = $wpdb->prefix . 'usermeta'; // WooCommerce stores meta data in wp_postmeta table, but user meta is in wp_usermeta

	// Query to get the meta data including the meta ID, meta key, and meta value
	$query = "
		SELECT umeta_id, meta_key, meta_value 
		FROM {$wpdb->prefix}usermeta 
		WHERE user_id = %d
	";
	$query = $wpdb->prepare($query, $user_id);

	$meta_data = $wpdb->get_results($query);
	$temp = [];
	// Check if meta data exists
	if (! empty($meta_data)) {
		foreach ($meta_data as $meta) {
			$temp[] = [
				"id" => $meta->umeta_id,
				"key" => $meta->meta_key,
				"value" => $meta->meta_value
			];
		}
	}
	$response->data['meta_data'] = $temp;
	return $response;
}

if (defined('WP_LOCAL_DEV_CORS') && WP_LOCAL_DEV_CORS) {
	function enable_dev_cors()
	{
		// Define allowed origins
		$allowed_origins = [
			'http://localhost:3000',
			'http://localhost:3001',
			'https://louis.aph.org',
			'https://vercel.app'
		];

		// Add custom origin if defined
		if (defined('WP_LOCAL_DEV_ORIGIN')) {
			$allowed_origins[] = WP_LOCAL_DEV_ORIGIN;
		}

		// Get the origin of the request
		$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

		// Check if the origin is allowed
		if (in_array($origin, $allowed_origins, true)) {
			header("Access-Control-Allow-Origin: {$origin}");
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
			header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Cart-Token, X-WP-Total, X-WP-TotalPages, Link, X-WC-Store-API-Nonce");
			header("Access-Control-Allow-Credentials: true");
		}

		// Handle preflight requests
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			status_header(200);
			exit();
		}
	}
	add_action('init', 'enable_dev_cors');

	// For REST API specifically
	add_action('rest_api_init', function () {
		remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
		add_filter('rest_pre_serve_request', function ($value) {
			// Define allowed origins (same as above)
			$allowed_origins = [
				'http://localhost:3000',
				'http://localhost:3001',
				'https://louis.aph.org',
				'https://vercel.app'
			];

			// Add custom origin if defined
			if (defined('WP_LOCAL_DEV_ORIGIN')) {
				$allowed_origins[] = WP_LOCAL_DEV_ORIGIN;
			}

			// Get the origin of the request
			$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

			// Check if the origin is allowed
			if (in_array($origin, $allowed_origins, true)) {
				header("Access-Control-Allow-Origin: {$origin}");
				header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
				header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Cart-Token");
				header("Access-Control-Allow-Credentials: true");
			}

			return $value;
		});
	}, 15);
}

?>