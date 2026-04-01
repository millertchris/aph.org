<?php // $current_user defined in parent file ?>
<div class="basic-info">
	<div class="wrapper">
		<div class="row">
			<div class="profile-info">
				<div class="col info">
					<div class="image">
                        <?php echo get_wp_user_avatar($current_user->ID, 'thumbnail'); ?>                                            
					</div>
					<div class="contact">
						<h1 class="h4 name"><?php echo $current_user->user_firstname; ?><?php echo $current_user->user_lastname; ?></h1>
						<p class="email"><?php echo $current_user->user_email; ?></p>
						<ul class="resources" style="margin-bottom: 30px;">
							<?php if(APH\Roles::userHas([APH\Roles::ADM, APH\Roles::EOT, APH\Roles::OOA], $current_user)) : ?>
								<li class="item"><a role="button" href="<?php echo home_url(); ?>/catalog-order-form/" class="plain-link"><i class="fas fa-book" aria-hidden="true"></i> Catalog Order Form</a></li>
								<!-- <li class="item"><a role="button" href="<?php echo home_url(); ?>/app/uploads/2019/02/2017-Ex-Officio-Trustee-Handbook-finished-1.docx" class="plain-link"><i class="fas fa-book" aria-hidden="true"></i> EOT Handbook</a></li> -->
								<li class="item"><a role="button" href="https://www.aph.org/app/uploads/2022/12/APH-Shop-Site-Manual-2023-11-22-final.docx" class="plain-link"><i class="fas fa-book" aria-hidden="true"></i> APH Shop Site Manual</a></li>
							<?php endif; ?>
						</ul>
						<?php
							// Change button text for Teachers
							if (APH\Roles::userHas(APH\Roles::TVI, $current_user)) {
								$orderText = 'Request';
							} else {
								$orderText = 'Order';
							}
						?>						
						<h2 class="h4 name"><?php echo $orderText; ?> History</h1>
						<ul>
							<li class="item"><a role="button" href="<?php echo home_url(); ?>/profile/orders-current/" class="plain-link"><i class="fas fa-shopping-cart" aria-hidden="true"></i> My <?php echo $orderText; ?>s</a></li>
							<?php if(APH\Roles::userHas([APH\Roles::ADM, APH\Roles::EOT, APH\Roles::OOA], $current_user)) : ?>
								<li class="item"><a role="button" href="<?php echo home_url(); ?>/profile/orders-other/" class="plain-link"><i class="fas fa-shopping-cart" aria-hidden="true"></i> Other FQ Orders</a></li>
								<li class="item"><a role="button" href="<?php echo home_url(); ?>/profile/orders-teacher/" class="plain-link"><i class="fas fa-shopping-cart" aria-hidden="true"></i> Teacher Orders</a></li>
							<?php endif ; ?>
						</ul>						
					</div>
				</div>
				<div class="col actions">
					<ul class="action-item">
						<li class="item"><a role="button" href="/my-account/edit-account" class="edit-profile plain-link"><i class="fas fa-edit" aria-hidden="true"></i> Edit My Profile</a></li>
						<li class="item"><a role="button" href="javascript:;" class="plain-link" data-micromodal-trigger="upload-avatar">Edit Profile Image</a></li>
						<?php if (APH\Roles::userHas([APH\Roles::ADM, APH\Roles::TVI, APH\Roles::EOT, APH\Roles::OOA], $current_user)) : ?>	
							<li class="item"><a role="button" href="/my-account/my-resources/" class="plain-link">My Resources</a></li>
							<li class="item"><a role="button" href="/profile/addresses" class="plain-link">Manage Addresses</a></li>
						<?php endif; ?>
						<li class="item"><a role="button" href="/quick-order" class="plain-link">Quick Order</a></li>
						<li class="item"><a role="button" href="/my-account/account-wishlists" class="plain-link">View Wishlist</a></li>
						<?php if (APH\Roles::userHas([APH\Roles::EOT, APH\Roles::OOA], $current_user)) : ?>	
							<li class="item"><a role="button" href="https://srs.aph.org" class="plain-link">SRS</a></li>
						<?php endif; ?>
						<li class="item"><a role="button" href="<?php echo wp_logout_url(home_url()); ?>"  class="plain-link">Logout</a></li>
					</ul>
				</div>
			</div>
			<?php if (APH\Roles::userHas([APH\Roles::EOT, APH\Roles::OOA, APH\Roles::NET], $current_user))
		    	include(locate_template('profile/credits.php'));
			?>
		</div>
	</div>
</div>

<div id="upload-avatar" class="modal" aria-hidden="true">
	<div class="bg" tabindex="-1" data-micromodal-close>
		<div class="dialog upload-avatar" role="dialog" aria-modal="true" aria-labelledby="upload-avatar-title" >
			<header>
				<p id="upload-avatar-title" class="h6">Edit Profile Image</p>
				<button class="close" aria-label="Close modal" data-micromodal-close></button>
			</header>

			<?php echo do_shortcode('[avatar_upload]'); ?>

		</div>
	</div>
</div>