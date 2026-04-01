<?php
    // =========================================================================
    // REGISTER & ENQUEUE
    // =========================================================================
    function mightyResources() {
        // $css_file = get_stylesheet_directory() . '/app/assets/css/style.min.css';
        // wp_enqueue_style('mightily-css', get_stylesheet_directory_uri() . '/app/assets/css/style.min.css', '', date('m.d.Y.H.i.s', filemtime($css_file)));
        wp_enqueue_style('mightily-css', get_stylesheet_directory_uri() . '/app/assets/css/style.min.css', '', '6.5.3');
        //wp_enqueue_style('roboto-font', '//fonts.googleapis.com/css?family=Roboto:400,700,900&display=swap', '');

        wp_deregister_script('jquery');
        wp_register_script('jquery', get_stylesheet_directory_uri() . '/app/assets/js/jquery.min.js', '', '2.2.4', false);
        wp_enqueue_script('jquery');

        wp_enqueue_script('font-awesome', '//kit.fontawesome.com/0fd6a41086.js', '', '1.0', false);
        wp_enqueue_script('carbon-js', get_stylesheet_directory_uri() . '/app/assets/js/carbon-components.min.js', '');
        wp_enqueue_script('mightily-js', get_stylesheet_directory_uri() . '/app/assets/js/scripts.min.js', ['jquery'], '6.5.3', true);

        // Load google maps api if we are on particular pages
        if(is_checkout()){
            wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAvBmad8cYmF0bEsHkhqEo3KAje5yXepxo&libraries=places&callback=initAutocomplete', '', '', true);           
        }
        // Manage Addresses Scripts
        if (\APH\Addresses::is_address_enabled()){
            wp_enqueue_script('babel-polyfill', get_stylesheet_directory_uri() . '/app/assets/js/babel.polyfill.min.js', '', '6.5.3', false);
            wp_enqueue_script('vue', 'https://unpkg.com/vue@2.6.11/dist/vue.js', '', '', false);
            wp_enqueue_script('vue-carbon', get_stylesheet_directory_uri() . '/app/assets/js/carbon.vue.min.js', '', '6.5.3', false);
        }
        
        // Remove media player js
        wp_deregister_script('wp-mediaelement');
        wp_deregister_style('wp-mediaelement');
        
        // Remove quick order style sheet
        wp_deregister_style('wqbo-style');

    }

    //======================================================================
    // META TAGS
    //======================================================================
    // Adding meta so that we can load it in non Wordpress pages i.e. Netforum
    function add_meta_tags() {
        echo '<meta name="viewport" content="width=device-width,initial-scale=1" />' . "\n";
    }

    //======================================================================
    // ACF Options Page
    //======================================================================
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' 	=> 'App Options',
            'menu_title'	=> 'App Options',
            'menu_slug' 	=> 'app-options',
            'redirect'		=> false
        ]);
    }
