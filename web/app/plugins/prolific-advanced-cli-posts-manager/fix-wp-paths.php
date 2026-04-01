<?php
/**
 * Quick fix for WordPress path detection
 * Run this on the server to fix the standalone runner
 */

echo "Fixing WordPress path detection...\n";

$plugin_dir = dirname(__FILE__);
$runner_file = $plugin_dir . '/standalone-runner.php';

if (!file_exists($runner_file)) {
    die("Error: standalone-runner.php not found!\n");
}

// Read the current file
$content = file_get_contents($runner_file);

// Find the problematic section and replace it
$old_pattern = '/require_once \$wp_root \. \'\/wp-load\.php\';/';
$new_replacement = 'require_once $wp_load_path;';

// Also fix the wp-load path detection
$old_section = '/\/\/ Find WordPress root.*?require_once \$wp_root \. \'\/wp-load\.php\';/s';

$new_section = '// Find WordPress root - handle different directory structures
$wp_root = dirname(__FILE__);
$wp_config_found = false;
$wp_load_found = false;

// First, try to find wp-config.php
while (!file_exists($wp_root . \'/wp-config.php\') && $wp_root !== \'/\') {
    $wp_root = dirname($wp_root);
}

if (file_exists($wp_root . \'/wp-config.php\')) {
    $wp_config_found = true;
}

// Now find wp-load.php (might be in a different location)
$wp_load_paths = array(
    $wp_root . \'/wp-load.php\',                    // Standard location
    $wp_root . \'/wp/wp-load.php\',                 // Bedrock/custom structure
    dirname($wp_root) . \'/wp/wp-load.php\',       // Alternative structure
    $wp_root . \'/wordpress/wp-load.php\',         // Subdirectory install
    $wp_root . \'/public/wp-load.php\',            // Public folder structure
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
define(\'WP_USE_THEMES\', false);
define(\'SHORTINIT\', false);

// Disable WooCommerce CLI runner to prevent conflicts
if (!defined(\'WC_CLI_RUNNER_DISABLED\')) {
    define(\'WC_CLI_RUNNER_DISABLED\', true);
}

require_once $wp_root . \'/wp-config.php\';
require_once $wp_load_path;';

// Apply the fix
$fixed_content = preg_replace($old_section, $new_section, $content);

if ($fixed_content && $fixed_content !== $content) {
    if (file_put_contents($runner_file, $fixed_content)) {
        echo "✅ Fixed WordPress path detection in standalone-runner.php\n";
        echo "Now try: prolific --help\n";
    } else {
        echo "❌ Failed to write the fixed file\n";
    }
} else {
    echo "⚠️  No changes needed or unable to apply fix\n";
    echo "The file might already be correct\n";
}

echo "Done.\n";
?>