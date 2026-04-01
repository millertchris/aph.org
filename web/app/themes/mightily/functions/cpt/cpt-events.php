<?php
    // Register Custom Post Type
    function event() {
        $labels = [
            'name'                  => _x('Events', 'Post Type General Name', 'text_domain'),
            'singular_name'         => _x('Event', 'Post Type Singular Name', 'text_domain'),
            'menu_name'             => __('Events', 'text_domain'),
            'name_admin_bar'        => __('Events', 'text_domain'),
            'archives'              => __('Event Archives', 'text_domain'),
            'attributes'            => __('Event Attributes', 'text_domain'),
            'parent_item_colon'     => __('Parent Event:', 'text_domain'),
            'all_items'             => __('All Events', 'text_domain'),
            'add_new_item'          => __('Add New Event', 'text_domain'),
            'add_new'               => __('Add New', 'text_domain'),
            'new_item'              => __('New Event', 'text_domain'),
            'edit_item'             => __('Edit Event', 'text_domain'),
            'update_item'           => __('Update Event', 'text_domain'),
            'view_item'             => __('View Event', 'text_domain'),
            'view_items'            => __('View Events', 'text_domain'),
            'search_items'          => __('Search Event', 'text_domain'),
            'not_found'             => __('Not found', 'text_domain'),
            'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
            'featured_image'        => __('Featured Image', 'text_domain'),
            'set_featured_image'    => __('Set featured image', 'text_domain'),
            'remove_featured_image' => __('Remove featured image', 'text_domain'),
            'use_featured_image'    => __('Use as featured image', 'text_domain'),
            'insert_into_item'      => __('Insert into item', 'text_domain'),
            'uploaded_to_this_item' => __('Uploaded to this item', 'text_domain'),
            'items_list'            => __('Events list', 'text_domain'),
            'items_list_navigation' => __('Events list navigation', 'text_domain'),
            'filter_items_list'     => __('Filter items list', 'text_domain'),
        ];
        $args = [
            'label'                 => __('Events', 'text_domain'),
            'description'           => __('A collection of event.', 'text_domain'),
            'labels'                => $labels,
            'supports'              => [ 'title', 'editor', 'thumbnail' ],
            'taxonomies'            => [ 'event_categories', 'event_tag' ],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-calendar',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'query_var' => true,
            'capability_type'       => 'post',
            'rewrite' => array('slug' => 'events')
        ];
        register_post_type('event', $args);
    }
    add_action('init', 'event', 0);

    // Register Custom Taxonomy
    function event_categories() {
        $labels = [
            'name'                       => _x('Event Categories', 'Taxonomy General Name', 'text_domain'),
            'singular_name'              => _x('Event Category', 'Taxonomy Singular Name', 'text_domain'),
            'menu_name'                  => __('Event Categories', 'text_domain'),
            'all_items'                  => __('All Events', 'text_domain'),
            'parent_item'                => __('Parent Event', 'text_domain'),
            'parent_item_colon'          => __('Parent Event:', 'text_domain'),
            'new_item_name'              => __('New Event Name', 'text_domain'),
            'add_new_item'               => __('Add New Event', 'text_domain'),
            'edit_item'                  => __('Edit Event', 'text_domain'),
            'update_item'                => __('Update Event', 'text_domain'),
            'view_item'                  => __('View Event', 'text_domain'),
            'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
            'add_or_remove_items'        => __('Add or remove items', 'text_domain'),
            'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
            'popular_items'              => __('Popular Events', 'text_domain'),
            'search_items'               => __('Search Events', 'text_domain'),
            'not_found'                  => __('Not Found', 'text_domain'),
            'no_terms'                   => __('No items', 'text_domain'),
            'items_list'                 => __('Events list', 'text_domain'),
            'items_list_navigation'      => __('Events list navigation', 'text_domain'),
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
        register_taxonomy('event_categories', [ 'event' ], $args);
    }
    add_action('init', 'event_categories', 0);

    function event_tag() {
        $labels = [
            'name'                       => _x('Event Tags', 'Taxonomy General Name', 'text_domain'),
            'singular_name'              => _x('Event Tag', 'Taxonomy Singular Name', 'text_domain'),
            'menu_name'                  => __('Event Tags', 'text_domain'),
            'all_items'                  => __('All Tags', 'text_domain'),
            'parent_item'                => __('Parent Tag', 'text_domain'),
            'parent_item_colon'          => __('Parent Tag:', 'text_domain'),
            'new_item_name'              => __('New Tag Name', 'text_domain'),
            'add_new_item'               => __('Add New Tag', 'text_domain'),
            'edit_item'                  => __('Edit Tag', 'text_domain'),
            'update_item'                => __('Update Tag', 'text_domain'),
            'view_item'                  => __('View Tag', 'text_domain'),
            'separate_items_with_commas' => __('Separate items with commas', 'text_domain'),
            'add_or_remove_items'        => __('Add or remove items', 'text_domain'),
            'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
            'popular_items'              => __('Popular Tags', 'text_domain'),
            'search_items'               => __('Search Tags', 'text_domain'),
            'not_found'                  => __('Not Found', 'text_domain'),
            'no_terms'                   => __('No items', 'text_domain'),
            'items_list'                 => __('Tags list', 'text_domain'),
            'items_list_navigation'      => __('Tags list navigation', 'text_domain'),
        ];
        $args = [
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
        ];
        register_taxonomy('event_tag', [ 'event' ], $args);
    }
    add_action('init', 'event_tag', 0);    
