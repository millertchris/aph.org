<div class="col credits">
	<div class="credit">
		<?php // APH\Templates::display_fq_accounts($current_user); ?>

		<?php if (APH\Roles::userHas(APH\Roles::NET, $current_user)) : ?>
			<div class="fq-balance">
				<?php include(locate_template('profile/cst-net-balances.php')); ?> 
			</div>
		<?php else : ?>
			<div class="fq-balance">		
				<?php include(locate_template('profile/eot-fq-balances.php')); ?> 
			</div>
		<?php endif; ?>
		<div class="buttons">
			<?php include(locate_template('profile/eot-shopper-invite.php')); ?>
		</div>
	</div>
</div>