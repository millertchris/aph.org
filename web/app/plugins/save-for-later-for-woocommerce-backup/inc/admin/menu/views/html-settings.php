<?php
/**
 * Admin HTML Settings
 *
 * @package Save For Later/Admin/Views
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class = "wrap <?php echo esc_attr( self::$plugin_slug ); ?>_wrapper_cover woocommerce">
	<h2></h2>
	<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_header">
		<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_title"><h2><?php esc_html_e( 'Save For Later', 'save-for-later-for-woocommerce' ); ?></h2></div>
		<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_logo"></div>
	</div>
	<form method = "post" action = "" enctype = "multipart/form-data">
		<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_wrapper">
			<ul class = "nav-tab-wrapper <?php echo esc_attr( self::$plugin_slug ); ?>_tab_ul">
					<?php
					foreach ( $tabs as $name => $label ) {
						?>
						<li class="<?php echo esc_attr( self::$plugin_slug ); ?>_tab_li <?php echo esc_html( $name ); ?>_li">
							<a href="
							<?php
							echo esc_url(
								sfl_get_settings_page_url(
									array(
										'page' => 'sfl_settings',
										'tab'  => $name,
									)
								)
							);
							?>
										" class="nav-tab <?php echo esc_html( self::$plugin_slug ); ?>_tab_a <?php echo esc_attr( $name ) . '_a ' . ( $current_tab == $name ? 'nav-tab-active' : '' ); ?>">
								<span><?php echo esc_html( $label ); ?></span>
							</a>
						</li>
					<?php } ?>
			</ul>
			<div class="<?php echo esc_attr( self::$plugin_slug ); ?>_tab_content">
				<?php

				/**
				 * Action hook fired Settings Start.
				 *
				 * @since 1.0
				 */
				do_action( sanitize_key( self::$plugin_slug . '_sections_' . $current_tab ) );
				?>
				<div class="<?php echo esc_attr( self::$plugin_slug ); ?>_tab_inner_content">
					<?php
					/* Display Error or Warning Messages */
					self::show_messages();

					/**
					 * Action hook fired Settings Tab content.
					 *
					 * @since 1.0
					 */
					do_action( sanitize_key( self::$plugin_slug . '_settings_' . $current_tab ) );

					/**
					 * Action hook fired to Display Settings Button.
					 *
					 * @since 1.0
					 */
					do_action( sanitize_key( self::$plugin_slug . '_settings_buttons_' . $current_tab ) );

					/**
					 * Action hook fired After Settings Button.
					 *
					 * @since 1.0
					 */
					do_action( sanitize_key( self::$plugin_slug . '_after_setting_buttons_' . $current_tab ) );
					?>
				</div>
			</div>
		</div>
	</form>
</div>
<?php
/**
 * Action hook fired End of Settings.
 *
 * @since 1.0
 */
do_action( esc_attr( self::$plugin_slug ) . '_settings_end' );
