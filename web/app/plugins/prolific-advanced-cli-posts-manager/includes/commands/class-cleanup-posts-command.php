<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_CLI_Command')) {
    return;
}

class Prolific_Cleanup_Posts_Command extends WP_CLI_Command {
    
    private $manager;
    
    public function __construct() {
        $this->manager = new Prolific_CLI_Posts_Manager();
    }
    
    /**
     * Perform bulk operations on WordPress posts with advanced filtering and safety features.
     *
     * ## OPTIONS
     *
     * [--operation=<operation>]
     * : The operation to perform. Options: list, delete, export-backup, modify
     * ---
     * default: list
     * options:
     *   - list
     *   - delete
     *   - export-backup
     *   - modify
     * ---
     *
     * [--posts]
     * : Target standard WordPress posts
     *
     * [--pages]
     * : Target WordPress pages
     *
     * [--custom-post-type=<type>]
     * : Target specific custom post type
     *
     * [--woocommerce-products]
     * : Target WooCommerce products (requires WooCommerce)
     *
     * [--events]
     * : Target event post types from event plugins
     *
     * [--status=<status>]
     * : Filter by post status (publish, draft, private, trash, any, etc.)
     * ---
     * default: any
     * ---
     *
     * [--date-from=<date>]
     * : Filter posts created from this date (YYYY-MM-DD format)
     *
     * [--date-to=<date>]
     * : Filter posts created up to this date (YYYY-MM-DD format)
     *
     * [--author=<author>]
     * : Filter by author ID, username, or email
     *
     * [--meta-key=<key>]
     * : Filter by custom field key
     *
     * [--meta-value=<value>]
     * : Filter by custom field value (use with --meta-key)
     *
     * [--meta-compare=<compare>]
     * : Comparison operator for meta value (=, !=, >, >=, <, <=, LIKE, etc.)
     * ---
     * default: =
     * ---
     *
     * [--category=<category>]
     * : Filter by category slug(s), comma-separated
     *
     * [--tag=<tag>]
     * : Filter by tag slug(s), comma-separated
     *
     * [--exclude-ids=<ids>]
     * : Exclude specific post IDs, comma-separated
     *
     * [--batch-size=<size>]
     * : Number of posts to process per batch
     * ---
     * default: 100
     * ---
     *
     * [--dry-run]
     * : Show what would be done without making actual changes
     *
     * [--force]
     * : Skip confirmation prompts for destructive operations
     *
     * [--backup]
     * : Force creation of backup before destructive operations
     *
     * [--modify-status=<status>]
     * : Change post status (for modify operation)
     *
     * [--modify-author=<author>]
     * : Change post author (for modify operation)
     *
     * [--modify-meta-key=<key>]
     * : Meta key to modify (for modify operation)
     *
     * [--modify-meta-value=<value>]
     * : New meta value (for modify operation)
     *
     * [--add-category=<category>]
     * : Add category to posts (for modify operation)
     *
     * [--remove-category=<category>]
     * : Remove category from posts (for modify operation)
     *
     * ## EXAMPLES
     *
     *     # List all draft posts
     *     wp prolific cleanup-posts --operation=list --posts --status=draft
     *
     *     # Delete all posts older than 30 days with confirmation
     *     wp prolific cleanup-posts --operation=delete --posts --date-to=2023-01-01
     *
     *     # Delete all WooCommerce products without confirmation (be careful!)
     *     wp prolific cleanup-posts --operation=delete --woocommerce-products --force
     *
     *     # List custom post type 'portfolio' items with specific meta
     *     wp prolific cleanup-posts --operation=list --custom-post-type=portfolio --meta-key=featured --meta-value=no
     *
     *     # Dry run delete of all trashed posts
     *     wp prolific cleanup-posts --operation=delete --status=trash --dry-run
     *
     *     # Export backup of draft posts without deleting them
     *     wp prolific cleanup-posts --operation=export-backup --posts --status=draft
     *
     *     # Change all draft posts to published
     *     wp prolific cleanup-posts --operation=modify --posts --status=draft --modify-status=publish
     *
     *     # Add category to specific posts
     *     wp prolific cleanup-posts --operation=modify --posts --meta-key=featured --meta-value=yes --add-category=featured
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assoc_args) {
        try {
            WP_CLI::line('Prolific Digital Advanced CLI Posts Manager v' . PROLIFIC_CLI_POSTS_MANAGER_VERSION);
            WP_CLI::line('========================================================');
            WP_CLI::line('');
            
            $operation = isset($assoc_args['operation']) ? $assoc_args['operation'] : 'list';
            
            $this->validate_operation($operation);
            $this->validate_target_types($assoc_args);
            
            $results = $this->manager->process_posts($operation, $assoc_args);
            
            if ($operation === 'list' && !empty($results)) {
                WP_CLI::success('Listed ' . count($results) . ' matching posts.');
            } elseif ($operation === 'delete') {
                if (isset($assoc_args['dry-run']) && $assoc_args['dry-run']) {
                    WP_CLI::success('Dry run completed successfully.');
                } else {
                    WP_CLI::success('Delete operation completed successfully.');
                }
            } elseif ($operation === 'modify') {
                if (isset($assoc_args['dry-run']) && $assoc_args['dry-run']) {
                    WP_CLI::success('Dry run completed successfully.');
                } else {
                    WP_CLI::success('Modify operation completed successfully.');
                }
            }
            
        } catch (Exception $e) {
            WP_CLI::error($e->getMessage());
        }
    }
    
    /**
     * List available backup files.
     *
     * ## EXAMPLES
     *
     *     wp prolific cleanup-posts list-backups
     *
     * @subcommand list-backups
     */
    public function list_backups($args, $assoc_args) {
        $safety_manager = new Prolific_Safety_Manager();
        $backups = $safety_manager->list_backups();
        
        if (empty($backups)) {
            WP_CLI::warning('No backup files found.');
            return;
        }
        
        WP_CLI::success('Found ' . count($backups) . ' backup file(s).');
    }
    
    /**
     * Restore posts from a backup file.
     *
     * ## OPTIONS
     *
     * <backup-file>
     * : The backup filename to restore from
     *
     * [--force]
     * : Skip confirmation prompts
     *
     * ## EXAMPLES
     *
     *     wp prolific cleanup-posts restore-backup prolific-backup-2023-12-01-10-30-45.json
     *
     * @subcommand restore-backup
     */
    public function restore_backup($args, $assoc_args) {
        if (empty($args[0])) {
            WP_CLI::error('Backup filename is required.');
        }
        
        $backup_filename = $args[0];
        $upload_dir = wp_upload_dir();
        $backup_file = $upload_dir['basedir'] . '/prolific-backups/' . $backup_filename;
        
        if (!file_exists($backup_file)) {
            WP_CLI::error("Backup file not found: {$backup_filename}");
        }
        
        if (!isset($assoc_args['force']) || !$assoc_args['force']) {
            WP_CLI::confirm("Are you sure you want to restore from backup '{$backup_filename}'? This will create new posts.");
        }
        
        try {
            $safety_manager = new Prolific_Safety_Manager();
            $results = $safety_manager->restore_from_backup($backup_file);
            
            WP_CLI::line('');
            WP_CLI::line('Restore Results:');
            WP_CLI::line('================');
            WP_CLI::success("Successfully restored: {$results['success']} posts");
            
            if ($results['failed'] > 0) {
                WP_CLI::warning("Failed to restore: {$results['failed']} posts");
                
                if (!empty($results['errors'])) {
                    WP_CLI::line('');
                    WP_CLI::line('Errors:');
                    foreach ($results['errors'] as $error) {
                        WP_CLI::line("  - {$error}");
                    }
                }
            }
            
        } catch (Exception $e) {
            WP_CLI::error('Restore failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Show statistics about posts in the database.
     *
     * ## OPTIONS
     *
     * [--post-type=<type>]
     * : Show stats for specific post type
     *
     * ## EXAMPLES
     *
     *     wp prolific cleanup-posts stats
     *     wp prolific cleanup-posts stats --post-type=product
     *
     * @subcommand stats
     */
    public function stats($args, $assoc_args) {
        WP_CLI::line('Post Statistics');
        WP_CLI::line('===============');
        WP_CLI::line('');
        
        $post_types = isset($assoc_args['post-type']) ? 
                     array($assoc_args['post-type']) : 
                     get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            if (!post_type_exists($post_type)) {
                WP_CLI::warning("Post type '{$post_type}' does not exist.");
                continue;
            }
            
            $this->show_post_type_stats($post_type);
        }
    }
    
    private function show_post_type_stats($post_type) {
        $post_type_obj = get_post_type_object($post_type);
        $label = $post_type_obj ? $post_type_obj->labels->name : $post_type;
        
        WP_CLI::line("Post Type: {$label} ({$post_type})");
        WP_CLI::line(str_repeat('-', 40));
        
        $statuses = array('publish', 'draft', 'private', 'trash', 'auto-draft');
        $total = 0;
        
        foreach ($statuses as $status) {
            $count = wp_count_posts($post_type);
            if (isset($count->$status)) {
                $status_count = $count->$status;
                $total += $status_count;
                WP_CLI::line(sprintf('%-12s: %d', ucfirst($status), $status_count));
            }
        }
        
        WP_CLI::line(sprintf('%-12s: %d', 'Total', $total));
        WP_CLI::line('');
    }
    
    /**
     * Clean up WordPress database by removing orphaned data.
     *
     * ## OPTIONS
     *
     * [--orphaned-meta]
     * : Remove orphaned post meta
     *
     * [--orphaned-comments]
     * : Remove orphaned comments
     *
     * [--revisions]
     * : Remove post revisions older than specified days
     *
     * [--revision-days=<days>]
     * : Number of days to keep revisions (default: 30)
     *
     * [--dry-run]
     * : Show what would be cleaned without making changes
     *
     * [--force]
     * : Skip confirmation prompts
     *
     * ## EXAMPLES
     *
     *     wp prolific cleanup-posts cleanup-database --orphaned-meta --dry-run
     *     wp prolific cleanup-posts cleanup-database --revisions --revision-days=7
     *
     * @subcommand cleanup-database
     */
    public function cleanup_database($args, $assoc_args) {
        WP_CLI::line('Database Cleanup Utility');
        WP_CLI::line('========================');
        WP_CLI::line('');
        
        $dry_run = isset($assoc_args['dry-run']) && $assoc_args['dry-run'];
        $force = isset($assoc_args['force']) && $assoc_args['force'];
        
        if (isset($assoc_args['orphaned-meta'])) {
            $this->cleanup_orphaned_meta($dry_run, $force);
        }
        
        if (isset($assoc_args['orphaned-comments'])) {
            $this->cleanup_orphaned_comments($dry_run, $force);
        }
        
        if (isset($assoc_args['revisions'])) {
            $days = isset($assoc_args['revision-days']) ? intval($assoc_args['revision-days']) : 30;
            $this->cleanup_revisions($days, $dry_run, $force);
        }
        
        if (!isset($assoc_args['orphaned-meta']) && 
            !isset($assoc_args['orphaned-comments']) && 
            !isset($assoc_args['revisions'])) {
            WP_CLI::line('No cleanup operations specified. Use --help for available options.');
        }
    }
    
    private function cleanup_orphaned_meta($dry_run, $force) {
        global $wpdb;
        
        WP_CLI::line('Checking for orphaned post meta...');
        
        $query = "
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} pm 
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
            WHERE p.ID IS NULL
        ";
        
        $count = $wpdb->get_var($query);
        
        if ($count == 0) {
            WP_CLI::success('No orphaned post meta found.');
            return;
        }
        
        WP_CLI::line("Found {$count} orphaned post meta entries.");
        
        if ($dry_run) {
            WP_CLI::success("Dry run: Would delete {$count} orphaned post meta entries.");
            return;
        }
        
        if (!$force) {
            WP_CLI::confirm("Delete {$count} orphaned post meta entries?");
        }
        
        $delete_query = "
            DELETE pm 
            FROM {$wpdb->postmeta} pm 
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
            WHERE p.ID IS NULL
        ";
        
        $deleted = $wpdb->query($delete_query);
        
        if ($deleted !== false) {
            WP_CLI::success("Deleted {$deleted} orphaned post meta entries.");
        } else {
            WP_CLI::error('Failed to delete orphaned post meta.');
        }
    }
    
    private function cleanup_orphaned_comments($dry_run, $force) {
        global $wpdb;
        
        WP_CLI::line('Checking for orphaned comments...');
        
        $query = "
            SELECT COUNT(*) 
            FROM {$wpdb->comments} c 
            LEFT JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID 
            WHERE p.ID IS NULL
        ";
        
        $count = $wpdb->get_var($query);
        
        if ($count == 0) {
            WP_CLI::success('No orphaned comments found.');
            return;
        }
        
        WP_CLI::line("Found {$count} orphaned comments.");
        
        if ($dry_run) {
            WP_CLI::success("Dry run: Would delete {$count} orphaned comments.");
            return;
        }
        
        if (!$force) {
            WP_CLI::confirm("Delete {$count} orphaned comments?");
        }
        
        $delete_query = "
            DELETE c 
            FROM {$wpdb->comments} c 
            LEFT JOIN {$wpdb->posts} p ON c.comment_post_ID = p.ID 
            WHERE p.ID IS NULL
        ";
        
        $deleted = $wpdb->query($delete_query);
        
        if ($deleted !== false) {
            WP_CLI::success("Deleted {$deleted} orphaned comments.");
        } else {
            WP_CLI::error('Failed to delete orphaned comments.');
        }
    }
    
    private function cleanup_revisions($days, $dry_run, $force) {
        global $wpdb;
        
        WP_CLI::line("Checking for post revisions older than {$days} days...");
        
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $query = $wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->posts} 
            WHERE post_type = 'revision' 
            AND post_date < %s
        ", $cutoff_date);
        
        $count = $wpdb->get_var($query);
        
        if ($count == 0) {
            WP_CLI::success('No old revisions found.');
            return;
        }
        
        WP_CLI::line("Found {$count} revisions older than {$days} days.");
        
        if ($dry_run) {
            WP_CLI::success("Dry run: Would delete {$count} old revisions.");
            return;
        }
        
        if (!$force) {
            WP_CLI::confirm("Delete {$count} old revisions?");
        }
        
        $delete_query = $wpdb->prepare("
            DELETE FROM {$wpdb->posts} 
            WHERE post_type = 'revision' 
            AND post_date < %s
        ", $cutoff_date);
        
        $deleted = $wpdb->query($delete_query);
        
        if ($deleted !== false) {
            WP_CLI::success("Deleted {$deleted} old revisions.");
        } else {
            WP_CLI::error('Failed to delete old revisions.');
        }
    }
    
    private function validate_operation($operation) {
        $valid_operations = array('list', 'delete', 'export-backup', 'modify');
        
        if (!in_array($operation, $valid_operations)) {
            throw new Exception("Invalid operation '{$operation}'. Valid operations: " . implode(', ', $valid_operations));
        }
    }
    
    private function validate_target_types($args) {
        $has_target = false;
        $target_flags = array('posts', 'pages', 'custom-post-type', 'woocommerce-products', 'events');
        
        foreach ($target_flags as $flag) {
            if (isset($args[$flag])) {
                $has_target = true;
                break;
            }
        }
        
        if (!$has_target) {
            $args['posts'] = true;
        }
    }
}