<?php
/**
 * Template: Instant Indexing IndexNow API.
 *
 * @package Smartcrwal
 */

$options_key         = ! empty( $options['indexnow_api_key'] ) ? $options['indexnow_api_key'] : '';
$key_location        = \SmartCrawl\Admin\Settings\Instant_Indexing::get()->get_key_location( $options_key );
$indexnow_post_types = ! empty( $options['indexnow_post_types'] ) ? $options['indexnow_post_types'] : array();
?>
<div id="wds-indenow-api-settings" class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<label class="sui-settings-label">
			<?php esc_html_e( 'IndexNow API Settings', 'wds' ); ?>
		</label>
		<p class="sui-description">
			<?php
			printf(
			/* translators: 1, 2: opening/closing anchor tags with learn more link */
				esc_html__( 'The IndexNow API key is automatically generated to verify site ownership. If you suspect the key has been compromised, you can generate a new one. %1$sLearn more%2$s', 'wds' ),
				'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/smartcrawl/#instant-indexing" target="_blank">',
				'</a>'
			);
			?>
		</p>
	</div>
	<div class="sui-box-settings-col-2">
		<div class="sui-form-field">
			<label class="sui-label">
				<?php esc_html_e( 'API Key', 'wds' ); ?></label>
			<input
					type="text"
					placeholder="<?php echo esc_attr__( '1ce79794c0b74cd39b7632af37', 'wds' ); ?>"
					name="<?php echo esc_attr( $_view['option_name'] ); ?>[indexnow_api_key]"
					readonly="readonly"
					class="sui-form-control smartcrawl-indexnow-key"
					value="<?php echo esc_html( $options_key ); ?>"
			/>
			<p>
				<button type="button" class="sui-button sui-button-ghost wds-indexnow-generate-key" name="submit">
					<?php echo esc_attr__( 'Generate new Key', 'wds' ); ?>
				</button>
			</p>
		</div>
		<div class="sui-form-field">
			<label class="sui-settings-label">
				<?php esc_html_e( 'API Key Location', 'wds' ); ?></label>
			<span class="sui-description"><?php esc_html_e( 'Click Verify Key to check if your API key is accessible to search engines. This will open the key file in your browser and display the API key.', 'wds' ); ?></span>
			<input
					type="text"
					placeholder="<?php echo esc_attr__( 'https://domain.com/1ce79794c0b74cd39b7632af37.txt', 'wds' ); ?>"
					name="<?php echo esc_attr( $_view['option_name'] ); ?>[indexnow_api_key_location]"
					class="sui-form-control smartcrawl-indexnow-key-location"
					value="<?php echo esc_url( $key_location ); ?>"
					readonly="readonly"
			/>
			<p>
				<a href="<?php echo esc_url( $key_location ); ?>" target="_blank"
				   class="sui-button sui-button-ghost"><?php echo esc_attr__( 'Verify Key', 'wds' ); ?></a>
			</p>
		</div>
	</div>
</div>
<div id="wds-indexnow-post-types" class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<label class="sui-settings-label">
			<?php esc_html_e( 'Automatically Index Post Types', 'wds' ); ?>
		</label>
		<p class="sui-description">
			<?php esc_html_e( 'Select the post types you want to automatically submit to IndexNow for indexing.', 'wds' ); ?>
		</p>
	</div>
	<div class="sui-box-settings-col-2">
		<div class="sui-box-settings-content">
			<label class="sui-settings-label">
				<?php esc_html_e( 'Post types', 'wds' ); ?>
			</label>
			<div class="sui-border-frame">
				<?php
				foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
					$is_checked = ( ! empty( $indexnow_post_types ) && in_array( $post_type, $indexnow_post_types, true ) );
					?>
					<label for="auto-<?php echo esc_html( $post_type ); ?>"
						   class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
						<input type="checkbox" id="auto-<?php echo esc_html( $post_type ); ?>"
							   name="<?php echo esc_attr( $_view['option_name'] ); ?>[indexnow_post_types][]"
							   value="<?php echo esc_attr( $post_type ); ?>"
								<?php checked( $is_checked ); ?>
							   aria-labelledby="label-auto-<?php echo esc_html( $post_type ); ?>"/>
						<span aria-hidden="true"></span>
						<span id="label-auto-<?php echo esc_html( $post_type ); ?>">
							<?php echo esc_html( ucfirst( $post_type ) ); ?>
						</span>
					</label>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
