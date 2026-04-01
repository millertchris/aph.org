<?php
/**
 * Template file for video shortcode.
 *
 * @var string $group      Video group.
 * @var array  $videos     Videos list.
 * @var array  $playlist   Video playlist data.
 * @var bool   $show_title Show title.
 *
 * @author  Joel James <joel@incsub.com>
 * @link    https://wpmudev.com
 * @since   1.7.0
 * @since   1.8.0 Removed getting embed from helper.
 *
 * @package WPMUDEV_Videos\Templates
 */

?>

<div class="wpmudev-videos-shortcode">
	<?php if ( $playlist && ! empty( $videos ) && isset( $playlist['id'] ) ) : // If playlist set. ?>
		<div class="wpmudev_video_group">
			<?php if ( $show_title ) : // Video wrapper. ?>
				<h3 class="wpmudev_video_group_title"><?php echo esc_attr( $playlist['title'] ); ?></h3>
			<?php endif; ?>
			<?php foreach ( $videos as $video ) : // Loop through each items. ?>
				<div class="wpmudev_video" style="max-width:<?php echo esc_attr( $width ); ?>px; max-height:<?php echo esc_attr( $height ); ?>px;">
					<?php echo $video['embed']; // phpcs:ignore ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php elseif ( ! empty( $videos ) ) : ?>
		<?php foreach ( $videos as $video ) : // Loop through each items. ?>
			<div class="wpmudev_video" style="max-width:<?php echo esc_attr( $width ); ?>px; max-height:<?php echo esc_attr( $height ); ?>px;">
				<?php echo $video['embed']; // phpcs:ignore ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>