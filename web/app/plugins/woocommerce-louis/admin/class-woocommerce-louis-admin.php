<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Louis
 * @subpackage Woocommerce_Louis/admin
 * @author     Mightily <sos@mightily.com>
 */
class Woocommerce_Louis_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-louis-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-louis-admin.js', array( 'wc-admin-meta-boxes' ), $this->version, true );

		if(parse_url( get_site_url(), PHP_URL_HOST ) == 'localhost'){
			$solr_url = 'https://staging.louis.aph.org/wp-json/solr/v1/query?index=louis';
			$add_to_cart_url = '/add-to-cart-from-louis?p=';
		} elseif(parse_url( get_site_url(), PHP_URL_HOST ) == 'staging.aph.org') {
			$solr_url = 'https://staging.louis.aph.org/wp-json/solr/v1/query?index=louis';
			$add_to_cart_url = '/add-to-cart-from-louis?p=';
		} else {
			$solr_url = 'https://louis.aph.org/wp-json/solr/v1/query?index=louis';
			$add_to_cart_url = '/add-to-cart-from-louis?p=';
		}

        wp_localize_script(
            $this->plugin_name,
            'woocommerce_louis',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
				'solrUrl' => $solr_url,
				'addToCartUrl' => $add_to_cart_url,
                'ajaxNonce' => wp_create_nonce('solr-nonce'),
                'restNonce' => wp_create_nonce('wp_rest')
            )
        );		

	}

	public function add_admin_add_louis_product($order_id){ ?>
        <button type="button" class="button add-louis-order-item" style="margin-left:4px;">Add Louis product(s)</button>
		<script type="text/template" id="tmpl-woocommerce-louis">
			<div class="wc-backbone-modal">
				<div class="wc-backbone-modal-content">
					<section class="wc-backbone-modal-main" role="main">
						<header class="wc-backbone-modal-header">
							<h1 id="louis-admin-heading" aria-live="polite"><?php esc_html_e( 'Add Louis Product(s)', 'woocommerce' ); ?></h1>
							<button class="louis-modal-close modal-close-link dashicons dashicons-no-alt">
								<span class="screen-reader-text">Close modal panel</span>
							</button>
						</header>
						<article>
							<form action="" method="post">
								<table class="widefat">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
											<th><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
										</tr>
									</thead>
									<?php
										$row = '
											<td><select class="wc-louis-product-search" name="item_id" data-allow_clear="true" data-display_stock="true" data-exclude_type="variable" data-placeholder="' . esc_attr__( 'Search for a Louis product&hellip;', 'woocommerce' ) . '"></select></td>
											<td><input type="number" step="1" min="0" max="9999" autocomplete="off" name="item_qty" placeholder="1" size="4" class="quantity" /></td>';
									?>
									<tbody data-row="<?php echo esc_attr( $row ); ?>">
										<tr>
											<?php echo $row; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</tr>
									</tbody>
								</table>
							</form>
						</article>
						<footer id="louis-admin-footer">
							<div class="inner">
								<button id="louis-btn-ok" class="button button-primary button-large"><?php esc_html_e( 'Add', 'woocommerce' ); ?></button>
							</div>
						</footer>
					</section>
				</div>
			</div>
			<div class="wc-backbone-modal-backdrop louis-modal-close"></div>
		</script>
		<script>
			jQuery( function( $ ) {
				$('button.button.add-louis-order-item').insertAfter('button.button.add-order-item');
			} );
		</script>
        <?php
    }

}
