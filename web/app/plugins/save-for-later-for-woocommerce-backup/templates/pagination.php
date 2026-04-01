<?php
/**
 * This template displays Save For Later table pagination
 *
 * This template can be overridden by copying it to yourtheme/save-for-later-for-woocommerce/pagination.php
 *
 * To maintain compatibility, Save For Later for WooCommerce will update the template files and you have to copy the updated files to your theme
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<nav class="pagination pagination-centered woocommerce-pagination">
	<ul class="page-numbers">
		<li><span class="page-numbers sfl_pagination sfl_first_pagination" data-page="1"><<</span>
		<li><span class="page-numbers sfl_pagination sfl_next_pagination" data-page="2"><</span></li>
		<?php
		for ( $i = 1; $i <= $page_count; $i++ ) {
			$display = false;
			$classes = array( 'sfl_pagination' );
			if ( $current_page <= 5 && $i <= 5 ) {
				$page_no = $i;
				$display = true;
			} else if ( $current_page > 5 ) {

				$overall_count = $current_page - 5 + $i;

				if ( $overall_count <= $current_page ) {
					$page_no = $overall_count;
					$display = true;
				}
			}

			if ( $current_page == $i ) {
				$classes[] = 'current';
			}

			if ( $display ) {
				?>
				<li><span data-page="<?php echo esc_attr( $page_no ); ?>" class="page-numbers <?php echo esc_attr( implode( ' ', $classes ) ); ?>"><?php echo esc_html( $page_no ); ?></span></li>
				<?php
			}
		}
		?>
		<li><span class="page-numbers sfl_pagination sfl_prev_pagination" data-page="<?php echo esc_attr( $next_page_count ); ?>">></span></li>
		<li><span class="page-numbers sfl_pagination sfl_last_pagination" data-page="<?php echo esc_attr( $page_count ); ?>">>></span></li>
	</ul>
</nav>
<?php
