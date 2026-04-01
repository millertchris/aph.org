<?php
/**
 * WC_USPS class file.
 *
 * @package WC_Shipping_USPS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WC_USPS_ABSPATH . 'includes/trait-util.php';

use WooCommerce\USPS\Util;

/**
 * WC_USPS class
 */
class WC_USPS {

	use Util;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_product_editor_compatibility' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WC_USPS_FILE ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'woocommerce_shipping_init', array( $this, 'init' ) );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_method' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_notices', array( $this, 'environment_check' ) );
		add_action( 'admin_notices', array( $this, 'migration_notice' ) );
		add_action( 'wp_ajax_wc_usps_dismiss_migration_notice', array( $this, 'dismiss_migration_notice' ) );

		include_once WC_USPS_ABSPATH . 'includes/class-wc-shipping-usps-admin.php';
		if ( is_admin() ) {
			new WC_Shipping_USPS_Admin();

		}

		include_once WC_USPS_ABSPATH . 'includes/class-product-editor.php';
	}

	/**
	 * Check the environment.
	 *
	 * @return void
	 */
	public function environment_check() {

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- It's a capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Error condition data.
		$usps_shipping_method     = $this->usps_shipping_method();
		$usps_user_id             = $usps_shipping_method ? $usps_shipping_method->get_option( 'user_id' ) : '';
		$usps_api_type            = $usps_shipping_method ? $usps_shipping_method->get_option( 'api_type' ) : '';
		$using_soap_api           = in_array( $usps_api_type, array( 'soap', '' ), true );
		$using_woo_themes_user_id = '150WOOTH2143' === $usps_user_id;
		$user_id_is_missing       = $usps_shipping_method && empty( $usps_user_id );

		// URLs.
		$wc_general_settings_url = admin_url( 'admin.php?page=wc-settings&tab=general' );
		$usps_webtools_api_url   = 'https://www.usps.com/business/web-tools-apis/welcome.htm';
		$usps_settings_url       = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=usps' );

		$error = '';
		if ( get_woocommerce_currency() !== 'USD' ) {
			$error = sprintf(
				// translators: %s is a link to WooCommerce general settings page.
				__( 'USPS requires that the <a href="%s">currency</a> is set to US Dollars.', 'woocommerce-shipping-usps' ),
				esc_url( $wc_general_settings_url )
			);
		} elseif ( ! in_array( WC()->countries->get_base_country(), array( 'US', 'PR', 'VI', 'MH', 'FM' ), true ) ) {
			$error = sprintf(
				// translators: %s is a link to WooCommerce general settings page.
				__( 'USPS requires that the <a href="%s">base country/region</a> is the United States.', 'woocommerce-shipping-usps' ),
				esc_url( $wc_general_settings_url )
			);
		} elseif ( $using_woo_themes_user_id || ( $user_id_is_missing && $this->instances_exist() && $using_soap_api ) ) {
			$error = sprintf(
				// translators: %1$s is a link to USPS API. %2$s is a anchor closer tag. %3$s is  a link to USPS settings page.
				__( 'The WooCommerce USPS User ID your site is currently using is no longer valid. Registering for an account at USPS is now required. <br />Please register for an %1$saccount at USPS%2$s and %3$senter your user ID here%2$s.', 'woocommerce-shipping-usps' ),
				'<a href="' . esc_url( $usps_webtools_api_url ) . '" target="_blank">',
				'</a>',
				'<a href="' . esc_url( $usps_settings_url ) . '" target="_blank">'
			);
		} elseif ( $user_id_is_missing && $using_soap_api ) {
			$error = sprintf(
				// translators: %1$s is a link to USPS API. %2$s is a anchor closer tag. %3$s is  a link to USPS settings page.
				__( 'WooCommerce USPS Shipping plugin requires you to %1$sregister for an account at USPS%2$s and %3$senter your user ID here%2$s.', 'woocommerce-shipping-usps' ),
				'<a href="' . esc_url( $usps_webtools_api_url ) . '" target="_blank">',
				'</a>',
				'<a href="' . esc_url( $usps_settings_url ) . '" target="_blank">'
			);
		}

		if ( ! empty( $error ) ) {
			echo '<div class="error"><p>' . wp_kses_post( $error ) . '</p></div>';
		}
	}

	/**
	 * Display migration notice for USPS API deprecation.
	 *
	 * @return void
	 */
	public function migration_notice() {
		// Only show on USPS settings page.
		if ( ! $this->is_usps_settings_page() && ! $this->is_usps_instance_settings_page() ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- It's a capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Only show notice if using legacy SOAP API.
		$usps_shipping_method = $this->usps_shipping_method();
		$usps_api_type        = $usps_shipping_method ? $usps_shipping_method->get_option( 'api_type' ) : '';
		$using_soap_api       = in_array( $usps_api_type, array( 'soap', '' ), true );
		if ( ! $using_soap_api ) {
			return;
		}

		// Check if notice has been dismissed.
		$is_dismissed = get_user_meta( get_current_user_id(), 'dismissed_wc_usps_migration_notice', true );
		if ( $is_dismissed ) {
			return;
		}
		?>
		<div class="notice notice-warning is-dismissible wc-usps-migration-notice">
			<?php
			echo wp_kses_post(
				sprintf(
					'<p><strong>%s</strong></p><p>%s</p>',
					esc_html__( 'Action Required: Check your USPS connection', 'woocommerce-shipping-usps' ),
					sprintf(
					// translators: %1$s is the documentation link tag.
						__( 'USPS is retiring the legacy Web Tools API on January 25, 2026. If you\'re still using the legacy API connection, your USPS shipping prices calculated at checkout will stop working unless you migrate to the REST API. %1$s.', 'woocommerce-shipping-usps' ),
						'<a href="https://woocommerce.com/document/usps-shipping-method/#important-update-usps-web-tools-apis-retirement" target="_blank">Learn how to switch to the REST API</a>'
					)
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Handle AJAX request to dismiss migration notice.
	 *
	 * @return void
	 */
	public function dismiss_migration_notice() {
		check_ajax_referer( 'wc_usps_dismiss_migration_notice', 'nonce' );

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- It's a capability from WooCommerce
		if ( current_user_can( 'manage_woocommerce' ) ) {
			update_user_meta( get_current_user_id(), 'dismissed_wc_usps_migration_notice', true );
		}

		wp_die();
	}

	/**
	 * Get the USPS shipping method if it exists, otherwise return false.
	 *
	 * @return WC_Shipping_USPS|false
	 */
	public function usps_shipping_method() {
		$wc_shipping          = WC()->shipping();
		$usps_shipping_method = isset( $wc_shipping->get_shipping_methods()['usps'] ) ? $wc_shipping->get_shipping_methods()['usps'] : false;

		return $usps_shipping_method instanceof WC_Shipping_USPS ? $usps_shipping_method : false;
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-shipping-usps', false, dirname( plugin_basename( WC_USPS_FILE ) ) . '/languages/' );
	}

	/**
	 * Declare High-Performance Order Storage (HPOS) compatibility
	 *
	 * @see https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 *
	 * @return void
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'woocommerce-shipping-usps/woocommerce-shipping-usps.php' );
		}
	}

	/**
	 * Declare Product Editor compatibility
	 *
	 * @see https://github.com/woocommerce/woocommerce/blob/trunk/docs/product-editor-development/product-editor.md#declaring-compatibility-with-the-product-editor
	 */
	public function declare_product_editor_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', 'woocommerce-shipping-usps/woocommerce-shipping-usps.php' );
		}
	}

	/**
	 * Settings page links.
	 *
	 * @param array $links List of plugin URLs.
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=usps' ) . '">' . __( 'Settings', 'woocommerce-shipping-usps' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Plugin page links to support and documentation
	 *
	 * @param  array  $links List of plugin links.
	 * @param  string $file Current file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( WC_USPS_FILE ) === $file ) {
			$row_meta = array(
				/**
				 * Filter to modify USPS documentation URL.
				 *
				 * @var string USPS documentation URL.
				 *
				 * @since 4.4.25
				 */
				'docs'    => '<a href="' . esc_url( apply_filters( 'woocommerce_shipping_usps_docs_url', 'https://docs.woocommerce.com/document/usps-shipping-method/' ) ) . '" title="' . esc_attr( __( 'View Documentation', 'woocommerce-shipping-usps' ) ) . '">' . __( 'Docs', 'woocommerce-shipping-usps' ) . '</a>',

				/**
				 * Filter to modify USPS support URL.
				 *
				 * @var string USPS support URL.
				 *
				 * @since 4.4.25
				 */
				'support' => '<a href="' . esc_url( apply_filters( 'woocommerce_shipping_usps_support_url', 'https://woocommerce.com/my-account/create-a-ticket/?select=18657' ) ) . '" title="' . esc_attr( __( 'Open a support request at WooCommerce.com', 'woocommerce-shipping-usps' ) ) . '">' . __( 'Support', 'woocommerce-shipping-usps' ) . '</a>',
			);
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}

	/**
	 * Load gateway class
	 */
	public function init() {
		require_once WC_USPS_ABSPATH . 'includes/class-wc-usps-privacy.php';
		include_once WC_USPS_ABSPATH . 'includes/class-wc-shipping-usps.php';
	}

	/**
	 * Add method to WC
	 *
	 * @param array $methods List of shipping methods.
	 */
	public function add_method( $methods ) {
		$methods['usps'] = 'WC_Shipping_USPS';

		return $methods;
	}

	/**
	 * Enqueue scripts
	 */
	public function assets() {
		if ( ! $this->is_usps_settings_page() && ! $this->is_usps_instance_settings_page() ) {
			return;
		}

		// Enqueue Css.
		wp_enqueue_style( 'usps-admin-css', plugin_dir_url( WC_USPS_FILE ) . 'assets/css/admin.css', '', WC_USPS_VERSION );

		// Enqueue scripts.
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'usps-admin', plugin_dir_url( WC_USPS_FILE ) . 'assets/js/admin.js', array( 'jquery' ), WC_USPS_VERSION, true );

		wp_localize_script(
			'usps-admin',
			'usps_settings',
			array(
				'is_instance_settings'   => $this->is_usps_instance_settings_page(),
				'is_usps_settings_page'  => $this->is_usps_settings_page(),
				'migration_notice_nonce' => wp_create_nonce( 'wc_usps_dismiss_migration_notice' ),
			)
		);

		// Custom flat rate boxes -- settings page only.
		if ( $this->is_usps_settings_page() ) {
			wp_enqueue_script( 'usps-custom-flat-rate-boxes', plugin_dir_url( WC_USPS_FILE ) . 'assets/js/custom-flat-rate-boxes.js', array(), WC_USPS_VERSION, true );

			$usps_method = $this->usps_shipping_method();
			if ( $usps_method ) {
				wp_localize_script(
					'usps-custom-flat-rate-boxes',
					'usps_custom_flat_rate_boxes_settings',
					array(
						'flat_rate_boxes' => $usps_method->flat_rate_boxes,
					)
				);
			}
		}
	}
}
