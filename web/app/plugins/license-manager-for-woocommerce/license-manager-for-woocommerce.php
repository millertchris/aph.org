<?php
/**
 * Plugin Name: License Manager for WooCommerce
 * Plugin URI: https://www.wpexperts.io/
 * Description: Easily sell and manage software license keys through your WooCommerce shop.
 * Version: 3.0.15
 * Author: LicenseManager
 * Author URI: https://www.licensemanager.at/
 * Requires at least: 4.7
 * Tested up to: 6.9
 * Requires PHP: 7.0
 * WC requires at least: 5.0
 * WC tested up to: 9.6
 */

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

require_once __DIR__ . '/vendor/freemius_integration.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/lmfwc-core-functions.php';
require_once __DIR__ . '/functions/lmfwc-license-functions.php';
require_once __DIR__ . '/functions/lmfwc-meta-functions.php';

// Define LMFWC_PLUGIN_FILE.
if (!defined('LMFWC_PLUGIN_FILE')) {
    define('LMFWC_PLUGIN_FILE', __FILE__);
    define('LMFWC_PLUGIN_DIR', __DIR__);
}

// Define LMFWC_PLUGIN_URL.
if (!defined('LMFWC_PLUGIN_URL')) {
    define('LMFWC_PLUGIN_URL', plugins_url('', __FILE__) . '/');
}

// Define LMFWC_VERSION.
if (!defined('LMFWC_VERSION')) {
    define('LMFWC_VERSION', '3.0.15');
}
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

/**
 * Main instance of LicenseManagerForWooCommerce.
 *
 * Returns the main instance of SN to prevent the need to use globals.
 *
 * @return Main
 */
function lmfwc()
{
    return Main::instance();
}

// Global for backwards compatibility.
$GLOBALS['license-manager-for-woocommerce'] = lmfwc();

// Add generic Pro conversion admin notice - show on all admin pages
add_action('admin_notices', function() {
    // Check if notice is dismissed
    $dismissed = get_user_meta(get_current_user_id(), 'lmfwc_pro_notice_dismisseds', true);
    if ($dismissed) {
        return;
    }

    // Only show to users who can manage options
    if (!current_user_can('manage_options')) {
        return;
    }

    ?>
    <div class="lmfwc-pro-conversion-notice notice notice-info is-dismissible" data-dismiss-action="lmfwc_dismiss_pro_notice">
        <div class="lmfwc-pro-notice-banner">
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            <div class="lmfwc-banner-confetti">
                <?php for ($i = 1; $i <= 18; $i++): ?>
                    <div class="lmfwc-banner-confetti-item lmfwc-banner-confetti-<?php echo $i; ?>"></div>
                <?php endfor; ?>
            </div>
            <div class="lmfwc-pro-notice-content">
                <div class="lmfwc-black-friday-section">
                    <div class="lmfwc-pro-notice-left">
                        <div class="lmfwc-pro-notice-icon lmfwc-celebration-icon">
                            🎉
                            <div class="lmfwc-confetti-container">
                                <div class="lmfwc-confetti lmfwc-confetti-1"></div>
                                <div class="lmfwc-confetti lmfwc-confetti-2"></div>
                                <div class="lmfwc-confetti lmfwc-confetti-3"></div>
                                <div class="lmfwc-confetti lmfwc-confetti-4"></div>
                                <div class="lmfwc-confetti lmfwc-confetti-5"></div>
                                <div class="lmfwc-confetti lmfwc-confetti-6"></div>
                                <div class="lmfwc-confetti lmfwc-confetti-7"></div>
                                <div class="lmfwc-confetti lmfwc-confetti-8"></div>
                            </div>
                        </div>
                        <div class="lmfwc-pro-notice-text">
                            <h3 class="lmfwc-bf-heading">
                                <a href="https://licensemanager.at/pricing/?utm_source=plugin&utm_medium=admin_notice&utm_campaign=black_friday&coupon=LMFW25" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e('BLACK FRIDAY DEALS', 'license-manager-for-woocommerce'); ?>
                                </a>
                            </h3>
                            <div class="lmfwc-bf-text">
                                <?php 
                                printf(
                                    esc_html__('License Manager For WooCommerce On Lifetime Plans - extra %s off', 'license-manager-for-woocommerce'),
                                    '<strong>25%</strong>'
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="lmfwc-pro-notice-right">
                        <button type="button" class="lmfwc-coupon-code-btn shine-button" data-coupon="LMFW25" aria-label="<?php esc_attr_e('Copy coupon code LMFW25', 'license-manager-for-woocommerce'); ?>">
                            <span class="lmfwc-coupon-text"><?php esc_html_e('Use Code', 'license-manager-for-woocommerce'); ?> <strong>LMFW25</strong></span>
                        </button>
                        <a href="https://licensemanager.at/pricing/?utm_source=plugin&utm_medium=admin_notice&utm_campaign=black_friday&coupon=LMFW25" target="_blank" rel="noopener noreferrer" class="lmfwc-pro-notice-button shine-button">
                            <?php esc_html_e('Upgrade to Pro', 'license-manager-for-woocommerce'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
});

add_action('admin_enqueue_scripts', function($hook) {
    $dismissed = get_user_meta(get_current_user_id(), 'lmfwc_pro_notice_dismisseds', true);
    if ($dismissed) {
        return;
    }

    wp_enqueue_style('lmfwc-pro-notice', LMFWC_PLUGIN_URL . 'assets/css/pro-notice.css', array(), LMFWC_VERSION);
    wp_enqueue_script('lmfwc-pro-notice', LMFWC_PLUGIN_URL . 'assets/js/pro-notice.js', array('jquery'), LMFWC_VERSION, true);
    wp_localize_script('lmfwc-pro-notice', 'lmfwcProNotice', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lmfwc_dismiss_pro_notice')
    ));
});

add_action('wp_ajax_lmfwc_dismiss_pro_notice', function() {
    check_ajax_referer('lmfwc_dismiss_pro_notice', 'nonce');
    update_user_meta(get_current_user_id(), 'lmfwc_pro_notice_dismisseds', true);
    wp_send_json_success();
});
