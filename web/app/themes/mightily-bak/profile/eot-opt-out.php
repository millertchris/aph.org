<?php if ( in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles) ): ?>
	<li class="item">
		<a href="javascript:;" class="plain-link" data-micromodal-trigger="opt-out">Opt-out</a>
		<div id="opt-out" class="modal" aria-hidden="true">
			<div class="bg" tabindex="-1" data-micromodal-close>
				<div class="dialog" role="dialog" aria-modal="true" aria-labelledby="opt-out-title" >
					<header>
						<p id="opt-out-title" class="h4">Opt-out of APH EOT Shop</p>
						<button class="close" aria-label="Close modal" data-micromodal-close><i class="fas fa-times" aria-hidden="true"></i></button>
					</header>
					<div id="opt-out-content">
						<form action="/opt-out" method="get">
							<label for="opt-out-email">Confirm Opt-out email address. Please type your email address and submit the form to opt-out. Your account priveleges will be changed and you will no longer be able to administer other shoppers and orders.</label>
							<input id="opt-out-email" type="text" placeholder="<?php echo $current_user->user_email; ?>" name="opt_out_email_address"/>
							<input type="submit" value="Opt Out"/>
						</form>
					</div>

				</div>
			</div>
		</div>
	</li>
<?php endif; ?>
