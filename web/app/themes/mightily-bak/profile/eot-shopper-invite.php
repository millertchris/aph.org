<?php if(in_array('eot', $current_user->roles)) : ?>

		<a href="javascript:;" class="btn" data-micromodal-trigger="invite-shopper">Invite Shopper</a>
		<?php
			$modal_state = '';
			if (isset($_GET["shopper_email_address"])) {
				$shopper_email = $_GET["shopper_email_address"];
				if ($shopper_email == NULL) {
					$modal_state = 'error';
				} elseif ($shopper_email != NULL) {
					$modal_state = 'success';
				}
			}
		?>
		<div id="invite-shopper" class="modal <?php echo $modal_state; ?>" aria-hidden="true">
			<div class="bg" tabindex="-1" data-micromodal-close>
				<div class="dialog" role="dialog" aria-modal="true" aria-labelledby="invite-shopper-title" >
					<header>
						<p id="invite-shopper-title" class="h4">Invite a Shopper</p>
						<button class="close" aria-label="Close modal" data-micromodal-close></button>
					</header>

					<div class="validation">
						<div class="success">
							<p>Your shopper has been invited! <br /><a href="/profile?shopper_invite_open=true">Invite another shopper</a>.</p>
						</div>
						<div class="error">
							<p>Check the email address and try again.</p>
						</div>
					</div>

					<div id="invite-shopper-content">
						<!-- <form action="/generate-invite" method="get">
							<label for="invite-shopper-email">Shopper Email Address</label>
							<input id="invite-shopper-email" type="text" placeholder="john.doe@email.com" name="shopper_email_address"/>
							<label for="invite-shopper-group">Choose a Federal Quota Account</label>
							<select id="invite-shopper-group" name="shopper_group_id">
							<?php foreach(wp_get_terms_for_user($current_user, 'user-group') as $group) : ?>
								<option value="<?php echo $group->term_id; ?>"><?php echo $group->name; ?></option>
							<?php endforeach; ?>
							</select>
							<input type="submit" value="Send Invite"/>
						</form> -->
						<form id="generate-invite" action="/">
							<label for="invite-shopper-email">Shopper Email Address</label>
							<input id="invite-shopper-email" type="text" placeholder="john.doe@email.com" name="shopper_email_address" required/>
							<label for="invite-shopper-group">Choose a Federal Quota Account</label>
							<!-- <select id="invite-shopper-group" name="shopper_group_id">
							<?php foreach(wp_get_terms_for_user($current_user, 'user-group') as $group) : ?>
								<option value="<?php echo $group->term_id; ?>"><?php echo $group->name; ?></option>
							<?php endforeach; ?>
							</select> -->
							<?php foreach(wp_get_terms_for_user($current_user, 'user-group') as $group) : ?>

								<label>
									<input type="checkbox" id="groud-id-<?php echo $group->term_id; ?>" name="group-id" value="<?php echo $group->term_id; ?>">
									<?php echo $group->name; ?>
								</label>
							<?php endforeach; ?>							
							<input id="send-shopper-invite" type="submit" value="Send Invite"/>
							<img id="shopper-invite-loader" style="display: none; width: 18px; margin-left: 5px; position: relative; top: 5px;" src="<?php echo get_template_directory_uri(); ?>/app/assets/img/loader.gif" alt="Loading"/>
						</form>						
					</div>

				</div>
			</div>
		</div>

<?php endif; ?>
