<?php
/**
 * Template: Sitemap Dashboard Widget.
 *
 * @package Smartcrwal
 */

$last_update_date      = empty( $last_update_date ) ? '' : $last_update_date;
$last_update_time      = empty( $last_update_time ) ? '' : $last_update_time;
$last_update_timestamp = empty( $last_update_timestamp ) ? '' : $last_update_timestamp;
$engines               = empty( $engines ) ? array() : $engines;
$sitemap_stats         = empty( $sitemap_stats ) ? array() : $sitemap_stats;
?>

<div class="wpmud wds-sitemaps-widget">
	<div class="wds-sitemaps-widget-left">
		<div>
			<?php
			printf(
				/* translators: %s: Number of items */
				esc_html__( 'Your sitemap contains %s.', 'wds' ),
				sprintf(
					'<a href="%1$s" target="_blank"><b>%2$d</b> %3$s</a>',
					esc_attr( \smartcrawl_get_sitemap_url() ),
					(int) \smartcrawl_get_array_value( $sitemap_stats, 'items' ),
					esc_html__( 'items', 'wds' )
				)
			);
			?>
		</div>
		<p>
			<?php echo esc_html( $last_update_timestamp ); ?>
		</p>
		<p>
			<a href='#update_sitemap' id='wds_update_now'><?php echo esc_html__( 'Update sitemap now', 'wds' ); ?></a>
		</p>
	</div>
</div>