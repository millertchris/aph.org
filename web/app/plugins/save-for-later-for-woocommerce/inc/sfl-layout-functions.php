<?php
/*
 * Layout functions
 *
 * @package Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'sfl_select2_html' ) ) {

	/**
	 * Return or display Select2 HTML
	 *
	 * @return string
	 */
	function sfl_select2_html( $args, $echo = true ) {
		$args = wp_parse_args(
			$args,
			array(
				'class'                   => '',
				'id'                      => '',
				'name'                    => '',
				'list_type'               => '',
				'action'                  => '',
				'placeholder'             => '',
				'exclude_global_variable' => 'no',
				'custom_attributes'       => array(),
				'multiple'                => true,
				'allow_clear'             => true,
				'selected'                => true,
				'options'                 => array(),
			)
		);

		$multiple = $args['multiple'] ? 'multiple="multiple"' : '';
		$name     = esc_attr( '' !== $args['name'] ? $args['name'] : $args['id'] ) . '[]';
		$options  = array_filter( sfl_check_is_array( $args['options'] ) ? $args['options'] : array() );

		$allowed_html = array(
			'select' => array(
				'id'                           => array(),
				'class'                        => array(),
				'data-placeholder'             => array(),
				'data-allow_clear'             => array(),
				'data-exclude-global-variable' => array(),
				'data-action'                  => array(),
				'multiple'                     => array(),
				'name'                         => array(),
			),
			'option' => array(
				'value'    => array(),
				'selected' => array(),
			),
		);

		// Custom attribute handling.
		$custom_attributes = sfl_format_custom_attributes( $args );

		ob_start();
		?><select <?php echo esc_attr( $multiple ); ?> 
			name="<?php echo esc_attr( $name ); ?>" 
			id="<?php echo esc_attr( $args['id'] ); ?>" 
			data-action="<?php echo esc_attr( $args['action'] ); ?>" 
			data-exclude-global-variable="<?php echo esc_attr( $args['exclude_global_variable'] ); ?>" 
			class="sfl_select2_search <?php echo esc_attr( $args['class'] ); ?>" 
			data-placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" 
			<?php echo wp_kses( implode( ' ', $custom_attributes ), $allowed_html ); ?>
			<?php echo $args['allow_clear'] ? 'data-allow_clear="true"' : ''; ?> >
				<?php
				if ( is_array( $args['options'] ) ) {
					foreach ( $args['options'] as $option_id ) {
						$option_value = '';
						switch ( $args['list_type'] ) {
							case 'post':
								$option_value = get_the_title( $option_id );
								break;
							case 'products':
								$option_value = get_the_title( $option_id ) . ' (#' . absint( $option_id ) . ')';
								break;
							case 'customers':
								$user = get_user_by( 'id', $option_id );
								if ( $user ) {
									$option_value = $user->display_name . '(#' . absint( $user->ID ) . ' &ndash; ' . $user->user_email . ')';
								}
								break;
						}

						if ( $option_value ) {
							?>
						<option value="<?php echo esc_attr( $option_id ); ?>" <?php echo $args['selected'] ? 'selected="selected"' : ''; // WPCS: XSS ok. ?>><?php echo esc_html( $option_value ); ?></option>
							<?php
						}
					}
				}
				?>
		</select>
		<?php
		$html = ob_get_clean();

		if ( $echo ) {
			echo wp_kses( $html, $allowed_html );
		}

		return $html;
	}
}

if ( ! function_exists( 'sfl_format_custom_attributes' ) ) {

	/**
	 * Format Custom Attributes
	 *
	 * @return array
	 */
	function sfl_format_custom_attributes( $value ) {
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value ) . '';
			}
		}

		return $custom_attributes;
	}
}

if ( ! function_exists( 'sfl_get_datepicker_html' ) ) {

	/**
	 * Return or display Datepicker HTML
	 *
	 * @return string
	 */
	function sfl_get_datepicker_html( $args, $echo = true ) {
		$args = wp_parse_args(
			$args,
			array(
				'class'             => '',
				'id'                => '',
				'name'              => '',
				'placeholder'       => '',
				'custom_attributes' => array(),
				'value'             => '',
				'wp_zone'           => true,
			)
		);

		$name = ( '' !== $args['name'] ) ? $args['name'] : $args['id'];

		$allowed_html = array(
			'input' => array(
				'id'          => array(),
				'type'        => array(),
				'placeholder' => array(),
				'class'       => array(),
				'value'       => array(),
				'name'        => array(),
				'min'         => array(),
				'max'         => array(),
				'style'       => array(),
			),
		);

		// Custom attribute handling.
		$custom_attributes = sfl_format_custom_attributes( $args );
		$value             = ! empty( $args['value'] ) ? FGF_Date_Time::get_date_object_format_datetime( $args['value'], 'date', $args['wp_zone'] ) : '';
		ob_start();
		?>
		<input type = "text" 
			   id="<?php echo esc_attr( $args['id'] ); ?>"
			   value = "<?php echo esc_attr( $value ); ?>"
			   class="sfl_datepicker <?php echo esc_attr( $args['class'] ); ?>" 
			   placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" 
			   <?php echo wp_kses( implode( ' ', $custom_attributes ), $allowed_html ); ?>
			   />
				
		<input type = "hidden" 
			   class="sfl_alter_datepicker_value" 
			   name="<?php echo esc_attr( $name ); ?>"
			   value = "<?php echo esc_attr( $args['value'] ); ?>"
			   /> 
		<?php
		$html = ob_get_clean();

		if ( $echo ) {
			echo wp_kses( $html, $allowed_html );
		}

		return $html;
	}
}

if ( ! function_exists( 'sfl_get_template' ) ) {

	/**
	 *  Get other templates from themes
	 */
	function sfl_get_template( $template_name, $args = array() ) {

		wc_get_template( $template_name, $args, 'sfl/', SFL()->templates() );
	}
}

if ( ! function_exists( 'sfl_get_template_html' ) ) {

	/**
	 *  Like sfl_get_template, but returns the HTML instead of outputting.
	 *
	 *  @return string
	 */
	function sfl_get_template_html( $template_name, $args = array() ) {

		ob_start();
		sfl_get_template( $template_name, $args );
		return ob_get_clean();
	}
}

if ( ! function_exists( 'sfl_render_product_image' ) ) {

	/**
	 *  Display Product image
	 *
	 *  @return string
	 */
	function sfl_render_product_image( $product, $echo = true ) {

		$allowed_html = array(
			'a'   => array(
				'href' => array(),
			),
			'img' => array(
				'class'  => array(),
				'src'    => array(),
				'alt'    => array(),
				'srcset' => array(),
				'sizes'  => array(),
				'width'  => array(),
				'height' => array(),
			),
		);

		if ( $echo ) {
			echo wp_kses( $product->get_image(), $allowed_html );
		}

		return $product->get_image();
	}
}

if ( ! function_exists( 'sfl_price' ) ) {

	/**
	 *  Display Price based wc_price function
	 *
	 *  @return string
	 */
	function sfl_price( $price, $echo = true ) {

		$allowed_html = array(
			'span' => array(
				'class' => array(),
			),
		);

		if ( $echo ) {
			echo wp_kses( wc_price( $price ), $allowed_html );
		}

		return wc_price( $price );
	}
}

if ( ! function_exists( 'sfl_table_menus_layout' ) ) {

	/**
	 *  Display saved/purchased/deleted table menus
	 *
	 *  @return string
	 */
	function sfl_table_menus_layout( $table_menus ) {
		ob_start();
		$contents = '<nav class="nav-tab-wrapper woo-nav-tab-wrapper" >';
		foreach ( $table_menus as $menu_key => $menu_label ) {
			$active_class   = ( isset( $_REQUEST['status'] ) && $_REQUEST['status'] == $menu_key ) ? 'nav-tab-active' : '';
			$table_menu_url = add_query_arg( array( 'status' => $menu_key ), sfl_get_base_url() );
			$contents      .= '<a class="nav-tab ' . $active_class . '" href="' . esc_url( $table_menu_url ) . '"> ' . esc_html__( $menu_label ) . ' </a>';
		}
		$contents .= '</nav>';

		ob_end_clean();

		$allowed_html = array(
			'nav' => array(
				'class' => array(),
			),
			'a'   => array(
				'class' => array(),
				'href'  => array(),
			),
		);

		echo wp_kses( $contents, $allowed_html );
	}
}
if ( ! function_exists( 'sfl_get_product_col_data' ) ) {

	/**
	 * Get Product name.
	 *
	 * @return html
	 */
	function sfl_get_product_col_data( $product_data, $product_id, $data ) {

		if ( is_user_logged_in() ) {
			$sfl_price = $data->get_product_price();
		} else {
			$sfl_price = sfl_get_cookie_data_by_key( 'sfl_product_price', $data );
		}

		$allowed_html = array(
			'a'    => array(
				'href' => array(),
			),
			'span' => array(
				'class' => array(),
			),
			'br'   => array(),
		);

		$content  = sfl_get_product_name_data( $product_data, $product_id );
		$content .= sfl_get_product_stock_data( $product_data );

		/**
		 * Check is product to Valid Display
		 *
		 * @since 3.8.0
		 */
		if ( apply_filters( 'slf_is_valid_to_display_price_diff', true, $product_data ) ) {
			$content .= sfl_get_product_price_differ_data( $product_data, $sfl_price );
		}

		echo wp_kses( $content, $allowed_html );
	}
}

if ( ! function_exists( 'sfl_get_product_name_data' ) ) {

	/**
	 *  Get Product name.
	 *
	 *  @return html
	 */
	function sfl_get_product_name_data( $product_data, $product_id ) {
		$allowed_html = array(
			'a' => array(
				'href' => array(),
			),
		);
		$content      = wp_kses( '<a href="' . esc_url( get_permalink( $product_id ) ) . '">' . $product_data->get_name() . '</a>', $allowed_html );

		return $content;
	}
}

if ( ! function_exists( 'sfl_get_product_stock_data' ) ) {

	/**
	 *  Get Product name
	 *
	 *  @return string
	 */
	function sfl_get_product_stock_data( $product ) {

		$allowed_html = array(
			'span' => array(
				'class' => array(),
			),
			'br'   => array(),
		);

		if ( ! $product->is_in_stock() ) {
			return wp_kses( '<br><span class="sfl-outofstock">' . esc_html__( 'Out Of Stock', 'save-for-later-for-woocommerce' ) . '</span>', $allowed_html );
		}

		if ( sfl_is_stock_managing( $product ) ) {
			return wp_kses( sprintf( '<br><span class="sfl-stock">%s in stock</span>', $product->get_stock_quantity() ), $allowed_html );
		}
	}
}

if ( ! function_exists( 'sfl_get_product_price_differ_data' ) ) {

	/**
	 *  Get Product Price Differ Data
	 *
	 *  @return html
	 */
	function sfl_get_product_price_differ_data( $product, $sfl_price, $content = '' ) {
		$product_price = sfl_get_tax_based_price( $product->get_id() );

		if ( $sfl_price < $product_price ) {
			$differ_price = (float) $product_price - (float) $sfl_price;

			return '<br>' . str_replace( '{increased_price}', sfl_price( $differ_price, false ), get_option( 'sfl_messages_sfl_price_inc_msg' ) );
		}

		if ( $sfl_price > $product_price ) {
			$differ_price = (float) $sfl_price - (float) $product_price;

			return '<br>' . str_replace( '{decreased_price}', sfl_price( $differ_price, false ), get_option( 'sfl_messages_sfl_price_dec_msg' ) );
		}

		return '';
	}
}


