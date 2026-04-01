<?php get_header(); ?>

<div class="intro">
	<div class="wrapper">
		<h1 class="h2">You searched for: <span class="search-term"><?php echo the_search_query(); ?></span></h1>
	</div>
</div>

<?php

	if (isset($_GET['engine'])) {
		$engine = $_GET['engine']; // taken from the SearchWP settings screen
		include(locate_template('search/search-cards.php'));
	} else {
		$engine = 'product_search'; // taken from the SearchWP settings screen
		include(locate_template('search/search-cards.php'));

		$engine = 'page_search'; // taken from the SearchWP settings screen
		include(locate_template('search/search-cards.php'));

		$engine = 'post_search'; // taken from the SearchWP settings screen
		include(locate_template('search/search-cards.php'));
	}

?>

<?php get_footer(); ?>
