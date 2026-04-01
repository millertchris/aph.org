<?php

    // =========================================================================
    // DEVELOPER TOOLS
    // =========================================================================
    require_once( dirname(__FILE__) . '/functions/tools/init.php');
    require_once( dirname(__FILE__) . '/classes/autoload.php');

    // =========================================================================
    // HELPER FUNCTIONS
    // =========================================================================
    require_once dirname(__FILE__) . '/functions/helpers.php'; //Functions that are used throughout the theme

    // =========================================================================
    // WORDPRESS HOOKS AND FUNCTIONS
    // =========================================================================

    require_once dirname(__FILE__) . '/functions/wp/base.php'; //Pulls in all of the functions that we create
    require_once dirname(__FILE__) . '/functions/wp-hooks.php'; //Action and filter hooks that use our functions

    // =========================================================================
    // CUSTOM POST TYPES
    // =========================================================================
    require_once dirname(__FILE__) . '/functions/cpt/cpt-documents.php';
    require_once dirname(__FILE__) . '/functions/cpt/cpt-people.php';
    require_once dirname(__FILE__) . '/functions/cpt/cpt-addresses.php';
    // require_once dirname(__FILE__) . '/functions/cpt/cpt-faq.php';

    // =========================================================================
    // WOOCOMMERCE HOOKS AND FUNCTIONS
    // =========================================================================
    require_once dirname(__FILE__) . '/functions/wc/base.php';
    require_once dirname(__FILE__) . '/functions/wc-hooks.php';

    require_once dirname(__FILE__) . '/functions/wc/class-wc-simple-registration.php';

    add_action( 'wo_before_authorize_method', 'custom_login_redirect' );
    function custom_login_redirect() {
        if ( ! is_user_logged_in() ) {
            wp_redirect( site_url() . '/my-account?redirect_to=' . urlencode( site_url() . $_SERVER['REQUEST_URI'] ) );
            exit;
        }
    }

//     add_action("init", "remove_cron_job"); 
// function remove_cron_job() {
//  wp_clear_scheduled_hook("wsal/log_alert"); 
// }

add_filter( 'woocommerce_disable_order_scheduling', '__return_true' );   

?>