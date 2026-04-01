<?php

    //======================================================================
    // ADDS A USER TO AN FQ ACCOUNT
    //======================================================================
    function add_user_to_fq_account($request_data) {
        $user_id = $request_data['id'];
        $fq_accounts = $request_data['fq_accounts'];

        wp_set_terms_for_user($user_id, 'user-group', $fq_accounts);

        return 'User added to FQ Accounts';
    }

    // Creates a rest route that updates and returns the FQ accounts
    function add_user_to_fq_account_endpoint() {
        register_rest_route('wp/v2', '/users/fq_account/(?P<id>[\\d]+)', [
            'methods' => 'POST',
            'callback' => 'add_user_to_fq_account',
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ]);
    }

    //======================================================================
    // ADDING FQ ACCOUNTS TO THE USER OBJECT IN THE REST API
    //======================================================================
    function create_api_fq_account_field() {
        register_rest_field(
            'user',
            'fq-accounts',
            [
                'get_callback'    => 'get_user_fq_accounts',
                'schema'          => 'view',
            ]
        );
    }

    function get_user_fq_accounts($object) {
        $taxonomy = 'user-group';
        $taxonomy_terms = get_terms($taxonomy, [
            'hide_empty' => 0,
            'fields' => 'ids'
        ]);

        $term_objects = [];
        $i = 0;

        $user_id = $object['id'];

        foreach ($taxonomy_terms as $key => $term) {
            $term_objects[$i] = get_term($term);
            $term_objects[$i]->acf = get_fields($taxonomy . '_' . $term);

            $term_objects[$i]->fq_users = get_objects_in_term($term, 'user-group');

            $i++;
        }

        $fq_accounts = [];
        $x = 0;

        foreach ($term_objects as $term) {
            foreach ($term->fq_users as $fq_user_id) {
                if ($fq_user_id == $user_id) {
                    $fq_accounts[$x]->fq_account_id = $term->term_id;
                    $fq_accounts[$x]->fq_account_name = $term->name;
                    $fq_accounts[$x]->fq_account_slug = $term->slug;
                    $x++;
                }
            }
        }

        return $fq_accounts;
    }

    //======================================================================
    // RETRIEVE FIELDS FOR FQ ACCOUNTS
    //======================================================================

    // Retrieves FQ Accounts aka "user-group"
    function get_fq_accounts() {

        // Get all term ID's in a given taxonomy
        $taxonomy = 'user-group';
        $taxonomy_terms = get_terms($taxonomy, [
            'hide_empty' => 0,
            'fields' => 'ids'
        ]);

        $term_objects = [];
        $i = 0;

        foreach ($taxonomy_terms as $key => $term) {
            $term_objects[$i] = get_term($term);
            $term_objects[$i]->acf = get_fields($taxonomy . '_' . $term);

            $term_objects[$i]->fq_users = get_objects_in_term($term, 'user-group');

            $i++;
        }

        return $term_objects;
    }

    // Creates a rest route that returns the FQ accounts
    function add_get_fq_accounts_api() {
        register_rest_route('wp/v2', '/fq-accounts', [
            'methods' => 'GET',
            'callback' => 'get_fq_accounts',
            'permission_callback' => function () {
                return current_user_can('edit_others_posts');
            }
        ]);
    }

    //======================================================================
    // UPDATE ACF FIELDS FOR FQ ACCOUNTS
    //======================================================================
    // Updates ACF fields for FQ Accounts aka "user-group"
    function post_fq_account($request_data) {
        $taxonomy = 'user-group';

        // $term_id = $request_data['term_id'];
        $term_name = $request_data['name'];
        $term_slug = $request_data['slug'];

        $the_term = get_term_by('slug', $term_slug, $taxonomy);

        foreach ($request_data['acf'] as $key => $value) {
            update_field($key, $value, $taxonomy . '_' . $the_term->term_id);
        }

        $term_objects = get_fq_accounts();
        return $term_objects;
    }

    // Creates a rest route that updates and returns the FQ accounts
    function add_post_fq_account_api() {
        register_rest_route('wp/v2', '/fq-accounts', [
            'methods' => 'POST',
            'callback' => 'post_fq_account',
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }            
        ]);
    }

    //======================================================================
    // Create a new FQ ACCOUNT
    //======================================================================

    // Creates a new FQ Accounts aka "user-group"
    function create_fq_account($request_data) {
        $taxonomy = 'user-group';
        $term_name = $request_data['name'];
        $term_slug = $request_data['slug'];

        $the_term = get_term_by('slug', $term_slug, $taxonomy);

        if (!$the_term) {
            wp_insert_term($term_name, $taxonomy, $arrayName = [
                'slug' => $term_slug,
            ]);
            return '{"success":"Account created"}';
        } else {
            return '{"success":"Already exists"}';
        }
    }

    // Creates a rest route that updates and returns the FQ accounts
    function add_create_fq_account_api() {
        register_rest_route('wp/v2', '/fq-accounts/create', [
            'methods' => 'POST',
            'callback' => 'create_fq_account',
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ]);
    }

    //======================================================================
    // ADDS A DISCONTINUED DATE TO PRODUCT
    //======================================================================
    function add_date_discontinued($request_data) {
        // var_dump($request_data['id']);
        // var_dump($request_data['date']);
        if(!current_user_can('edit_posts')){
            return 'Not Sufficient Permissions';
        }

        // Get product object
        $product = wc_get_product($request_data['id']);
        $date_discontinued = urldecode($request_data['date']);

        if(!$product){
            return 'Product Not Found';
        }
        // Get product attributes
        $attributes = get_post_meta($product->get_id(), '_product_attributes', true);
        
        // Add the new term and associate it with the product
        wp_set_object_terms($product->get_id(), $date_discontinued, 'pa_date-discontinued', false);

        // Create the array of data needed to store attribute with product
        $new_attribute_data = array( 
            'name' => 'pa_date-discontinued', 
            'value' => $date_discontinued,
            'is_visible' => '1',
            'is_variation' => '0',
            'is_taxonomy' => '1'
        );

        // Set the product meta to new set of attributes. If attributes meta is empty, we need to add instead of update
        if($attributes == '' || !$attributes){
            $single_attribute['pa_date-discontinued'] = $new_attribute_data;
            add_post_meta($product->get_id(), '_product_attributes', $single_attribute, true);
        } else {
            // Append this new attribute to the array of existing attributes
            $attributes['pa_date-discontinued'] = $new_attribute_data;
            update_post_meta($product->get_id(), '_product_attributes', $attributes);
        }        

        return 'Date Discontinued Added';
    }

    // Creates a rest route that updates and returns the FQ accounts
    function add_date_discontinued_endpoint() {
        register_rest_route('wc/v2', 'add-date-discontinued', [
            'methods' => 'POST',
            'callback' => 'add_date_discontinued',
            'permission_callback' => function () {
                return current_user_can( 'edit_others_posts' );
            }
        ]);
    }    