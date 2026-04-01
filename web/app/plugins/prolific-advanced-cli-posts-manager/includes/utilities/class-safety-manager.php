<?php

if (!defined('ABSPATH')) {
    exit;
}

class Prolific_Safety_Manager {
    
    private $backup_dir;
    private $log_file;
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->backup_dir = $upload_dir['basedir'] . '/prolific-backups';
        $this->log_file = $upload_dir['basedir'] . '/prolific-logs/prolific-' . date('Y-m-d') . '.log';
        $this->ensure_directories_exist();
    }
    
    private function ensure_directories_exist() {
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
            file_put_contents($this->backup_dir . '/.htaccess', 'deny from all');
        }
    }
    
    public function create_backup($posts) {
        if (empty($posts)) {
            throw new Exception('No posts provided for backup');
        }
        
        $timestamp = date('Y-m-d-H-i-s');
        $backup_filename = "prolific-backup-{$timestamp}.json";
        $backup_filepath = $this->backup_dir . '/' . $backup_filename;
        
        $backup_data = array(
            'timestamp' => $timestamp,
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => PROLIFIC_CLI_POSTS_MANAGER_VERSION,
            'site_url' => get_site_url(),
            'total_posts' => count($posts),
            'posts' => array()
        );
        
        $batch_size = 50;
        $batches = array_chunk($posts, $batch_size);
        
        foreach ($batches as $batch_index => $batch) {
            WP_CLI::line("Creating backup batch " . ($batch_index + 1) . "/" . count($batches) . "...");
            
            foreach ($batch as $post_id) {
                $post_data = $this->get_post_backup_data($post_id);
                if ($post_data) {
                    $backup_data['posts'][] = $post_data;
                }
            }
        }
        
        $json_data = json_encode($backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($json_data === false) {
            throw new Exception('Failed to encode backup data to JSON');
        }
        
        $bytes_written = file_put_contents($backup_filepath, $json_data, LOCK_EX);
        
        if ($bytes_written === false) {
            throw new Exception('Failed to write backup file');
        }
        
        $this->log('INFO', "Backup created successfully: {$backup_filename}", array(
            'file_size' => $this->format_bytes(filesize($backup_filepath)),
            'post_count' => count($backup_data['posts'])
        ));
        
        WP_CLI::success("Backup created: {$backup_filename} (" . $this->format_bytes($bytes_written) . ")");
        
        $this->cleanup_old_backups();
        
        return $backup_filepath;
    }
    
    private function get_post_backup_data($post_id) {
        $post = get_post($post_id);
        
        if (!$post) {
            $this->log('WARNING', "Post not found for backup: {$post_id}");
            return null;
        }
        
        $post_data = array(
            'ID' => $post->ID,
            'post_author' => $post->post_author,
            'post_date' => $post->post_date,
            'post_date_gmt' => $post->post_date_gmt,
            'post_content' => $post->post_content,
            'post_title' => $post->post_title,
            'post_excerpt' => $post->post_excerpt,
            'post_status' => $post->post_status,
            'comment_status' => $post->comment_status,
            'ping_status' => $post->ping_status,
            'post_password' => $post->post_password,
            'post_name' => $post->post_name,
            'to_ping' => $post->to_ping,
            'pinged' => $post->pinged,
            'post_modified' => $post->post_modified,
            'post_modified_gmt' => $post->post_modified_gmt,
            'post_content_filtered' => $post->post_content_filtered,
            'post_parent' => $post->post_parent,
            'guid' => $post->guid,
            'menu_order' => $post->menu_order,
            'post_type' => $post->post_type,
            'post_mime_type' => $post->post_mime_type,
            'comment_count' => $post->comment_count
        );
        
        $post_meta = get_post_meta($post_id);
        if (!empty($post_meta)) {
            $post_data['post_meta'] = $post_meta;
        }
        
        $taxonomies = get_object_taxonomies($post->post_type);
        if (!empty($taxonomies)) {
            $post_data['taxonomies'] = array();
            foreach ($taxonomies as $taxonomy) {
                $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'all'));
                if (!empty($terms) && !is_wp_error($terms)) {
                    $post_data['taxonomies'][$taxonomy] = $terms;
                }
            }
        }
        
        $comments = get_comments(array(
            'post_id' => $post_id,
            'status' => 'all'
        ));
        
        if (!empty($comments)) {
            $post_data['comments'] = array();
            foreach ($comments as $comment) {
                $post_data['comments'][] = array(
                    'comment_ID' => $comment->comment_ID,
                    'comment_post_ID' => $comment->comment_post_ID,
                    'comment_author' => $comment->comment_author,
                    'comment_author_email' => $comment->comment_author_email,
                    'comment_author_url' => $comment->comment_author_url,
                    'comment_author_IP' => $comment->comment_author_IP,
                    'comment_date' => $comment->comment_date,
                    'comment_date_gmt' => $comment->comment_date_gmt,
                    'comment_content' => $comment->comment_content,
                    'comment_karma' => $comment->comment_karma,
                    'comment_approved' => $comment->comment_approved,
                    'comment_agent' => $comment->comment_agent,
                    'comment_type' => $comment->comment_type,
                    'comment_parent' => $comment->comment_parent,
                    'user_id' => $comment->user_id
                );
            }
        }
        
        return $post_data;
    }
    
    public function restore_from_backup($backup_file) {
        if (!file_exists($backup_file)) {
            throw new Exception("Backup file not found: {$backup_file}");
        }
        
        $backup_content = file_get_contents($backup_file);
        if ($backup_content === false) {
            throw new Exception("Failed to read backup file: {$backup_file}");
        }
        
        $backup_data = json_decode($backup_content, true);
        if ($backup_data === null) {
            throw new Exception("Invalid JSON in backup file: {$backup_file}");
        }
        
        if (!isset($backup_data['posts']) || !is_array($backup_data['posts'])) {
            throw new Exception("Invalid backup data structure");
        }
        
        $results = array('success' => 0, 'failed' => 0, 'errors' => array());
        $total_posts = count($backup_data['posts']);
        
        WP_CLI::line("Restoring {$total_posts} posts from backup...");
        
        foreach ($backup_data['posts'] as $index => $post_data) {
            $progress = sprintf('[%d/%d]', $index + 1, $total_posts);
            WP_CLI::line("Restoring post {$progress}: {$post_data['post_title']}");
            
            try {
                $restored_post_id = $this->restore_single_post($post_data);
                if ($restored_post_id) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to restore post: {$post_data['post_title']}";
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error restoring post '{$post_data['post_title']}': " . $e->getMessage();
                $this->log('ERROR', "Restore error for post '{$post_data['post_title']}'", array('error' => $e->getMessage()));
            }
        }
        
        $this->log('INFO', "Backup restoration completed", $results);
        return $results;
    }
    
    private function restore_single_post($post_data) {
        $post_args = array(
            'post_author' => $post_data['post_author'],
            'post_date' => $post_data['post_date'],
            'post_date_gmt' => $post_data['post_date_gmt'],
            'post_content' => $post_data['post_content'],
            'post_title' => $post_data['post_title'],
            'post_excerpt' => $post_data['post_excerpt'],
            'post_status' => $post_data['post_status'],
            'comment_status' => $post_data['comment_status'],
            'ping_status' => $post_data['ping_status'],
            'post_password' => $post_data['post_password'],
            'post_name' => $post_data['post_name'],
            'to_ping' => $post_data['to_ping'],
            'pinged' => $post_data['pinged'],
            'post_parent' => $post_data['post_parent'],
            'menu_order' => $post_data['menu_order'],
            'post_type' => $post_data['post_type'],
            'post_mime_type' => $post_data['post_mime_type'],
            'import_id' => $post_data['ID']
        );
        
        $new_post_id = wp_insert_post($post_args);
        
        if (is_wp_error($new_post_id)) {
            throw new Exception($new_post_id->get_error_message());
        }
        
        if (isset($post_data['post_meta']) && is_array($post_data['post_meta'])) {
            foreach ($post_data['post_meta'] as $meta_key => $meta_values) {
                foreach ($meta_values as $meta_value) {
                    add_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value));
                }
            }
        }
        
        if (isset($post_data['taxonomies']) && is_array($post_data['taxonomies'])) {
            foreach ($post_data['taxonomies'] as $taxonomy => $terms) {
                $term_ids = array();
                foreach ($terms as $term) {
                    $existing_term = get_term_by('slug', $term->slug, $taxonomy);
                    if ($existing_term) {
                        $term_ids[] = $existing_term->term_id;
                    } else {
                        $new_term = wp_insert_term($term->name, $taxonomy, array(
                            'slug' => $term->slug,
                            'description' => $term->description
                        ));
                        if (!is_wp_error($new_term)) {
                            $term_ids[] = $new_term['term_id'];
                        }
                    }
                }
                
                if (!empty($term_ids)) {
                    wp_set_object_terms($new_post_id, $term_ids, $taxonomy);
                }
            }
        }
        
        return $new_post_id;
    }
    
    public function list_backups($show_cli_output = true) {
        // Ensure backup directory exists
        if (!is_dir($this->backup_dir)) {
            if ($show_cli_output && defined('WP_CLI') && WP_CLI) {
                WP_CLI::line('Backup directory does not exist: ' . $this->backup_dir);
            }
            return array();
        }
        
        // Check if directory is readable
        if (!is_readable($this->backup_dir)) {
            throw new Exception('Backup directory is not readable: ' . $this->backup_dir);
        }
        
        $backups = glob($this->backup_dir . '/prolific-backup-*.json');
        
        if ($backups === false) {
            throw new Exception('Failed to scan backup directory: ' . $this->backup_dir);
        }
        
        if (empty($backups)) {
            if ($show_cli_output && defined('WP_CLI') && WP_CLI) {
                WP_CLI::line('No backups found.');
            }
            return array();
        }
        
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $backup_list = array();
        
        if ($show_cli_output && defined('WP_CLI') && WP_CLI) {
            WP_CLI::line('Available backups:');
            WP_CLI::line('==================');
        }
        
        foreach ($backups as $backup_file) {
            try {
                // Skip if file doesn't exist or isn't readable
                if (!file_exists($backup_file) || !is_readable($backup_file)) {
                    continue;
                }
                
                $filename = basename($backup_file);
                $file_size = filesize($backup_file);
                $file_time = filemtime($backup_file);
                
                if ($file_size === false || $file_time === false) {
                    continue; // Skip files we can't get info for
                }
                
                $filesize = $this->format_bytes($file_size);
                $created = date('Y-m-d H:i:s', $file_time);
                
                $backup_info = $this->get_backup_info($backup_file);
                $post_count = isset($backup_info['total_posts']) ? $backup_info['total_posts'] : 'Unknown';
                
                if ($show_cli_output && defined('WP_CLI') && WP_CLI) {
                    WP_CLI::line("{$filename} | {$created} | {$filesize} | {$post_count} posts");
                }
                
                $backup_list[] = array(
                    'filename' => $filename,
                    'filepath' => $backup_file,
                    'created' => $created,
                    'size' => $filesize,
                    'post_count' => $post_count,
                    'raw_size' => $file_size,
                    'timestamp' => $file_time
                );
            } catch (Exception $e) {
                // Log error but continue processing other backups
                if ($show_cli_output && defined('WP_CLI') && WP_CLI) {
                    WP_CLI::warning("Error processing backup file {$backup_file}: " . $e->getMessage());
                }
                continue;
            }
        }
        
        return $backup_list;
    }
    
    private function get_backup_info($backup_file) {
        $content = file_get_contents($backup_file);
        if ($content === false) {
            return array();
        }
        
        $data = json_decode($content, true);
        if ($data === null) {
            return array();
        }
        
        return $data;
    }
    
    private function cleanup_old_backups() {
        $retention_days = get_option('prolific_cli_posts_backup_retention_days', 30);
        $cutoff_time = time() - ($retention_days * 24 * 60 * 60);
        
        $backups = glob($this->backup_dir . '/prolific-backup-*.json');
        $deleted_count = 0;
        
        foreach ($backups as $backup_file) {
            if (filemtime($backup_file) < $cutoff_time) {
                if (unlink($backup_file)) {
                    $deleted_count++;
                    $this->log('INFO', "Deleted old backup: " . basename($backup_file));
                }
            }
        }
        
        if ($deleted_count > 0) {
            WP_CLI::line("Cleaned up {$deleted_count} old backup(s).");
        }
    }
    
    private function format_bytes($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    private function log($level, $message, $context = array()) {
        $timestamp = date('Y-m-d H:i:s');
        $context_str = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $log_entry = "[{$timestamp}] [{$level}] {$message}{$context_str}" . PHP_EOL;
        
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
}