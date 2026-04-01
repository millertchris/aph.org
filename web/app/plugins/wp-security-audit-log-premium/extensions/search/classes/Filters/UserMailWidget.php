<?php
/**
 * User Role Widget
 *
 * User Role widget class file.
 *
 * @package wsal
 * @subpackage search
 * @since 3.2.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WSAL_AS_Filters_UserMailWidget' ) ) {

	/**
	 * WSAL_AS_Filters_UserMailWidget.
	 *
	 * Class: IP Widget.
	 */
	class WSAL_AS_Filters_UserMailWidget extends WSAL_AS_Filters_SingleSelectWidget {

		/**
		 * Render widget field.
		 */
		protected function render_field() {
			?>
			<div class="wsal-widget-container">
				<input type="text"
					class="<?php echo esc_attr( $this->get_safe_name() ); ?>"
					id="<?php echo esc_attr( $this->id ); ?>"
					data-prefix="<?php echo esc_attr( $this->prefix ); ?>"
					placeholder="<?php esc_html_e( 'Enter users email to filter', 'wp-security-audit-log' ); ?>"
				/>
				<button id="<?php echo esc_attr( "wsal-add-$this->prefix-filter" ); ?>" class="button wsal-button wsal-filter-add-button"><?php esc_html_e( 'Add this filter', 'wp-security-audit-log' ); ?></button>
			</div>
			<?php
		}

		/**
		 * Method: Render JS in footer regarding this widget.
		 */
		public function static_footer() {
			?>
			<script type="text/javascript">
				jQuery( '<?php echo esc_attr( "#wsal-add-$this->prefix-filter" ); ?>' ).click( function( event ) {
					event.preventDefault();
					var username_input = jQuery( 'input.<?php echo esc_attr( $this->get_safe_name() ); ?>' );
					var username = username_input.val();
					if ( username.length == 0 ) return;
					var username_filter_value = username_input.attr( 'data-prefix' ) + ':' + username;
					window.WsalAs.AddFilter( username_filter_value );
				} );
				jQuery( document ).ready( function( $ ) {
					// Username validation.
					var username_error = jQuery( '<span />' );
					username_error.addClass( 'wsal-input-error' );
					username_error.text( '* Invalid Email' );
					var username_label = jQuery( 'label[for="<?php echo esc_attr( $this->id ); ?>"]' );
					username_label.append( username_error );

					$( 'input.<?php echo esc_attr( $this->get_safe_name() ); ?>' ).on( 'change keyup paste', function() {
						var username_value = $( this ).val();
						var username_add_btn = $( '<?php echo esc_attr( "#wsal-add-$this->prefix-filter" ); ?>' );
						username_error.hide();
						username_add_btn.removeAttr( 'disabled' );

						var username_pattern = /^[\w\.-]+@[a-zA-Z\d\.-]+\.[a-zA-Z]{2,}$/
;
						if ( username_value.length && ! username_pattern.test( username_value ) ) {
							username_error.show();
							username_add_btn.attr( 'disabled', 'disabled' );
						}
					} );
				} );
			</script>
			<?php
		}
	}
}
