<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Ignition Updater Class
 *
 * Base class for the Ignition Updater.
 *
 * @package WordPress
 * @subpackage Ignition Updater
 * @category Core
 * @author Ignition
 * @since 3.0.0
 *
 */
 
// When changing this class name all plugins' IgniteWoo Updater code must also be changed because it looks for this class name
class IgniteWoo_Updater {
	
	public $updater;
	public $admin;
	private $token;
	private $plugin_url;
	private $plugin_path;
	public $version;
	public $file;
	private $products;

	/**
	 * Constructor.
	 * @param string $file The base file of the plugin.
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct ( $file, $version ) {
		global $ignition_updater_token;

		$this->token = $ignition_updater_token;
		
		// If multisite, plugin must be network activated. First make sure the is_plugin_active_for_network function exists
		if( is_multisite() && ! is_network_admin() ) {
			remove_action( 'admin_notices', 'ignition_updater_notice' ); // remove admin notices for plugins outside of network admin
			if ( !function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			if( !is_plugin_active_for_network( plugin_basename( $file ) ) )
				add_action( 'admin_notices', array( $this, 'admin_notice_require_network_activation' ) );
			return;
		}

		$this->file = $file;
		$this->version = $version;
		$this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
		$this->plugin_path = trailingslashit( dirname( $file ) );

		$this->products = array();

		$this->load_plugin_textdomain();

		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $this->file, array( $this, 'activation' ) );

		if ( is_admin() ) {
			// Load the admin.
			require_once( 'class-ignition-updater-admin.php' );
			$this->admin = new Ignition_Updater_Admin( $file );

			// Don't do this yet... we don't sell themes at this time
			// Look for enabled updates across all themes (active or otherwise). If they are available, queue them.
			// add_action( 'init', array( $this, 'maybe_queue_theme_updates' ), 1 );

			// Get queued plugin updates - Run on init so themes are loaded as well as plugins.
			add_action( 'init', array( $this, 'load_queued_updates' ), 2 );
		}

		$this->add_notice_unlicensed_product();

		add_filter( 'site_transient_' . 'update_plugins', array( $this, 'change_update_information' ) );
		
		//apply_filters( 'http_request_timeout', 5, $url )
		add_filter( 'http_request_timeout', array( &$this, 'maybe_set_http_timeout' ), 99999, 2 );
		
		// Runs after WP does plugin update installs, check if any IGN plugin were updated, 
		// if so, remove our transient.
		add_action( 'upgrader_process_complete', array( &$this, 'my_upgrade_function' ), 10, 2 );
		
		add_filter( 'http_request_host_is_external', array( &$this, 'http_request_host_is_localhost_network' ), 999, 3 );

		// PACKAGE SIGNING: NOT IMPLEMENTED YET. 
		// WAIT FOR WP TO ADD SIGNING TO PLUGINS IN FUTURE VERSION BEYOND 5.2.1. 
		// SEE: wp-admin/includes/file.php, function download_url() { }
		// 
		// This filter adds the trusted public key so WP can use it to verify plugin downloads. 
		// This is used when the sending server injects a X-Content-Signature header into the download response:
		// BUT this only allows for one signature key, which makes it difficult to retire a key and replace it with a new key. 
		// add_filter( 'wp_trusted_keys', array( &$this, 'add_trusted_key'), 1, 1 );
		
		// OR INSTEAD, THIS IS MORE IDEAL: 
		
		// The IgniteWoo API plugin signs zip packages with its private key and stores those *.sig files on
		// the ignitewoo server. These two filters add a signature host to WP and get a signature URL: 
		// add_filter( 'wp_signature_hosts', array( &$this, 'add_signature_host' ), 1, 1 );
		// add_filter( 'wp_signature_url', array( &$this, 'get_signature_url' ) );
		
		// Soft-failure can be imposed against the URL like this: 
		// add_filter( 'wp_signature_softfail', array( &$this, 'allow_upgrade_signature_softfail' ) );
		
		
	} // End __construct()

	function http_request_host_is_localhost_network( $result = false, $host = '', $url = '' ) {
		// Allow testing on internal systems - e.g. localhost, 127.0.0.1, etc.
		// This is primarily for internal testing by IgniteWoo
		if ( false !== strpos( $url, '://localhost' ) && ( false !== strpos( $url, 'wc-api=product-key-api' ) || false !== strpos( $url, 'wc-api=ignitewoo_api' ) ) ) { 
			return true; 
		} else { 
			return $result;
		}
	}

	/*
	public function add_trusted_key( $trusted_keys ) { 
		// ADD PUBLIC KEY, used by WP to verify plugin package signature.
		// EXAMPLE OF WHAT A BASE64 ENCODED EKY MIGHT LOOK LIKE: 
		$trusted_keys[] = 'csj65c3D/Afc+ws8Qu5r8m1ZFn+TdR/zDkeul1WxRmc=';
		return $trusted_keys; 
	}
	*/
	
	/**
	* Add signature host to those know to WP so that signatures can be obtained from IgniteWoo when necessary: 
	*
	* @since 3.0
	* 
	* @param string $hosts		An array of signature hosts known to WP 
	* 
	* NOTE: NOT IMPLEMENTED 
	*/
	public function add_signature_host( $hosts = array() ) { 
		$hosts[] = 'https://ignitewoo.com/';
		return $hosts;
	}
	
	/**
	* Whether to allow signature softfail for download packages from ignitewoo.com
	*
	* @since 3.0
	* 
	* @param string $hosts		An array of signature hosts known to WP 
	*
	* NOTE: NOT IMPLEMENTED 
	*/
	public function allow_upgrade_signature_softfail( $softfail, $url ) { 
		if ( false === strpos( $url, 'https://ignitewoo.com/' ) || false === strpos( $url, 'request=ignitewoo_update' ) ) { 
			return $softfail;
		}
		
		return true;
	}
	
	
	/**
	* Filter the URL where the signature for a file is located IF the URL it came from is ignitewoo.com. 
	*
	* @since 3.0
	*
	* @param false|string $signature_url The URL where signatures can be found for a file, or false if none are known.
	* @param string $url                 The URL of the package being verified.
	*
	* NOTE: NOT IMPLEMENTED 
	*
	*/
	public function get_signature_url( $signature_url, $url ) { 
		
		if ( false === strpos( $url, 'https://ignitewoo.com/' ) || false === strpos( $url, 'request=ignitewoo_update' ) ) { 
			return $signature_url; 
		}
		
		// Modify request param to get the signature file contents and send it back.
		$signature_url = str_replace( 'request=ignitewoo_update', 'request=get_signature_for_update', $url );
		
		return $signature_url; 
		
		/* For direct access do this instead: 
		$url_path = parse_url( $url, PHP_URL_PATH );
		
		if ( empty( $url_path ) ) { 
			return $signature_url; 
		}
		
		$parts = explode( '/', $url_path );

		$filename = end( $parts );
		
		if ( '.zip' !== substr( $filename, -4 ) ) {
			return $signature_url;
		}

		$filename = str_replace( '.zip', '.sig', $filename );
		
		$signature_url = 'https://ignitewoo.com/whatever/subdir/we/want/to/use/for/direct/access/'. $filename; 

		return $signature_url;
		*/
	}
	
	function my_upgrade_function( $upgrader_object, $options ) {

		if ( empty( $options ) || empty( $options['plugins'] ) ) { 
			return;
		}
		
		foreach( $options['plugins']  as $plugin) { 
			delete_transient( 'ign_' . esc_attr( sanitize_title( $plugin ) ) . '_latest_version' );
		}

		delete_transient( 'ignition_helper_updates' );
		
		return;
		
	}
	
	// Maybe increase HTTP request timeout if the request is being sent to IgniteWoo, this helps when the site has heavy loads
	public function maybe_set_http_timeout( $timeout = 5, $url = '' ) { 
		
		if ( empty( $url ) ) {
			return $url;
		}
		
		$url = parse_url( $url );
		
		if ( empty( $url ) || !isset( $url['host'] ) ) { 
			return $url;
		}
		
		if ( false === strpos( $url['host'], 'ignitewoo.com') ) { 
			return $timeout; 
		} else { 
			return 10;
		}
	}
	
	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'ignition-updater', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'ignition-updater';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/languages/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'ignition-updater' . '-version', $this->version );
		}
	} // End register_plugin_version()

	/**
	 * Queue updates for any themes that have valid update credentials.
	 * @access  public
	 * @since   1.2.0
	 * @return  void
	 */
	public function maybe_queue_theme_updates () {
		$themes = wp_get_themes();
		if ( is_array( $themes ) && 0 < count( $themes ) ) {
			foreach ( $themes as $k => $v ) {
				// Search for the text file.
				$file = $this->_maybe_find_theme_info_file( $v );
				if ( ! is_wp_error( $file ) ) {
					$parsed = $this->_parse_theme_info_file( $file );
					if ( ! is_wp_error( $parsed ) ) {
						$this->add_product( $parsed[2], $parsed[1], $parsed[0] ); // 0: file, 1: file_id, 2: product_id.
					}
				}
			}
		}
	} // End maybe_queue_theme_updates()

	/**
	 * Maybe find the theme_info.txt file.
	 * @access  private
	 * @since   1.2.0
	 * @param   object $theme WP_Theme instance.
	 * @return  object/string WP_Error object if not found, path to the file, if it exists.
	 */
	private function _maybe_find_theme_info_file ( $theme ) {
		$response = new WP_Error( 404, __( 'Theme Information File Not Found.', 'ignition-updater' ) );
		$txt_files = $theme->get_files( 'txt', 0 );
		if ( isset( $txt_files['theme_info.txt'] ) ) {
			$response = $txt_files['theme_info.txt'];
		}
		return $response;
	} // End _maybe_find_theme_info_file()

	/**
	 * Parse a given theme_info.txt file.
	 * @access  private
	 * @since   1.2.0
	 * @param   string $file The path to the file to be parsed.
	 * @return  object/array WP_Error object if the data is incorrect, array, if it is accurate.
	 */
	private function _parse_theme_info_file ( $file ) {
		$response = new WP_Error( 500, __( 'Theme Information File is Inaccurate. Please try again.', 'ignition-updater' ) );
		if ( is_string( $file ) && file_exists( $file ) ) {
			$contents = file_get_contents( $file );
			$contents = explode( "\n", $contents );
			// Sanity check on the parsed array.
			if ( ( 3 == count( $contents ) ) && stristr( $contents[2], '/style.css' ) ) {
				$response = $contents;
			}
		}
		return $response;
	} // End _parse_theme_info_file()

	/**
	 * load_queued_updates function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_queued_updates() {
		global $ignitewoo_queued_updates;

		if ( ! empty( $ignitewoo_queued_updates ) && is_array( $ignitewoo_queued_updates ) ) { 
			foreach ( $ignitewoo_queued_updates as $plugin ) { 
				if ( is_object( $plugin ) && ! empty( $plugin->file ) && ! empty( $plugin->file_id ) && ! empty( $plugin->product_id ) ) {
					$this->add_product( $plugin->file, $plugin->file_id, $plugin->product_id );
				}
			}
		}
	} // End load_queued_updates()

	/**
	 * Add a product to await a license key for activation.
	 *
	 * Add a product into the array, to be processed with the other products.
	 *
	 * @since  1.0.0
	 * @param string $file The base file of the product to be activated.
	 * @param string $file_id The unique file ID of the product to be activated.
	 * @return  void
	 */
	public function add_product ( $file, $file_id, $product_id ) {

		if ( $file != '' && ! isset( $this->products[$file] ) ) { $this->products[$file] = array( 'file_id' => $file_id, 'product_id' => $product_id ); }
	} // End add_product()

	/**
	 * Remove a product from the available array of products.
	 *
	 * @since     1.0.0
	 * @param     string $key The key to be removed.
	 * @return    boolean
	 */
	public function remove_product ( $file ) {
		$response = false;
		if ( $file != '' && in_array( $file, array_keys( $this->products ) ) ) { unset( $this->products[$file] ); $response = true; }
		return $response;
	} // End remove_product()

	/**
	 * Return an array of the available product keys.
	 * @since  1.0.0
	 * @return array Product keys.
	 */
	public function get_products () {
		return (array) $this->products;
	} // End get_products()

	/**
	 * Display require network activation error.
	 * @since  1.0.0
	 * @return  void
	 */
	public function admin_notice_require_network_activation () {
		echo '<div class="error"><p>' . __( 'IgniteWoo Updater must be network activated when in multisite environment.', 'ignition-updater' ) . '</p></div>';
	} // End admin_notice_require_network_activation()

	/**
	 * Add action for queued products to display message for unlicensed products.
	 * @access  public
	 * @since   1.1.0
	 * @return  void
	 */
	public function add_notice_unlicensed_product () {
		global $ignitewoo_queued_updates;

		if ( !is_array( $ignitewoo_queued_updates ) || count( $ignitewoo_queued_updates ) < 0 ) 
			return;

		foreach ( $ignitewoo_queued_updates as $key => $update ) {

			add_action( 'in_plugin_update_message-' . $update->file, array( $this, 'need_license_message' ), 10, 2 );
		}
		
	} // End add_notice_unlicensed_product()

	/**
	 * Message displayed if license not activated
	 * @param  array $plugin_data
	 * @param  object $r
	 * @return void
	 */
	public function need_license_message ( $plugin_data, $r ) {

		if ( empty( $r->package ) ) {
			echo wp_kses_post( '<div class="ignition-updater-plugin-upgrade-notice">' . __( 'To enable this update please activate your IgniteWoo license by visiting the Dashboard > IgniteWoo Licenses screen.', 'ignition-updater' ) . '</div>' );
		} else if ( !empty( $r->upgrade_notice ) ) { 
			?>
			<div class="ignition-updater-plugin-upgrade-notice"><?php echo $r->upgrade_notice ?></div>
			<?php
		}
	} // End need_license_message()

	/**
	 * Change the update information for unlicense Ignition products
	 * @param  object $transient The update-plugins transient
	 * @return object
	 */
	public function change_update_information ( $transient ) {
		global $pagenow;
		
		//If we are on the update core page, change the update message for unlicensed products
		if ( ( 'update-core.php' == $pagenow ) && $transient && isset( $transient->response ) && ! isset( $_GET['action'] ) ) {

			global $ignitewoo_queued_updates;

			if ( empty( $ignitewoo_queued_updates ) ) 
				return $transient;

			$notice_text = __( 'To enable this update please activate your IgniteWoo license by visiting the Dashboard > IgniteWoo Licenses screen.' , 'ignition-updater' );

			foreach ( $ignitewoo_queued_updates as $key => $value ) {

				if ( !empty( $transient->response[ $value->file ]->updater_upgrade_required_notice ) ) {
					$transient->response[ $value->file ]->upgrade_notice = wp_kses_post( $transient->response[ $value->file ]->updater_upgrade_required_notice );
					
				} else if ( isset( $transient->response[ $value->file ] ) && isset( $transient->response[ $value->file ]->package ) && '' == $transient->response[ $value->file ]->package && ( FALSE === stristr($transient->response[ $value->file ]->upgrade_notice, $notice_text ) ) ) {
					$message = '<div class="ignition-updater-plugin-upgrade-notice">' . $notice_text . '</div>';
					$transient->response[ $value->file ]->upgrade_notice = wp_kses_post( $message );
				}
			}
		}

		return $transient;
	} // End change_update_information()

} // End Class
