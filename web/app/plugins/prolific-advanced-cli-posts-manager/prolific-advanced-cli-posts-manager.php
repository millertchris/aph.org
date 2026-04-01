<?php
/**
 * Plugin Name: Prolific Digital Advanced CLI Posts Manager
 * Plugin URI: https://prolificdigital.com
 * Description: Advanced WordPress CLI tool for bulk post management operations with safety features and comprehensive filtering options.
 * Version: 1.0.0
 * Author: Prolific Digital
 * Author URI: https://prolificdigital.com
 * Text Domain: prolific-advanced-cli-posts-manager
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PROLIFIC_CLI_POSTS_MANAGER_VERSION', '1.0.0');
define('PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_FILE', __FILE__);
define('PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PROLIFIC_CLI_POSTS_MANAGER_INCLUDES_DIR', PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR . 'includes/');
define('PROLIFIC_CLI_POSTS_MANAGER_COMMANDS_DIR', PROLIFIC_CLI_POSTS_MANAGER_INCLUDES_DIR . 'commands/');
define('PROLIFIC_CLI_POSTS_MANAGER_UTILITIES_DIR', PROLIFIC_CLI_POSTS_MANAGER_INCLUDES_DIR . 'utilities/');
define('PROLIFIC_CLI_POSTS_MANAGER_INTERFACES_DIR', PROLIFIC_CLI_POSTS_MANAGER_INCLUDES_DIR . 'interfaces/');

class Prolific_Advanced_CLI_Posts_Manager {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->includes();
        $this->init();
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('init', array($this, 'load_textdomain'));
    }
    
    private function includes() {
        require_once PROLIFIC_CLI_POSTS_MANAGER_INTERFACES_DIR . 'interface-post-operation.php';
        require_once PROLIFIC_CLI_POSTS_MANAGER_UTILITIES_DIR . 'class-safety-manager.php';
        require_once PROLIFIC_CLI_POSTS_MANAGER_UTILITIES_DIR . 'class-post-query-builder.php';
        require_once PROLIFIC_CLI_POSTS_MANAGER_UTILITIES_DIR . 'class-post-filter.php';
        require_once PROLIFIC_CLI_POSTS_MANAGER_INCLUDES_DIR . 'class-prolific-cli-posts-manager.php';
        
        if (is_admin()) {
            require_once PROLIFIC_CLI_POSTS_MANAGER_INCLUDES_DIR . 'class-prolific-admin.php';
        }
        
        if (defined('WP_CLI') && WP_CLI) {
            require_once PROLIFIC_CLI_POSTS_MANAGER_COMMANDS_DIR . 'class-cleanup-posts-command.php';
        }
    }
    
    private function init() {
        if (defined('WP_CLI') && WP_CLI) {
            $this->register_cli_commands();
        }
        
        if (is_admin()) {
            new Prolific_Admin('prolific-advanced-cli-posts-manager', PROLIFIC_CLI_POSTS_MANAGER_VERSION);
        }
    }
    
    private function register_cli_commands() {
        WP_CLI::add_command('prolific cleanup-posts', 'Prolific_Cleanup_Posts_Command');
    }
    
    public function activate() {
        $this->create_directories();
        $this->set_default_options();
        $this->install_cli_command();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        $this->uninstall_cli_command();
        flush_rewrite_rules();
    }
    
    private function create_directories() {
        $upload_dir = wp_upload_dir();
        $logs_dir = $upload_dir['basedir'] . '/prolific-logs';
        $backups_dir = $upload_dir['basedir'] . '/prolific-backups';
        
        if (!file_exists($logs_dir)) {
            wp_mkdir_p($logs_dir);
            file_put_contents($logs_dir . '/.htaccess', 'deny from all');
        }
        
        if (!file_exists($backups_dir)) {
            wp_mkdir_p($backups_dir);
            file_put_contents($backups_dir . '/.htaccess', 'deny from all');
        }
    }
    
    private function set_default_options() {
        $default_options = array(
            'batch_size' => 100,
            'backup_retention_days' => 30,
            'log_level' => 'INFO',
            'log_retention_days' => 30,
            'require_confirmation' => true
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option("prolific_cli_posts_{$option}") === false) {
                add_option("prolific_cli_posts_{$option}", $value);
            }
        }
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            'prolific-advanced-cli-posts-manager',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
    
    private function install_cli_command() {
        $this->log_cli_action('Starting CLI command installation...');
        
        $plugin_dir = PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR;
        $runner_script = $plugin_dir . 'standalone-runner.php';
        $install_script = $plugin_dir . 'install.php';
        
        if (!file_exists($runner_script)) {
            $this->log_cli_action('Warning: standalone-runner.php not found, skipping CLI installation');
            return;
        }
        
        if (!file_exists($install_script)) {
            $this->log_cli_action('Warning: install.php not found, using fallback installation');
            $this->fallback_install();
            return;
        }
        
        // Try to run the smart installer
        if ($this->run_smart_installer($install_script)) {
            $this->show_cli_success_message();
        } else {
            $this->show_cli_manual_message();
        }
    }
    
    private function run_smart_installer($install_script) {
        // Check if we can execute PHP scripts
        if (!function_exists('exec') || in_array('exec', explode(',', ini_get('disable_functions')))) {
            $this->log_cli_action('Info: exec() function disabled, trying direct execution');
            return $this->run_installer_directly($install_script);
        }
        
        $output = array();
        $return_var = 0;
        
        // Change to plugin directory and run install script
        $plugin_dir = PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR;
        $command = "cd " . escapeshellarg($plugin_dir) . " && php " . escapeshellarg($install_script) . " 2>&1";
        
        @exec($command, $output, $return_var);
        
        $output_text = implode("\n", $output);
        $this->log_cli_action("Installation output: " . $output_text);
        
        // Check if installation was successful
        if ($return_var === 0 && (strpos($output_text, 'SUCCESS') !== false || strpos($output_text, 'Installation complete') !== false)) {
            $this->log_cli_action('✓ Smart installer completed successfully');
            return true;
        } else {
            $this->log_cli_action('Smart installer failed or had issues, trying direct execution');
            return $this->run_installer_directly($install_script);
        }
    }
    
    private function run_installer_directly($install_script) {
        $this->log_cli_action('Running installer directly...');
        
        // Capture output by including the installer and capturing output
        ob_start();
        $current_dir = getcwd();
        chdir(PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR);
        
        try {
            include $install_script;
            $output = ob_get_contents();
            ob_end_clean();
            chdir($current_dir);
            
            $this->log_cli_action("Direct installer output: " . $output);
            
            // Check if installation was successful
            if (strpos($output, 'SUCCESS') !== false || strpos($output, 'Installation complete') !== false) {
                $this->log_cli_action('✓ Direct installer completed successfully');
                return true;
            } else {
                $this->log_cli_action('Direct installer completed with warnings');
                return false;
            }
        } catch (Exception $e) {
            ob_end_clean();
            chdir($current_dir);
            $this->log_cli_action('Error running direct installer: ' . $e->getMessage());
            return false;
        }
    }
    
    private function fallback_install() {
        $this->log_cli_action('Running fallback installation...');
        
        $plugin_dir = PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR;
        $command_wrapper = '/usr/local/bin/prolific';
        $temp_wrapper = $plugin_dir . 'temp-prolific-command';
        
        $wrapper_content = <<<EOF
#!/bin/bash
# Prolific CLI Posts Manager - Global Command
# Auto-generated wrapper script

PLUGIN_DIR="$plugin_dir"
RUNNER_SCRIPT="\$PLUGIN_DIR/standalone-runner.php"

if [ ! -f "\$RUNNER_SCRIPT" ]; then
    echo "Error: Prolific CLI runner not found at \$RUNNER_SCRIPT"
    echo "Please ensure the plugin is properly installed."
    exit 1
fi

# Forward all arguments to the runner
php "\$RUNNER_SCRIPT" "\$@"
EOF;
        
        if (file_put_contents($temp_wrapper, $wrapper_content) !== false) {
            chmod($temp_wrapper, 0755);
            
            if (is_writable('/usr/local/bin/')) {
                if (copy($temp_wrapper, $command_wrapper)) {
                    $this->log_cli_action('✓ Global "prolific" command installed successfully');
                    $this->show_cli_success_message();
                } else {
                    $this->try_sudo_install($temp_wrapper, $command_wrapper);
                }
            } else {
                $this->try_sudo_install($temp_wrapper, $command_wrapper);
            }
            
            unlink($temp_wrapper);
        } else {
            $this->log_cli_action('Error: Could not create temporary command wrapper');
        }
    }
    
    private function try_sudo_install($temp_wrapper, $command_wrapper) {
        $this->log_cli_action('Attempting installation with elevated privileges...');
        
        // Check if exec() function is available
        if (!function_exists('exec')) {
            $this->log_cli_action('Info: exec() function disabled, manual installation required');
            $this->show_cli_manual_message();
            return;
        }
        
        // Check if we can run shell commands
        if (ini_get('safe_mode') || in_array('exec', explode(',', ini_get('disable_functions')))) {
            $this->log_cli_action('Info: Shell execution disabled, manual installation required');
            $this->show_cli_manual_message();
            return;
        }
        
        $output = array();
        $return_var = 0;
        
        @exec("sudo cp '$temp_wrapper' '$command_wrapper' 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            @exec("sudo chmod +x '$command_wrapper' 2>&1", $output, $return_var);
            if ($return_var === 0) {
                $this->log_cli_action('✓ Global "prolific" command installed with sudo');
                $this->show_cli_success_message();
            } else {
                $this->log_cli_action('Error: Could not make command executable');
                $this->show_cli_manual_message();
            }
        } else {
            $this->log_cli_action('Info: Could not install globally, manual installation available');
            $this->show_cli_manual_message();
        }
    }
    
    private function uninstall_cli_command() {
        $this->log_cli_action('Removing CLI command...');
        
        $command_wrapper = '/usr/local/bin/prolific';
        
        if (file_exists($command_wrapper)) {
            if (is_writable('/usr/local/bin/')) {
                if (unlink($command_wrapper)) {
                    $this->log_cli_action('✓ Global "prolific" command removed');
                } else {
                    $this->log_cli_action('Warning: Could not remove global command');
                }
            } else {
                // Only try exec if it's available
                if (function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')))) {
                    $output = array();
                    $return_var = 0;
                    @exec("sudo rm -f '$command_wrapper' 2>&1", $output, $return_var);
                    if ($return_var === 0) {
                        $this->log_cli_action('✓ Global "prolific" command removed with sudo');
                    } else {
                        $this->log_cli_action('Warning: Could not remove global command');
                    }
                } else {
                    $this->log_cli_action('Info: Manual removal required for global command');
                }
            }
        }
    }
    
    private function show_cli_success_message() {
        if (is_admin()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<h3>🎉 Prolific CLI Posts Manager Activated!</h3>';
                echo '<p><strong>CLI command installation completed successfully!</strong></p>';
                echo '<p>You can now use these commands:</p>';
                echo '<ul style="list-style: disc; margin-left: 20px;">';
                echo '<li><code>prolific --posts</code> - List all posts</li>';
                echo '<li><code>prolific-posts</code> - Quick posts listing (alias)</li>';
                echo '<li><code>prolific --help</code> - Show all available options</li>';
                echo '<li><code>prolific --operation=delete --posts --status=draft --dry-run</code> - Preview deletions</li>';
                echo '</ul>';
                echo '<p><strong>Note:</strong> If commands don\'t work immediately, restart your terminal or run <code>source ~/.bashrc</code></p>';
                echo '<p><a href="' . admin_url('tools.php?page=prolific-cli-posts-manager') . '" class="button button-primary">View Installation Guide & Commands</a></p>';
                echo '</div>';
            });
        }
    }
    
    private function show_cli_manual_message() {
        if (is_admin()) {
            add_action('admin_notices', function() {
                $plugin_dir = PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR;
                echo '<div class="notice notice-info is-dismissible">';
                echo '<h3>ℹ️ Prolific CLI Posts Manager Activated!</h3>';
                echo '<p><strong>CLI installation needs manual setup.</strong></p>';
                echo '<p>Run this smart installer command for multiple installation options:</p>';
                echo '<p><code>cd ' . esc_html($plugin_dir) . ' && php install.php</code></p>';
                echo '<p>Or use the standalone runner directly without installation:</p>';
                echo '<p><code>php ' . esc_html($plugin_dir) . 'standalone-runner.php --help</code></p>';
                echo '<p><a href="' . admin_url('tools.php?page=prolific-cli-posts-manager') . '" class="button button-primary">View Complete Installation Guide</a></p>';
                echo '</div>';
            });
        }
    }
    
    private function log_cli_action($message) {
        if (function_exists('error_log')) {
            error_log('[Prolific CLI] ' . $message);
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Prolific CLI] ' . $message);
        }
    }
}

function prolific_advanced_cli_posts_manager() {
    return Prolific_Advanced_CLI_Posts_Manager::instance();
}

prolific_advanced_cli_posts_manager();