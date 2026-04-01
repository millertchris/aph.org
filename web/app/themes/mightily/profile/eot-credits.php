<div class="col credits">
	<div class="credit">
		<?php APH\Templates::display_fq_accounts($current_user); ?>
		<div class="fq-balance" style="display:none;">		
			<?php include(locate_template('profile/eot-fq-balances.php')); ?> 
		</div>
		<div class="buttons">
			<?php include(locate_template('profile/eot-shopper-invite.php')); ?>
		</div>
	</div>
</div>