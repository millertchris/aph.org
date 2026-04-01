<?php

if (!defined('ABSPATH')) {
    exit;
}

class Prolific_Admin {
    
    private $plugin_name;
    private $version;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_prolific_get_post_stats', array($this, 'ajax_get_post_stats'));
        add_action('wp_ajax_prolific_list_backups', array($this, 'ajax_list_backups'));
    }
    
    public function add_admin_menu() {
        add_management_page(
            'Prolific CLI Posts Manager',
            'CLI Posts Manager',
            'manage_options',
            'prolific-cli-posts-manager',
            array($this, 'display_admin_page')
        );
    }
    
    public function enqueue_scripts($hook) {
        if ('tools_page_prolific-cli-posts-manager' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            $this->plugin_name,
            PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_URL . 'assets/admin/css/prolific-admin.css',
            array(),
            $this->version,
            'all'
        );
        
        wp_enqueue_script(
            $this->plugin_name,
            PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_URL . 'assets/admin/js/prolific-admin.js',
            array('jquery'),
            $this->version,
            false
        );
        
        wp_localize_script(
            $this->plugin_name,
            'prolific_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('prolific_admin_nonce')
            )
        );
    }
    
    public function display_admin_page() {
        ?>
        <div class="wrap prolific-admin">
            <h1>
                <span class="dashicons dashicons-admin-tools"></span>
                Prolific CLI Posts Manager
            </h1>
            
            <div class="prolific-admin-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#installation" class="nav-tab nav-tab-active">CLI Installation</a>
                    <a href="#commands" class="nav-tab">Available Commands</a>
                    <a href="#stats" class="nav-tab">Database Stats</a>
                    <a href="#backups" class="nav-tab">Backup Management</a>
                    <a href="#settings" class="nav-tab">Settings</a>
                </nav>
                
                <div id="installation" class="tab-content active">
                    <?php $this->render_installation_tab(); ?>
                </div>
                
                <div id="commands" class="tab-content">
                    <?php $this->render_commands_tab(); ?>
                </div>
                
                <div id="stats" class="tab-content">
                    <?php $this->render_stats_tab(); ?>
                </div>
                
                <div id="backups" class="tab-content">
                    <?php $this->render_backups_tab(); ?>
                </div>
                
                <div id="settings" class="tab-content">
                    <?php $this->render_settings_tab(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_installation_tab() {
        $plugin_dir = PROLIFIC_CLI_POSTS_MANAGER_PLUGIN_DIR;
        $is_command_installed = file_exists('/usr/local/bin/prolific');
        $current_user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown';
        ?>
        <div class="prolific-installation-section">
            <h2>CLI Command Installation Guide</h2>
            
            <?php if ($is_command_installed): ?>
                <div class="notice notice-success inline">
                    <h3>✅ CLI Command Already Installed!</h3>
                    <p>The global <code>prolific</code> command is available. You can use it from anywhere on your server.</p>
                </div>
            <?php else: ?>
                <div class="notice notice-warning inline">
                    <h3>⚠️ CLI Command Not Installed</h3>
                    <p>The global <code>prolific</code> command needs to be installed manually.</p>
                </div>
            <?php endif; ?>
            
            <div class="installation-methods">
                <h3>Installation Methods</h3>
                
                <div class="method-grid">
                    
                    <!-- Method 1: No-Sudo Installation -->
                    <div class="installation-method <?php echo $is_command_installed ? 'method-success' : 'method-primary'; ?>">
                        <h4><span class="dashicons dashicons-admin-tools"></span> Method 1: Smart Installation (No Sudo Required) ⭐</h4>
                        
                        <div class="method-steps">
                            <div class="step">
                                <strong>One Command Installation:</strong>
                                <div class="code-block">
                                    <code>cd <?php echo esc_html($plugin_dir); ?> && php install.php</code>
                                    <button class="copy-btn" data-copy="cd <?php echo esc_attr($plugin_dir); ?> && php install.php">Copy & Run</button>
                                </div>
                                <p class="step-note">✨ <strong>No sudo required!</strong> This installer tries multiple methods automatically:</p>
                                
                                <div class="installation-methods-list">
                                    <ul>
                                        <li>🏠 <strong>User Local Command</strong> - Creates <code>~/bin/prolific</code></li>
                                        <li>🔗 <strong>Shell Aliases</strong> - Adds <code>prolific</code>, <code>prolific-posts</code> commands</li>
                                        <li>📁 <strong>Local Wrapper</strong> - Creates <code>./prolific</code> in plugin directory</li>
                                        <li>🌍 <strong>Global Command</strong> - Only if you have permissions (no sudo needed)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="method-result">
                            <strong>After installation, you can use any of these:</strong>
                            <ul>
                                <li><code>prolific --posts</code> - If global or user local installation succeeded</li>
                                <li><code>prolific-posts</code> - Quick alias for listing posts</li>
                                <li><code>./prolific --posts</code> - From plugin directory</li>
                                <li><code>php standalone-runner.php --posts</code> - Direct usage</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Method 2: Manual Script Installation -->
                    <div class="installation-method">
                        <h4><span class="dashicons dashicons-editor-code"></span> Method 2: Shell Script Installation</h4>
                        
                        <div class="method-steps">
                            <div class="step">
                                <strong>Alternative:</strong> Use the installation shell script
                                <div class="code-block">
                                    <code>cd <?php echo esc_html($plugin_dir); ?> && ./install-cli.sh</code>
                                    <button class="copy-btn" data-copy="cd <?php echo esc_attr($plugin_dir); ?> && ./install-cli.sh">Copy</button>
                                </div>
                                <p class="step-note">This provides more detailed output and sets up shell aliases.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Method 3: Direct Usage -->
                    <div class="installation-method">
                        <h4><span class="dashicons dashicons-media-code"></span> Method 3: Direct Usage (No Installation)</h4>
                        
                        <div class="method-steps">
                            <div class="step">
                                <strong>Use without global installation:</strong>
                                <div class="code-block">
                                    <code>php <?php echo esc_html($plugin_dir); ?>standalone-runner.php --posts</code>
                                    <button class="copy-btn" data-copy="php <?php echo esc_attr($plugin_dir); ?>standalone-runner.php --posts">Copy</button>
                                </div>
                                <p class="step-note">Run commands directly without installing globally. Replace <code>--posts</code> with any other options.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Why CLI Installation? -->
            <div class="why-cli-section">
                <h3><span class="dashicons dashicons-info"></span> Why Use CLI Installation?</h3>
                
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <h4>🚀 Bypass WP-CLI Issues</h4>
                        <p>Avoids WooCommerce and PHP 8.4 compatibility conflicts that prevent standard WP-CLI from working.</p>
                    </div>
                    
                    <div class="benefit-item">
                        <h4>⚡ Better Performance</h4>
                        <p>Direct WordPress access without WP-CLI overhead, faster execution for bulk operations.</p>
                    </div>
                    
                    <div class="benefit-item">
                        <h4>🛡️ Same Safety Features</h4>
                        <p>All backup, confirmation, and safety features work exactly the same as designed.</p>
                    </div>
                    
                    <div class="benefit-item">
                        <h4>🎯 Full Feature Access</h4>
                        <p>Access to all filtering options, operations, and advanced features from command line.</p>
                    </div>
                </div>
            </div>
            
            <!-- Troubleshooting -->
            <div class="troubleshooting-section">
                <h3><span class="dashicons dashicons-sos"></span> Troubleshooting</h3>
                
                <div class="troubleshooting-grid">
                    <div class="issue-item">
                        <h4>🔒 Permission Denied</h4>
                        <p><strong>Problem:</strong> Cannot write to <code>/usr/local/bin/</code></p>
                        <p><strong>Solution:</strong> The installer will prompt for your password automatically. Make sure you have sudo access.</p>
                    </div>
                    
                    <div class="issue-item">
                        <h4>📁 Command Not Found</h4>
                        <p><strong>Problem:</strong> <code>prolific: command not found</code> after installation</p>
                        <p><strong>Solution:</strong> Restart your terminal or run <code>source ~/.bashrc</code></p>
                    </div>
                    
                    <div class="issue-item">
                        <h4>🔧 PHP Not Found</h4>
                        <p><strong>Problem:</strong> <code>php: command not found</code></p>
                        <p><strong>Solution:</strong> Make sure PHP CLI is installed: <code>which php</code></p>
                    </div>
                    
                    <div class="issue-item">
                        <h4>📂 WordPress Not Found</h4>
                        <p><strong>Problem:</strong> Cannot find WordPress installation</p>
                        <p><strong>Solution:</strong> Make sure you're running from the correct plugin directory path.</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Test -->
            <div class="quick-test-section">
                <h3><span class="dashicons dashicons-yes"></span> Quick Test Commands</h3>
                <p>After installation, test with these safe commands:</p>
                
                <div class="test-commands">
                    <div class="test-command">
                        <code>prolific --help</code>
                        <button class="copy-btn" data-copy="prolific --help">Copy</button>
                        <span class="command-desc">Show all available options</span>
                    </div>
                    
                    <div class="test-command">
                        <code>prolific --posts --status=publish</code>
                        <button class="copy-btn" data-copy="prolific --posts --status=publish">Copy</button>
                        <span class="command-desc">List published posts (safe read-only)</span>
                    </div>
                    
                    <div class="test-command">
                        <code>prolific --operation=delete --posts --status=draft --limit=10 --dry-run</code>
                        <button class="copy-btn" data-copy="prolific --operation=delete --posts --status=draft --limit=10 --dry-run">Copy</button>
                        <span class="command-desc">Preview deletion of first 10 drafts (no changes made)</span>
                    </div>
                </div>
            </div>
            
            <!-- Current Status -->
            <div class="status-section">
                <h3><span class="dashicons dashicons-dashboard"></span> Current System Status</h3>
                
                <div class="status-grid">
                    <div class="status-item">
                        <strong>Plugin Directory:</strong>
                        <code><?php echo esc_html($plugin_dir); ?></code>
                    </div>
                    
                    <div class="status-item">
                        <strong>Standalone Runner:</strong>
                        <span class="<?php echo file_exists($plugin_dir . 'standalone-runner.php') ? 'status-good' : 'status-bad'; ?>">
                            <?php echo file_exists($plugin_dir . 'standalone-runner.php') ? '✅ Available' : '❌ Missing'; ?>
                        </span>
                    </div>
                    
                    <div class="status-item">
                        <strong>Installation Script:</strong>
                        <span class="<?php echo file_exists($plugin_dir . 'install.php') ? 'status-good' : 'status-bad'; ?>">
                            <?php echo file_exists($plugin_dir . 'install.php') ? '✅ Available' : '❌ Missing'; ?>
                        </span>
                    </div>
                    
                    <div class="status-item">
                        <strong>Global Command:</strong>
                        <span class="<?php echo $is_command_installed ? 'status-good' : 'status-pending'; ?>">
                            <?php echo $is_command_installed ? '✅ Installed' : '⏳ Not Installed'; ?>
                        </span>
                    </div>
                    
                    <div class="status-item">
                        <strong>Current User:</strong>
                        <code><?php echo esc_html($current_user); ?></code>
                    </div>
                    
                    <div class="status-item">
                        <strong>Server Shell Access:</strong>
                        <span class="<?php echo (function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')))) ? 'status-good' : 'status-bad'; ?>">
                            <?php echo (function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')))) ? '✅ Available' : '❌ Disabled'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_commands_tab() {
        ?>
        <div class="prolific-commands-section">
            <h2>Available Prolific CLI Commands</h2>
            <p>Use these commands in your terminal or command line after installation:</p>
            
            <div class="command-categories">
                
                <div class="command-category">
                    <h3><span class="dashicons dashicons-list-view"></span> Basic Operations</h3>
                    
                    <div class="command-item">
                        <h4>List Posts</h4>
                        <code>prolific --posts</code>
                        <p>Lists all WordPress posts with details.</p>
                        
                        <div class="command-variations">
                            <strong>Variations:</strong>
                            <ul>
                                <li><code>--pages</code> - Target WordPress pages</li>
                                <li><code>--custom-post-type=TYPE</code> - Target specific custom post type</li>
                                <li><code>--status=STATUS</code> - Filter by post status (draft, publish, private, etc.)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="command-item">
                        <h4>Delete Posts</h4>
                        <code>prolific --operation=delete --posts --dry-run</code>
                        <p>Safely delete posts with backup creation. Use --dry-run first to preview.</p>
                        
                        <div class="command-variations">
                            <strong>Safety Options:</strong>
                            <ul>
                                <li><code>--dry-run</code> - Show what would be deleted without making changes</li>
                                <li><code>--force</code> - Skip confirmation prompts</li>
                                <li><code>--backup</code> - Force backup creation</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="command-category">
                    <h3><span class="dashicons dashicons-filter"></span> Advanced Filtering</h3>
                    
                    <div class="command-item">
                        <h4>Date Range Filtering</h4>
                        <code>prolific --posts --date-from=2023-01-01 --date-to=2023-12-31</code>
                        <p>Filter posts by creation date range.</p>
                    </div>
                    
                    <div class="command-item">
                        <h4>Meta Field Filtering</h4>
                        <code>prolific --posts --meta-key=featured --meta-value=no</code>
                        <p>Filter posts by custom field values.</p>
                        
                        <div class="command-variations">
                            <strong>Meta Comparison Operators:</strong>
                            <ul>
                                <li><code>--meta-compare==</code> - Equals (default)</li>
                                <li><code>--meta-compare=!=</code> - Not equals</li>
                                <li><code>--meta-compare=></code> - Greater than</li>
                                <li><code>--meta-compare=LIKE</code> - Contains text</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="command-item">
                        <h4>Taxonomy Filtering</h4>
                        <code>prolific --posts --category=news,updates --tag=featured</code>
                        <p>Filter posts by categories, tags, or other taxonomies.</p>
                    </div>
                    
                    <div class="command-item">
                        <h4>Author Filtering</h4>
                        <code>prolific --posts --author=john.doe</code>
                        <p>Filter posts by author ID, username, or email.</p>
                    </div>
                </div>
                
                <div class="command-category">
                    <h3><span class="dashicons dashicons-products"></span> Specialized Post Types</h3>
                    
                    <div class="command-item">
                        <h4>WooCommerce Products</h4>
                        <code>prolific --woocommerce-products --status=draft</code>
                        <p>Target WooCommerce products with product-specific filtering.</p>
                        
                        <div class="command-variations">
                            <strong>WooCommerce Options:</strong>
                            <ul>
                                <li><code>--sku=PATTERN</code> - Filter by SKU pattern</li>
                                <li><code>--stock-status=STATUS</code> - Filter by stock status</li>
                                <li><code>--price-min=AMOUNT</code> - Minimum price filter</li>
                                <li><code>--product-category=CATEGORY</code> - Filter by product category</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="command-item">
                        <h4>Event Posts</h4>
                        <code>prolific --events --event-start-date=2023-01-01</code>
                        <p>Target event post types from popular event plugins.</p>
                        
                        <div class="command-variations">
                            <strong>Event Options:</strong>
                            <ul>
                                <li><code>--event-start-date=DATE</code> - Filter by event start date</li>
                                <li><code>--event-end-date=DATE</code> - Filter by event end date</li>
                                <li><code>--event-venue=VENUE</code> - Filter by event venue</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="command-category">
                    <h3><span class="dashicons dashicons-backup"></span> Backup Management</h3>
                    
                    <div class="command-item">
                        <h4>Export Backup</h4>
                        <code>prolific --operation=export-backup --posts --status=draft</code>
                        <p>Create a backup of matching posts without deleting them.</p>
                    </div>
                    
                    <div class="command-item">
                        <h4>Modify Posts</h4>
                        <code>prolific --operation=modify --posts --status=draft --dry-run</code>
                        <p>Bulk modify posts (status changes, meta updates). Use --dry-run first.</p>
                    </div>
                </div>
                
                
                <div class="command-category">
                    <h3><span class="dashicons dashicons-performance"></span> Performance Options</h3>
                    
                    <div class="command-item">
                        <h4>Limit Number of Posts</h4>
                        <code>prolific --operation=delete --posts --status=draft --limit=50</code>
                        <p>Process only a specific number of posts (useful for testing or gradual cleanup).</p>
                    </div>
                    
                    <div class="command-item">
                        <h4>Batch Processing</h4>
                        <code>prolific --posts --batch-size=50</code>
                        <p>Control processing batch size for performance optimization.</p>
                    </div>
                    
                    <div class="command-item">
                        <h4>Exclude Specific Posts</h4>
                        <code>prolific --posts --exclude-ids=1,2,3,4</code>
                        <p>Exclude specific post IDs from operations.</p>
                    </div>
                </div>
            </div>
            
            <div class="usage-examples">
                <h3><span class="dashicons dashicons-lightbulb"></span> Common Usage Examples</h3>
                
                <div class="example-grid">
                    <div class="example-item">
                        <h4>Clean Up Draft Posts (Gradual)</h4>
                        <code>prolific --operation=delete --posts --status=draft --limit=100 --dry-run</code>
                        <p>First, preview the first 100 draft posts that would be deleted</p>
                        <code>prolific --operation=delete --posts --status=draft --limit=100</code>
                        <p>Then, delete only the first 100 drafts with backup creation</p>
                    </div>
                    
                    <div class="example-item">
                        <h4>Remove Old WooCommerce Products</h4>
                        <code>prolific --operation=delete --woocommerce-products --date-to=2022-12-31 --dry-run</code>
                        <p>Preview old WooCommerce products before removal</p>
                    </div>
                    
                    <div class="example-item">
                        <h4>Archive Specific Category</h4>
                        <code>prolific --operation=export-backup --posts --category=archived</code>
                        <p>Create backup of posts in "archived" category without deleting</p>
                    </div>
                    
                    <div class="example-item">
                        <h4>Modify Post Status</h4>
                        <code>prolific --operation=modify --posts --status=draft --dry-run</code>
                        <p>Preview bulk modifications to posts before applying changes</p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_stats_tab() {
        ?>
        <div class="prolific-stats-section">
            <h2>Database Statistics</h2>
            <p>Overview of posts and data in your WordPress database.</p>
            
            <div id="stats-loading" class="loading-spinner" style="display: none;">
                <span class="spinner is-active"></span>
                Loading statistics...
            </div>
            
            <div id="stats-content">
                <button id="refresh-stats" class="button button-secondary">
                    <span class="dashicons dashicons-update"></span>
                    Refresh Statistics
                </button>
                
                <div id="stats-data"></div>
            </div>
        </div>
        <?php
    }
    
    private function render_backups_tab() {
        ?>
        <div class="prolific-backups-section">
            <h2>Backup Management</h2>
            <p>Manage backup files created by the CLI Posts Manager.</p>
            
            <div id="backups-loading" class="loading-spinner" style="display: none;">
                <span class="spinner is-active"></span>
                Loading backups...
            </div>
            
            <div id="backups-content">
                <button id="refresh-backups" class="button button-secondary">
                    <span class="dashicons dashicons-update"></span>
                    Refresh Backup List
                </button>
                
                <div id="backups-data"></div>
            </div>
        </div>
        <?php
    }
    
    private function render_settings_tab() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $batch_size = get_option('prolific_cli_posts_batch_size', 100);
        $backup_retention = get_option('prolific_cli_posts_backup_retention_days', 30);
        $log_level = get_option('prolific_cli_posts_log_level', 'INFO');
        $log_retention = get_option('prolific_cli_posts_log_retention_days', 30);
        $require_confirmation = get_option('prolific_cli_posts_require_confirmation', true);
        
        ?>
        <div class="prolific-settings-section">
            <h2>Plugin Settings</h2>
            <p>Configure default behavior and safety settings.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('prolific_settings_save', 'prolific_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="batch_size">Default Batch Size</label>
                        </th>
                        <td>
                            <input type="number" id="batch_size" name="batch_size" value="<?php echo esc_attr($batch_size); ?>" min="10" max="1000" />
                            <p class="description">Number of posts to process in each batch (default: 100)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="backup_retention">Backup Retention (Days)</label>
                        </th>
                        <td>
                            <input type="number" id="backup_retention" name="backup_retention" value="<?php echo esc_attr($backup_retention); ?>" min="1" max="365" />
                            <p class="description">How long to keep backup files (default: 30 days)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="log_level">Log Level</label>
                        </th>
                        <td>
                            <select id="log_level" name="log_level">
                                <option value="DEBUG" <?php selected($log_level, 'DEBUG'); ?>>Debug</option>
                                <option value="INFO" <?php selected($log_level, 'INFO'); ?>>Info</option>
                                <option value="WARNING" <?php selected($log_level, 'WARNING'); ?>>Warning</option>
                                <option value="ERROR" <?php selected($log_level, 'ERROR'); ?>>Error</option>
                            </select>
                            <p class="description">Minimum log level to record</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="log_retention">Log Retention (Days)</label>
                        </th>
                        <td>
                            <input type="number" id="log_retention" name="log_retention" value="<?php echo esc_attr($log_retention); ?>" min="1" max="365" />
                            <p class="description">How long to keep log files (default: 30 days)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="require_confirmation">Require Confirmation</label>
                        </th>
                        <td>
                            <input type="checkbox" id="require_confirmation" name="require_confirmation" value="1" <?php checked($require_confirmation); ?> />
                            <label for="require_confirmation">Require confirmation for destructive operations</label>
                            <p class="description">When enabled, CLI commands will ask for confirmation before deleting posts</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    private function save_settings() {
        if (!wp_verify_nonce($_POST['prolific_settings_nonce'], 'prolific_settings_save')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $batch_size = intval($_POST['batch_size']);
        $batch_size = max(10, min(1000, $batch_size));
        update_option('prolific_cli_posts_batch_size', $batch_size);
        
        $backup_retention = intval($_POST['backup_retention']);
        $backup_retention = max(1, min(365, $backup_retention));
        update_option('prolific_cli_posts_backup_retention_days', $backup_retention);
        
        $log_level = sanitize_text_field($_POST['log_level']);
        $valid_levels = array('DEBUG', 'INFO', 'WARNING', 'ERROR');
        if (in_array($log_level, $valid_levels)) {
            update_option('prolific_cli_posts_log_level', $log_level);
        }
        
        $log_retention = intval($_POST['log_retention']);
        $log_retention = max(1, min(365, $log_retention));
        update_option('prolific_cli_posts_log_retention_days', $log_retention);
        
        $require_confirmation = isset($_POST['require_confirmation']) ? true : false;
        update_option('prolific_cli_posts_require_confirmation', $require_confirmation);
        
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    public function ajax_get_post_stats() {
        if (!wp_verify_nonce($_POST['nonce'], 'prolific_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $stats = array();
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            $counts = wp_count_posts($post_type->name);
            $stats[$post_type->name] = array(
                'label' => $post_type->labels->name,
                'counts' => (array) $counts,
                'total' => array_sum((array) $counts)
            );
        }
        
        wp_send_json_success($stats);
    }
    
    public function ajax_list_backups() {
        if (!isset($_POST['nonce'])) {
            wp_send_json_error('No nonce provided');
            return;
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'prolific_admin_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        try {
            $safety_manager = new Prolific_Safety_Manager();
            $backups = $safety_manager->list_backups(false); // Don't show CLI output
            
            wp_send_json_success($backups);
        } catch (Exception $e) {
            wp_send_json_error('Failed to load backups: ' . $e->getMessage());
        }
    }
}