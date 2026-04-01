<?php
/**
 * Standalone Prolific CLI Posts Manager Runner
 * Bypasses WP-CLI initialization issues with WooCommerce
 * 
 * Usage: php standalone-runner.php --operation=list --posts
 */

// Prevent direct access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

// Find WordPress root - handle different directory structures
$wp_root = dirname(__FILE__);
$wp_config_found = false;
$wp_load_found = false;

// First, try to find wp-config.php
while (!file_exists($wp_root . '/wp-config.php') && $wp_root !== '/') {
    $wp_root = dirname($wp_root);
}

if (file_exists($wp_root . '/wp-config.php')) {
    $wp_config_found = true;
} else {
    // Check specific paths for user's structure
    $wp_config_paths = array(
        '/files/web/wp/wp-config.php',
        '/files/wp-config.php',
        '/files/web/wp-config.php'
    );
    
    foreach ($wp_config_paths as $config_path) {
        if (file_exists($config_path)) {
            $wp_root = dirname($config_path);
            $wp_config_found = true;
            break;
        }
    }
}

// Now find wp-load.php (might be in a different location)
$wp_load_paths = array(
    $wp_root . '/wp-load.php',                    // Standard location
    $wp_root . '/wp/wp-load.php',                 // Bedrock/custom structure
    dirname($wp_root) . '/wp/wp-load.php',       // Alternative structure
    $wp_root . '/wordpress/wp-load.php',         // Subdirectory install
    $wp_root . '/public/wp-load.php',            // Public folder structure
    '/files/web/wp/wp-load.php',                 // User's specific structure
    '/files/wp/wp-load.php',                     // Alternative /files structure
    dirname(dirname($wp_root)) . '/web/wp/wp-load.php',  // Up two levels then web/wp
    dirname(dirname(dirname($wp_root))) . '/web/wp/wp-load.php',  // Up three levels then web/wp
);

$wp_load_path = null;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        $wp_load_path = $path;
        $wp_load_found = true;
        break;
    }
}

if (!$wp_config_found) {
    die("Error: Could not find wp-config.php. Please run this script from within a WordPress installation.\n");
}

if (!$wp_load_found) {
    die("Error: Could not find wp-load.php. Tried these locations:\n" . implode("\n", $wp_load_paths) . "\n");
}

// Load WordPress
define('WP_USE_THEMES', false);
define('SHORTINIT', false);

// Disable WooCommerce CLI runner to prevent conflicts
if (!defined('WC_CLI_RUNNER_DISABLED')) {
    define('WC_CLI_RUNNER_DISABLED', true);
}

require_once $wp_root . '/wp-config.php';
require_once $wp_load_path;

// Override WooCommerce CLI runner if it exists
if (class_exists('WC_CLI_Runner')) {
    remove_action('plugins_loaded', array('WC_CLI_Runner', 'after_wp_load'));
}

// Parse command line arguments
$args = array();
$assoc_args = array();

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    
    if (strpos($arg, '--') === 0) {
        $parts = explode('=', substr($arg, 2), 2);
        if (count($parts) === 2) {
            $assoc_args[$parts[0]] = $parts[1];
        } else {
            $assoc_args[$parts[0]] = true;
        }
    } else {
        $args[] = $arg;
    }
}

// Mock WP_CLI class for our plugin
if (!class_exists('WP_CLI')) {
    class WP_CLI {
        public static function line($message) {
            echo $message . "\n";
        }
        
        public static function success($message) {
            echo "\033[32mSuccess: " . $message . "\033[0m\n";
        }
        
        public static function warning($message) {
            echo "\033[33mWarning: " . $message . "\033[0m\n";
        }
        
        public static function error($message) {
            echo "\033[31mError: " . $message . "\033[0m\n";
            exit(1);
        }
        
        public static function log($message) {
            echo $message . "\n";
        }
        
        public static function confirm($message) {
            echo $message . " ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            fclose($handle);
            $response = strtolower(trim($line));
            
            if ($response !== 'y' && $response !== 'yes') {
                self::error('Operation cancelled by user.');
            }
        }
    }
}

// Define constants if not already defined
if (!defined('WP_CLI')) {
    define('WP_CLI', true);
}

// Initialize our plugin
$plugin_dir = plugin_dir_path(__FILE__);

// Include our plugin files
require_once $plugin_dir . 'includes/interfaces/interface-post-operation.php';
require_once $plugin_dir . 'includes/utilities/class-safety-manager.php';
require_once $plugin_dir . 'includes/utilities/class-post-query-builder.php';
require_once $plugin_dir . 'includes/utilities/class-post-filter.php';
require_once $plugin_dir . 'includes/class-prolific-cli-posts-manager.php';

// Create our command instance
class Prolific_Standalone_Runner {
    private $manager;
    
    public function __construct() {
        $this->manager = new Prolific_CLI_Posts_Manager();
    }
    
    public function run($args, $assoc_args) {
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
    
    public function show_help() {
        echo "Prolific CLI Posts Manager - Standalone Runner\n";
        echo "==============================================\n\n";
        echo "Usage: php standalone-runner.php [options]\n\n";
        echo "Operations:\n";
        echo "  --operation=list          List matching posts (default)\n";
        echo "  --operation=delete        Delete matching posts\n";
        echo "  --operation=export-backup Create backup without deleting\n";
        echo "  --operation=modify        Modify matching posts\n\n";
        echo "Targets:\n";
        echo "  --posts                   Target WordPress posts\n";
        echo "  --pages                   Target WordPress pages\n";
        echo "  --custom-post-type=TYPE   Target specific post type\n";
        echo "  --woocommerce-products    Target WooCommerce products\n";
        echo "  --events                  Target event post types\n\n";
        echo "Filters:\n";
        echo "  --status=STATUS           Filter by post status\n";
        echo "  --date-from=YYYY-MM-DD    Filter from date\n";
        echo "  --date-to=YYYY-MM-DD      Filter to date\n";
        echo "  --author=AUTHOR           Filter by author\n";
        echo "  --meta-key=KEY            Filter by meta key\n";
        echo "  --meta-value=VALUE        Filter by meta value\n";
        echo "  --category=SLUG           Filter by category\n";
        echo "  --tag=SLUG                Filter by tag\n\n";
        echo "Options:\n";
        echo "  --dry-run                 Preview without making changes\n";
        echo "  --force                   Skip confirmations\n";
        echo "  --batch-size=N            Processing batch size (default: 100)\n";
        echo "  --limit=N                 Maximum number of posts to process\n\n";
        echo "Examples:\n";
        echo "  php standalone-runner.php --posts\n";
        echo "  php standalone-runner.php --operation=delete --posts --status=draft --dry-run\n";
        echo "  php standalone-runner.php --operation=delete --posts --status=draft --limit=50\n";
        echo "  php standalone-runner.php --operation=export-backup --posts --category=news\n";
        echo "  php standalone-runner.php --woocommerce-products --limit=5  # Debug WooCommerce\n\n";
    }
}

// Show help if requested or no arguments
if (empty($assoc_args) || isset($assoc_args['help']) || isset($assoc_args['h'])) {
    $runner = new Prolific_Standalone_Runner();
    $runner->show_help();
    exit(0);
}

// Run the command
$runner = new Prolific_Standalone_Runner();
$runner->run($args, $assoc_args);