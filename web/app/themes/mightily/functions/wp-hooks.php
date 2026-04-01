<?php
    //======================================================================
    // WORDPRESS ACTIONS
    //======================================================================
    add_filter('admin_body_class', function($classes) {
        return APH\Templates::add_admin_body_class($classes);
    }, 10, 1);

    add_action('admin_enqueue_scripts', 'ds_admin_theme_style');
    
    add_action('after_setup_theme', 'theme_slug_setup');
    
    add_action('login_enqueue_scripts', 'ds_admin_theme_style');
    add_action('rest_api_init', function(){
        if ( defined( 'WC_ABSPATH' ) ) {
            include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
        }
    });
    add_action('rest_api_init', 'add_get_fq_accounts_api');
    add_action('rest_api_init', 'add_post_fq_account_api');
    add_action('rest_api_init', 'add_create_fq_account_api');
    add_action('rest_api_init', 'add_user_to_fq_account_endpoint');
    add_action('rest_api_init', 'create_api_fq_account_field');
    add_action('rest_api_init', 'add_date_discontinued_endpoint');
    add_action('rest_api_init', 'add_get_id_from_sku_api');
    add_action('rest_api_init', 'add_create_woocommerce_product_api');

    
    add_action('wp_enqueue_scripts', 'mightyResources');
    
    add_action('wp_head', 'add_meta_tags', 1);

    add_action('in_admin_footer', 'add_usersnap');
    add_action('wp_footer', 'add_usersnap');

    add_action('wp_before_admin_bar_render', 'remove_wp_profile', 0);

    add_filter('login_redirect', 'wp_login_redirect', 10, 3);

    add_action('template_redirect', 'restrict_profile_access');
    add_filter('woocommerce_registration_redirect', 'woocommerce_register_redirect');
    add_action('init', 'add_view_order_capability', 11);
    add_action('init', 'order_update_init');

    remove_action('wp_head', 'rsd_link'); // remove really simple discovery link
    remove_action('wp_head', 'wp_generator'); // remove wordpress version
    remove_action('wp_head', 'feed_links', 2); // remove rss feed links
    remove_action('wp_head', 'feed_links_extra', 3); // remove all extra rss feed links
    remove_action('wp_head', 'index_rel_link'); // remove link to index page
    remove_action('wp_head', 'wlwmanifest_link'); // remove wlwmanifest.xml (needed to support windows live writer)
    remove_action('wp_head', 'start_post_rel_link', 10, 0); // remove random post link
    remove_action('wp_head', 'parent_post_rel_link', 10, 0); // remove parent post link
    remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // remove the next and previous post links
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    remove_action('wp_head', 'rest_output_link_wp_head'); // remove JSON link from head
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

    //======================================================================
    // WORDPRESS FILTERS
    //======================================================================
    add_filter('acf/prepare_field/name=fq_account_balance', 'disable_acf_field');
    add_filter('acf/prepare_field/name=fq_outstanding_balance', 'disable_acf_field');
    add_filter('acf/prepare_field/name=fq_available_funding', 'disable_acf_field');
    add_filter('acf/prepare_field/name=fq_overspent', 'disable_acf_field');
    add_filter('acf/settings/remove_wp_meta_box', '__return_true'); //Drastically speed up the load times of the post edit page!
    add_filter('embed_oembed_html', 'video_embed_wrapper', 10, 3);
    add_filter('excerpt_more', 'new_excerpt_more');
    add_filter('jpg_quality', 'high_jpg_quality');
    add_filter('video_embed_html', 'video_embed_wrapper'); // Jetpack
    add_filter('gform_tabindex', '__return_false'); // Disabling the tab index on gravity forms
    add_filter('wpseo_next_rel_link', '__return_false');
    add_filter('wpseo_prev_rel_link', '__return_false');

    add_filter('searchwp\swp_query\args', function($args){
        if (isset($args['facetwp'])){
            $args['posts_per_page'] = -1;
        }
        return $args;
    });

    add_filter('spn/columns-display', '__return_false');

    add_action('wp', function(){
        if(get_field('disable_shop', 'option')){
            // If is a shop action page or account page redirect to the field set, if its set
            if(is_page('profile') || is_page('my-account') || is_cart() || is_checkout() || is_account_page()){
                $target_url = get_field('when_disabled_redirect_to', 'option');
                if($target_url && $target_url != ''){
                    wp_redirect($target_url);
                    exit;
                } else {
                    wp_redirect(get_home_url());
                    exit;
                }
            }
        }
    });

    add_filter('the_password_form', function($form ){
        return APH\Templates::add_error_message_to_password_form($form);
    }, 10, 1);

    add_filter('gettext', function($translated, $untranslated, $domain){
        return APH\Templates::update_strings($translated, $untranslated, $domain);
    }, 999, 3);

    add_filter('gform_us_states', function($states){
        $territories = array(
            2 =>'American Samoa',
            12=>'Guam',
            37 =>'Northern Mariana Islands',
            42 =>'Puerto Rico',
            48 =>'United States Minor Outlying Islands',
            49 =>'U.S. Virgin Islands'
        );
        foreach ( $territories as $key => $t ) {
            array_splice( $states, $key, 0, $t );
        }
        return $states;
    }); 

    add_filter('acf/load_field/name=primary_core_or_ecc_area', function($field){
        // reset choices
        $field['choices'] = array();      
        // get the textarea value from options page without any formatting
        $choices = get_field('pc_ecc_options', 'option', false);
        // explode the value so that each line is a new array piece
        $choices = explode("\n", $choices);
        // remove any unwanted white space
        $choices = array_map('trim', $choices);
        // loop through array and add to field 'choices'
        if( is_array($choices) ) {
            foreach( $choices as $choice ) {
                $field['choices'][ $choice ] = $choice;
            }
        }
        // return the field
        return $field;
    });

    add_filter('acf/load_field/name=target_audience', function($field){
        // reset choices
        $field['choices'] = array();      
        // get the textarea value from options page without any formatting
        $choices = get_field('ta_options', 'option', false);
        // explode the value so that each line is a new array piece
        $choices = explode("\n", $choices);
        // remove any unwanted white space
        $choices = array_map('trim', $choices);
        // loop through array and add to field 'choices'
        if( is_array($choices) ) {
            foreach( $choices as $choice ) {
                $field['choices'][ $choice ] = $choice;
            }
        }
        // return the field
        return $field;
    }); 
    
    add_filter('facetwp_pager_html', function($html, $params){
        return APH\Templates::facetwp_pager_html($html, $params);
    }, 10, 2);  

    add_filter('posts_where', function($where){
        $where = str_replace( "meta_key = 'occurrences_$", "meta_key LIKE 'occurrences_%", $where );
        return $where;
    }, 10, 1);

    add_filter( 'rest_allowed_cors_headers', function($allow_headers){
        $louis_referrer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME);
        $louis_referrer .= '://' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        if(in_array($louis_referrer, [
            'https://staging.louis.aph.org',
            'https://louis.aph.org',
            'http://aph-local',
            'http://aph-louis.local',
            'https://aph-local',
            'https://aph-louis.local',
            'localhost'
        ])){
            $allow_headers[] = 'X-WC-Store-API-Nonce';
            $allow_headers[] = 'Nonce';
        }
        return $allow_headers; 
    });

    add_filter('rest_exposed_cors_headers', function($expose_headers){
        $louis_referrer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME);
        $louis_referrer .= '://' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        if(in_array($louis_referrer, [
            'https://staging.louis.aph.org',
            'https://louis.aph.org',
            'http://aph-local',
            'http://aph-louis.local',
            'https://aph-local',
            'https://aph-louis.local',
            'localhost'
        ])){
            $expose_headers[] = 'X-WC-Store-API-Nonce';
            $expose_headers[] = 'Nonce';
        }        
        return $expose_headers;
    } );
    
    add_filter('facetwp_facet_sources', function($sources){
        $sources['posts']['choices']['post_name'] = 'Post Name';
        return $sources;
    });