<?php
/**
 * Admin Settings Class.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Settings' ) ) {

	/**
	 * SFL_Settings Class.
	 */
	class SFL_Settings {

		/**
		 * Setting pages.
		 *
		 * @var Array
		 */
		private static $settings = array();

		/**
		 * Error messages.
		 *
		 * @var Array
		 */
		private static $errors = array();

		/**
		 * Plugin slug.
		 *
		 * @var String
		 */
		private static $plugin_slug = 'sfl';

		/**
		 * Update messages.
		 *
		 * @var Array
		 */
		private static $messages = array();

		/**
		 * Include the settings page classes.
		 *
		 * @since 1.0
		 */
		public static function get_settings_pages() {
			if ( ! empty( self::$settings ) ) {
				return self::$settings;
			}

			include_once SFL_ABSPATH . 'inc/abstracts/class-sfl-settings-page.php';

			$settings = array();
			$tabs     = self::settings_page_tabs();

			foreach ( $tabs as $tab_name ) {
				$settings[ sanitize_key( $tab_name ) ] = include 'tabs/' . sanitize_key( $tab_name ) . '.php';
			}

			/**
			 * Filter for Settings Pages
			 *
			 * @since 1.0
			 */
			self::$settings = apply_filters( sanitize_key( self::$plugin_slug . '_get_settings_pages' ), $settings );

			return self::$settings;
		}

		/**
		 * Add a message.
		 *
		 * @since 1.0
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text;
		}

		/**
		 * Add an error.
		 *
		 * @since 1.0
		 * @param String $text Error message.
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text;
		}

		/**
		 * Output messages + errors.
		 *
		 * @since 1.0
		 */
		public static function show_messages() {
			if ( count( self::$errors ) > 0 ) {
				foreach ( self::$errors as $error ) {
					self::error_message( $error );
				}
			} elseif ( count( self::$messages ) > 0 ) {
				foreach ( self::$messages as $message ) {
					self::success_message( $message );
				}
			}
		}

		/**
		 * Show an success message.
		 *
		 * @since 1.0
		 * @param String  $text Success Message.
		 * @param Boolean $echo Priority of Success Message.
		 */
		public static function success_message( $text, $echo = true ) {
			ob_start();
			$contents = '<div id="message " class="updated inline ' . esc_html( self::$plugin_slug ) . '_save_msg"><p><strong>' . esc_html( $text ) . '</strong></p></div>';
			ob_end_clean();

			if ( $echo ) {
				$allowed_html = array(
					'div'    => array(
						'class' => array(),
					),
					'p'      => array(),
					'i'      => array(
						'class'       => array(),
						'aria-hidden' => array(),
					),
					'strong' => array(),
				);

				echo wp_kses( $contents, $allowed_html );
			} else {
				return $contents;
			}
		}

		/**
		 * Show an error message.
		 *
		 * @since 1.0
		 * @param String  $text Error Message.
		 * @param Boolean $echo Priority of Error Message.
		 */
		public static function error_message( $text, $echo = true ) {
			ob_start();
			$contents = '<div id="message" class="error inline"><p><strong>' . esc_html( $text ) . '</strong></p></div>';
			ob_end_clean();

			if ( $echo ) {
				$allowed_html = array(
					'div'    => array(
						'class' => array(),
					),
					'p'      => array(),
					'i'      => array(
						'class'       => array(),
						'aria-hidden' => array(),
					),
					'strong' => array(),
				);

				echo wp_kses( $contents, $allowed_html );
			} else {
				return $contents;
			}
		}

		/**
		 * Settings page tabs
		 *
		 * @since 1.0
		 * @return Array
		 */
		public static function settings_page_tabs() {
			return array(
				'general',
				'advanced',
				'messages',
				'localization',
			);
		}

		/**
		 * Handles the display of the settings page in admin.
		 *
		 * @since 1.0
		 */
		public static function output() {
			global $current_section, $current_tab;

			/**
			 * Action hook fired Before Settings Start.
			 *
			 * @since 1.0
			 */
			do_action( sanitize_key( self::$plugin_slug . '_settings_start' ) );

			$tabs = sfl_get_allowed_setting_tabs();

			/* Include admin html settings */
			include_once 'views/html-settings.php';
		}

		/**
		 * Handles the display of the settings page buttons in page.
		 *
		 * @since 1.0
		 */
		public static function output_buttons( $reset = true ) {
			include_once 'views/html-settings-buttons.php';
		}

		/**
		 * Output admin fields.
		 *
		 * @since 1.0
		 */
		public static function output_fields( $value ) {

			if ( ! isset( $value['type'] ) || 'sfl_custom_fields' != $value['type'] ) {
				return;
			}

			$value['id']                = isset( $value['id'] ) ? $value['id'] : '';
			$value['css']               = isset( $value['css'] ) ? $value['css'] : '';
			$value['desc']              = isset( $value['desc'] ) ? $value['desc'] : '';
			$value['title']             = isset( $value['title'] ) ? $value['title'] : '';
			$value['class']             = isset( $value['class'] ) ? $value['class'] : '';
			$value['default']           = isset( $value['default'] ) ? $value['default'] : '';
			$value['name']              = isset( $value['name'] ) ? $value['name'] : $value['id'];
			$value['placeholder']       = isset( $value['placeholder'] ) ? $value['placeholder'] : '';
			$value['without_label']     = isset( $value['without_label'] ) ? $value['without_label'] : false;
			$value['custom_attributes'] = isset( $value['custom_attributes'] ) ? $value['custom_attributes'] : '';
			$custom_attributes          = sfl_format_custom_attributes( $value ); // Custom attribute handling.
			$allowed_html               = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'style'  => array(),
				'min'    => array(),
				'max'    => array(),
				'span'   => array(
					'class'    => array(),
					'data-tip' => array(),
				),
			);
			$field_description          = WC_Admin_Settings::get_field_description( $value ); // Description handling.
			$description                = $field_description['description'];
			$tooltip_html               = $field_description['tooltip_html'];

			// Switch based on type.
			switch ( $value['sfl_field'] ) {
				case 'subtitle':
					?>
					<tr valign="top" >
						<th scope="row" colspan="2">
							<?php echo esc_html( $value['title'] ); ?><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
							<p><?php echo wp_kses( $description, $allowed_html ); ?></p>
						</th>
					</tr>
					<?php
					break;

				case 'button':
					?>
					<tr valign="top">
						<?php if ( ! $value['without_label'] ) : ?>
							<th scope="row">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
							</th>
						<?php endif; ?>
						<td>
							<button
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['fgf_field'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo wp_kses( implode( ' ', $custom_attributes ), $allowed_html ); ?>
								><?php echo esc_html( $value['default'] ); ?> </button>
								<?php echo wp_kses( $description, $allowed_html ); ?>
						</td>
					</tr>
					<?php
					break;

				case 'ajaxmultiselect':
					$option_value = get_option( $value['id'], $value['default'] );
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
						</th>
						<td>
							<?php
							$value['options'] = $option_value;
							sfl_select2_html( $value );
							echo wp_kses( $description, $allowed_html );
							?>
						</td>
					</tr>
					<?php
					break;

				case 'datepicker':
					$value['value'] = get_option( $value['id'], $value['default'] );
					if ( ! isset( $value['datepickergroup'] ) || 'start' == $value['datepickergroup'] ) :
						?>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
							</th>
							<td>
								<fieldset>
									<?php
								endif;
								echo isset( $value['label'] ) ? esc_html( $value['label'] ) : '';
								fgf_get_datepicker_html( $value );
								echo wp_kses( $description, $allowed_html );

					if ( ! isset( $value['datepickergroup'] ) || 'end' == $value['datepickergroup'] ) :
						?>
								</fieldset>
							</td>
						</tr>
					<?php endif; ?>
					<?php
					break;
				case 'wpeditor':
					$option_value = get_option( $value['id'], $value['default'] );
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
						</th>
						<td>
							<?php
							wp_editor(
								$option_value,
								$value['id'],
								array(
									'media_buttons' => false,
									'editor_class'  => esc_attr( $value['class'] ),
								)
							);

							echo wp_kses( $description, $allowed_html );
							?>
						</td>
					</tr>
					<?php
					break;
			}
		}

		/**
		 * Save admin fields.
		 *
		 * @since 1.0
		 * @param String $value Value.
		 * @param Array  $option Field Options.
		 * @param String $raw_value Array Value.
		 */
		public static function save_fields( $value, $option, $raw_value ) {

			if ( ! isset( $option['type'] ) || 'hrr_custom_fields' != $option['type'] ) {
				return $value;
			}

			$value = null;

			// Format the value based on option type.
			switch ( $option['sfl_field'] ) {
				case 'ajaxmultiselect':
					$value = array_filter( (array) $raw_value );
					break;
				case 'wpeditor':
				case 'datepicker':
					$value = wc_clean( $raw_value );
					break;
			}

			return $value;
		}

		/**
		 * Reset admin fields.
		 *
		 * @since 1.0
		 */
		public static function reset_fields( $options ) {
			if ( ! is_array( $options ) ) {
				return false;
			}

			// Loop options and get values to reset.
			foreach ( $options as $option ) {
				if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) || ! isset( $option['default'] ) ) {
					continue;
				}

				update_option( $option['id'], $option['default'] );
			}
			return true;
		}
	}

}
