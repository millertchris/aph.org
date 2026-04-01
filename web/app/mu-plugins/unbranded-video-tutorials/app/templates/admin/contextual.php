<?php
/**
 * Contextual help section template.
 *
 * @var array $videos Videos list.
 *
 * @author  Joel James <joel@incsub.com>
 * @since   1.8.0
 *
 * @package WPMUDEV_Videos\Templates
 */

?>

<div class="metabox-holder">
	<?php foreach ( $videos as $video ) : ?>
		<div id="wpmudev_vid_<?php esc_attr( $video['id'] ); ?>" class="postbox" style="width: 520px;float: left;margin-right: 10px;">
			<h3 class="hndle"><span><?php echo esc_attr( $video['title'] ); ?></span></h3>
			<div class="inside">
				<?php echo $video['content']; // phpcs:ignore ?>
			</div>
		</div>
	<?php endforeach; ?>

	<div class="clear"></div>
</div>