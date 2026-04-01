<?php
/**
 * Dashboard Welcome Modal.
 *
 * @package SmartCrawl
 */

use SmartCrawl\Services\Service;
use SmartCrawl\Settings;

$modal_id = 'wds-welcome-modal';

$options      = Settings::get_specific_options( 'wds_settings_options' );
$service      = Service::get( Service::SERVICE_SITE );
$button_color = $service->is_member() ? 'blue' : 'purple';
?>

<div class="sui-modal sui-modal-md">
	<div
		role="dialog"
		id="<?php echo esc_attr( $modal_id ); ?>"
		class="sui-modal-content <?php echo esc_attr( $modal_id ); ?>-dialog"
		aria-modal="true"
		aria-labelledby="<?php echo esc_attr( $modal_id ); ?>-dialog-title"
		aria-describedby="<?php echo esc_attr( $modal_id ); ?>-dialog-description">

		<div class="sui-box" role="document">
			<div class="sui-box-header sui-flatten sui-content-center sui-spacing-top--40">
				<div class="sui-box-banner" role="banner" aria-hidden="true">
					<?php /* translators: %s: plugin title */ ?>
					<img src="<?php echo esc_attr( SMARTCRAWL_PLUGIN_URL ); ?>assets/images/upgrade-welcome-header.svg" alt="<?php printf( esc_html__( '%s works with other SEO Plugins.', 'wds' ), esc_attr( \smartcrawl_get_plugin_title() ) ); ?>"/>
				</div>
				<button
					class="sui-button-icon sui-button-float--right" data-modal-close
					id="<?php echo esc_attr( $modal_id ); ?>-close-button"
					type="button"
				>
					<span class="sui-icon-close sui-md" aria-hidden="true"></span>
					<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this dialog window', 'wds' ); ?></span>
				</button>
				<h3 class="sui-box-title sui-lg" id="<?php echo esc_attr( $modal_id ); ?>-dialog-title">
					<?php
					printf(
						/* translators: 1,2: strong tag, 3: plugin title */
						esc_html__( '%1$sNew! Instant Indexing%2$s', 'wds' ),
						'<strong>',
						'</strong>',
						esc_html( \smartcrawl_get_plugin_title() )
					);
					?>
					<?php if ( ! $service->is_member() ) : ?>
						<span class="sui-tag sui-tag-pro"><?php esc_html_e( 'Pro', 'wds' ); ?></span>
					<?php endif; ?>
				</h3>

				<div class="sui-box-body">
					<p class="sui-description" id="<?php echo esc_attr( $modal_id ); ?>-dialog-description">
						<?php
						printf(
						/* translators: 1,2,3,4,5,6: strong tag */
							esc_html__(
								'Hi there! We\'re excited to introduce Instant Indexing in SmartCrawl. Now, you can instantly notify search engines like %1$sBing%2$s and %3$sYandex%4$s using %5$sIndexNow%6$s whenever pages on your site are added, updated, or removed.',
								'wds'
							),
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>',
							'<strong>',
							'</strong>',
						);
						?>
					</p>
					<p class="sui-description" id="<?php echo esc_attr( $modal_id ); ?>-dialog-description">
						<?php
						printf(
						/* translators: 1,2: anchor tag */
							esc_html__(
								'Take control of your SEO with real-time visibility and faster search engine updates. %1$sLearn more%2$s',
								'wds'
							),
							'<a href="https://wpmudev.com/docs/wpmu-dev-plugins/smartcrawl/#instant-indexing" target="_blank" rel="noopener noreferrer">',
							'</a>',
						);
						?>
					</p>

					<button
						id="<?php echo esc_attr( $modal_id ); ?>-get-started"
						type="button"
						class="sui-button sui-button-<?php echo $button_color; ?> wds-disabled-during-request">
						<span class="sui-loading-text">
							<?php
								if ( $service->is_member() ) {
									esc_html_e( 'Activate Instant Indexing', 'wds' );
								} else {
									esc_html_e( 'Upgrade to pro to activate', 'wds' );
								}
							?>
						</span>
						<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>