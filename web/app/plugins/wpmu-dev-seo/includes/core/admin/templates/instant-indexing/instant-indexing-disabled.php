<?php
/**
 * Template: Instant Indexing Free.
 *
 * @package Smartcrwal
 */

$is_member = ! empty( $is_member ) ? $is_member : false;
?>
<form method='post'>
	<div class="sui-box">
		<div class="sui-box-body">
			<?php
			$this->render_view(
				'disabled-component-inner',
				array(
					'content'             => esc_html__( 'Notify search engines like Bing and Yandex via the IndexNow API whenever pages are added and updated. You can also submit URLs manually.', 'wds' ),
					'button_text'         => esc_html__( 'Activate', 'wds' ),
					'button_class'        => 'wds-activate-instant-indexing-component',
					'component'           => 'instant_indexing',
					'upgrade_tag'         => 'smartcrawl_instant-indexing_upgrade_button',
					'premium_feature'     => true,
					'image'               => $is_member ? 'module-activate.png' : 'plugins-smartcrawl-icon.png',
					'upgrade_button_text' => esc_html__( 'Upgrade to Unlock Instant Indexing', 'wds' ),
				)
			);
			?>
		</div>
	</div>
</form>