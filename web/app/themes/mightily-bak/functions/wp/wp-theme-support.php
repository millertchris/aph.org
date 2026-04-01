<?php

    // =========================================================================
    // ADD RSS LINKS TO HEAD SECTION
    // =========================================================================
    add_theme_support('automatic-feed-links');

    // =========================================================================
    // ENABLES FEATURED IMAGES FOR PAGES AND POSTS
    // =========================================================================
    // This enables post thumbnails for all post types,
    // if you want to enable this feature for specific post types,
    // use the array to include the type of post
    ## add_theme_support('post-thumbnails', array('post', 'page'));
    add_theme_support('post-thumbnails');

    // =========================================================================
    // TITLE TAG - RECOMMENDED
    // =========================================================================
    // Since Version 4.1, themes should use add_theme_support() in the functions.php
    // file in order to support title tag
    function theme_slug_setup() {
        add_theme_support('title-tag');
    }
    
    // =========================================================================
    // HTML5 Support
    // =========================================================================
    add_theme_support( 'html5', array( 'caption' ) );

    function year_shortcode() {
        $year = date('Y');
        return $year;
    }
    add_shortcode('year', 'year_shortcode');
    
    function customer_service_shortcode(){
        return get_field('customer_service_shortcode', 'option');      
    }
    add_shortcode('customer_service', 'customer_service_shortcode'); 