<?php
/**
 * Template: Sitemap JS for Dashboard Widget.
 *
 * @package Smartcrwal
 */

$updating = empty( $updating ) ? '' : $updating;
$updated  = empty( $updated ) ? '' : $updated;
?>
<script type="text/javascript">
	;(function ($) {
		$(function () {
			$("#wds_update_now").click(function () {
				var me = $(this);
				me.html("<?php echo esc_js( $updating ); ?>");

				$.post(ajaxurl, {"action": "wds_update_sitemap"}, function () {
					me.html("<?php echo esc_js( $updated ); ?>");
					window.location.reload();
				});

				return false;
			});
		});
	})(jQuery);
</script>