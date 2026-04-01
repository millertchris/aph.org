<?php
/**
 *
 * Teacher request template
 *
 * The file is prone to modifications after plugin upgrade or alike; customizations are advised via hooks/filters
 *
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action('woocommerce_email_header', $email_heading, $email); ?>
<p>Your request has been submitted to your EOT for review and approval. If you have any questions about the approval process please reach out to him/her directly.</p>
<p>
<?php
if($eot_user_id){

    echo get_user_meta($eot_user_id, 'first_name', true) . ' ' . get_user_meta($eot_user_id, 'last_name', true);
    echo '<br />';
    echo get_user_meta($eot_user_id, 'billing_phone', true);
    echo '<br />';
    echo get_user_meta($eot_user_id, 'billing_email', true);

}

?>
</p>
<?php
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
