<?php

// Template name: Profile
get_header();

$current_user = wp_get_current_user();

?>

<div class="interior-page">

	<div class="profile">

		<?php include(locate_template('profile/basic-info.php')); ?>

		<?php if (APH\Roles::userHas([APH\Roles::TVI, APH\Roles::EOT, APH\Roles::OOA], $current_user) && !APH\Roles::userHas(APH\Roles::NET, $current_user))
		    include(locate_template('profile/quota-users.php'));
		?>

        <?php if (APH\Roles::userHas([APH\Roles::OPS], $current_user))
            include(locate_template('profile/quota-full-list.php'));
        ?>

		<div class="layout basic-content">
			<div class="wrapper">
				<div class="row">
					<div class="col" style="text-align: center;">
						<h1 class="h2">Contact us for support.</h1>
						<a href="/contact" class="btn black">Contact us</a>
					</div>
				</div>
			</div>
		</div>


	</div>

</div>

<?php get_footer(); ?>
