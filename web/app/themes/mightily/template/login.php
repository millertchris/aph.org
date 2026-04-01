<?php
// Template name: Login

get_header(); ?>

<div class="interior-page">
	<div class="layout login">
		<div class="wrapper">
			<div class="row">
				<h1>Login</h1>
				<?php
					$error = '';
					if (isset($_GET['login'])) {
						$error = $_GET['login'];
					}
				?>
				<?php if ($error == 'empty'): ?>
					<p class="alert error">One or more fields are empty.</p>
				<?php elseif($error == 'failed'): ?>
					<p class="alert error">Your login credentials are not correct, please try again.</p>
				<?php endif; ?>
				<?php wp_login_form(); ?>
				<a href="<?php echo home_url('/my-account/lost-password'); ?>">Lost your password?</a>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
