<?php

/**
 * Plugin Name: WooCommerce HPOS Custom Order Columns
 * Plugin URI: https://prolificdigital.com
 * Description: Restores custom order columns for WooCommerce HPOS (High-Performance Order Storage) that were previously available via Admin Columns Pro. Works with both HPOS and Legacy order storage.
 * Version: 1.0.0
 * Author: Prolific Digital
 * Author URI: https://prolificdigital.com
 * Requires at least: 5.0
 * Tested up to: 6.4
 * WC requires at least: 7.0
 * WC tested up to: 8.5
 * Text Domain: wc-hpos-custom-order-columns
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * INSTALLATION & CONFIGURATION
 * ============================
 *
 * 1. Upload this file to /wp-content/plugins/
 * 2. Activate the plugin through the 'Plugins' menu in WordPress
 * 3. The custom columns will appear in WooCommerce > Orders
 * 4. Use Screen Options to show/hide specific columns
 *
 * ADMIN COLUMNS PRO COMPATIBILITY
 * ===============================
 *
 * If Admin Columns Pro is active, it overrides WooCommerce's native column system.
 * To restore your custom columns with Admin Columns Pro:
 *
 * 1. Go to Admin Columns Pro settings
 * 2. Find "WooCommerce Orders" or "Orders" in the list
 * 3. Add these Custom Field columns manually:
 *
 *    SYSPRO Customer #: Custom Field > User Meta > sysproCustomer (for order customer)
 *    WC Customer #: Custom Field > _customer_user
 *    FQ Account: Custom Field > _fq_account_name  
 *    SYSPRO Order #: Custom Field > syspro_order_number
 *    Created By: Custom Field > _created_by
 *    Card Type: Custom Field > _wc_authorize_net_aim_card_type
 *    Email: Custom Field > _billing_email
 *    Billing State: Custom Field > _billing_state
 *    Shipping State: Custom Field > _shipping_state
 *
 * 4. Save the configuration
 *
 * CUSTOMIZATION
 * =============
 *
 * To modify the meta keys used for data retrieval, update the following constants:
 * - SYSPRO_CUSTOMER_META_KEY: User meta key for SYSPRO Customer # (default: 'sysproCustomer')
 * - Order meta keys are defined in the get_column_definitions() method:
 *   - _customer_user (WC Customer #)
 *   - _fq_account_name (FQ Account)
 *   - syspro_order_number (SYSPRO Order #)
 *   - _created_by (Created By)
 *   - _wc_authorize_net_aim_card_type (Card Type)
 *   - _billing_email (Email)
 *   - _billing_state (Billing State)
 *   - _shipping_state (Shipping State)
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_HPOS_CUSTOM_COLUMNS_VERSION', '1.0.0');
define('WC_HPOS_CUSTOM_COLUMNS_PLUGIN_FILE', __FILE__);
define('WC_HPOS_CUSTOM_COLUMNS_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Main plugin class for WooCommerce HPOS Custom Order Columns
 */
class WC_HPOS_Custom_Order_Columns
{

    /**
     * Meta key for SYSPRO Customer # stored in user meta
     */
    const SYSPRO_CUSTOMER_META_KEY = 'sysproCustomer';

    /**
     * Cache for user data to avoid N+1 queries
     *
     * @var array
     */
    private static $user_cache = array();

    /**
     * Initialize the plugin
     */
    public static function init()
    {
        // Check if WooCommerce is active
        if (! class_exists('WooCommerce')) {
            add_action('admin_notices', array(__CLASS__, 'woocommerce_missing_notice'));
            return;
        }

        // Declare HPOS compatibility
        add_action('before_woocommerce_init', array(__CLASS__, 'declare_hpos_compatibility'));

        // Initialize hooks based on order storage type - use admin_init for better timing
        add_action('admin_init', array(__CLASS__, 'setup_hooks'), 5);

        // Also hook into current_screen for screen-specific setup
        add_action('current_screen', array(__CLASS__, 'setup_screen_hooks'));

        // Add admin styles
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_styles'));

        // Load plugin textdomain
        add_action('plugins_loaded', array(__CLASS__, 'load_textdomain'));

        // Register columns for screen options
        add_filter('default_hidden_columns', array(__CLASS__, 'set_default_hidden_columns'), 10, 2);
    }

    /**
     * Declare HPOS compatibility
     */
    public static function declare_hpos_compatibility()
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }

    /**
     * Setup hooks based on whether HPOS is enabled or Legacy orders are used
     */
    public static function setup_hooks()
    {
        if (self::is_hpos_enabled()) {
            // HPOS hooks - Hook both possible filter names since order_type might be empty
            add_filter('woocommerce_shop_order_list_table_columns', array(__CLASS__, 'add_order_columns'), 999);
            add_filter('woocommerce__list_table_columns', array(__CLASS__, 'add_order_columns'), 999);

            add_action('woocommerce_shop_order_list_table_custom_column', array(__CLASS__, 'render_order_column'), 999, 2);
            add_action('woocommerce__list_table_custom_column', array(__CLASS__, 'render_order_column'), 999, 2);

            add_filter('woocommerce_shop_order_list_table_sortable_columns', array(__CLASS__, 'add_sortable_columns'), 999);
            add_filter('woocommerce__list_table_sortable_columns', array(__CLASS__, 'add_sortable_columns'), 999);

            add_filter('woocommerce_shop_order_list_table_query_args', array(__CLASS__, 'handle_sorting'), 999);
            add_filter('woocommerce__list_table_query_args', array(__CLASS__, 'handle_sorting'), 999);
        } else {
            // Legacy hooks (posts table)
            add_filter('manage_edit-shop_order_columns', array(__CLASS__, 'add_order_columns'), 999);
            add_action('manage_shop_order_posts_custom_column', array(__CLASS__, 'render_legacy_order_column'), 999, 2);
            add_filter('manage_edit-shop_order_sortable_columns', array(__CLASS__, 'add_sortable_columns'), 999);
            add_action('pre_get_posts', array(__CLASS__, 'handle_legacy_sorting'), 999);
        }

        // Show admin notice if Admin Columns Pro is detected
        if (self::is_admin_columns_pro_active()) {
            add_action('admin_notices', array(__CLASS__, 'admin_columns_pro_notice'));
        }
    }

    /**
     * Check if HPOS is enabled
     *
     * @return bool
     */
    public static function is_hpos_enabled()
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil')) {
            return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }
        return false;
    }

    /**
     * Setup screen-specific hooks when on the orders page
     * 
     * @param WP_Screen $screen Current screen object
     */
    public static function setup_screen_hooks($screen)
    {
        // Check if we're on the orders page
        if (! $screen || ('woocommerce_page_wc-orders' !== $screen->id && 'edit-shop_order' !== $screen->id)) {
            return;
        }

        // Register columns with screen
        if ('woocommerce_page_wc-orders' === $screen->id) {
            // For HPOS, manually register columns with the screen
            add_filter('manage_' . $screen->id . '_columns', array(__CLASS__, 'register_screen_columns'));
        }
    }

    /**
     * Register columns with WordPress screen system
     * 
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public static function register_screen_columns($columns)
    {
        // This ensures columns appear in Screen Options
        return self::add_order_columns($columns);
    }

    /**
     * Set default hidden columns for new users
     * 
     * @param array $hidden Hidden columns
     * @param WP_Screen $screen Current screen
     * @return array
     */
    public static function set_default_hidden_columns($hidden, $screen)
    {
        if (! $screen || ('woocommerce_page_wc-orders' !== $screen->id && 'edit-shop_order' !== $screen->id)) {
            return $hidden;
        }

        // By default, don't hide our columns
        return $hidden;
    }

    /**
     * Check if Admin Columns Pro is active
     * 
     * @return bool
     */
    public static function is_admin_columns_pro_active()
    {
        return is_plugin_active('admin-columns-pro/admin-columns-pro.php');
    }

    /**
     * Show admin notice when Admin Columns Pro is active
     */
    public static function admin_columns_pro_notice()
    {
        $screen = get_current_screen();
        if (! $screen || 'woocommerce_page_wc-orders' !== $screen->id) {
            return;
        }
?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong><?php esc_html_e('WC HPOS Custom Order Columns:', 'wc-hpos-custom-order-columns'); ?></strong>
                <?php esc_html_e('Admin Columns Pro is active and overrides the native column system. To see your custom columns, please configure them manually in Admin Columns Pro settings using the meta keys listed in the plugin documentation.', 'wc-hpos-custom-order-columns'); ?>
                <a href="#" onclick="this.parentElement.parentElement.style.display='none';"><?php esc_html_e('Dismiss', 'wc-hpos-custom-order-columns'); ?></a>
            </p>
        </div>
    <?php
    }

    /**
     * Get column definitions
     *
     * @return array Column definitions with keys, labels, and meta information
     */
    public static function get_column_definitions()
    {
        return array(
            'syspro_customer' => array(
                'label' => __('SYSPRO Customer #', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'numeric' => true,
            ),
            'wc_customer' => array(
                'label' => __('WC Customer #', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'numeric' => true,
            ),
            'fq_account' => array(
                'label' => __('FQ Account', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'meta_key' => '_fq_account_name',
            ),
            'syspro_order' => array(
                'label' => __('SYSPRO Order #', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'meta_key' => 'syspro_order_number',
            ),
            'created_by' => array(
                'label' => __('Created By', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'meta_key' => '_created_by',
            ),
            'payment_method_custom' => array(
                'label' => __('Payment Method', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
            ),
            'card_type' => array(
                'label' => __('Card Type', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'meta_key' => '_wc_authorize_net_aim_card_type',
            ),
            'customer_custom' => array(
                'label' => __('Customer', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
            ),
            'email_custom' => array(
                'label' => __('Email', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'meta_key' => '_billing_email',
            ),
            'billing_state_custom' => array(
                'label' => __('Billing State', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'meta_key' => '_billing_state',
            ),
            'shipping_state_custom' => array(
                'label' => __('Shipping State', 'wc-hpos-custom-order-columns'),
                'sortable' => true,
                'meta_key' => '_shipping_state',
            ),
        );
    }

    /**
     * Add custom columns to the orders table
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public static function add_order_columns($columns)
    {
        // Debug mode - uncomment to see when this is called
        // error_log( 'WC_HPOS_Custom_Order_Columns::add_order_columns called with ' . count( $columns ) . ' columns' );

        $column_definitions = self::get_column_definitions();

        // Insert custom columns before the 'wc_actions' column if it exists
        $new_columns = array();
        foreach ($columns as $key => $label) {
            if ($key === 'wc_actions') {
                // Add all custom columns before actions
                foreach ($column_definitions as $column_key => $definition) {
                    $new_columns[$column_key] = $definition['label'];
                }
            }
            $new_columns[$key] = $label;
        }

        // If 'wc_actions' doesn't exist, append custom columns at the end
        if (! isset($columns['wc_actions'])) {
            foreach ($column_definitions as $column_key => $definition) {
                $new_columns[$column_key] = $definition['label'];
            }
        }

        return $new_columns;
    }

    /**
     * Render custom column content for HPOS
     *
     * @param string $column Column key
     * @param WC_Order $order Order object
     */
    public static function render_order_column($column, $order)
    {
        if (! is_a($order, 'WC_Order')) {
            return;
        }

        $column_definitions = self::get_column_definitions();
        if (! isset($column_definitions[$column])) {
            return;
        }

        echo wp_kses_post(self::get_column_content($column, $order));
    }

    /**
     * Render custom column content for Legacy orders
     *
     * @param string $column Column key
     * @param int $order_id Order ID
     */
    public static function render_legacy_order_column($column, $order_id)
    {
        $column_definitions = self::get_column_definitions();
        if (! isset($column_definitions[$column])) {
            return;
        }

        $order = wc_get_order($order_id);
        if (! $order) {
            echo '—';
            return;
        }

        echo wp_kses_post(self::get_column_content($column, $order));
    }

    /**
     * Get the content for a specific column
     *
     * @param string $column Column key
     * @param WC_Order $order Order object
     * @return string Column content
     */
    public static function get_column_content($column, $order)
    {
        switch ($column) {
            case 'syspro_customer':
                return self::get_syspro_customer_number($order);

            case 'wc_customer':
                return self::get_wc_customer_number($order);

            case 'fq_account':
                return self::get_order_meta_with_fallback($order, '_fq_account_name');

            case 'syspro_order':
                return self::get_order_meta_with_fallback($order, 'syspro_order_number');

            case 'created_by':
                return self::get_created_by($order);

            case 'payment_method_custom':
                return self::get_payment_method($order);

            case 'card_type':
                return self::get_order_meta_with_fallback($order, '_wc_authorize_net_aim_card_type');

            case 'customer_custom':
                return self::get_customer_display($order);

            case 'email_custom':
                return self::get_email_display($order);

            case 'billing_state_custom':
                return self::get_order_meta_with_fallback($order, '_billing_state');

            case 'shipping_state_custom':
                return self::get_order_meta_with_fallback($order, '_shipping_state');

            default:
                return '—';
        }
    }

    /**
     * Get SYSPRO Customer # from user meta
     *
     * @param WC_Order $order Order object
     * @return string SYSPRO Customer # or fallback
     */
    public static function get_syspro_customer_number($order)
    {
        $customer_id = $order->get_customer_id();

        if (! $customer_id) {
            return '—';
        }

        // Use cached user data if available
        if (isset(self::$user_cache[$customer_id])) {
            $user_data = self::$user_cache[$customer_id];
        } else {
            $user_data = get_userdata($customer_id);
            if ($user_data) {
                self::$user_cache[$customer_id] = $user_data;
            }
        }

        if (! $user_data) {
            return '—';
        }

        $syspro_customer = get_user_meta($customer_id, self::SYSPRO_CUSTOMER_META_KEY, true);

        return $syspro_customer ? esc_html($syspro_customer) : '—';
    }

    /**
     * Get WC Customer # (user ID)
     *
     * @param WC_Order $order Order object
     * @return string WC Customer # or fallback
     */
    public static function get_wc_customer_number($order)
    {
        $customer_id = $order->get_customer_id();

        // Fallback to legacy meta if needed
        if (! $customer_id && ! self::is_hpos_enabled()) {
            $customer_id = get_post_meta($order->get_id(), '_customer_user', true);
        }

        return $customer_id ? esc_html($customer_id) : '—';
    }

    /**
     * Get Created By display
     *
     * @param WC_Order $order Order object
     * @return string Created By display or fallback
     */
    public static function get_created_by($order)
    {
        $created_by = $order->get_meta('_created_by');

        if (! $created_by) {
            return '—';
        }

        // If it's a numeric ID, try to get the user display name
        if (is_numeric($created_by)) {
            $user_id = intval($created_by);

            // Use cached user data if available
            if (isset(self::$user_cache[$user_id])) {
                $user_data = self::$user_cache[$user_id];
            } else {
                $user_data = get_userdata($user_id);
                if ($user_data) {
                    self::$user_cache[$user_id] = $user_data;
                }
            }

            if ($user_data) {
                return esc_html($user_data->display_name);
            }
        }

        // Return raw text if not a valid user ID
        return esc_html($created_by);
    }

    /**
     * Get Payment Method display
     *
     * @param WC_Order $order Order object
     * @return string Payment Method display or fallback
     */
    public static function get_payment_method($order)
    {
        $payment_method = $order->get_payment_method_title();

        if (! $payment_method) {
            // Try fallback meta keys
            $payment_method = $order->get_meta('_payment_method_title');
            if (! $payment_method) {
                $payment_method = $order->get_meta('_payment_method');
            }
        }

        return $payment_method ? esc_html($payment_method) : '—';
    }

    /**
     * Get Customer display with link to user profile if applicable
     *
     * @param WC_Order $order Order object
     * @return string Customer display HTML
     */
    public static function get_customer_display($order)
    {
        $customer_id = $order->get_customer_id();

        if ($customer_id) {
            // Use cached user data if available
            if (isset(self::$user_cache[$customer_id])) {
                $user_data = self::$user_cache[$customer_id];
            } else {
                $user_data = get_userdata($customer_id);
                if ($user_data) {
                    self::$user_cache[$customer_id] = $user_data;
                }
            }

            if ($user_data) {
                $edit_url = admin_url('user-edit.php?user_id=' . $customer_id);
                return sprintf(
                    '<a href="%s">%s</a>',
                    esc_url($edit_url),
                    esc_html($user_data->display_name)
                );
            }
        }

        // Fallback to billing name for guest orders
        $billing_name = $order->get_formatted_billing_full_name();
        return $billing_name ? esc_html($billing_name) : '—';
    }

    /**
     * Get Email display as mailto link
     *
     * @param WC_Order $order Order object
     * @return string Email display HTML or fallback
     */
    public static function get_email_display($order)
    {
        $email = $order->get_meta('_billing_email');

        if (! $email) {
            $email = $order->get_billing_email();
        }

        if ($email && is_email($email)) {
            return sprintf(
                '<a href="mailto:%s">%s</a>',
                esc_attr($email),
                esc_html($email)
            );
        }

        return '—';
    }

    /**
     * Get order meta with fallback
     *
     * @param WC_Order $order Order object
     * @param string $meta_key Meta key to retrieve
     * @return string Meta value or fallback
     */
    public static function get_order_meta_with_fallback($order, $meta_key)
    {
        $value = $order->get_meta($meta_key);
        return $value ? esc_html($value) : '—';
    }

    /**
     * Add sortable columns
     *
     * @param array $sortable Existing sortable columns
     * @return array Modified sortable columns
     */
    public static function add_sortable_columns($sortable)
    {
        $column_definitions = self::get_column_definitions();

        foreach ($column_definitions as $column_key => $definition) {
            if (! empty($definition['sortable'])) {
                if (! empty($definition['numeric'])) {
                    $sortable[$column_key] = array($column_key, true, __('Numeric', 'wc-hpos-custom-order-columns'));
                } else {
                    $sortable[$column_key] = $column_key;
                }
            }
        }

        return $sortable;
    }

    /**
     * Handle sorting for HPOS
     *
     * @param array $args Query arguments
     * @return array Modified query arguments
     */
    public static function handle_sorting($args)
    {
        if (! isset($_GET['orderby'])) {
            return $args;
        }

        $orderby = sanitize_text_field(wp_unslash($_GET['orderby']));
        $order = isset($_GET['order']) ? sanitize_text_field(wp_unslash($_GET['order'])) : 'asc';

        $column_definitions = self::get_column_definitions();

        if (! isset($column_definitions[$orderby])) {
            return $args;
        }

        $definition = $column_definitions[$orderby];

        // Handle specific column sorting
        switch ($orderby) {
            case 'syspro_customer':
                // Custom sorting for user meta - this is complex and may need custom query handling
                break;

            case 'wc_customer':
                $args['meta_key'] = '_customer_user';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = $order;
                break;

            case 'customer_custom':
                // Sort by billing name meta
                $args['meta_key'] = '_billing_first_name';
                $args['orderby'] = 'meta_value';
                $args['order'] = $order;
                break;

            default:
                if (! empty($definition['meta_key'])) {
                    $args['meta_key'] = $definition['meta_key'];
                    $args['orderby'] = ! empty($definition['numeric']) ? 'meta_value_num' : 'meta_value';
                    $args['order'] = $order;
                }
                break;
        }

        return $args;
    }

    /**
     * Handle sorting for Legacy orders
     *
     * @param WP_Query $query Query object
     */
    public static function handle_legacy_sorting($query)
    {
        if (! is_admin() || ! $query->is_main_query()) {
            return;
        }

        if ('shop_order' !== $query->get('post_type')) {
            return;
        }

        $orderby = $query->get('orderby');
        if (! $orderby) {
            return;
        }

        $column_definitions = self::get_column_definitions();

        if (! isset($column_definitions[$orderby])) {
            return;
        }

        $definition = $column_definitions[$orderby];

        // Handle specific column sorting for legacy
        switch ($orderby) {
            case 'wc_customer':
                $query->set('meta_key', '_customer_user');
                $query->set('orderby', 'meta_value_num');
                break;

            case 'customer_custom':
                $query->set('meta_key', '_billing_first_name');
                $query->set('orderby', 'meta_value');
                break;

            default:
                if (! empty($definition['meta_key'])) {
                    $query->set('meta_key', $definition['meta_key']);
                    $query->set('orderby', ! empty($definition['numeric']) ? 'meta_value_num' : 'meta_value');
                }
                break;
        }
    }

    /**
     * Add admin styles for column layout
     */
    public static function admin_styles()
    {
        $screen = get_current_screen();

        if (! $screen || ($screen->id !== 'woocommerce_page_wc-orders' && $screen->id !== 'edit-shop_order')) {
            return;
        }

        $css = "
        .wp-list-table .column-syspro_customer,
        .wp-list-table .column-wc_customer,
        .wp-list-table .column-syspro_order { width: 100px; }
        .wp-list-table .column-fq_account,
        .wp-list-table .column-created_by,
        .wp-list-table .column-payment_method_custom,
        .wp-list-table .column-card_type { width: 120px; }
        .wp-list-table .column-billing_state_custom,
        .wp-list-table .column-shipping_state_custom { width: 80px; }
        .wp-list-table .column-email_custom { width: 200px; }
        .wp-list-table .column-customer_custom { width: 150px; }
        ";

        wp_add_inline_style('wp-admin', $css);
    }

    /**
     * Load plugin textdomain
     */
    public static function load_textdomain()
    {
        load_plugin_textdomain('wc-hpos-custom-order-columns', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Show admin notice if WooCommerce is not active
     */
    public static function woocommerce_missing_notice()
    {
    ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('WooCommerce HPOS Custom Order Columns requires WooCommerce to be installed and active.', 'wc-hpos-custom-order-columns'); ?></p>
        </div>
<?php
    }
}

// Initialize the plugin - use early priority to ensure we run before other plugins
add_action('plugins_loaded', array('WC_HPOS_Custom_Order_Columns', 'init'), 5);

// Activation hook
register_activation_hook(__FILE__, function () {
    if (! class_exists('WooCommerce')) {
        wp_die(
            esc_html__('This plugin requires WooCommerce to be installed and active.', 'wc-hpos-custom-order-columns'),
            esc_html__('Plugin Activation Error', 'wc-hpos-custom-order-columns'),
            array('back_link' => true)
        );
    }
});
