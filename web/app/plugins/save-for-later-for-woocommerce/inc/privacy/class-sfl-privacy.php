<?php
/**
 * SFL Compliance.
 *
 * @package Privacy Policy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Privacy' ) ) :

	/**
	 * SFL_Privacy class.
	 */
	class SFL_Privacy {

		/**
		 * SFL_Privacy constructor.
		 */
		public function __construct() {
			$this->init_hooks();
		}

		/**
		 * Register plugin.
		 */
		public function init_hooks() {
			// This hook registers Booking System privacy content.
			add_action( 'admin_init', array( __CLASS__, 'register_privacy_content' ), 20 );
		}

		/**
		 * Register Privacy Content.
		 */
		public static function register_privacy_content() {
			if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
				return;
			}

			$content = self::get_privacy_message();
			if ( $content ) {
				wp_add_privacy_policy_content( esc_html__( 'Save For Later', 'save-for-later-for-woocommerce' ), $content );
			}
		}

		/**
		 * Prepare Privacy Content.
		 */
		public static function get_privacy_message() {

			return self::get_privacy_message_html();
		}

		/**
		 * Get Privacy Content.
		 */
		public static function get_privacy_message_html() {
			ob_start();
			?>
			<p><?php esc_html_e( 'This includes the basics of what personal data your store may collect, store & share. Depending on what settings are enabled furthermore which additional plugins used, the specific information shared by your store will vary.', 'save-for-later-for-woocommerce' ); ?></p>
			<h2><?php esc_html_e( 'What the Plugin Does?', 'save-for-later-for-woocommerce' ); ?></h2>
			<p><?php esc_html_e( ' This plugin allows users to move products from cart to Save for Later list which they can move back to cart when ready to buy.', 'save-for-later-for-woocommerce' ); ?> </p>
			<h2><?php esc_html_e( 'What We Collect and Store?', 'save-for-later-for-woocommerce' ); ?></h2>
			<h4><?php esc_html_e( 'Username and Email ID', 'save-for-later-for-woocommerce' ); ?></h4>
			<ul>
				<li>
					<p><?php esc_html_e( 'We record the usernames and email ids of the logged-in users to identify the users who have added products to their Save for Later List.', 'save-for-later-for-woocommerce' ); ?></p>
				</li>
			</ul>
			<?php
			$contents = ob_get_contents();
			ob_end_clean();

			return $contents;
		}
	}

	new SFL_Privacy();

endif;
