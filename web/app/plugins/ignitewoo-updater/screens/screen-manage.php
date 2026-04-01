<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div id="col-container" class="about-wrap">
	<div class="col-wrap">
		<form id="activate-products" method="post" action="" class="validate">
			<input type="hidden" name="action" value="activate-products" />
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->page_slug ); ?>" />
			<?php

			require_once( $this->classes_path . 'class-ignition-updater-licenses-table.php' );
			$this->list_table = new Ignition_Updater_Licenses_Table();
			$this->list_table->data = $this->get_detected_products();
			
			$this->list_table->prepare_items();
			$this->list_table->display();
			?>
			<p class="submit ignition-helper-submit-wrapper">
			<?php
			submit_button( __( 'Activate Licenses', 'ignition-updater' ), 'button-primary', null, false );
			echo '&nbsp;<a href="' . esc_url( $this->my_subscriptions_url ) . '" title="' . __( 'Manage my Licenses', 'ignition-updater' ) . '" class="button manage-subscriptions-link">' . __( 'Manage my Licenses', 'ignition-updater' ) . '</a>' . "\n";
			?>
			</p><!--/.submit-->
			<?php wp_nonce_field( 'wt-helper-activate-license', 'wt-helper-nonce' ); ?>
		</form>
	</div><!--/.col-wrap-->
</div><!--/#col-container-->
