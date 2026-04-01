<!doctype html>
<?php
    $template_url = get_template_directory_uri();
    $current_user = wp_get_current_user();
	$site_url = get_site_url();
	
	$body_classes = banner_class() . ' ' . get_current_user_role();
?>

<html <?php language_attributes(); ?>>
<head>

<link rel="apple-touch-icon" sizes="180x180" href="<?php echo $template_url; ?>/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $template_url; ?>/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="192x192" href="<?php echo $template_url; ?>/favicon/android-chrome-192x192.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $template_url; ?>/favicon/favicon-16x16.png">
<link rel="manifest" href="<?php echo $template_url; ?>/favicon/site.webmanifest">
<link rel="mask-icon" href="<?php echo $template_url; ?>/favicon/safari-pinned-tab.svg" color="#f84785">
<meta name="apple-mobile-web-app-title" content="APH">
<meta name="application-name" content="APH">
<meta name="msapplication-TileColor" content="#f84785">
<meta name="theme-color" content="#ffffff">
	<?php if (is_search()) {
    ?>
		<meta name="robots" content="noindex, nofollow">
	<?php
} ?>

	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>">

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php if (is_singular()) {
        wp_enqueue_script('comment-reply');
    } ?>

	<?php wp_head(); ?>
	<script>
		var stylesheet_directory_uri = "<?php echo get_stylesheet_directory_uri(); ?>";
	</script>

</head>
<body <?php body_class($body_classes); ?>>
	<?php if (!isset($_COOKIE['cookie_notice'])): ?>
		<!-- Cookie Notice -->
		<!-- <div class="notice cookie" aria-label="Cookie Notice">
			<div class="wrapper">
			<p>This website uses cookies to ensure you get the best experience. <a class="cookie-notice-link" href="<?php echo $site_url; ?>/privacy-policy">Learn more about our cookie policy</a></p>
			<button class="cookie-notice-close">Got it!</button>
			</div>
		</div> -->
	<?php endif; ?>

	<a href="#main-menu" class="skip-nav">Skip to main menu</a>
	<a href="#main" class="skip-nav">Skip to main content</a>

	<header class="header">
		<?php include(locate_template('inc/banner-env.php')); ?>
		<?php if(get_field('notification_banner_active', 'option')) : ?>
		<?php if (!isset($_COOKIE['sitewide_notice'])): ?>
		<div class="notice site-wide notice-small <?php echo get_field('notification_banner_color', 'option'); ?>">
			<div class="wrapper">
				<div class="content">
					<?php the_field('notification_banner', 'option'); ?>
					<a href="#" title="Close this message" class="site-wide-notice-close">Close</a>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>		
		<div class="nav-top">
			<div class="wrapper">
				<div class="logo">
					<a href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo $template_url; ?>/app/assets/img/logo-white.svg" alt="American Printing House for the Blind Home Page"></a>
					<span class="tag-line">Welcome Everyone</span>
          <span class="screen-reader-text">APH Home Page</span>
				</div>
        <div class="search-wrapper">
          <?php get_search_form();?>
        </div>


				<div class="header-btns buttons">
					<a class="btn search" href="#">Search</a>
					<a class="btn donate" href="https://aph.givecloud.co/donate" target="_blank">Donate</a>
				</div>

				<div class="search-results">
					<p class="h4">Search Results</p>

				</div>

				<div class="action-items">
					<ul class="menu white">
						<li class="menu-item mobile-search">
							<a href="#"><i class="fas fa-search" aria-hidden="true"></i> <span class="text">Search</span></a>
						</li>
						<li class="menu-item">
							<?php if (!is_user_logged_in()) : ?>
								<a class="login-link" href="/my-account">Login</a>
							<?php else : ?>
								<a href="/profile" aria-label="Account menu, contains additional list items for your account">
									<?php if(get_user_meta(get_current_user_id(), 'first_name') && is_array(get_user_meta(get_current_user_id(), 'first_name'))) : ?>
										<span class="sub-text mobile-hidden-text">Hi, <?php echo get_user_meta(get_current_user_id(), 'first_name')[0]; ?></span>
									<?php endif; ?>
									<i class="fas fa-user-circle" aria-hidden="true"></i><span class="text mobile-hidden-text"> Account</span>
								</a>
								<ul class="sub-menu right">
									<li class="menu-item"><a href="/profile">View Profile</a></li>
									<li class="menu-item"><a href="/my-account/account-wishlists">View Wishlist</a></li>
									<?php if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles)): ?>
										<li class="menu-item"><a href="https://srs.aph.org" target="_blank">SRS</a></li>
									<?php endif; ?>
									<li class="menu-item"><a href="<?php echo wp_logout_url(home_url()); ?>">Logout</a></li>
								</ul>
							<?php endif; ?>
						</li>
						<li class="menu-item">
							<?php APH\Templates::cart_preview_link(); ?>
							<?php APH\Templates::cart_preview_content(); ?>
						</li>
						<li class="menu-item">
							<button class="hamburger hamburger--elastic mobile-btn" aria-label="Navigation Menu" type="button">
								<span class="hamburger-box">
									<span class="hamburger-inner"></span>
								</span>
							</button>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="nav-bottom">
			<div class="wrapper">
				<nav class="nav" aria-label="Main Menu">
					<ul id="main-menu" class="menu main-menu">
						<?php
                            $args = [
                                'menu' => 'main-menu',
                                'container' => 'false',
                                'items_wrap' => '%3$s'
                            ];
                         ?>
						<?php wp_nav_menu($args); ?>
					</ul>

					<ul class="menu action-menu">
						<?php
                            $args = [
                                'menu' => 'secondary-menu',
                                'container' => 'false',
                                'items_wrap' => '%3$s'
                            ];
                         ?>
						<?php wp_nav_menu($args); ?>
					</ul>
				</nav>
			</div>
		</div>
	</header>

<?php if (!is_woocommerce()): ?>
	<main id="main">
<?php endif; ?>
