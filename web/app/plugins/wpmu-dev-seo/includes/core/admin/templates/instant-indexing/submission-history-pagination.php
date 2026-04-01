<?php
/**
 * Template: Instant Indexing History Pagination.
 *
 * @package Smartcrwal
 */
?>
<ul class="sui-pagination">
	<?php for ( $i = 1; $i <= $total_pages; $i ++ ) : ?>
		<li class="<?php echo ( $i === $page ) ? 'sui-active' : ''; ?>">
			<a href="#" class="wds-submission-pagination"
			   data-page="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></a>
		</li>
	<?php endfor; ?>
</ul>