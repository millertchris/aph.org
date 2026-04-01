<?php
/**
 * Template: Instant Indexing Submit URL.
 *
 * @package Smartcrwal
 */
?>
<div id="wds-indexnow-submit-url" class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<label for="extra-sitemap-urls" class="sui-settings-label">
			<?php esc_html_e( 'Submit URLs', 'wds' ); ?>
		</label>
		<p class="sui-description">
			<?php esc_html_e( 'Enter the URLs you want to manually submit for indexing. Add one URL per line.', 'wds' ); ?>
		</p>
	</div>
	<div class="sui-box-settings-col-2">
		<div class="sui-form-field">
			<label for="wds-indexnow-urls" id="label-wds-indexnow-urls" class="sui-label">
				<?php esc_html_e( 'Enter URLs (One per line, up to 100 URLs)', 'wds' ); ?>
			</label>
			<textarea id="wds-indexnow-urls" class="sui-form-control"
					  name="<?php echo esc_attr( $_view['option_name'] ); ?>[submit_urls]"
					  placeholder="https://domain.com/about&#10;https://domain.com/contact" rows="5"></textarea>
		</div>
		<p>
			<button type="button" class="sui-button sui-button-blue wds-indexnow-submit-urls" name="submit" aria-live="polite">
				<span class="sui-button-text-default"><?php echo esc_attr__( 'Submit URLs', 'wds' ); ?></span>
				<span class="sui-button-text-onload">
					<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
					<?php echo esc_attr__( 'Submitting...', 'wds' ); ?>
				</span>
			</button>
		</p>
	</div>
</div>