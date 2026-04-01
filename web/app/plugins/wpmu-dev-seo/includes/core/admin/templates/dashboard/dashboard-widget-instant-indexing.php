<?php
/**
 * Template: Dashboard Instant Indexing Widget.
 *
 * @package SmartCrawl
 */

namespace SmartCrawl;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Services\Service;

if ( ! Admin_Settings::is_tab_allowed( Settings::TAB_INSTANT_INDEXING ) ) {
	return;
}

$service       = Service::get( Service::SERVICE_SITE );
$page_url      = Admin_Settings::admin_url( Settings::TAB_INSTANT_INDEXING );
$option_value  = Settings::get_specific_options( 'wds_settings_options' );
$enabled       = $option_value[ Settings::COMP_INSTANT_INDEXING ] ?? 0;
$is_member     = $service->is_member();
$hide_disables = \smartcrawl_get_array_value( $option_value, 'hide_disables', true );

if ( ! $enabled && $hide_disables ) {
	return '';
}
?>
<section id="<?php echo esc_attr( \SmartCrawl\Admin\Settings\Dashboard::BOX_INSTANT_INDEXING ); ?>"
		 class="sui-box wds-dashboard-widget">
	<div class="sui-box-header">
		<h2 class="sui-box-title">
			<span class="wds-rocket-icon" aria-hidden="true"></span>
			<?php esc_html_e( 'Instant Indexing', 'wds' ); ?>
		</h2>
		<?php if ( ! $is_member ) : ?>
			<span class="sui-tag sui-tag-pro sui-tooltip"
				  data-tooltip="<?php esc_html_e( 'Upgrade to SmartCrawl Pro', 'wds' ); ?>">
				<?php esc_html_e( 'Pro', 'wds' ); ?>
			</span>
		<?php elseif ( $enabled ) : ?>
			<span class="sui-tag wds-right sui-tag-sm sui-tag-blue"><?php esc_html_e( 'Active', 'wds' ); ?></span>
		<?php endif; ?>
	</div>

	<div class="sui-box-body">
		<p>
			<?php
			if ( ! $is_member ) {
				esc_html_e( 'Instantly notify search engines like Bing and Yandex whenever your site’s content changes using our IndexNow API integration.', 'wds' );
			} elseif ( ! $enabled ) {
				esc_html_e( 'Automatically notify Bing and Yandex whenever you add, update, or remove pages, helping your content appear in search results faster.', 'wds' );
			} else {
				esc_html_e( 'Your site is now set to instantly notify Bing and Yandex via the IndexNow API. Fine-tune when and how URLs are submitted by adjusting your configuration.', 'wds' );
			}
			?>
		</p>
	</div>

	<div class="sui-box-footer">
		<?php if ( ! $is_member ) : ?>
			<a target="_blank" class="sui-button sui-button-purple"
			   href="https://wpmudev.com/project/smartcrawl-wordpress-seo/?utm_source=smartcrawl&utm_medium=plugin&utm_campaign=smartcrawl_instant-indexing_dash_upsell_notice">
				<?php esc_html_e( 'Upgrade to Pro', 'wds' ); ?>
			</a>
		<?php elseif ( ! $enabled ) : ?>
			<button type="button" data-option-id="wds_settings_options"
					data-flag="<?php echo esc_attr( Settings::COMP_INSTANT_INDEXING ); ?>" data-value="1"
					class="wds-activate-component wds-disabled-during-request sui-button sui-button-blue">
				<span class="sui-loading-text"><?php esc_html_e( 'Activate', 'wds' ); ?></span>
				<span class="sui-icon-loader sui-loading" aria-hidden="true"></span>
			</button>
		<?php else : ?>
			<a href="<?php echo esc_url( $page_url ); ?>" class="sui-button sui-button-ghost">
				<span class="sui-icon-wrench-tool" aria-hidden="true"></span>
				<?php esc_html_e( 'Configure', 'wds' ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>