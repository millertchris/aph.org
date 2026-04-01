<h1>Search Results</h1>
<div class="accordion-wrapper">
	<ul data-accordion class="bx--accordion tabs wc-tabs">
        <li data-accordion-item class="bx--accordion__item tab">
            <button class="bx--accordion__heading h4" aria-expanded="false" aria-controls="pane1">
                <h1 class="bx--accordion__title"><?php the_field('search_help_title', 'option'); ?></h1>
            </button>
            <div id="pane1" class="bx--accordion__content">
                <div class="bx--accordion__content-wrapper">
                    <?php the_field('search_help_content', 'option'); ?>
                </div>
            </div>
        </li>
	</ul>
</div>
<div class="fieldset search-field">
    <div class="search-field-wrapper">
        <?php echo facetwp_display( 'facet', 'search_term' ); ?>
    </div>
</div>
<p class="h6 subhead">Searching for textbooks from APH or other accessible media producers? <a href="https://louis.aph.org">Go to Louis</a>.</p>
<button id="open-filters"><i class="fas fa-filter" aria-hidden="true"></i> Additional Filters</button>
<div class="search-results-inner">
    <aside class="search-filters-aside">
        <div class="search-filter-wrapper">
            <h2 class="h6">Search Filters</h2>
            <a class="skip-filters-link" href="#search-results-main">Skip filters</a>
            <?php echo facetwp_display( 'facet', 'content_types' ); ?>
            <?php echo facetwp_display( 'facet', 'product_discontinued' ); ?>           
            <?php echo facetwp_display( 'facet', 'fq_eligible' ); ?>
            <?php echo facetwp_display( 'facet', 'blog_category' ); ?>
            <?php echo facetwp_display( 'facet', 'product_category' ); ?>
            <!-- Age Range -->
            <?php echo facetwp_display( 'facet', 'product_age_range' ); ?>
            <!-- Grade -->
            <?php echo facetwp_display( 'facet', 'product_grade_range' ); ?>
            <!-- Product Type -->
            <?php echo facetwp_display( 'facet', 'product_type' ); ?>             
            <!-- Media -->
            <?php echo facetwp_display( 'facet', 'product_media' ); ?>  
            <!-- Format -->
            <?php echo facetwp_display( 'facet', 'product_format' ); ?>  
            <!-- Paper Type -->
            <?php echo facetwp_display( 'facet', 'product_paper_type' ); ?>  
            <!-- Braille -->
            <?php echo facetwp_display( 'facet', 'product_braille' ); ?>  
            <!-- Software -->
            <?php echo facetwp_display( 'facet', 'product_os' ); ?>  
            <!-- Language -->     
            <?php echo facetwp_display( 'facet', 'product_language' ); ?>
            
            <button id="close-filters">Done</button>    
        </div>
    </aside>