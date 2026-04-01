<?php 

/**
 * Class WooCommerce_Simple_Registration.
 *
 * Main WooCommerce_Simple_Registration class initializes the plugin.
 *
 * @class		WooCommerce_Simple_Registration
 * @version		1.0.0
 * @author		Astoundify
 */
class WooCommerce_Simple_Registration {

	/**
	 * Instace of WooCommerce_Simple_Registration.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var object $instance The instance of WooCommerce_Simple_Registration.
	 */
	private static $instance;


	/**
	 * Construct.
	 *
	 * Initialize the class and plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// woocommerce_simple_registration shortcode
		// add_shortcode( 'woocommerce_simple_registration', array( $this, 'registration_template' ) );

		// add a body class on this page
		// add_filter( 'body_class', array( $this, 'body_class' ) );

		// add first name and last name to register form
		add_action( 'woocommerce_register_form_start', array( $this, 'add_name_input' ) );
		add_action( 'woocommerce_register_form_end', array( $this, 'add_group_input' ) );
		add_action( 'woocommerce_created_customer', array( $this, 'save_meta_input' ) );

		// Settings.
		add_filter( 'woocommerce_account_settings', array( $this, 'account_settings' ) );

		// Filter WP Register URL.
		add_filter( 'register_url', array( $this, 'register_url' ) );

		/**
		 * WooCommerce Social Login Support
		 * @link http://www.woothemes.com/products/woocommerce-social-login/
		 * @since 1.3.0
		 */
//		if( function_exists( 'init_woocommerce_social_login' ) ){
//			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/wc-social-login.php' );
//		}
	}

	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 * @return object Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) )  {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Registartion template.
	 *
	 * Return the registration template contents.
	 *
	 * @return string HTML registration form template.
	 */
	public function registration_template() {

		ob_start();

			if ( ! is_user_logged_in() ) :

				$message = apply_filters( 'woocommerce_registration_message', '' );

				if ( ! empty( $message ) ) :
					wc_add_notice( $message );
				endif;

				wc_get_template( 'registration-form.php', array(), 'woocommerce-simple-registration/', plugin_dir_path( __FILE__ ) . 'templates/' );

			else :
				echo do_shortcode( '[woocommerce_my_account]' );
			endif;

			$return = ob_get_contents();
		ob_end_clean();

		return $return;

	}

	/**
	 * Add First Name & Last Name
	 * To disable this simply use this code:
	 * `add_filter( 'woocommerce_simple_registration_name_fields', '__return_false' );`
	 * @since 1.3.0
	 */
	public function add_name_input(){
		// Name Field Option.
		$enabled = 'yes' === WC_Admin_Settings::get_option( 'woocommerce_simple_registration_name_fields', 'yes' ) ? true : false;
		$required = 'yes' === WC_Admin_Settings::get_option( 'woocommerce_simple_registration_name_fields_required', 'no' ) ? true : false;

		/* Filter to disable this feature. */
		if( ! apply_filters( 'woocommerce_simple_registration_name_fields', true ) || ! $enabled ){
			return;
		}
		?>
		<p class="woocommerce-FormRow woocommerce-FormRow--first form-row form-row-first">
			<label for="reg_sr_firstname"><?php _e( 'First Name', 'woocommerce-simple-registration' ); ?><?php echo( $required ? ' <span class="required">*</span>' : '' ) ?></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="sr_firstname" id="reg_sr_firstname" value="<?php if ( ! empty( $_POST['sr_firstname'] ) ) echo esc_attr( $_POST['sr_firstname'] ); ?>" <?php echo( $required ? ' required' : '' ) ?>/>
		</p>

		<p class="woocommerce-FormRow woocommerce-FormRow--last form-row form-row-last">
			<label for="reg_sr_lastname"><?php _e( 'Last Name', 'woocommerce-simple-registration' ); ?><?php echo( $required ? ' <span class="required">*</span>' : '' ) ?></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="sr_lastname" id="reg_sr_lastname" value="<?php if ( ! empty( $_POST['sr_lastname'] ) ) echo esc_attr( $_POST['sr_lastname'] ); ?>" <?php echo( $required ? ' required' : '' ) ?> />
		</p>
		<?php
	}
	public function add_group_input(){
		// Group Field Option.
		if (!empty($_GET['group'])) : ?>
			<input type="hidden" class="woocommerce-Input woocommerce-Input--hidden input-hidden" name="group" id="reg_sr_group" value="<?php echo $_GET['group']; ?>" />
		<?php endif; ?>

		<?php
	}
	/**
	 * Save First Name and Last Name
	 * @since 1.3.0
	 * @see WC/includes/wc-user-functions.php line 114
	 */
	public function save_meta_input( $customer_id ){
		// Name Field Option.
		$enable = 'yes' === WC_Admin_Settings::get_option( 'woocommerce_simple_registration_name_fields', 'yes' ) ? true : false;

		/* Filter to disable this feature. */
		if( ! apply_filters( 'woocommerce_simple_registration_name_fields', true ) || ! $enable ){
			return;
		}

		/* Strip slash everything */
		$request = stripslashes_deep( $_POST );

		/* Save First Name */
		if ( isset( $request['sr_firstname'] ) && !empty( $request['sr_firstname'] ) ) {
			update_user_meta( $customer_id, 'first_name', sanitize_text_field( $request['sr_firstname'] ) );
		}
		/* Save Last Name */
		if ( isset( $request['sr_lastname'] ) && !empty( $request['sr_lastname'] ) ) {
			update_user_meta( $customer_id, 'last_name', sanitize_text_field( $request['sr_lastname'] ) );
		}
		/* Save User Group */
		if ( isset( $request['group'] ) && !empty( $request['group'] ) ) {
			//$encrypter = new AphEncrypter(CRYPT_KEY);
			//$group_id = $encrypter->decryptString($request['group']);
			$group_ids = \APH\Encrypter::decryptString($request['group']);
			$group_ids = explode('||', $group_ids);
			$group_slug_array = [];
			foreach($group_ids as $group_id){
				$group_object = get_term($group_id, 'user-group');
				// $group_slug = $group_object->slug;
				$group_slug_array[] = $group_object->slug;
				// Add the customer to the EOTs group

			}
			wp_set_object_terms($customer_id, $group_slug_array, 'user-group');
			// Add the teacher role to this user
			$wp_user_object = new WP_User($customer_id);
			$wp_user_object->set_role('teacher');			
		}		
	}

	/**
	 * Settings
	 *
	 * @since 1.5.0
	 *
	 * @param array $settings WooCommerce Accounts Settings.
	 * @return array
	 */
	public function account_settings( $settings ) {
		$page = array(
			array(
				'title'         => __( 'Registration page', 'woocommerce-simple-registration' ),
				'desc'          => __( 'Use this page as WordPress registration URL. Page contents: [woocommerce_simple_registration]', 'woocommerce-simple-registration' ),
				'id'            => 'woocommerce_simple_registration_register_page',
				'default'       => 0,
				'type'          => 'single_select_page',
				'args'          => array(
					'option_none_value' => 0,
					'show_option_none' => esc_html__( 'Select a page&hellip;', 'woocommerce-simple-registration' ),
				),
				'class'         => 'wc-enhanced-select',
				'css'           => 'min-width:300px;',
				'desc_tip'      => true,
			),
		);

		array_splice( $settings, 2, 0, $page );

		$name = array(
			array(
				'desc'          => __( 'Enable first and last name fields.', 'woocommerce-simple-registration' ),
				'id'            => 'woocommerce_simple_registration_name_fields',
				'default'       => 'yes',
				'checkboxgroup'   => '',
				'type'          => 'checkbox',
			),
			array(
				'desc'          => __( 'Require first and last name fields.', 'woocommerce-simple-registration' ),
				'id'            => 'woocommerce_simple_registration_name_fields_required',
				'default'       => 'no',
				'checkboxgroup'   => '',
				'type'          => 'checkbox',
			),
		);

		array_splice( $settings, 9, 0, $name );

		return $settings;
	}

	/**
	 * Register URL
	 *
	 * @since 1.5.0
	 *
	 * @param string $url Registration URL.
	 * @return string $url
	 */
	public function register_url( $url ) {
		$register_page = WC_Admin_Settings::get_option( 'woocommerce_simple_registration_register_page', 0 );

		if ( $register_page && get_permalink( $register_page ) ) {
			$url = esc_url( get_permalink( $register_page ) );
		}

		return $url;
	}

}

function WooCommerce_Simple_Registration() {
	return WooCommerce_Simple_Registration::instance();
}

add_action( 'init', 'WooCommerce_Simple_Registration' );

//function myplugin_registration_save( $username, $email, $validation_errors) {
//
//	file_put_contents('register.txt', print_r('no', true));
//
//}
//
//add_action( 'woocommerce_register_post', 'myplugin_registration_save', 10, 3 );
//
//function myplugin_check_fields($validation_error, $username, $password, $email) {
//	if($email == 'lance@mightily.com'){
//		$validation_error->add( 'demo_error', __( '<strong>ERROR</strong>: This is a demo error.', 'aph' ) );
//		wc_add_notice( 'This is my custom error', 'error' );
//		wc_print_notices();
//	}
//	
//	file_put_contents('register.txt', print_r($validation_error, true));
////    $errors->add( 'demo_error', __( '<strong>ERROR</strong>: This is a demo error.', 'my_textdomain' ) );
//    return $validation_error;
//}
//
//add_filter( 'woocommerce_process_registration_errors', 'myplugin_check_fields', 10, 4 );
//
//function log_reg_error($data){
//	file_put_contents('reg-error.txt', print_r($data, true));
//}
//
//add_filter( 'woocommerce_new_customer_data', 'log_reg_error', 10, 1 );