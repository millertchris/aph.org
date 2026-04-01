<?php

if (!defined('ABSPATH')) {
    exit;
}

class Prolific_Post_Query_Builder {
    
    public function build_query($args) {
        // Determine if we can use WordPress native status filtering
        $use_wp_status_filter = false;
        $wp_post_status = 'any';
        
        if (isset($args['status'])) {
            $standard_statuses = array('publish', 'draft', 'private', 'pending', 'future', 'trash', 'auto-draft', 'inherit');
            if (in_array($args['status'], $standard_statuses)) {
                $use_wp_status_filter = true;
                $wp_post_status = $args['status'];
            }
        }
        
        // Set posts per page - can use limit if we're using WordPress status filtering
        $posts_per_page = -1;
        if (isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 && $use_wp_status_filter) {
            $posts_per_page = (int) $args['limit'];
        }
        
        $query_args = array(
            'posts_per_page' => $posts_per_page,
            'post_status' => $wp_post_status,
            'fields' => 'ids',
            'meta_query' => array(),
            'date_query' => array(),
            'orderby' => 'ID',
            'order' => 'ASC'
        );
        
        $query_args['post_type'] = $this->get_post_types($args);
        
        if (isset($args['date-from']) || isset($args['date-to'])) {
            $query_args['date_query'] = $this->build_date_query($args);
        }
        
        if (isset($args['author'])) {
            $query_args['author'] = $this->sanitize_author($args['author']);
        }
        
        if (isset($args['meta-key']) && isset($args['meta-value'])) {
            $query_args['meta_query'][] = $this->build_meta_query($args);
        }
        
        if (isset($args['exclude-ids'])) {
            $query_args['post__not_in'] = $this->parse_excluded_ids($args['exclude-ids']);
        }
        
        if (isset($args['category'])) {
            $query_args = $this->add_taxonomy_query($query_args, 'category', $args['category']);
        }
        
        if (isset($args['tag'])) {
            $query_args = $this->add_taxonomy_query($query_args, 'post_tag', $args['tag']);
        }
        
        return apply_filters('prolific_pre_query_args', $query_args, $args);
    }
    
    private function get_post_types($args) {
        $post_types = array();
        
        if (isset($args['posts']) && $args['posts']) {
            $post_types[] = 'post';
        }
        
        if (isset($args['pages']) && $args['pages']) {
            $post_types[] = 'page';
        }
        
        if (isset($args['custom-post-type'])) {
            $custom_type = sanitize_key($args['custom-post-type']);
            if (post_type_exists($custom_type)) {
                $post_types[] = $custom_type;
            }
        }
        
        if (isset($args['woocommerce-products']) && $args['woocommerce-products']) {
            if (class_exists('WooCommerce')) {
                $post_types[] = 'product';
            } else {
                WP_CLI::warning('WooCommerce is not active. Skipping product post type.');
            }
        }
        
        if (isset($args['events']) && $args['events']) {
            $event_types = $this->get_event_post_types();
            if (!empty($event_types)) {
                $post_types = array_merge($post_types, $event_types);
            }
        }
        
        if (empty($post_types)) {
            $has_specific_request = isset($args['events']) || isset($args['woocommerce-products']) || isset($args['custom-post-type']);
            if (!$has_specific_request) {
                $post_types = array('post');
            }
        }
        
        return $post_types;
    }
    
    private function get_event_post_types() {
        $event_types = array();
        
        $known_event_types = array(
            'tribe_events',
            'event',
            'em_event',
            'ai1ec_event',
            'mec-events',
            'event-organiser'
        );
        
        foreach ($known_event_types as $type) {
            if (post_type_exists($type)) {
                $event_types[] = $type;
            }
        }
        
        if (!empty($event_types)) {
            WP_CLI::line('Found event post types: ' . implode(', ', $event_types));
        } else {
            WP_CLI::warning('No event post types found. Make sure your event plugin is active.');
        }
        
        return $event_types;
    }
    
    private function build_date_query($args) {
        $date_query = array();
        
        if (isset($args['date-from'])) {
            $from_date = $this->parse_date($args['date-from']);
            if ($from_date) {
                $date_query['after'] = array(
                    'year' => $from_date['year'],
                    'month' => $from_date['month'],
                    'day' => $from_date['day']
                );
            }
        }
        
        if (isset($args['date-to'])) {
            $to_date = $this->parse_date($args['date-to']);
            if ($to_date) {
                $date_query['before'] = array(
                    'year' => $to_date['year'],
                    'month' => $to_date['month'],
                    'day' => $to_date['day']
                );
            }
        }
        
        if (!empty($date_query)) {
            $date_query['inclusive'] = true;
        }
        
        return $date_query;
    }
    
    private function parse_date($date_string) {
        $date = DateTime::createFromFormat('Y-m-d', $date_string);
        
        if (!$date) {
            return false;
        }
        
        return array(
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('m'),
            'day' => (int) $date->format('d')
        );
    }
    
    private function sanitize_author($author) {
        if (is_numeric($author)) {
            return (int) $author;
        }
        
        $user = get_user_by('login', $author);
        if (!$user) {
            $user = get_user_by('email', $author);
        }
        
        return $user ? $user->ID : 0;
    }
    
    private function build_meta_query($args) {
        $meta_query = array(
            'key' => sanitize_key($args['meta-key']),
            'compare' => '='
        );
        
        if (isset($args['meta-compare'])) {
            $compare = strtoupper($args['meta-compare']);
            $valid_compare = array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS');
            
            if (in_array($compare, $valid_compare)) {
                $meta_query['compare'] = $compare;
            }
        }
        
        if ($meta_query['compare'] === 'EXISTS' || $meta_query['compare'] === 'NOT EXISTS') {
            unset($meta_query['value']);
        } elseif ($meta_query['compare'] === 'IN' || $meta_query['compare'] === 'NOT IN') {
            $meta_query['value'] = array_map('trim', explode(',', $args['meta-value']));
        } elseif ($meta_query['compare'] === 'BETWEEN' || $meta_query['compare'] === 'NOT BETWEEN') {
            $values = array_map('trim', explode(',', $args['meta-value']));
            if (count($values) >= 2) {
                $meta_query['value'] = array($values[0], $values[1]);
            }
        } else {
            $meta_query['value'] = $args['meta-value'];
        }
        
        if (isset($args['meta-type'])) {
            $type = strtoupper($args['meta-type']);
            $valid_types = array('CHAR', 'NUMERIC', 'BINARY', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED');
            
            if (in_array($type, $valid_types)) {
                $meta_query['type'] = $type;
            }
        }
        
        return $meta_query;
    }
    
    private function parse_excluded_ids($exclude_string) {
        $ids = array_map('trim', explode(',', $exclude_string));
        $excluded_ids = array();
        
        foreach ($ids as $id) {
            if (is_numeric($id) && $id > 0) {
                $excluded_ids[] = (int) $id;
            }
        }
        
        return $excluded_ids;
    }
    
    private function add_taxonomy_query($query_args, $taxonomy, $terms) {
        if (!isset($query_args['tax_query'])) {
            $query_args['tax_query'] = array();
        }
        
        $term_list = array_map('trim', explode(',', $terms));
        
        $query_args['tax_query'][] = array(
            'taxonomy' => $taxonomy,
            'field' => $this->is_numeric_array($term_list) ? 'term_id' : 'slug',
            'terms' => $term_list,
            'operator' => 'IN'
        );
        
        return $query_args;
    }
    
    private function is_numeric_array($array) {
        foreach ($array as $item) {
            if (!is_numeric($item)) {
                return false;
            }
        }
        return true;
    }
    
    public function build_woocommerce_query($args) {
        if (!class_exists('WooCommerce')) {
            throw new Exception('WooCommerce is not active');
        }
        
        $query_args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
            'meta_query' => array()
        );
        
        if (isset($args['sku'])) {
            $query_args['meta_query'][] = array(
                'key' => '_sku',
                'value' => $args['sku'],
                'compare' => 'LIKE'
            );
        }
        
        if (isset($args['stock-status'])) {
            $stock_status = sanitize_key($args['stock-status']);
            $valid_statuses = array('instock', 'outofstock', 'onbackorder');
            
            if (in_array($stock_status, $valid_statuses)) {
                $query_args['meta_query'][] = array(
                    'key' => '_stock_status',
                    'value' => $stock_status,
                    'compare' => '='
                );
            }
        }
        
        if (isset($args['price-min']) || isset($args['price-max'])) {
            $price_query = array('key' => '_price', 'type' => 'NUMERIC');
            
            if (isset($args['price-min']) && isset($args['price-max'])) {
                $price_query['value'] = array(
                    floatval($args['price-min']),
                    floatval($args['price-max'])
                );
                $price_query['compare'] = 'BETWEEN';
            } elseif (isset($args['price-min'])) {
                $price_query['value'] = floatval($args['price-min']);
                $price_query['compare'] = '>=';
            } elseif (isset($args['price-max'])) {
                $price_query['value'] = floatval($args['price-max']);
                $price_query['compare'] = '<=';
            }
            
            $query_args['meta_query'][] = $price_query;
        }
        
        if (isset($args['product-type'])) {
            $product_type = sanitize_key($args['product-type']);
            $valid_types = array('simple', 'grouped', 'external', 'variable');
            
            if (in_array($product_type, $valid_types)) {
                $query_args['tax_query'][] = array(
                    'taxonomy' => 'product_type',
                    'field' => 'slug',
                    'terms' => $product_type
                );
            }
        }
        
        if (isset($args['product-category'])) {
            $categories = array_map('trim', explode(',', $args['product-category']));
            
            $query_args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $categories,
                'operator' => 'IN'
            );
        }
        
        return $query_args;
    }
    
    public function build_event_query($args) {
        $event_types = $this->get_event_post_types();
        
        if (empty($event_types)) {
            throw new Exception('No event post types found');
        }
        
        $query_args = array(
            'post_type' => $event_types,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids',
            'meta_query' => array()
        );
        
        if (isset($args['event-start-date']) || isset($args['event-end-date'])) {
            $meta_query = $this->build_event_date_query($args);
            if (!empty($meta_query)) {
                $query_args['meta_query'] = array_merge($query_args['meta_query'], $meta_query);
            }
        }
        
        if (isset($args['event-venue'])) {
            $venue_query = $this->build_event_venue_query($args['event-venue']);
            if (!empty($venue_query)) {
                $query_args['meta_query'][] = $venue_query;
            }
        }
        
        return $query_args;
    }
    
    private function build_event_date_query($args) {
        $meta_queries = array();
        
        $date_meta_keys = array(
            '_EventStartDate',
            '_event_start_date',
            'event_start_date',
            '_start_date'
        );
        
        foreach ($date_meta_keys as $meta_key) {
            if (isset($args['event-start-date'])) {
                $meta_queries[] = array(
                    'key' => $meta_key,
                    'value' => $args['event-start-date'],
                    'compare' => '>=',
                    'type' => 'DATE'
                );
            }
            
            if (isset($args['event-end-date'])) {
                $meta_queries[] = array(
                    'key' => $meta_key,
                    'value' => $args['event-end-date'],
                    'compare' => '<=',
                    'type' => 'DATE'
                );
            }
        }
        
        return $meta_queries;
    }
    
    private function build_event_venue_query($venue) {
        $venue_meta_keys = array(
            '_EventVenue',
            '_event_venue',
            'event_venue',
            '_venue'
        );
        
        foreach ($venue_meta_keys as $meta_key) {
            return array(
                'key' => $meta_key,
                'value' => $venue,
                'compare' => 'LIKE'
            );
        }
        
        return array();
    }
}