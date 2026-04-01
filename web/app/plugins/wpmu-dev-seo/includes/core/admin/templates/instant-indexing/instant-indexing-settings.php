<?php
/**
 * Template: Instant Indexing Settings.
 *
 * @package Smartcrwal
 */

$options = ! empty( $_view['options'] ) ? $_view['options'] : array();
$apis    = array(
	'indexnow-api' => esc_html__( 'IndexNow API', 'wds' ),
	'google-api'   => esc_html__( 'Google API', 'wds' ),
);
?>
<p>
	<?php esc_html_e( 'Use the options below to configure the API keys and settings for Instant Indexing.', 'wds' ); ?>
</p>

<div class="sui-tabs">
	<div role="tablist" class="sui-tabs-menu">
		<?php foreach ( $apis as $api_id => $api_label ) : ?>
			<button
					type="button"
					role="tab"
					id="<?php echo esc_attr( $api_id ); ?>"
					class="sui-tab-item <?php echo $api_id === 'indexnow-api' ? 'active' : ''; ?>"
					aria-controls="<?php echo esc_attr( $api_id . '-content' ); ?>"
					aria-selected="<?php echo $api_id === 'indexnow-api' ? 'true' : 'false'; ?>"
					tabindex="<?php echo $api_id === 'indexnow-api' ? '0' : '-1'; ?>">
				<?php echo esc_html( $api_label ); ?>
				<?php if ( 'google-api' === $api_id ) { ?>
					<span class="sui-tag sui-tag-blue coming-soon-tag coming-soon-tag--sm">
						<?php esc_html_e( 'coming soon', 'wds' ); ?>
					</span>
				<?php } ?>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="sui-tabs-content">
		<?php foreach ( $apis as $api_id => $api_label ) : ?>
			<div
					role="tabpanel"
					tabindex="0"
					id="<?php echo esc_attr( $api_id . '-content' ); ?>"
					class="sui-tab-content <?php echo $api_id === 'indexnow-api' ? 'active' : ''; ?>"
					aria-labelledby="<?php echo esc_attr( $api_id ); ?>"
				<?php echo $api_id !== 'indexnow-api' ? 'hidden' : ''; ?>>
				<p>
					<?php
					$this->render_view(
						"instant-indexing/instant-indexing-{$api_id}",
						array( 'options' => $options )
					);
					?>
				</p>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<div id="wds-indexnow-deactivate" class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Deactivate', 'wds' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'No longer need Instant indexing? This will deactivate this feature.', 'wds' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<button type="button" class="sui-button-ghost sui-button wds-deactivate-instant-indexing-component">
			<span>
				<span class="sui-icon-power-on-off" aria-hidden="true"></span>
				<?php esc_html_e( 'Deactivate', 'wds' ); ?>
			</span>
		</button>
	</div>
</div>