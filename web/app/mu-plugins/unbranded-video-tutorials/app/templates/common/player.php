<?php
/**
 * Custom video player.
 *
 * @var string $src           iframe url.
 * @var string $host          Video host.
 * @var string $title         Video title.
 * @var string $thumbnail_url Custom thumbnail.
 *
 * @since   1.7
 * @since   1.8 Rewrote the player.
 *
 * @package WPMUDEV_Videos\Templates
 */

?>

<div
		class="lazyframe"
	<?php if ( in_array( $host, array( 'youtube', 'vimeo' ), true ) ) : // Only youtube and vimeo is supported. ?>
		data-vendor="<?php echo esc_attr( $host ); ?>"
	<?php else : ?>
		data-host="<?php echo esc_attr( $host ); ?>"
	<?php endif; ?>
		data-title="<?php echo esc_attr( $title ); ?>"
		data-thumbnail="<?php echo esc_url( $thumbnail_url ); ?>"
		data-src="<?php echo esc_url( $src ); ?>"
		data-ratio="16:9"
		data-initinview="false"
>
</div>