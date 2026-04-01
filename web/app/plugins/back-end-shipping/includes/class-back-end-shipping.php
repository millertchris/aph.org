<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://mightily.com
 * @since      1.0.0
 *
 * @package    Back_End_Shipping
 * @subpackage Back_End_Shipping/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Back_End_Shipping
 * @subpackage Back_End_Shipping/includes
 * @author     Mightily <sos@mightily.com>
 */
class Back_End_Shipping {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Back_End_Shipping_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	protected $update_url;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BACK_END_SHIPPING_VERSION' ) ) {
			$this->version = BACK_END_SHIPPING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'back-end-shipping';
		$this->update_url = 'http://updates.mightily.com/automatic-theme-plugin-update/api';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Back_End_Shipping_Loader. Orchestrates the hooks of the plugin.
	 * - Back_End_Shipping_i18n. Defines internationalization functionality.
	 * - Back_End_Shipping_Admin. Defines all hooks for the admin area.
	 * - Back_End_Shipping_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-back-end-shipping-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-back-end-shipping-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-back-end-shipping-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-back-end-shipping-public.php';

		$this->loader = new Back_End_Shipping_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Back_End_Shipping_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Back_End_Shipping_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Back_End_Shipping_Admin( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_ajax_remove_shipping_line_item', $plugin_admin, 'remove_shipping_item_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_remove_shipping_line_item', $plugin_admin, 'remove_shipping_item_ajax' );

		$this->loader->add_action( 'wp_ajax_add_shipping_line_item', $plugin_admin, 'add_shipping_item_ajax' );
	    $this->loader->add_action( 'wp_ajax_nopriv_add_shipping_line_item', $plugin_admin, 'add_shipping_item_ajax' );

		$this->loader->add_action( 'woocommerce_admin_order_item_values', $plugin_admin, 'add_order_item_value', 10, 3);

		$this->loader->add_action( 'woocommerce_admin_order_item_headers', $plugin_admin, 'add_order_item_header', 10, 1);

		$this->loader->add_action( 'woocommerce_order_item_add_line_buttons', $plugin_admin, 'add_admin_calculate_shipping', 10, 1);

		$this->loader->add_filter( 'woocommerce_admin_html_order_item_class', $plugin_admin, 'add_admin_class_line_item', 10, 3);

		// Require that all line items have _shipped meta data when moving order to processing
		$this->loader->add_action( 'woocommerce_process_shop_order_meta', $plugin_admin, 'validate_meta_data', 10, 2);

		// These hooks are used to manage plugin updates
		$this->loader->add_filter( 'pre_set_site_transient_update_plugins', $this, 'check_for_plugin_update' );
		$this->loader->add_filter( 'plugins_api', $this, 'plugin_api_call', 10, 3);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Back_End_Shipping_Public( $this->get_plugin_name(), $this->get_version() );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_bes_rest' );

		// Add line item meta data of _shipped at checkout
		$this->loader->add_action( 'woocommerce_checkout_create_order', $plugin_public, 'add_line_item_meta', 10, 2 );		

	}

	public function check_for_plugin_update($checked_data) {
		global $wp_version;

		//Comment out these two lines during testing.
		if (empty($checked_data->checked))
			return $checked_data;
		$args = array(
			'slug' => $this->plugin_name,
			'version' => $checked_data->checked[$this->plugin_name .'/'. $this->plugin_name .'.php'],
		);
		//print_r($args);
		$request_string = array(
				'body' => array(
					'action' => 'basic_check',
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);

		// Start checking for an update
		$raw_response = wp_remote_post($this->update_url, $request_string);

		if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
			$response = unserialize($raw_response['body']);

		if (is_object($response) && !empty($response)) // Feed the update data into WP updater
			$checked_data->response[$this->plugin_name .'/'. $this->plugin_name .'.php'] = $response;

		return $checked_data;

	}

	public function plugin_api_call($def, $action, $args) {
		global $wp_version;

		if (!isset($args->slug) || ($args->slug != $this->plugin_name))
			return false;

		// Get the current version
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[$this->plugin_name .'/'. $this->plugin_name .'.php'];
		$args->version = $current_version;

		$request_string = array(
				'body' => array(
					'action' => $action,
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);

		$request = wp_remote_post($this->update_url, $request_string);

		if (is_wp_error($request)) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
			$res = unserialize($request['body']);

			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
		}

		return $res;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Back_End_Shipping_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function get_update_url() {
		return $this->update_url;
	}

}
