<?php

if (!defined('ABSPATH')) {
    exit;
}

class Prolific_CLI_Posts_Manager {
    
    private $safety_manager;
    private $query_builder;
    private $post_filter;
    private $log_file;
    
    public function __construct() {
        $this->safety_manager = new Prolific_Safety_Manager();
        $this->query_builder = new Prolific_Post_Query_Builder();
        $this->post_filter = new Prolific_Post_Filter();
        $this->init_logging();
    }
    
    private function init_logging() {
        $upload_dir = wp_upload_dir();
        $logs_dir = $upload_dir['basedir'] . '/prolific-logs';
        $this->log_file = $logs_dir . '/prolific-' . date('Y-m-d') . '.log';
    }
    
    public function process_posts($operation, $args) {
        try {
            $this->log('INFO', 'Starting post processing operation: ' . $operation, $args);
            
            if (!$this->validate_permissions($operation)) {
                throw new Exception('Insufficient permissions for this operation');
            }
            
            $this->validate_args($args);
            
            $posts = $this->get_posts($args);
            $this->log('INFO', 'Found ' . count($posts) . ' posts matching criteria');
            
            if (empty($posts)) {
                WP_CLI::warning('No posts found matching the specified criteria.');
                return array();
            }
            
            $operation_summary = $this->get_operation_summary($operation, $posts, $args);
            $this->display_summary($operation_summary);
            
            if (isset($args['dry-run']) && $args['dry-run']) {
                WP_CLI::success('Dry run completed. No changes were made.');
                return $posts;
            }
            
            if ($this->requires_confirmation($operation, $args)) {
                $this->confirm_operation($operation_summary);
            }
            
            $backup_file = null;
            if ($this->is_destructive_operation($operation) || $operation === 'export-backup') {
                $backup_file = $this->safety_manager->create_backup($posts);
                $this->log('INFO', 'Backup created: ' . $backup_file);
                
                if ($operation === 'export-backup') {
                    WP_CLI::success('Export backup completed successfully.');
                    return array('success' => count($posts), 'failed' => 0, 'backup_file' => $backup_file);
                }
            }
            
            $results = $this->execute_operation($operation, $posts, $args);
            
            $this->log('INFO', 'Operation completed successfully', $results);
            return $results;
            
        } catch (Exception $e) {
            $this->log('ERROR', 'Operation failed: ' . $e->getMessage(), $args);
            WP_CLI::error($e->getMessage());
        }
    }
    
    private function validate_permissions($operation) {
        if (defined('WP_CLI') && WP_CLI) {
            return true;
        }
        
        $required_caps = $this->get_required_capabilities($operation);
        
        foreach ($required_caps as $cap) {
            if (!current_user_can($cap)) {
                return false;
            }
        }
        
        return true;
    }
    
    private function get_required_capabilities($operation) {
        switch ($operation) {
            case 'delete':
                return array('manage_options', 'delete_posts', 'delete_pages', 'delete_private_posts');
            case 'list':
                return array('read');
            default:
                return array('manage_options');
        }
    }
    
    private function validate_args($args) {
        if (isset($args['date-from']) && !$this->is_valid_date($args['date-from'])) {
            throw new Exception('Invalid date format for --date-from. Use YYYY-MM-DD format.');
        }
        
        if (isset($args['date-to']) && !$this->is_valid_date($args['date-to'])) {
            throw new Exception('Invalid date format for --date-to. Use YYYY-MM-DD format.');
        }
        
        if (isset($args['batch-size']) && (!is_numeric($args['batch-size']) || $args['batch-size'] < 1)) {
            throw new Exception('Batch size must be a positive integer.');
        }
        
        if (isset($args['custom-post-type'])) {
            $post_type = $args['custom-post-type'];
            if (!post_type_exists($post_type)) {
                throw new Exception("Post type '{$post_type}' does not exist.");
            }
        }
    }
    
    private function is_valid_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    private function get_posts($args) {
        $query_args = $this->query_builder->build_query($args);
        
        if (empty($query_args['post_type'])) {
            WP_CLI::warning('No post types specified or found.');
            return array();
        }
        
        // Debug output
        WP_CLI::line('Searching for post types: ' . implode(', ', $query_args['post_type']));
        WP_CLI::line('Posts per page: ' . $query_args['posts_per_page']);
        
        $posts = get_posts($query_args);
        WP_CLI::line('Found ' . count($posts) . ' posts before status filtering');
        
        // Only apply custom status filtering if WordPress didn't handle it
        if (isset($args['status']) && $query_args['post_status'] === 'any') {
            WP_CLI::line('Filtering by custom status: ' . $args['status']);
            $posts_before_filter = count($posts);
            $posts = $this->post_filter->filter_by_status($posts, $args['status']);
            WP_CLI::line('After status filtering: ' . count($posts) . ' posts (filtered out ' . ($posts_before_filter - count($posts)) . ')');
            
            // Debug: Show a few post statuses
            if (count($posts) === 0 && $posts_before_filter > 0) {
                WP_CLI::line('Debug: Checking actual post statuses...');
                $all_posts = get_posts($query_args);
                $status_counts = array();
                foreach (array_slice($all_posts, 0, 10) as $post_id) {
                    $status = get_post_status($post_id);
                    if (!isset($status_counts[$status])) {
                        $status_counts[$status] = 0;
                    }
                    $status_counts[$status]++;
                }
                foreach ($status_counts as $status => $count) {
                    WP_CLI::line("  Found $count posts with status: '$status'");
                }
            }
        } elseif (isset($args['status'])) {
            WP_CLI::line('Used WordPress native status filtering for: ' . $args['status']);
        }
        
        // Apply limit after status filtering (for custom statuses)
        if (isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0) {
            $limit = (int) $args['limit'];
            if (count($posts) > $limit) {
                WP_CLI::line('Applying limit: ' . $limit . ' posts (reduced from ' . count($posts) . ')');
                $posts = array_slice($posts, 0, $limit);
            }
        }
        
        return $posts;
    }
    
    private function get_operation_summary($operation, $posts, $args) {
        $summary = array(
            'operation' => $operation,
            'post_count' => count($posts),
            'post_types' => $this->get_post_types_summary($posts),
            'estimated_time' => $this->estimate_operation_time(count($posts)),
            'args' => $args
        );
        
        return $summary;
    }
    
    private function get_post_types_summary($posts) {
        $types = array();
        foreach ($posts as $post_id) {
            $post_type = get_post_type($post_id);
            if (!isset($types[$post_type])) {
                $types[$post_type] = 0;
            }
            $types[$post_type]++;
        }
        return $types;
    }
    
    private function estimate_operation_time($post_count) {
        $batch_size = get_option('prolific_cli_posts_batch_size', 100);
        $batches = ceil($post_count / $batch_size);
        return $batches * 2;
    }
    
    private function display_summary($summary) {
        WP_CLI::line('');
        WP_CLI::line('Operation Summary:');
        WP_CLI::line('==================');
        WP_CLI::line('Operation: ' . ucfirst($summary['operation']));
        WP_CLI::line('Total Posts: ' . $summary['post_count']);
        WP_CLI::line('Estimated Time: ~' . $summary['estimated_time'] . ' seconds');
        WP_CLI::line('');
        WP_CLI::line('Post Types:');
        foreach ($summary['post_types'] as $type => $count) {
            WP_CLI::line("  - {$type}: {$count}");
        }
        WP_CLI::line('');
    }
    
    private function requires_confirmation($operation, $args) {
        if (isset($args['force']) && $args['force']) {
            return false;
        }
        
        return $this->is_destructive_operation($operation) && 
               get_option('prolific_cli_posts_require_confirmation', true);
    }
    
    private function is_destructive_operation($operation) {
        return in_array($operation, array('delete', 'modify'));
    }
    
    private function confirm_operation($summary) {
        $message = sprintf(
            'Are you sure you want to %s %d posts? This action cannot be undone. [y/N]: ',
            $summary['operation'],
            $summary['post_count']
        );
        
        fwrite(STDOUT, $message);
        $confirmation = strtolower(trim(fgets(STDIN)));
        
        if ($confirmation !== 'y' && $confirmation !== 'yes') {
            WP_CLI::error('Operation cancelled by user.');
        }
    }
    
    private function execute_operation($operation, $posts, $args) {
        $batch_size = isset($args['batch-size']) ? 
                     intval($args['batch-size']) : 
                     get_option('prolific_cli_posts_batch_size', 100);
        
        $batches = array_chunk($posts, $batch_size);
        $total_batches = count($batches);
        $results = array('success' => 0, 'failed' => 0, 'errors' => array());
        
        foreach ($batches as $batch_index => $batch) {
            $progress = sprintf('[%d/%d]', $batch_index + 1, $total_batches);
            WP_CLI::line("Processing batch {$progress}...");
            
            $batch_results = $this->process_batch($operation, $batch, $args);
            
            $results['success'] += $batch_results['success'];
            $results['failed'] += $batch_results['failed'];
            $results['errors'] = array_merge($results['errors'], $batch_results['errors']);
            
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
            }
            
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }
        
        $this->display_results($results);
        return $results;
    }
    
    private function process_batch($operation, $posts, $args) {
        $results = array('success' => 0, 'failed' => 0, 'errors' => array());
        
        foreach ($posts as $post_id) {
            try {
                switch ($operation) {
                    case 'delete':
                        $success = wp_delete_post($post_id, true);
                        break;
                    case 'list':
                        $success = true;
                        $this->display_post_info($post_id);
                        break;
                    case 'export-backup':
                        $success = true;
                        break;
                    case 'modify':
                        $success = $this->modify_post($post_id, $args);
                        break;
                    default:
                        throw new Exception("Unknown operation: {$operation}");
                }
                
                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to {$operation} post ID: {$post_id}";
                }
                
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error processing post ID {$post_id}: " . $e->getMessage();
                $this->log('ERROR', "Batch processing error for post {$post_id}", array('error' => $e->getMessage()));
            }
        }
        
        return $results;
    }
    
    private function display_post_info($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        WP_CLI::line(sprintf(
            'ID: %d | Type: %s | Status: %s | Title: %s | Date: %s',
            $post->ID,
            $post->post_type,
            $post->post_status,
            $post->post_title,
            $post->post_date
        ));
    }
    
    private function modify_post($post_id, $args) {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        $updates = array();
        $success = true;
        
        if (isset($args['modify-status'])) {
            $new_status = sanitize_key($args['modify-status']);
            if ($new_status !== $post->post_status) {
                $updates['ID'] = $post_id;
                $updates['post_status'] = $new_status;
            }
        }
        
        if (isset($args['modify-author'])) {
            $author_id = $this->get_author_id($args['modify-author']);
            if ($author_id && $author_id !== $post->post_author) {
                $updates['ID'] = $post_id;
                $updates['post_author'] = $author_id;
            }
        }
        
        if (!empty($updates)) {
            $result = wp_update_post($updates, true);
            if (is_wp_error($result)) {
                $success = false;
            }
        }
        
        if (isset($args['modify-meta-key']) && isset($args['modify-meta-value'])) {
            $meta_key = sanitize_key($args['modify-meta-key']);
            $meta_value = $args['modify-meta-value'];
            update_post_meta($post_id, $meta_key, $meta_value);
        }
        
        if (isset($args['add-category'])) {
            $this->modify_post_categories($post_id, $args['add-category'], 'add');
        }
        
        if (isset($args['remove-category'])) {
            $this->modify_post_categories($post_id, $args['remove-category'], 'remove');
        }
        
        return $success;
    }
    
    private function get_author_id($author) {
        if (is_numeric($author)) {
            $user = get_user_by('ID', intval($author));
            return $user ? $user->ID : false;
        }
        
        $user = get_user_by('login', $author);
        if (!$user) {
            $user = get_user_by('email', $author);
        }
        
        return $user ? $user->ID : false;
    }
    
    private function modify_post_categories($post_id, $categories, $action) {
        $category_slugs = array_map('trim', explode(',', $categories));
        $category_ids = array();
        
        foreach ($category_slugs as $slug) {
            $term = get_term_by('slug', $slug, 'category');
            if ($term) {
                $category_ids[] = $term->term_id;
            }
        }
        
        if (empty($category_ids)) {
            return;
        }
        
        $current_categories = wp_get_post_categories($post_id);
        
        if ($action === 'add') {
            $new_categories = array_unique(array_merge($current_categories, $category_ids));
        } else {
            $new_categories = array_diff($current_categories, $category_ids);
        }
        
        wp_set_post_categories($post_id, $new_categories);
    }
    
    private function display_results($results) {
        WP_CLI::line('');
        WP_CLI::line('Operation Results:');
        WP_CLI::line('==================');
        WP_CLI::success("Successfully processed: {$results['success']}");
        
        if ($results['failed'] > 0) {
            WP_CLI::warning("Failed: {$results['failed']}");
            
            if (!empty($results['errors'])) {
                WP_CLI::line('');
                WP_CLI::line('Errors:');
                foreach ($results['errors'] as $error) {
                    WP_CLI::line("  - {$error}");
                }
            }
        }
    }
    
    public function log($level, $message, $context = array()) {
        $log_level = get_option('prolific_cli_posts_log_level', 'INFO');
        $levels = array('DEBUG' => 0, 'INFO' => 1, 'WARNING' => 2, 'ERROR' => 3);
        
        if ($levels[$level] < $levels[$log_level]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $log_entry = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}