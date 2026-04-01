<?php
/* Template Name: Search Results */
get_header();
?>

<section class="layout search-results">
    <div class="wrapper">
        <?php if(facetwp_activated()) : ?>
            <?php get_template_part('inc/search/search-form'); ?>
            <?php get_template_part('inc/search/search-query'); ?>
        <?php else : ?>
            <h2>Error: FacetWP is not installed or activated.</h2>
        <?php endif; ?>
    </div>
</section>
<section class="layout basic-content">
	<div class="content">
		<div class="wrapper">
            <div class="col" style="text-align: center;">
                <h2 class="h4">Need assistance? Contact Customer Service, we’re here to help.</h2>
                <?php echo do_shortcode('[copy_of_csr]'); ?>
            </div>
		</div>
	</div>
</section>
<?php get_footer(); ?>