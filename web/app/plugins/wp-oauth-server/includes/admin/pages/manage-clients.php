<?php function wo_admin_manage_clients_page() {
	 wp_enqueue_style( 'wo_admin' );
	wp_enqueue_script( 'wo_admin' );
	?>
	<div class="wrap">

		<h2><?php _e( 'Clients', 'wp-oauth' ); ?>
			<a class="add-new-h2 "
			   href="<?php echo admin_url( 'admin.php?page=wo_add_client' ); ?>"
			   title="Batch"><?php _e( 'Add New Client', 'wp-oauth' ); ?></a>
		</h2>

		<div class="section group">
			<div class="col span_4_of_6">
				<?php
				$CodeTableList = new WO_Table();
				$CodeTableList->prepare_items();
				$CodeTableList->display();
				?>
			</div>

			<div class="col span_2_of_6 sidebar">
				<div class="module">
					<h3>Plugin Documentation</h3>
					<div class="inner">
						<p>
							Our robust documentation will help you through the process is need be. You can view the
							documentation by visiting <a href="https://wp-oauth.com/documentation/?utm_source=plugin-admin&utm_medium=settings-page" target="_blank">https://wp-oauth.com/documentation/</a>.
						</p>

						<strong>Build <?php echo _WO()->version; ?></strong>
					</div>
				</div>
			</div>
		</div>

	</div>
	<?php
}
