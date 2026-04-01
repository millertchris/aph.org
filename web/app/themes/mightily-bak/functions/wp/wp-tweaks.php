<?php

    // =========================================================================
    // ADDING SUPPORT FOR ADDITIONAL FILE TYPES
    // =========================================================================
    function custom_mime_types( $mimes ) {
        $mimes['brf'] = 'text/plain';
        $mimes['epub'] = 'application/epub+zip';
        $mimes['zip'] = 'application/zip';
        $mimes['apk'] = 'application/vnd.android.package-archive';
        return $mimes;
    }
    add_filter( 'upload_mimes', 'custom_mime_types' );

    // =========================================================================
    // MANAGE REDIRECTS AFTER LOGIN
    // =========================================================================
    function all_login_redirect($redirect, $user) {
        if(isset($user->roles) && is_array($user->roles)){
            $roles = array_reverse($user->roles);
            $role = array_pop($roles);
        } else {
            $role = '';
        }
        if (isset($_GET['redirect_to']) && strpos($_GET['redirect_to'], 'oauth') !== false) {          
            return $_GET['redirect_to'];
        }
        if ($redirect == home_url('/checkout/')) {
            return $redirect;
        }
        if ($role == 'administrator') {
            return get_admin_url();
        }
        if ($role == 'editor') {
            return get_admin_url();
        }         
        if ($role == 'customer_service') {
            return get_admin_url(null, '/edit.php?post_type=shop_order');
        }
        if ($role == 'finance_administrator') {
            return get_admin_url(null, '/admin.php?page=wc-reports');
        }       
        return home_url('/profile/');
    }

    function wc_login_redirect($redirect, $user) {
        return all_login_redirect($redirect, $user);
    }
    
    function wp_login_redirect($redirect, $request, $user) {
        return all_login_redirect($redirect, $user);
    }

    // =========================================================================
    // REDIRECT FROM CHECKOUT IF NOT LOGGED IN
    // =========================================================================
    // APH-493 Redirect users who login at the checkout to the cart
    function filter_woocommerce_login_redirect( $redirect, $user ) { 
        // make filter magic happen here...
        if( $redirect == home_url('/checkout/') ) {
            $redirect = home_url('/cart/'); 
        } elseif(strpos($redirect, 'oauth') !== false){
            $redirect = $redirect;
        } else {
            $redirect = home_url('/profile/');
        }
        return $redirect;
    }; 

    // =========================================================================
    // REDIRECT AFTER LOGOUT
    // =========================================================================
    function all_logout_redirect(){
        wp_redirect(home_url());
        exit();
    }

    // =========================================================================
    // REDIRECT AFTER REGISTRATION
    // =========================================================================
    function woocommerce_register_redirect($redirect) {
        return home_url('/profile/');
    }

    //======================================================================
    // ADDING SUPPORT FOR ACCESSIBLITY
    //======================================================================
    function facet_stuff() {
        return TRUE;
    }
    add_filter('facetwp_load_a11y', 'facet_stuff');
        
    //======================================================================
    // ADDING SUBMIT BUTTON TO FACETWP
    //======================================================================
    add_action('wp_footer', 'add_facetwp_submit', 100);

    function add_facetwp_submit() {
    ?>
    <script>(function($) {
    $(document).on('facetwp-loaded', function() {
        $('.facetwp-search-wrap').each(function() {
            if ($(this).find('.facetwp-search-submit').length < 1) {
                $(this).find('.facetwp-search').after('<button onclick="FWP.reset()">Reset</button>');
                $(this).find('.facetwp-search').after('<button class="facetwp-search-submit" onclick="FWP.refresh()">Submit</button>');
            }
        });
        $('.facetwp-input-wrap').each(function() {
            if ($(this).find('.facetwp-search-submit').length < 1) {
                $(this).find('.facetwp-search').after('<button onclick="FWP.reset()">Reset</button>');
                $(this).find('.facetwp-search').after('<button class="facetwp-search-submit" onclick="FWP.refresh()">Submit</button>');
            }
        });
        $('.facetwp-input-wrap').addClass('facetwp-search-wrap');
        $('i.facetwp-icon').addClass('facetwp-btn');      
    });
    })(jQuery);
    </script>
    <?php
    }

    //======================================================================
    // ADDING LABELS TO FACETWP
    //======================================================================
    function fwp_add_facet_labels() {
        ?>
        <script>
        (function($) {
            function getTextNodesIn(node, includeWhitespaceNodes) {
                var textNodes = [], nonWhitespaceMatcher = /\S/;
                function getTextNodes(node) {
                    if (node.nodeType == 3) {
                        if (includeWhitespaceNodes || nonWhitespaceMatcher.test(node.nodeValue)) {
                            textNodes.push(node);
                        }
                    } else {
                        for (var i = 0, len = node.childNodes.length; i < len; ++i) {
                            getTextNodes(node.childNodes[i]);
                        }
                    }
                }
                getTextNodes(node);
                return textNodes;
            }            
            $(document).on('facetwp-loaded', function() {
                // Add facet label above input group
                $('.facetwp-facet').each(function() {
                    var $facet = $(this);
                    if($facet.find('>.facet-label').length == 0){
                        var facet_name = $facet.attr('data-name');
                        var facet_label = FWP.settings.labels[facet_name];
                        $facet.prepend('<p class="facet-label">' + facet_label + '</p>');
                    }
                });
                // Replace some labels with other words
                $('.facetwp-facet-content_types .facetwp-radio').each(function(){
                    var textNodes = getTextNodesIn($(this)[0]);
                    var cache = $(this).children();
                    //console.log(textNodes);
                    if(textNodes[0] && textNodes[0].data == 'Posts '){
                        $(this).text('Articles ').append(cache);
                    }
                    if(textNodes[0] && textNodes[0].data == 'Pages '){
                        $(this).text('Web Pages ').append(cache);
                    }                    
                });
            });
        })(jQuery);
        </script>
        <?php
        }
    add_action( 'wp_footer', 'fwp_add_facet_labels', 100 );

    // =========================================================================
    // ADDING SUPPORT FOR FACETWP
    // =========================================================================
    add_filter( 'facetwp_is_main_query', function( $bool, $query ) {
        return ( true === $query->get( 'facetwp' ) ) ? true : $bool;
    }, 10, 2 );

    //======================================================================
    // ADD AN ADMIN SCRIPT TO MANAGE JS
    //======================================================================
    function admin_resources() {
        wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAvBmad8cYmF0bEsHkhqEo3KAje5yXepxo&libraries=places&callback=initAutocomplete', '', '', true);
        wp_register_script('admin_script', get_stylesheet_directory_uri() . '/app/assets/js/admin-scripts.min.js');
        wp_enqueue_script('admin_script');
        echo '<style>
            p.form-field._billing_country_field.admin-field-disabled { pointer-events: none; }
            p.form-field._billing_country_field.admin-field-disabled .select2-container { opacity: 0.5; }
            p.form-field._billing_state_field.admin-field-disabled { pointer-events: none; }
            p.form-field._billing_state_field.admin-field-disabled .select2-container { opacity: 0.5; }
            .pac-container { width: 380px !important; }
        </style>';
    }
    add_action('admin_enqueue_scripts', 'admin_resources');

  //======================================================================
    // ADD USERSNAP TO FRONT END AND BACK END
    //======================================================================
    function add_usersnap() {
        $user = wp_get_current_user();
        if ( in_array( 'administrator', (array) $user->roles ) ) : ?>
            <script type="text/javascript">
            (function() { var s = document.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = '//api.usersnap.com/load/4d44b0a6-ff19-4944-b8e5-b7a960f1beac.js';
            var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })();
            </script>
        <?php endif; ?>
	<?php
    }

    //======================================================================
    // REMOVING NOTICES FOR ALL BUT ADMINS
    //======================================================================
    // function ds_admin_theme_style() {
    //     if (is_user_role('customer_service')) {
    //         echo '<style>#wpfooter, .notice, .is-dismissible, .update-nag, .updated, .error, .is-dismissible { display: none !important; }</style>';
    //     }
    // }

    add_action('admin_enqueue_scripts', 'ds_admin_theme_style');
    add_action('login_enqueue_scripts', 'ds_admin_theme_style');
    function ds_admin_theme_style() {
        if (!current_user_can('administrator')) {
            echo '<style>.notice, .update-nag, .updated, .is-dismissible { display: none !important; }</style>';
            // echo '<style>.notice, .update-nag, .updated, .error, .is-dismissible { display: none !important; }</style>';
        }
    }

    //======================================================================
    // REMOVING CUSTOMER SERVICE ADMIN BAR ITEMS
    //======================================================================
    function remove_wp_profile() {
        if (is_user_role('customer_service') || is_user_role('eot') || is_user_role('eot-assistant')) {
            global $wp_admin_bar;

            /* **edit-profile is the ID** */
            $wp_admin_bar->remove_menu('edit-profile');
            $wp_admin_bar->remove_menu('edit');
            $wp_admin_bar->remove_menu('new-content');
            $wp_admin_bar->remove_menu('my-blogs');
            $wp_admin_bar->remove_menu('my-account');
            $wp_admin_bar->remove_menu('my-account-with-avatar');
            // $wp_admin_bar->remove_menu('site-name');
        }
    }

    //======================================================================
    // RESTRICTING PROFILE ACCESS
    //======================================================================
    function restrict_profile_access() {
        if (is_page('profile') && !is_user_logged_in()) {
            wp_redirect(home_url('/my-account'));
            exit;
        }
    }

    //======================================================================
    // NUMERIC SEARCH NAVIGATION
    //======================================================================
    function numeric_posts_nav() {
        if (is_singular()) {
            return;
        }

        global $wp_query;

        /** Stop execution if there's only 1 page */
        if ($wp_query->max_num_pages <= 1) {
            return;
        }

        $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
        $max   = intval($wp_query->max_num_pages);

        /** Add current page to the array */
        if ($paged >= 1) {
            $links[] = $paged;
        }

        /** Add the pages around the current page to the array */
        if ($paged >= 3) {
            $links[] = $paged - 1;
            $links[] = $paged - 2;
        }

        if (($paged + 2) <= $max) {
            $links[] = $paged + 2;
            $links[] = $paged + 1;
        }

        echo '<div class="navigation"><ul>' . "\n";

        /** Previous Post Link */
        if (get_previous_posts_link()) {
            printf('<li>%s</li>' . "\n", get_previous_posts_link());
        }

        /** Link to first page, plus ellipses if necessary */
        if (! in_array(1, $links)) {
            $class = 1 == $paged ? ' class="active"' : '';

            printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link(1)), '1');

            if (! in_array(2, $links)) {
                echo '<li>�</li>';
            }
        }

        /** Link to current page, plus 2 pages in either direction if necessary */
        sort($links);
        foreach ((array) $links as $link) {
            $class = $paged == $link ? ' class="active"' : '';
            printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($link)), $link);
        }

        /** Link to last page, plus ellipses if necessary */
        if (! in_array($max, $links)) {
            if (! in_array($max - 1, $links)) {
                echo '<li>�</li>' . "\n";
            }

            $class = $paged == $max ? ' class="active"' : '';
            printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($max)), $max);
        }

        /** Next Post Link */
        if (get_next_posts_link()) {
            printf('<li>%s</li>' . "\n", get_next_posts_link());
        }

        echo '</ul></div>' . "\n";
    }

    //======================================================================
    // Add responsive container to embeds
    //======================================================================
    function video_embed_wrapper($html) {
        return '<div class="video-wrapper">' . $html . '</div>';
    }

    //======================================================================
    // REPLACE EXCERPT
    //======================================================================
    // Replaces the excerpt "Read More" text with a link
    function new_excerpt_more($more) {
        global $post;
        return '...';
        // return '<a class="moretag" href="'. get_permalink($post->ID) . '"> ...read more.</a>';
    }

    //======================================================================
    // CUSTOM EXCERPT
    //======================================================================
    // This function can break slider functionality
    // May want to deprecate using this function
    function custom_excerpt($limit) {
        $excerpt = explode(' ', get_the_excerpt(), $limit);
        if (count($excerpt)>=$limit) {
            array_pop($excerpt);
            $excerpt = implode(' ', $excerpt) . '...';
        } else {
            $excerpt = implode(' ', $excerpt);
        }
        $excerpt = preg_replace('`[[^]]*]`', '', $excerpt);
        return $excerpt;
    }

    //======================================================================
    // CUSTOM EXCERPT
    //======================================================================
    // Using this function in slider-item.php for listing layouts
    function aph_custom_excerpt_length( $length ) {
        return 20;
    }

    // =========================================================================
    // REGISTERING SIDEBAR
    // =========================================================================
    if (function_exists('register_sidebar')) {
        register_sidebar([
            'name' => 'Sidebar Widgets',
            'id'   => 'sidebar-widgets',
            'description'   => 'These are widgets for the sidebar.',
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2>',
            'after_title'   => '</h2>'
        ]);
    }

    // =========================================================================
    // ENABLES 100% JPEG QUALITY
    // =========================================================================
    // Wordpress will compress uploads to 90% of their original size
    function high_jpg_quality() {
        return 100;
    }

    // =========================================================================
    // CHANGE DISPLAY NAMES OF SOME FACET OPTIONS
    // =========================================================================
    add_filter( 'facetwp_facet_render_args', function( $args ) {
        if ($args['facet']['name'] == 'product_discontinued') {
            foreach($args['values'] as $facetkey => $facetvalue){
                if($facetvalue['facet_display_value'] == 'Yes'){
                    $args['values'][$facetkey]['facet_display_value'] = 'Discontinued';
                }
                if($facetvalue['facet_display_value'] == 'Replacement Item'){
                    $args['values'][$facetkey]['facet_display_value'] = 'Replacements';
                }
                if($facetvalue['facet_display_value'] == 'Consumable Item'){
                    $args['values'][$facetkey]['facet_display_value'] = 'Consumables';
                }
                if($facetvalue['facet_display_value'] == 'Optional Part'){
                    $args['values'][$facetkey]['facet_display_value'] = 'Optional Parts';
                }                                
            }
        }
        if ($args['facet']['name'] == 'content_types') {
            foreach($args['values'] as $facetkey => $facetvalue){
                if($facetvalue['facet_display_value'] == 'Products'){
                    $args['values'][$facetkey]['facet_display_value'] = 'Shop';
                }
                if($facetvalue['facet_display_value'] == 'Pages'){
                    $args['values'][$facetkey]['facet_display_value'] = 'Web Pages';
                }
                if($facetvalue['facet_display_value'] == 'Posts'){
                    $args['values'][$facetkey]['facet_display_value'] = 'Blog Posts';
                }                               
            }
        }                 
        return $args;
    });

    // =========================================================================
    // COMBINE DISCONTINUED AND REPLACEMENT ITEMS
    // =========================================================================
    add_filter( 'facetwp_index_row', function( $params, $class ) {
        if ( 'product_replacement_item' == $params['facet_name'] ) {
            $params['facet_name'] = 'product_discontinued';
        }
        return $params;
    }, 10, 2 );       

    // =========================================================================
    // ADD NEW OPTION TO REPLACEMENT ITEMS FILTER WHEN NO ATTRIBUTE IS SET
    // =========================================================================
    add_filter( 'facetwp_indexer_row_data', function( $rows, $params ) {     
        if('product_replacement_item' == $params['facet']['name']){
            $post_id = $params['defaults']['post_id'];
            if(get_post_type($post_id) == 'product'){
                if(empty(wc_get_product_terms($post_id, 'pa_replacement-part'))){
                    if(!get_field('discontinued', $post_id)){
                        $new_row = $params['defaults'];
                        $new_row['facet_value'] = 'product';
                        $new_row['facet_display_value'] = 'Products';
                        $rows[] = $new_row;
                    }
                }
            }
        }
        return $rows;
    }, 10, 2 );    

    // Modify output of audio shortcode to include track element
    add_filter( 'wp_audio_shortcode', function($html, $atts, $audio, $post_id, $library){
        $html_array = explode("</audio>", $html);
        $file_url = '';
        if($atts['mp3'] != ''){
            $file_url = $atts['mp3'];
        }
        if($atts['ogg'] != ''){
            $file_url = $atts['ogg'];
        }
        if($atts['flac'] != ''){
            $file_url = $atts['flac'];
        }
        if($atts['m4a'] != ''){
            $file_url = $atts['m4a'];
        }
        if($atts['wav'] != ''){
            $file_url = $atts['wav'];
        }
        $audio_id = attachment_url_to_postid($file_url);
        $vtt_file = get_field('vtt_file', $audio_id);
        $html_new = $html_array[0];
        $html_new .= '<track src="'.$vtt_file.'" kind="captions" srclang="en" label="english_captions"/>';
        $html_new .= '</audio>';
        return $html_new;        
    }, 10, 5 );

    function remove_wc_status_widget(){
        remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal');    
    }
    add_action('wp_user_dashboard_setup', 'remove_wc_status_widget', 20);
    add_action('wp_dashboard_setup', 'remove_wc_status_widget', 20);    