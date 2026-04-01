<?php
/**
 * CBR Setting 
 *
 * @class   CBR_Admin_Notice
 * @package WooCommerce/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CBR_Admin_Notice class
 *
 * @since 1.0.0
 */
class CBR_Admin_Notice {
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0.0
	 * @return CBR_Admin_Notice
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var object Class Instance
	*/
	private static $instance;
	
	/*
	* construct function
	*
	* @since 1.0.0
	*/
	public function __construct() {
		$this->init();
	}

	/*
	* init function
	*
	* @since 1.0.0
	*/
	public function init() {
		add_action( 'admin_init', array( $this, 'cbr_pro_notice_ignore_cb_377' ) );

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		
		if ( 'woocommerce-product-country-base-restrictions' != $page ) {
			// Analytics for WooCommerce Subscriptions Notice
			add_action( 'admin_notices', array( $this, 'cbr_pro_admin_notice_377' ) );
			
		}
		add_action('cbr_settings_admin_notice', array( $this, 'cbr_settings_admin_notice' ) );
	}

	public function cbr_settings_admin_notice() {
		include 'views/admin_message_panel.php';
	}

		/*
	* Dismiss admin notice for trackship
	*/
	public function cbr_pro_notice_ignore_cb_377() {
		if ( isset( $_GET['cbr-pro-update-notice-377'] ) ) {
			if (isset($_GET['nonce'])) {
				$nonce = sanitize_text_field($_GET['nonce']);
				if (wp_verify_nonce($nonce, 'cbr_pro_dismiss_notice_377')) {
					update_option('cbr_pro_update_ignore_377', 'true');
				}
			}
		}
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function cbr_pro_admin_notice_377() {
		
		if ( get_option('cbr_pro_update_ignore_377') ) {
			return;
		}
		
		$nonce = wp_create_nonce('cbr_pro_dismiss_notice_377');
		$dismissable_url = esc_url(add_query_arg(['cbr-pro-update-notice-377' => 'true', 'nonce' => $nonce]));

		?>
		<style>		
		.wp-core-ui .notice.cbr-pro-dismissable-notice-377{
			position: relative;
			padding-right: 38px;
			border-left-color: #3b64d3;
		}
		.wp-core-ui .notice.cbr-pro-dismissable-notice-377 h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.cbr-pro-dismissable-notice-377 a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.cbr_pro_notice_btn_377 {
			background: #3b64d3;
			color: #fff;
			border-color: #3b64d3;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 10px;
		}
		.cbr-pro-dismissable-notice-377 strong{
			font-weight:bold;
		}
		</style>
		<div class="notice updated notice-success cbr-pro-dismissable-notice-377">
			<a href="<?php echo $dismissable_url; ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>
			<h2>🌍 Upgrade to Country Based Restrictions PRO – Gain Full Control Over Who Sees What!</h2>
			<p>Enhance your store's flexibility with CBR PRO:</p>
			<ul>
				<li>✅ Restrict product visibility by country, category, tag, or shipping class</li>
				<li>✅ Show or hide prices, payment gateways, and checkout options by location</li>
				<li>✅ Use a frontend country detection widget</li>
				<li>✅ Bulk import restrictions with a CSV file</li>
				<li>✅ Enable debug mode and customize restrictions with ease</li>
			</ul>
			<p>🎁 Special Offer: Get 20% OFF with coupon code CBRPRO20 – limited time only!</p>
			<p>
				<a href="https://www.zorem.com/product/country-based-restriction-pro/" class="button-primary cbr_pro_notice_btn_377">👉 Upgrade to CBR PRO</a>
				<a class="button-primary cbr_pro_notice_btn_377" href="<?php echo $dismissable_url; ?>">Dismiss</a>
			</p>
		</div>
		<?php
	}
	
}

