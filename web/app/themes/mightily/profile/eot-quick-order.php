<?php //if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles) || in_array('teacher', $current_user->roles)): ?>

	<a href="javascript:;" class="plain-link" data-micromodal-trigger="quick-order">Quick Order</a>

	<div id="quick-order" class="modal" aria-hidden="true">
		<div class="bg" tabindex="-1" data-micromodal-close>
			<div class="modal-wrapper">
				<div class="dialog" role="dialog" aria-modal="true" aria-labelledby="quick-order-title" >

		<header>
			<p id="quick-order-title" class="h4 mobile-hide">Quick Order</p>
			<button class="close" aria-label="Close modal" data-micromodal-close></button>
			<div class="quick-order-message mobile-show">
				<h2>Quick Order is currently only available on desktop, mobile coming soon!</h2>
			</div>
		</header>

		<div id="quick-order-content" class="mobile-hide">
			<?php if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles)): ?>
				<div class="fq-accounts-wrapper">
					<?php display_fq_accounts($current_user); ?>
				</div>
			<?php endif; ?>
                <?php echo do_shortcode('[quick_order_form id="2366"]');?>
		</div>

	</div>
			</div>
		</div>
	</div>
<?php //endif; ?>
