<div class="col credits">
	<div class="credit">
		<?php APH\Templates::display_fq_accounts($current_user); ?>
		<div class="buttons">
		<?php if(!APH\Roles::userHas(APH\Roles::NET, $current_user))
			include(locate_template('profile/eot-shopper-invite.php'));
		?>
		</div>
	</div>
</div>