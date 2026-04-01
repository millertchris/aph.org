<?php

    //======================================================================
    // Getting the current URL
    //======================================================================
    function get_current_env() {
        $current_env = $_SERVER['HTTP_HOST'];

        return $current_env;
    }

    //======================================================================
    // Gathering all site environments except production
    //======================================================================
    function site_envs() {
        $site_env = [
            'staging.aph.org',
            'dev.aph.org',
            'aph.local',
            'localhost'
        ];

        return $site_env;
    }

    //======================================================================
    // Returns TRUE if the environment is not production
    //======================================================================
    function show_env_banner() {
        $current_env = get_current_env();
        $accepted_site_env = site_envs();

        if (in_array($current_env, $accepted_site_env)) {
            return TRUE;
        }

        return FALSE;
    }

    //======================================================================
    // Returns a class for an active site banner
    //======================================================================
    function banner_class() {

        $class = '';

        if (show_env_banner()) {
            $class = 'site-banner-active';
        }

        return $class;
    }

    //======================================================================
    // Getting all of the current users roles
    //======================================================================
    function get_current_user_roles() {
        $curent_user = wp_get_current_user();
        $user_roles = $curent_user->roles;

        if ($user_roles == NULL) {
            $user_roles = ['guest'];
        }

        return $user_roles;
    }

    //======================================================================
    // Echoing all of the users roles
    //======================================================================
    function display_users_roles() {
        $role_counter = 0;
        $user_roles = get_current_user_roles();
        foreach ($user_roles as $key => $value) {
            $role_counter++;

            if ($role_counter < 2) {
                echo $value;
            } else {
                echo ', ' . $value;
            }
        }
    }

    //======================================================================
    // Disables any ACF field passed through this function
    //======================================================================
    function disable_acf_field($field) {
        $field['disabled'] = true;
        return $field;
    }

    //======================================================================
    // Checking if FacetWP is Activated
    //======================================================================
    function facetwp_activated() {
        if (function_exists('facetwp_display')) {
            return true;
        }

        return false;
    }

    //======================================================================
    // Checking if ACF is Activated
    //======================================================================
    function acf_activated() {
        if (function_exists('get_field')) {
            return true;
        }

        return false;
    }

    //======================================================================
    // ACF Responsive Image
    //======================================================================
    function acf_responsive_image($image_id, $image_size, $max_width) {

        // check the image ID is not blank
        if ($image_id != '') {

            // set the default src image size
            $image_src = wp_get_attachment_image_url($image_id, $image_size);

            // set the srcset with various image sizes
            $image_srcset = wp_get_attachment_image_srcset($image_id, $image_size);

            // generate the markup for the responsive image
            echo 'src="' . $image_src . '" srcset="' . $image_srcset . '" sizes="(max-width: ' . $max_width . ') 100vw, ' . $max_width . '"';
        }
    }

    //======================================================================
    // Checking if user has a given user role
    //======================================================================
    function is_user_role($user_role, $user_id = null) {
        if ($user_id) {
            $user = get_user_by('id', $user_id);
        } else {
            $user = get_user_by('id', get_current_user_id());
        }

        if (isset($user->roles) && in_array($user_role, $user->roles)) {
            return true;
        } else {
            return false;
        }
    }

    //======================================================================
    // Getting the currently logged in users role
    //======================================================================
    function get_current_user_role() {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
            $roles = array_reverse($roles);
            $the_role = 'role-' . array_pop($roles);
            return $the_role;
        } else {
            return false;
        }
    }

    function get_order_number_from_url(){
        // We are grabbing the order number from the end of the url
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url = substr($url, 0, -1);
        preg_match("/[^\/]+$/", $url, $matches);
        return $matches[0];
    }

    function get_fqa_from_url(){
        $order_number = get_order_number_from_url();
        $order = wc_get_order($order_number);
        if($order){
            return get_post_meta($order->get_id(), '_fq_account_name', true);
        } else {
            return false;
        }        
    }

    function formatSizeUnits($bytes){
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2);
            $bytes = $bytes.' <abbr title="Gigabyte">GB</abbr>';
        }
        elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2);
            $bytes = $bytes.' <abbr title="Megabyte">MB</abbr>';
        }
        elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2);
            $bytes = $bytes.' <abbr title="Kilobyte">KB</abbr>';
        }
        elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        }
        else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }
    
    function get_image_url($post){
        $placeholder_url = get_stylesheet_directory_uri() . '/app/assets/img/aph-placeholder.png';
        $attachment_url = get_the_post_thumbnail_url($post->ID, 'medium');
        if($attachment_url){
            if($attachment_url == 'http://nyc3.digitaloceanspaces.com/aph/app/uploads/2018/10/26162145/Product-Image-Placeholder.jpg' || $attachment_url == 'http://nyc3.digitaloceanspaces.com/aph/app/uploads/2018/10/26162145/Product-Image-Placeholder-300x225.jpg' || $attachment_url == 'https://nyc3.digitaloceanspaces.com/aph/app/uploads/2018/10/26162145/Product-Image-Placeholder-300x225.jpg'){
                return $placeholder_url;
            } else {
                return $attachment_url;
            }
        } else {
            return $placeholder_url;
        }
    }

    function get_product_image_url($product){
        $placeholder_url = get_stylesheet_directory_uri() . '/app/assets/img/aph-placeholder.png';
        if($product->get_image_id()){
            $attachment_url = wp_get_attachment_image_url($product->get_image_id(), 'medium');
            if($attachment_url == 'http://nyc3.digitaloceanspaces.com/aph/app/uploads/2018/10/26162145/Product-Image-Placeholder.jpg' || $attachment_url == 'http://nyc3.digitaloceanspaces.com/aph/app/uploads/2018/10/26162145/Product-Image-Placeholder-300x225.jpg' || $attachment_url == 'https://nyc3.digitaloceanspaces.com/aph/app/uploads/2018/10/26162145/Product-Image-Placeholder-300x225.jpg'){
                return $placeholder_url;
            } else {
                return $attachment_url;
            }
        } else {
            return $placeholder_url;
        }
    }

    function aph_get_product_tag_list($id, $sep, $before, $after) {
        $taglist = strip_tags( wc_get_product_tag_list( $id, ", ", '', '' ) );
        return $before . $taglist . $after;
        return '';
    }