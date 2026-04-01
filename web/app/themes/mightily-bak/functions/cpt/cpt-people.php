<?php
    // Register Custom Post Type
    function people() {
        $labels = [
            'name'                  => _x('People', 'Post Type General Name', 'text_domain'),
            'singular_name'         => _x('People', 'Post Type Singular Name', 'text_domain'),
            'menu_name'             => __('People', 'text_domain'),
            'name_admin_bar'        => __('People', 'text_domain'),
            'archives'              => __('Item Archives', 'text_domain'),
            'attributes'            => __('Item Attributes', 'text_domain'),
            'parent_item_colon'     => __('Parent Item:', 'text_domain'),
            'all_items'             => __('All Items', 'text_domain'),
            'add_new_item'          => __('Add New Item', 'text_domain'),
            'add_new'               => __('Add New', 'text_domain'),
            'new_item'              => __('New Item', 'text_domain'),
            'edit_item'             => __('Edit Item', 'text_domain'),
            'update_item'           => __('Update Item', 'text_domain'),
            'view_item'             => __('View Item', 'text_domain'),
            'view_items'            => __('View Items', 'text_domain'),
            'search_items'          => __('Search Item', 'text_domain'),
            'not_found'             => __('Not found', 'text_domain'),
            'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
            'featured_image'        => __('Featured Image', 'text_domain'),
            'set_featured_image'    => __('Set featured image', 'text_domain'),
            'remove_featured_image' => __('Remove featured image', 'text_domain'),
            'use_featured_image'    => __('Use as featured image', 'text_domain'),
            'insert_into_item'      => __('Insert into item', 'text_domain'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'text_domain'),
            'items_list'            => __('Items list', 'text_domain'),
            'items_list_navigation' => __('Items list navigation', 'text_domain'),
            'filter_items_list'     => __('Filter items list', 'text_domain'),
        ];
        $args = [
            'label'                 => __('People', 'text_domain'),
            'description'           => __('A collection of people.', 'text_domain'),
            'labels'                => $labels,
            'supports'              => [ 'title', 'editor', 'thumbnail' ],
            'taxonomies'            => [ 'people_categories' ],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-groups',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
        ];
        register_post_type('people', $args);
    }
    add_action('init', 'people', 0);

    // Register Custom Taxonomy
    function people_categories() {
        $labels = [
            'name'                       => _x('People Categories', 'Taxonomy General Name', 'text_domain'),
            'singular_name'              => _x('People Category', 'Taxonomy Singular Name', 'text_domain'),
            'menu_name'                  => __('People Category', 'text_domain'),
            'all_items'                  => __('All Items', 'text_domain'),
            'parent_item'                => __('Parent Item', 'text_domain'),
            'parent_item_colon'          => __('Parent Item:', 'text_domain'),
            'new_item_name'              => __('New Item Name', 'text_domain'),
            'add_new_item'               => __('Add New Item', 'text_domain'),
            'edit_item'                  => __('Edit Item', 'text_domain'),
            'update_item'                => __('Update Item', 'text_domain'),
            'view_item'                  => __('View Item', 'text_domain'),
            'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
            'add_or_remove_items'        => __('Add or remove items', 'text_domain'),
            'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
            'popular_items'              => __('Popular Items', 'text_domain'),
            'search_items'               => __('Search Items', 'text_domain'),
            'not_found'                  => __('Not Found', 'text_domain'),
            'no_terms'                   => __('No items', 'text_domain'),
            'items_list'                 => __('Items list', 'text_domain'),
            'items_list_navigation'      => __('Items list navigation', 'text_domain'),
        ];
        $args = [
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        ];
        register_taxonomy('people_categories', [ 'people' ], $args);
    }
    add_action('init', 'people_categories', 0);
