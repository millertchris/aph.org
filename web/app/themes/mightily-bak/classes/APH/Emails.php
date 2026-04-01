<?php
/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-08-22
 * Time: 09:56
 */

namespace APH;

/**
 * Manage helpers for the templates and overrides.
 *
 * Class Templates
 * @package APH
 */
class Emails
{
    // =========================================================================
    // SEND EMAIL TO TEACHER WHEN ORDER IS APPROVED BY EOT
    // =========================================================================
    static function maybe_send_teacher_processing_email($order_id) {
        $order = wc_get_order($order_id);
        // Check if customer id is a teacher and this is an account funds order
        $userdata = get_userdata($order->get_customer_id());
        if(($order->get_payment_method() == 'accountfunds' || $order->get_payment_method() == 'eot_gateway') && in_array('teacher', $userdata->roles)){
            // Need to send an email to the customer that the order has been approved
            $emails = wc()->mailer()->emails;
            // Access the default subject for new order email notifications
            $teacher_processing_email = new $emails['Teacher_Processing_Email'];
            // Trigger the notification email
            $teacher_processing_email->trigger($order_id, $order);             
        }
    }

    // =========================================================================
    // SEND EMAIL TO EOT AND TEACHER WHEN NEW REQUEST IS MADE
    // =========================================================================
    static function maybe_send_new_request_emails($order_id) {
        // After an order is placed

        // Using the order_id, get the order object
        $order = wc_get_order($order_id);

        // if the payment gateway is EOT funds
        if ($order->get_payment_method() == 'eot_gateway') {

            // Send an email to the EOT that is in the same group as the user � there may be more than one

            // Get id of user who placed the order
            $user_id = $order->get_user_id();       

            // Use the user id to get their group id
            foreach (wp_get_terms_for_user($user_id, 'user-group') as $group) {
                $user_group_id = $group->term_id;
            }

            // file_put_contents('jen.txt', print_r($group, true));

            // Loop through all users with role EOT
            $args = [
                'role' => 'eot'
            ];
            // Create the WP_User_Query object
            $wp_user_query = new \WP_User_Query($args);
            $users = $wp_user_query->get_results();

            // file_put_contents('users.txt', print_r($users, true));
            $eot_user_id = false;

            if (!empty($users)) {

                // loop through each EOT user
                foreach ($users as $user) {

                    // print all the user's data
                    // file_put_contents('user-info.txt', print_r($user, true));

                    $user_info = get_userdata($user->ID);

                    // get the user id of all EOTs
                    $id = $user_info->ID;
                    // file_put_contents('user-id.txt', print_r($id, true));

                    // find the EOT's group id
                    foreach (wp_get_terms_for_user($id, 'user-group') as $eot_group) {
                        $eot_group_id = $eot_group->term_id;

                        // if the EOT's group id matches the group id from the user who placed the order
                        if ($eot_group_id == $user_group_id) {

                            // set the var for email to teacher
                            $eot_user_id = $id;

                            // get the email address of the EOT
                            $eot_email = $user_info->user_email;
                            // file_put_contents('user-email.txt', print_r($eot_email, true));

                            // Get all the email class instances
                            $emails = wc()->mailer()->emails;

                            // Access the default subject for new order email notifications
                            $new_eot_request = new $emails['EOT_Gateway_Email'];
                            // file_put_contents('eot-email.txt', print_r($new_eot_request, true));

                            // Trigger the notification email
                            $new_eot_request->trigger($order_id, $eot_email);
                        }
                    }
                }
            }
            // Trigger email to teacher who submitted the order
            // Get the userdata of the teacher who placed the order
            $current_user_info = get_userdata($user_id);
            $current_user_email = $current_user_info->user_email;
            // Get all the email class instances
            $current_emails = wc()->mailer()->emails;
            // Access the default subject for new order email notifications
            $teacher_request = new $current_emails['Teacher_Request_Email'];
            // file_put_contents('eot-email.txt', print_r($new_eot_request, true));
            // Trigger the notification email
            $teacher_request->trigger($order_id, $current_user_email, $eot_user_id); 

        }
    }

    // =========================================================================
    // SEND LICENSE KEY EMAIL TO SECONDARY EMAIL ADDRESS MANUAL TRIGGER
    // =========================================================================
    static function maybe_send_secondary_license_email_manual(){
        global $post;
        $order = wc_get_order($post->ID);
        $digital_download_email = $order->get_meta('_digital_download_email');
        if($digital_download_email && $digital_download_email != ''){
            $emails = wc()->mailer()->emails;
            // Access the default subject for new order email notifications
            $secondary_email = new $emails['Secondary_Download_Email'];
            $secondary_email->trigger($order->get_id(), false, $digital_download_email);
        }
    }

    // =========================================================================
    // SEND LICENSE KEY EMAIL TO SECONDARY EMAIL ADDRESS AUTOMATIC TRIGGER
    // =========================================================================
    static function maybe_send_secondary_license_email_automatic($order_id){
        $order = wc_get_order($order_id);
        $digital_download_email = $order->get_meta('_digital_download_email');
        $digital_download_email_sent = $order->get_meta('_digital_download_email_sent');
        if($digital_download_email && $digital_download_email != ''){
            // If email has been sent, dont send again
            if($digital_download_email_sent != '1'){
                $emails = wc()->mailer()->emails;
                // Access the default subject for new order email notifications
                $secondary_email = new $emails['Secondary_Download_Email'];
                $secondary_email->trigger($order->get_id(), false, $digital_download_email);
                // Add order meta so we know the secondary email has been sent. We don't want to send duplicates.
                update_post_meta($order_id, '_digital_download_email_sent', '1');
            }
        }
    }
    
    // =========================================================================
    // CHANGE THE INVOICE EMAIL HEADING IF THE ORDER HAS QUOTE STATUS
    // =========================================================================
    static function maybe_change_email_heading($heading, $order){
        if($order->get_status() == 'quote'){
            if($order->get_meta('reminder_email_sent') == '1'){
                $heading = 'Reminder, Quote #' . $order->get_id() . ' is open';
                $order->update_meta_data('reminder_email_sent', '0');
            } else {
                $heading = 'Quote #' . $order->get_id();
            }
        }
        return $heading;
    }
    
    // =========================================================================
    // ADD SOME ADDITIONAL CONTENT TO QUOTE EMAIL
    // =========================================================================
    static function maybe_add_quote_content($order, $sent_to_admin, $plain_text, $email){
        if($order->get_status() == 'quote'){ ?>
            <p>To confirm your quote, please submit a Purchase Order referencing the above quote number to <a href="mailto:cs@aph.org">CS@aph.org</a>, or contact APH Customer Service to pay by credit card. For this or other questions regarding this quote, please contact APH Customer Service at 1-800-223-1839 between 8am and 8pm EST Monday through Friday.</p>
            <p>Pricing is valid for the next 30 days. All sales are in USD. Customer is responsible for import fees, taxes, and duties.</p>
        <?php }
    }    

}
