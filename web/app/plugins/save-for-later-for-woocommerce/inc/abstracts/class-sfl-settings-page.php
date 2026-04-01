<?php
/**
 * Settings Page/Tab
 *
 * @package Abstract
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Settings_Page' ) ) {

	/**
	 * SFL_Settings_Page.
	 */
	abstract class SFL_Settings_Page {

		/**
		 * Setting page id.
		 *
		 * @var Integer
		 */
		protected $id = '';

		/**
		 * Setting page label.
		 *
		 * @var String
		 */
		protected $label = '';

		/**
		 * Setting page code.
		 *
		 * @var String
		 */
		protected $code = '';

		/**
		 * Plugin slug.
		 *
		 * @var String
		 */
		protected $plugin_slug = 'sfl';

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( sanitize_key( $this->plugin_slug . '_settings_tabs_array' ), array( $this, 'add_settings_page' ), 20 );
			add_action( sanitize_key( $this->plugin_slug . '_sections_' . $this->id ), array( $this, 'output_sections' ) );
			add_action( sanitize_key( $this->plugin_slug . '_settings_' . $this->id ), array( $this, 'output' ) );
			add_action( sanitize_key( $this->plugin_slug . '_settings_buttons_' . $this->id ), array( $this, 'output_buttons' ) );
			add_action( sanitize_key( $this->plugin_slug . '_settings_save_' . $this->id ), array( $this, 'save' ) );
			add_action( sanitize_key( $this->plugin_slug . '_settings_reset_' . $this->id ), array( $this, 'reset' ) );
			add_action( sanitize_key( $this->plugin_slug . '_after_setting_buttons_' . $this->id ), array( $this, 'output_extra_fields' ) );
		}

		/**
		 * Get settings page ID.
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get settings page label.
		 */
		public function get_label() {
			return $this->label;
		}

		/**
		 * Get plugin slug.
		 */
		public function get_plugin_slug() {
			return $this->plugin_slug;
		}

		/**
		 * Add this page to settings.
		 */
		public function add_settings_page( $pages ) {
			$pages[ $this->id ] = $this->label;

			return $pages;
		}

		/**
		 * Get settings array.
		 */
		public function get_settings( $current_section = '' ) {
			$settings = array();
			$function = $current_section . '_section_array';

			if ( method_exists( $this, $function ) ) {
				$settings = $this->$function();
			}

			/**
			 * Filter for Settings.
			 *
			 * @since 1.0
			 */
			return apply_filters( sanitize_key( $this->plugin_slug . '_get_settings_' . $this->id ), $settings, $current_section );
		}

		/**
		 * Get sections.
		 */
		public function get_sections() {
			/**
			 * Filter for Settings Sections
			 *
			 * @since 1.0
			 */
			return apply_filters( sanitize_key( $this->plugin_slug . '_get_sections_' . $this->id ), array() );
		}

		/**
		 * Output sections.
		 */
		public function output_sections() {
			global $current_section;

			$sections = $this->get_sections();

			if ( ! sfl_check_is_array( $sections ) || 1 === count( $sections ) ) {
				return;
			}

			$section = '<ul class="subsubsub ' . $this->plugin_slug . '_sections ' . $this->plugin_slug . '_subtab">';

			foreach ( $sections as $id => $label ) {
				$section .= '<li>'
						. '<a href="' . esc_url(
							sfl_get_settings_page_url(
								array(
									'tab'     => $this->id,
									'section' => sanitize_title( $id ),
								)
							)
						) . '" '
						. 'class="' . ( $current_section == $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a></li> | ';
			}

			$section = rtrim( $section, '| ' );

			$section .= '</ul><br class="clear">';

			$allowed_html = array(
				'ul' => array(
					'class' => array(),
				),
				'li' => array(),
				'br' => array( 'class' => array() ),
				'a'  => array(
					'href'  => array(),
					'class' => array(),
				),
			);

			echo wp_kses( $section, $allowed_html );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section, $current_sub_section;

			$section = ( $current_sub_section ) ? $current_sub_section : $current_section;

			$settings = $this->get_settings( $section );

			WC_Admin_Settings::output_fields( $settings );

			/**
			 * Action hook fired Settings Display.
			 *
			 * @since 1.0
			 */
			do_action( sanitize_key( $this->plugin_slug . '_' . $this->id . '_' . $section . '_display' ) );
		}

		/**
		 * Output the settings buttons.
		 */
		public function output_buttons() {

			SFL_Settings::output_buttons();
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section, $current_sub_section;

			$section = ( $current_sub_section ) ? $current_sub_section : $current_section;

			if ( ! isset( $_POST['save'] ) || empty( $_POST['save'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				return;
			}

			check_admin_referer( 'sfl_save_settings', '_sfl_nonce' );

			$settings = $this->get_settings( $section );

			WC_Admin_Settings::save_fields( $settings );
			SFL_Settings::add_message( esc_html__( 'Your settings have been saved', 'save-for-later-for-woocommerce' ) );

			/**
			 * Action hook fired Settings Save.
			 *
			 * @since 1.0
			 */
			do_action( sanitize_key( $this->plugin_slug . '_' . $this->id . '_settings_after_save' ) );
		}

		/**
		 * Reset settings.
		 */
		public function reset() {
			global $current_section, $current_sub_section;

			$section = ( $current_sub_section ) ? $current_sub_section : $current_section;

			if ( ! isset( $_POST['reset'] ) || empty( $_POST['reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				return;
			}

			check_admin_referer( 'sfl_reset_settings', '_sfl_nonce' );

			$settings = $this->get_settings( $section );
			SFL_Settings::reset_fields( $settings );
			SFL_Settings::add_message( esc_html__( 'Your settings have been reset', 'save-for-later-for-woocommerce' ) );

			/**
			 * Action hook fired Settings Reset.
			 *
			 * @since 1.0
			 */
			do_action( sanitize_key( $this->plugin_slug . '_' . $this->id . '_settings_after_reset' ) );
		}

		/**
		 * Output the extra fields
		 */
		public function output_extra_fields() {
		}

		/**
		 * Get option key
		 *
		 * @since 1.0
		 * @param String $key
		 * @return String
		 */
		public function get_option_key( $key ) {
			return sanitize_key( $this->plugin_slug . '_' . $this->id . '_' . $key );
		}
	}
}
