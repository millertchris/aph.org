<?php
/**
 * General Tab.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'SFL_General_Tab' ) ) {
	return new SFL_General_Tab();
}

/**
 * SFL_General_Tab.
 */
class SFL_General_Tab extends SFL_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = esc_html__( 'General', 'save-for-later-for-woocommerce' );

		add_action( 'woocommerce_admin_field_sfl_guest_fields', array( __CLASS__, 'guest_fields' ) );

		add_action( 'sfl_general_settings_after_save', array( $this, 'after_save' ) );

		add_action( 'sfl_general_settings_after_reset', array( $this, 'after_reset' ) );

		parent::__construct();
	}

	/**
	 * Output the General Tab content.
	 */
	public function general_section_array() {
		return array(
			array(
				'type'  => 'title',
				'title' => __( 'General Settings', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_general_section',
			),
			array(
				'type' => 'sfl_shortcode_notice',
			),
			array(
				'title'   => __( 'Allow "Save for Later" for Logged-in Users', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'enable_sfl' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Allow "Save for Later" for Guest Users', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'enable_guest_sfl' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'type' => 'sfl_guest_fields',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_general_section',
			),
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Display Settings', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_display_section',
			),

			array(
				'title'   => esc_html__( 'Positions to display the Saved for Later Table', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_table_position' ),
				'type'    => 'select',
				'default' => 'woocommerce_after_cart',
				'options' => array(
					'woocommerce_before_cart'       => esc_html( 'WooCommerce Before Cart', 'save-for-later-for-woocommerce' ),
					'woocommerce_before_cart_table' => esc_html( 'WooCommerce Before Cart Table', 'save-for-later-for-woocommerce' ),
					'woocommerce_after_cart_table'  => esc_html( 'WooCommerce After Cart Table', 'save-for-later-for-woocommerce' ),
					'woocommerce_after_cart'        => esc_html( 'WooCommerce After Cart', 'save-for-later-for-woocommerce' ),
				),
			),

			array(
				'title'             => esc_html__( 'Pagination for Saved Later Table', 'save-for-later-for-woocommerce' ),
				'id'                => 'sfl_table_pagination',
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 1 ),
				'default'           => '5',
			),

			array(
				'type' => 'sectionend',
				'id'   => 'sfl_display_section',
			),
		);
	}

	/**
	 * Guest Fields.
	 */
	public static function guest_fields() {
		$timeout      = get_option( 'sfl_general_guest_timeout' );
		$timeout_type = get_option( 'sfl_general_guest_timeout_type' );
		?>
		</tr>
		<tr>         
			<th> <?php esc_html_e( 'Set a time to display the"Saved For Later" products on cart page for Guest Users', 'save-for-later-for-woocommerce' ); ?> </th> 
			<td>
				<input name="sfl_general_guest_timeout" id="sfl_general_guest_timeout" value="<?php echo esc_attr( $timeout ); ?>" class="sfl_general_guest_fields" type="text" >
				<select name="sfl_general_guest_timeout_type" id="sfl_general_guest_timeout_type" class="sfl_general_guest_fields">
					<option value="<?php echo esc_attr( '1' ); ?>" <?php echo selected( $timeout_type, 1 ); ?>><?php esc_html_e( 'Mins', 'save-for-later-for-woocommerce' ); ?></option>
					<option value="<?php echo esc_attr( '2' ); ?>" <?php echo selected( $timeout_type, 2 ); ?>><?php esc_html_e( 'Hours', 'save-for-later-for-woocommerce' ); ?></option>
					<option value="<?php echo esc_attr( '3' ); ?>" <?php echo selected( $timeout_type, 3 ); ?>><?php esc_html_e( 'Days', 'save-for-later-for-woocommerce' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<?php
	}

	/**
	 * Shortcode Notice.
	 */
	public static function shortcode_notice() {
		?>
		</tr>
		<tr>         
			<th colspan="2"></th> 
		</tr>
		<tr>
		<?php
	}

	/**
	 * Save settings.
	 */
	public function after_save() {

		if ( ! isset( $_POST['save'] ) || empty( $_POST['save'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			return;
		}

		check_admin_referer( 'sfl_save_settings', '_sfl_nonce' );

		// Guest user save.
		$custom_fields = array( 'sfl_general_guest_timeout', 'sfl_general_guest_timeout_type' );

		foreach ( $custom_fields as $each_meta ) {
			if ( isset( $_POST[ "$each_meta" ] ) ) {
				update_option( "$each_meta", wc_clean( wp_unslash( $_POST[ "$each_meta" ] ) ) );
			}
		}
	}

	/**
	 * Save settings.
	 */
	public function after_reset() {

		if ( ! isset( $_POST['reset'] ) || empty( $_POST['reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			return;
		}

		check_admin_referer( 'sfl_reset_settings', '_sfl_nonce' );

		// Guest user save.
		$custom_fields = array(
			'sfl_general_guest_timeout'      => 60,
			'sfl_general_guest_timeout_type' => 1,
		);
		foreach ( $custom_fields as $each_meta => $value ) {
			delete_option( $each_meta );
			add_option( $each_meta, $value );
		}
	}
}

return new SFL_General_Tab();
