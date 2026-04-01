<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class ASPWC_Admin_Settings.
 *
 * Admin settings class handles everything related to settings.
 *
 * @class		ASPWC_Admin_Settings
 * @version		1.0.0
 * @author		Jeroen Sormani
 */
class ASPWC_Admin_Settings {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add shipping section
		add_filter( 'woocommerce_get_sections_shipping', array( $this, 'add_section' ) );

		// Add settings
		add_filter( 'woocommerce_get_settings_shipping', array( $this, 'section_settings' ), 10, 2 );

		// Table field type
		add_action( 'woocommerce_admin_field_advanced_shipping_packages_settings_table', array( $this, 'generate_table_field' ) );

		// Add special 'field type' for the package configuration screen.
		add_action( 'woocommerce_admin_field_advanced_shipping_package', array( $this, 'edit_package_screen' ) );

		// Hide meta data in order admin
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_package_name_order_admin' ), 10 );

		// Save package
		add_filter( 'woocommerce_admin_settings_sanitize_option_advanced_shipping_package', array( $this, 'save_package' ), 10, 3 );
	}


	/**
	 * Settings page array.
	 *
	 * Get settings page fields array.
	 *
	 * @since 1.0.0
	 */
	public function get_overview_settings() {

		$settings = apply_filters( 'advanced_shipping_packages_for_woocommerce_settings', array(

			array(
				'title' => __( 'Advanced Shipping Packages', 'advanced-shipping-packages-for-woocommerce' ),
				'type'  => 'title',
			),

			array(
				'title'    => __( 'Enable/Disable', 'advanced-shipping-packages-for-woocommerce' ),
				'desc'     => __( 'Enable Advanced Shipping Packages', 'advanced-shipping-packages-for-woocommerce' ),
				'id'       => 'enable_woocommerce_advanced_shipping_packages',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false
			),

			array(
				'title'    => __( 'Display packages', 'advanced-shipping-packages-for-woocommerce' ),
				'desc'     => __( 'Display packages separately on the thank you page and emails', 'advanced-shipping-packages-for-woocommerce' ),
				'desc_tip' => __( 'Default shows a single \'Shipping\' line', 'advanced-shipping-packages-for-woocommerce' ),
				'id'       => 'advanced_shipping_packages_display_separately',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false
			),

			array(
				'title'    => __( 'Default package name', 'advanced-shipping-packages-for-woocommerce' ),
				'placeholder' => __( 'Leave empty to use default package name', 'advanced-shipping-packages-for-woocommerce' ),
				'id'       => 'advanced_shipping_packages_default_package_name',
				'default'  => '',
				'class'    => 'regular-input',
				'type'     => 'text',
				'autoload' => false
			),

			array(
				'title' => __( 'Advanced Shipping Packages', 'advanced-shipping-packages-for-woocommerce' ),
				'type'  => 'advanced_shipping_packages_settings_table',
			),

			array(
				'type' => 'sectionend',
			),

		) );

		return $settings;
	}


	/**
	 * Get package settings.
	 *
	 * Get the (individual) package settings array.
	 *
	 * @since 1.2.0
	 *
	 * @param  int   $id ID of the package.
	 * @return array     List of settings.
	 */
	public function get_package_settings( $id ) {
		return apply_filters( 'advanced_shipping_packages_for_woocommerce_package_settings', array(
			array(
				'title' => __( 'Advanced Shipping Packages', 'advanced-shipping-packages-for-woocommerce' ),
				'type'  => 'advanced_shipping_package',
				'id'    => 'advanced_shipping_package',
			),
		) );
	}


	/**
	 * Add shipping section.
	 *
	 * Add a new 'extra shipping options' section under the shipping tab.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $sections List of existing shipping sections.
	 * @return array           List of modified shipping sections.
	 */
	public function add_section( $sections ) {
		$sections['advanced_shipping_packages'] = __( 'Packages', 'advanced-shipping-packages-for-woocommerce' );

		return $sections;
	}


	/**
	 * ASPWC settings.
	 *
	 * Add the settings to the Packages shipping section.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $settings        Current settings.
	 * @param string $current_section Slug of the current section
	 * @return array                   Modified settings.
	 */
	public function section_settings( $settings, $current_section ) {
		if ( 'advanced_shipping_packages' === $current_section ) {
			if ( isset( $_GET['id'] ) ) {
				if ( $_GET['id'] == 'new' ) {
					$new_id = wp_insert_post( array(
						'post_type' => 'shipping_package',
						'post_status' => 'publish',
					) );
					wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_packages&id=' . $new_id ) );
					die;
				}

				$settings = $this->get_package_settings( absint( $_GET['id'] ) );
			} else {
				$settings = $this->get_overview_settings();
			}
		}

		return $settings;
	}


	/**
	 * Table field type.
	 *
	 * Load and render table as a field type.
	 */
	public function generate_table_field() {
		require_once plugin_dir_path( __FILE__ ) . 'views/html-advanced-shipping-packages-table.php';
	}


	/**
	 * Edit package screen.
	 *
	 * The output for the 'Edit package' screen on the settings area.
	 * This is accomplished through a custom 'field'. Not ideal, but what we got to work with.
	 *
	 * @since 1.2.0
	 */
	public function edit_package_screen() {
		$package = get_post( absint( $_GET['id'] ) );

		if ( ! $package ) {
			die( 'Invalid package!' );
		}

		include_once dirname( __FILE__ ) . '/views/html-admin-page-package.php';
	}

	/**
	 * Save package.
	 *
	 * Save the package configuration.
	 * Using the 'woocommerce_admin_settings_sanitize_option_{advanced_shipping_package} hook.
	 *
	 * @since 1.2.0
	 *
	 * @param  mixed  $value     Value of the saved option. In this case its the package ID.
	 * @param  string $option    Option name.
	 * @param  mixed  $raw_value Raw value.
	 * @return mixed             Value to save.
	 */
	public function save_package( $value, $option, $raw_value ) {
		$package = get_post( $value );
		if ( ! $package ) {
			return false;
		}

		if ( ! isset( $_POST['aspwc_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['aspwc_meta_box_nonce'], 'aspwc_meta_box' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return false;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return false;
		}

		// Post
		wp_update_post( array(
			'ID'         => $package->ID,
			'post_title' => sanitize_text_field( $_POST['post_title'] ),
		) );

		// Save sanitized conditions
		update_post_meta( $package->ID, '_conditions', wpc_sanitize_conditions( $_POST['conditions'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Sanitize product conditions
		update_post_meta( $package->ID, '_product_conditions', wpc_sanitize_conditions( $_POST['_product_conditions'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Save name
		update_post_meta( $package->ID, '_name', wp_kses_post( $_POST['_name'] ) );

		// Save excluded rates
		$exclude_shipping = wc_clean( $_POST['_exclude_shipping'] ?? array() );
		update_post_meta( $package->ID, '_exclude_shipping', array_filter( $exclude_shipping ) ); // nosemgrep: audit.php.lang.misc.array-filter-no-callback

		$include_shipping = wc_clean( $_POST['_include_shipping'] ?? array() );
		update_post_meta( $package->ID, '_include_shipping', array_filter( $include_shipping ) ); // nosemgrep: audit.php.lang.misc.array-filter-no-callback

		return $value;
	}

	/**
	 * Hide package name.
	 *
	 * Hide the package name in the order admin.
	 *
	 * @since 1.2.0
	 *
	 * @param  array $hidden List of existing hidden meta keys.
	 * @return array         List of modified hidden meta keys.
	 */
	public function hide_package_name_order_admin( $hidden ) {
		$hidden[] = 'package_name';

		return $hidden;
	}
}
