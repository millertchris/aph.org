<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mightily.com
 * @since      1.0.0
 *
 * @package    Back_End_Shipping
 * @subpackage Back_End_Shipping/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Back_End_Shipping
 * @subpackage Back_End_Shipping/admin
 * @author     Mightily <sos@mightily.com>
 */
class Back_End_Shipping_Admin {

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

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Back_End_Shipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Back_End_Shipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/back-end-shipping-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Back_End_Shipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Back_End_Shipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/back-end-shipping-admin.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->plugin_name, 'besAjax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'add_security' => wp_create_nonce('add_shipping_line_item'),
			'remove_security' => wp_create_nonce('remove_shipping_line_item'),
		));

	}

	public function remove_shipping_item_ajax() {

		check_ajax_referer('remove_shipping_line_item', 'security');

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		try {
            $shipping_line_item_id = isset( $_POST['data'] ) ? $_POST['data'] : 0;
            $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
            $order    = wc_get_order( $order_id );

            $product_ids = wc_get_order_item_meta($shipping_line_item_id, 'Ids', true);
            $response['removed_products'] = $product_ids;
            // Array of product ids
            $shipping_product_ids_array = explode(',', $product_ids);
            $response['removed_products_array'] = $shipping_product_ids_array;
            $reponse['order_items'] = [];
            // Loop through order items. If this order item has a product id in our array of product ids, we need to remove the _shipped meta data.
            foreach($order->get_items() as $order_item){

                if (in_array($order_item->get_product_id(), $shipping_product_ids_array)) {
                    $order_item->delete_meta_data('_shipped');
                    $order_item->save_meta_data();
                    $order_item->save();
                    $response['message'] = 'Removed meta data';
                }
            }
		} catch (Exception $e) {
			wp_send_json_error(array('error' => $e->getMessage()));
		}
		// wp_send_json_success must be outside the try block not to break phpunit tests.
		wp_send_json_success($response);
        wp_die();

	}

	public function add_shipping_item_ajax() {

		check_ajax_referer('add_shipping_line_item', 'security');

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		$response = array();

		try {
            $shipping_items = isset( $_POST['data'] ) ? $_POST['data'] : [];
			$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
			$order    = wc_get_order( $order_id );

			if ( ! $order ) {
				throw new Exception( __( 'Invalid order', 'woocommerce' ) );
			}

			$order_taxes      = $order->get_taxes();
			$shipping_methods = WC()->shipping() ? WC()->shipping()->load_shipping_methods() : array();

			ob_start(); ?>
            <?php foreach($shipping_items as $shipping_item) : ?>

                <?php

                $shipping_item_array = explode("||", $shipping_item);
                $shipping_label = $shipping_item_array[0];
                $shipping_cost = $shipping_item_array[1];
                $shipping_product_names = $shipping_item_array[2];
                $shipping_method_id = $shipping_item_array[3];
                $shipping_instance_id = $shipping_item_array[4];
                $shipping_product_ids = $shipping_item_array[5];

                // Add new shipping.
    			$item = new WC_Order_Item_Shipping();
    			$item->set_shipping_rate(new WC_Shipping_Rate());
    			$item->set_order_id($order_id);
                $item->set_method_id($shipping_method_id);
                $item->set_instance_id($shipping_instance_id);
                $item->set_method_title($shipping_label);
                $item->set_total($shipping_cost);
                //$item->add_meta_data('Items', $shipping_product_names);
				$item->add_meta_data('Items', str_replace('×', '&times;', $shipping_product_names));
                $item->add_meta_data('Ids', $shipping_product_ids);
                $item->save_meta_data();
    			$item_id = $item->save();

                $shipping_product_ids_array = explode(',', $shipping_product_ids);
                // Set the 'shipped' meta data for the order line items associated with this shipping item
                foreach($order->get_items() as $order_item){
                    if (in_array($order_item->get_product_id(), $shipping_product_ids_array)) {
                        $order_item->add_meta_data('_shipped', '1');
                        $order_item->save_meta_data();
                        $order_item->save();
                    }
                }

                ?>
                <tr class="shipping <?php echo ( ! empty( $class ) ) ? esc_attr( $class ) : ''; ?>" data-order_item_id="<?php echo esc_attr( $item_id ); ?>">
                	<td class="thumb"><div></div></td>

                	<td class="name">
                		<div class="view">
                			<?php echo esc_html( $item->get_name() ? $item->get_name() : __( 'Shipping', 'woocommerce' ) ); ?>
                		</div>
                		<div class="edit" style="display: none;">
                			<input type="hidden" name="shipping_method_id[]" value="<?php echo esc_attr( $item_id ); ?>" />
                			<input type="text" class="shipping_method_name" placeholder="<?php esc_attr_e( 'Shipping name', 'woocommerce' ); ?>" name="shipping_method_title[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $item->get_name() ); ?>" />
                			<select class="shipping_method" name="shipping_method[<?php echo esc_attr( $item_id ); ?>]">
                				<optgroup label="<?php esc_attr_e( 'Shipping method', 'woocommerce' ); ?>">
                					<option value=""><?php esc_html_e( 'N/A', 'woocommerce' ); ?></option>
                					<?php
                					$found_method = false;

                					foreach ( $shipping_methods as $method ) {
                						$current_method = ( 0 === strpos( $item->get_method_id(), $method->id ) ) ? $item->get_method_id() : $method->id;

                						echo '<option value="' . esc_attr( $current_method ) . '" ' . selected( $item->get_method_id() === $current_method, true, false ) . '>' . esc_html( $method->get_method_title() ) . '</option>';

                						if ( $item->get_method_id() === $current_method ) {
                							$found_method = true;
                						}
                					}

                					if ( ! $found_method && $item->get_method_id() ) {
                						echo '<option value="' . esc_attr( $item->get_method_id() ) . '" selected="selected">' . esc_html__( 'Other', 'woocommerce' ) . '</option>';
                					} else {
                						echo '<option value="other">' . esc_html__( 'Other', 'woocommerce' ) . '</option>';
                					}
                					?>
                				</optgroup>
                			</select>
                		</div>

                		<?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, null ); ?>
                        <?php
                        $hidden_order_itemmeta = apply_filters(
                        	'woocommerce_hidden_order_itemmeta', array(
                        		'_qty',
                        		'_tax_class',
                        		'_product_id',
                        		'_variation_id',
                        		'_line_subtotal',
                        		'_line_subtotal_tax',
                        		'_line_total',
                        		'_line_tax',
                        		'method_id',
                        		'cost',
                        		'_reduced_stock',
                        	)
                        );
                        ?><div class="view">
                        	<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
                        		<table cellspacing="0" class="display_meta">
                        			<?php
                        			foreach ( $meta_data as $meta_id => $meta ) :
                        				if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
                        					continue;
                        				}
                        				?>
                        				<tr>
                        					<th width="1%"><?php echo wp_kses_post( $meta->display_key ); ?>:</th>
                        					<td><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?></td>
                        				</tr>
                        			<?php endforeach; ?>
                        		</table>
                        	<?php endif; ?>
                        </div>
                        <div class="edit" style="display: none;">
                        	<table class="meta" cellspacing="0">
                        		<tbody class="meta_items">
                        			<?php if ( $meta_data = $item->get_formatted_meta_data( '' ) ) : ?>
                        				<?php
                        				foreach ( $meta_data as $meta_id => $meta ) :
                        					if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
                        						continue;
                        					}
                        					?>
                        					<tr data-meta_id="<?php echo esc_attr( $meta_id ); ?>">
                        						<td>
                        							<input type="text" maxlength="255" placeholder="<?php esc_attr_e( 'Name (required)', 'woocommerce' ); ?>" name="meta_key[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]" value="<?php echo esc_attr( $meta->key ); ?>" />
                        							<textarea placeholder="<?php esc_attr_e( 'Value (required)', 'woocommerce' ); ?>" name="meta_value[<?php echo esc_attr( $item_id ); ?>][<?php echo esc_attr( $meta_id ); ?>]"><?php echo esc_textarea( rawurldecode( $meta->value ) ); ?></textarea>
                        						</td>
                        						<td width="1%"><button class="remove_order_item_meta button">&times;</button></td>
                        					</tr>
                        				<?php endforeach; ?>
                        			<?php endif; ?>
                        		</tbody>
                        		<tfoot>
                        			<tr>
                        				<td colspan="4"><button class="add_order_item_meta button"><?php esc_html_e( 'Add&nbsp;meta', 'woocommerce' ); ?></button></td>
                        			</tr>
                        		</tfoot>
                        	</table>
                        </div>
                		<?php do_action( 'woocommerce_after_order_itemmeta', $item_id, $item, null ); ?>
                	</td>

                	<?php do_action( 'woocommerce_admin_order_item_values', null, $item, absint( $item_id ) ); ?>

                	<td class="item_cost" width="1%">&nbsp;</td>
                	<td class="quantity" width="1%">&nbsp;</td>

                	<td class="line_cost" width="1%">
                		<div class="view">
                			<?php
                			echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );
                			$refunded = $order->get_total_refunded_for_item( $item_id, 'shipping' );
                			if ( $refunded ) {
                				echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
                			}
                			?>
                		</div>
                		<div class="edit" style="display: none;">
                			<input type="text" name="shipping_cost[<?php echo esc_attr( $item_id ); ?>]" placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>" value="<?php echo esc_attr( wc_format_localized_price( $item->get_total() ) ); ?>" class="line_total wc_input_price" />
                		</div>
                		<div class="refund" style="display: none;">
                			<input type="text" name="refund_line_total[<?php echo absint( $item_id ); ?>]" placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>" class="refund_line_total wc_input_price" />
                		</div>
                	</td>

                	<?php
                	if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
                		foreach ( $order_taxes as $tax_item ) {
                			$tax_item_id    = $tax_item->get_rate_id();
                			$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
                			?>
                			<td class="line_tax" width="1%">
                				<div class="view">
                					<?php
                					echo ( '' !== $tax_item_total ) ? wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) ) : '&ndash;';
                					$refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id, 'shipping' );
                					if ( $refunded ) {
                						echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
                					}
                					?>
                				</div>
                				<div class="edit" style="display: none;">
                					<input type="text" name="shipping_taxes[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]" placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>" value="<?php echo ( isset( $tax_item_total ) ) ? esc_attr( wc_format_localized_price( $tax_item_total ) ) : ''; ?>" class="line_tax wc_input_price" />
                				</div>
                				<div class="refund" style="display: none;">
                					<input type="text" name="refund_line_tax[<?php echo absint( $item_id ); ?>][<?php echo esc_attr( $tax_item_id ); ?>]" placeholder="<?php echo esc_attr( wc_format_localized_price( 0 ) ); ?>" class="refund_line_tax wc_input_price" data-tax_id="<?php echo esc_attr( $tax_item_id ); ?>" />
                				</div>
                			</td>
                			<?php
                		}
                	}
                	?>
                	<td class="wc-order-edit-line-item">
                		<?php if ( $order->is_editable() ) : ?>
                			<div class="wc-order-edit-line-item-actions">
                				<a class="edit-order-item" href="#"></a><a class="delete-order-item" href="#"></a>
                			</div>
                		<?php endif; ?>
                	</td>
                </tr>
            <?php endforeach; ?>
			<?php $response['html'] = ob_get_clean();

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
		// wp_send_json_success must be outside the try block not to break phpunit tests.
		wp_send_json_success( $response );
        wp_die();

    }

	public function add_order_item_value($product, $item, $item_id){ ?>
		<td class="item_bes_ship" width="1%">
			<div class="view" style="text-align: center;">
				<?php if($product) : ?>
					<?php if($item->get_meta('_shipped') && $item->get_meta('_shipped') == 1) : ?>
						<span style="color: green;" class="dashicons dashicons-yes"></span> Shipped
					<?php else : ?>
						<input type="checkbox" name="bes_ship" value="<?php echo $product->get_id(); ?>" class="bes_ship_checkbox" checked>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</td>
	<?php }

	public function add_order_item_header($order){ ?>
        <th class="item_bes_ship"><label>Ship?&nbsp;<input type="checkbox" name="bes_ship_select_all" value="1" class="bes_ship_checkbox_select_all"></label></th>
    <?php }

	public function add_admin_calculate_shipping($order_id){ ?>
		<?php
			// $user = wp_get_current_user();
			// if (in_array('eot', (array) $user->roles ) || in_array('eot-assistant', (array) $user->roles )) {
			// 	return false;
			// }
		?>
        <button type="button" class="button calculate-shipping">Calculate Shipping</button>
        <script type="text/template" id="tmpl-shipping-rates">
        	<div class="wc-backbone-modal">
        		<div class="wc-backbone-modal-content">
        			<section class="wc-backbone-modal-main" role="main">
        				<header class="wc-backbone-modal-header">
        					<h1><?php esc_html_e( 'Choose a Shipping Method', 'woocommerce' ); ?></h1>
        					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
        						<span class="screen-reader-text">Close modal panel</span>
        					</button>
        				</header>
        				<article>
                            <p>Choose a shipping method for each package below.</p>
                            <# if ( data.errors.length > 0 ) { #>
                                <p><strong>Note:</strong> Shipping could not be calculated for the following item(s): <# _(data.errors).each(function(sku, key) { #> {{{ sku }}} <# }); #>.</p>
                            <# } #>
                            <div id="shipping_method" class="woocommerce-shipping-methods">
                            <# _(data.packages).each(function(package, key) { #>
                                <div class="shipping-package shipping-package-{{{ key }}}">
                                    <span><strong>{{{ package.package_label }}}</strong> <br />
                                    Items: <em>{{{ package.product_names }}}</em></span>
                                    <p style="margin-bottom: 30px;">
										<label>
										<select name="bes_shipping_method-package-{{{ key }}}">
                                        <# _(package.rates).each(function(rate, keyb) { #>
												<option value="{{{ rate.shipping_label }}}||{{{ rate.shipping_cost }}}||{{{ package.product_names }}}||{{{ rate.shipping_id }}}||{{{ rate.shipping_instance_id }}}||{{{ package.product_ids }}}" <# if(keyb == 0){ #>selected<# } #>>{{{ rate.shipping_label }}}: ${{{ rate.shipping_cost }}}</option>
                                        <# }); #>
										</select>
										</label>
                                    </p>									
                                </div>
                            <# }); #>
                            </div>
        				</article>
        				<footer>
        					<div class="inner">
                                <!-- This doesnt do anything yet. Perhaps we should dynamically add the shipping line item based on the option selected above -->
        						<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e( 'Add', 'woocommerce' ); ?></button>
        					</div>
        				</footer>
        			</section>
        		</div>
        	</div>
        	<div class="wc-backbone-modal-backdrop modal-close"></div>
        </script>

        <?php
    }

	public function add_admin_class_line_item($class, $item, $order) {
        // Maybe modify $example in some way.
        return $class . ' bes-productid-' . $item->get_product_id();
	}
	
	public function validate_meta_data($post_id, $post) {
		if (is_user_role('eot') || is_user_role('eot-assistant')){
			if($_POST['order_status'] == 'wc-processing'){
				$order = wc_get_order($post_id);
				foreach($order->get_items() as $order_item){
					if($order_item->get_meta('_shipped') != '1'){
						wp_die('There are line items on this order that do not have a shipping method or are not marked as "Shipped." Please verify every line item is marked "Shipped" before moving this order to Processing. <a href="javascript:history.back()">Go back to order.</a>');
						break;
					}
				}
			}
		}
		if (is_user_role('customer_service') || is_user_role('administrator')){
			if($_POST['order_status'] == 'wc-processing'){
				$order = wc_get_order($post_id);
				foreach($order->get_items() as $order_item){
					// var_dump($order_item->get_product()->is_downloadable());
					// check if product is not downloadable and not virtual
					$is_downloadable = $order_item->get_product()->is_downloadable();
					$is_virtual = $order_item->get_product()->is_virtual();
					if(
						$order_item->get_meta('_shipped') != '1'
						&& !( $is_downloadable || $is_virtual )
					){
						wp_die('There are line items on this order that do not have a shipping method or are not marked as "Shipped." Please verify every line item is marked "Shipped" before moving this order to Processing. <a href="javascript:history.back()">Go back to order.</a>');
						break;
					}
				}
			}
		}		
    }	

}
