<?php

if (!defined('ABSPATH')) {
    exit;
}

class Prolific_Post_Filter {
    
    public function filter_by_status($posts, $target_status) {
        if (empty($posts) || empty($target_status)) {
            return $posts;
        }
        
        $filtered_posts = array();
        
        foreach ($posts as $post_id) {
            $post_status = get_post_status($post_id);
            
            if ($this->status_matches($post_status, $target_status)) {
                $filtered_posts[] = $post_id;
            }
        }
        
        return $filtered_posts;
    }
    
    private function status_matches($post_status, $target_status) {
        if ($target_status === 'any') {
            return true;
        }
        
        if ($target_status === 'public') {
            return in_array($post_status, array('publish', 'inherit'));
        }
        
        if ($target_status === 'non-public') {
            return !in_array($post_status, array('publish', 'inherit'));
        }
        
        return $post_status === $target_status;
    }
    
    public function filter_by_content($posts, $content_filters) {
        if (empty($posts) || empty($content_filters)) {
            return $posts;
        }
        
        $filtered_posts = array();
        
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            
            if (!$post) {
                continue;
            }
            
            if ($this->content_matches($post, $content_filters)) {
                $filtered_posts[] = $post_id;
            }
        }
        
        return $filtered_posts;
    }
    
    private function content_matches($post, $filters) {
        if (isset($filters['title-contains'])) {
            $search_term = strtolower($filters['title-contains']);
            $title = strtolower($post->post_title);
            
            if (strpos($title, $search_term) === false) {
                return false;
            }
        }
        
        if (isset($filters['content-contains'])) {
            $search_term = strtolower($filters['content-contains']);
            $content = strtolower($post->post_content);
            
            if (strpos($content, $search_term) === false) {
                return false;
            }
        }
        
        if (isset($filters['title-regex'])) {
            if (!preg_match('/' . $filters['title-regex'] . '/i', $post->post_title)) {
                return false;
            }
        }
        
        if (isset($filters['content-regex'])) {
            if (!preg_match('/' . $filters['content-regex'] . '/i', $post->post_content)) {
                return false;
            }
        }
        
        if (isset($filters['min-length'])) {
            $content_length = strlen(strip_tags($post->post_content));
            if ($content_length < intval($filters['min-length'])) {
                return false;
            }
        }
        
        if (isset($filters['max-length'])) {
            $content_length = strlen(strip_tags($post->post_content));
            if ($content_length > intval($filters['max-length'])) {
                return false;
            }
        }
        
        return true;
    }
    
    public function filter_by_meta($posts, $meta_filters) {
        if (empty($posts) || empty($meta_filters)) {
            return $posts;
        }
        
        $filtered_posts = array();
        
        foreach ($posts as $post_id) {
            if ($this->meta_matches($post_id, $meta_filters)) {
                $filtered_posts[] = $post_id;
            }
        }
        
        return $filtered_posts;
    }
    
    private function meta_matches($post_id, $filters) {
        foreach ($filters as $meta_key => $criteria) {
            $meta_value = get_post_meta($post_id, $meta_key, true);
            
            if (!$this->evaluate_meta_criteria($meta_value, $criteria)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function evaluate_meta_criteria($meta_value, $criteria) {
        $compare = isset($criteria['compare']) ? $criteria['compare'] : '=';
        $value = $criteria['value'];
        
        switch ($compare) {
            case '=':
                return $meta_value == $value;
            case '!=':
                return $meta_value != $value;
            case '>':
                return floatval($meta_value) > floatval($value);
            case '>=':
                return floatval($meta_value) >= floatval($value);
            case '<':
                return floatval($meta_value) < floatval($value);
            case '<=':
                return floatval($meta_value) <= floatval($value);
            case 'LIKE':
                return strpos($meta_value, $value) !== false;
            case 'NOT LIKE':
                return strpos($meta_value, $value) === false;
            case 'IN':
                return in_array($meta_value, (array) $value);
            case 'NOT IN':
                return !in_array($meta_value, (array) $value);
            case 'EXISTS':
                return !empty($meta_value);
            case 'NOT EXISTS':
                return empty($meta_value);
            default:
                return $meta_value == $value;
        }
    }
    
    public function filter_by_taxonomy($posts, $taxonomy_filters) {
        if (empty($posts) || empty($taxonomy_filters)) {
            return $posts;
        }
        
        $filtered_posts = array();
        
        foreach ($posts as $post_id) {
            if ($this->taxonomy_matches($post_id, $taxonomy_filters)) {
                $filtered_posts[] = $post_id;
            }
        }
        
        return $filtered_posts;
    }
    
    private function taxonomy_matches($post_id, $filters) {
        foreach ($filters as $taxonomy => $criteria) {
            $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            
            if (is_wp_error($terms)) {
                continue;
            }
            
            if (!$this->evaluate_taxonomy_criteria($terms, $criteria)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function evaluate_taxonomy_criteria($post_terms, $criteria) {
        $operator = isset($criteria['operator']) ? strtoupper($criteria['operator']) : 'IN';
        $terms = (array) $criteria['terms'];
        
        switch ($operator) {
            case 'IN':
                return !empty(array_intersect($post_terms, $terms));
            case 'NOT IN':
                return empty(array_intersect($post_terms, $terms));
            case 'AND':
                return count(array_intersect($post_terms, $terms)) === count($terms);
            case 'EXISTS':
                return !empty($post_terms);
            case 'NOT EXISTS':
                return empty($post_terms);
            default:
                return !empty(array_intersect($post_terms, $terms));
        }
    }
    
    public function filter_by_date_advanced($posts, $date_filters) {
        if (empty($posts) || empty($date_filters)) {
            return $posts;
        }
        
        $filtered_posts = array();
        
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            
            if (!$post) {
                continue;
            }
            
            if ($this->advanced_date_matches($post, $date_filters)) {
                $filtered_posts[] = $post_id;
            }
        }
        
        return $filtered_posts;
    }
    
    private function advanced_date_matches($post, $filters) {
        $post_date = strtotime($post->post_date);
        $post_modified = strtotime($post->post_modified);
        
        if (isset($filters['created-days-ago'])) {
            $days_ago = intval($filters['created-days-ago']);
            $cutoff_date = strtotime("-{$days_ago} days");
            
            if ($post_date > $cutoff_date) {
                return false;
            }
        }
        
        if (isset($filters['modified-days-ago'])) {
            $days_ago = intval($filters['modified-days-ago']);
            $cutoff_date = strtotime("-{$days_ago} days");
            
            if ($post_modified > $cutoff_date) {
                return false;
            }
        }
        
        if (isset($filters['not-modified-since'])) {
            $days = intval($filters['not-modified-since']);
            $cutoff_date = strtotime("-{$days} days");
            
            if ($post_modified > $cutoff_date) {
                return false;
            }
        }
        
        if (isset($filters['created-before-date'])) {
            $before_date = strtotime($filters['created-before-date']);
            if ($post_date >= $before_date) {
                return false;
            }
        }
        
        if (isset($filters['created-after-date'])) {
            $after_date = strtotime($filters['created-after-date']);
            if ($post_date <= $after_date) {
                return false;
            }
        }
        
        return true;
    }
    
    public function filter_by_comment_count($posts, $comment_filters) {
        if (empty($posts) || empty($comment_filters)) {
            return $posts;
        }
        
        $filtered_posts = array();
        
        foreach ($posts as $post_id) {
            $comment_count = wp_count_comments($post_id);
            $total_comments = $comment_count->approved + $comment_count->awaiting_moderation;
            
            if ($this->comment_count_matches($total_comments, $comment_filters)) {
                $filtered_posts[] = $post_id;
            }
        }
        
        return $filtered_posts;
    }
    
    private function comment_count_matches($count, $filters) {
        if (isset($filters['min-comments'])) {
            if ($count < intval($filters['min-comments'])) {
                return false;
            }
        }
        
        if (isset($filters['max-comments'])) {
            if ($count > intval($filters['max-comments'])) {
                return false;
            }
        }
        
        if (isset($filters['no-comments']) && $filters['no-comments']) {
            if ($count > 0) {
                return false;
            }
        }
        
        return true;
    }
    
    public function filter_orphaned_posts($posts) {
        if (empty($posts)) {
            return $posts;
        }
        
        $orphaned_posts = array();
        
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            
            if (!$post) {
                continue;
            }
            
            if ($this->is_orphaned($post)) {
                $orphaned_posts[] = $post_id;
            }
        }
        
        return $orphaned_posts;
    }
    
    private function is_orphaned($post) {
        if ($post->post_parent == 0) {
            return false;
        }
        
        $parent = get_post($post->post_parent);
        
        return !$parent || $parent->post_status === 'trash';
    }
    
    public function filter_duplicate_posts($posts, $criteria = 'title') {
        if (empty($posts)) {
            return $posts;
        }
        
        $duplicates = array();
        $seen = array();
        
        foreach ($posts as $post_id) {
            $post = get_post($post_id);
            
            if (!$post) {
                continue;
            }
            
            $key = $this->get_duplicate_key($post, $criteria);
            
            if (isset($seen[$key])) {
                $duplicates[] = $post_id;
            } else {
                $seen[$key] = $post_id;
            }
        }
        
        return $duplicates;
    }
    
    private function get_duplicate_key($post, $criteria) {
        switch ($criteria) {
            case 'title':
                return md5(strtolower(trim($post->post_title)));
            case 'content':
                return md5(trim($post->post_content));
            case 'slug':
                return md5($post->post_name);
            case 'title-content':
                return md5(strtolower(trim($post->post_title)) . trim($post->post_content));
            default:
                return md5(strtolower(trim($post->post_title)));
        }
    }
    
    public function get_available_statuses() {
        global $wp_post_statuses;
        
        $statuses = array();
        
        foreach ($wp_post_statuses as $status => $status_object) {
            $statuses[] = array(
                'name' => $status,
                'label' => $status_object->label,
                'public' => $status_object->public,
                'internal' => $status_object->internal
            );
        }
        
        return $statuses;
    }
}