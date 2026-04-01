<?php
/**
 * Template: Instant Indexing History Rows.
 *
 * @package Smartcrwal
 */

use SmartCrawl\Instant_Indexing\IndexNow_API;

if ( ! empty( $paged_submissions ) ) : ?>
	<?php foreach ( $paged_submissions as $submission ) :
		$message     = IndexNow_API::get()->get_error_message( $submission['status'] );
		$extra_count = count( $submission['url'] ) - 1; ?>
		<tr>
			<td colspan="2"><?php echo esc_html( date( 'j, F, Y', $submission['time'] ) ); ?></td>
			<td class="sui-table-item-title" colspan="4">
				<?php echo esc_url( $submission['url'][0] ); ?>
				<?php if ( $extra_count > 0 ) : ?>
					<span class="extra-count">[+<?php echo $extra_count; ?>]</span>
				<?php endif; ?>
			</td>
			<td><?php echo esc_html( ucfirst( $submission['type'] ?? 'N/A' ) ); ?></td>
			<td><span class="sui-tooltip"
					  data-tooltip="<?php echo esc_html( $message ); ?>"><?php echo esc_html( $submission['status'] ); ?></span>
			</td>
			<td><?php echo esc_html( $submission['message'] ?? 'N/A' ); ?></td>
		</tr>
	<?php endforeach; ?>
<?php endif; ?>