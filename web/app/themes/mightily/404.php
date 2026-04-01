<?php get_header(); ?>

	<div class="interior-page">

		<div class="layout error-404 basic-content">
            <div class="wrapper">
                <div class="content">
                    <h1 class="h3">Page not found.</h1>
                    <p>Here are some suggestions to help you find what you're searching for:</p>
                    <p>
                        <ul>
                            <li><a href="/" target="_self">American Printing House Homepage</a></li>
                            <li><a href="/frequently-asked-questions/" target="_self">Frequently Asked Questions</a></li>
                            <li><a href="/customer-service/" target="_self">Product Support</a></li>
                            <li><a href="/search-results/" target="_self">Search</a></li>
                        </ul>
                    </p>
                    <h2 class="h4">Need assistance? Contact Customer Service, we're here to help.</h2>
                    <p><?php echo do_shortcode('[copy_of_csr]'); ?></p>
                </div>
            </div>
        </div>

	</div>

<?php get_footer(); ?>
