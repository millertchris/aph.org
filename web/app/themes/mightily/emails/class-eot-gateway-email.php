<?php

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Welcome Email class used to send out welcome emails to customers purchasing a course
 *
 * @extends \WC_Email
 */

class EOT_Gateway_Email extends WC_Email
{

    /**
     * Set email defaults
     */
    public function __construct()
    {

        // Unique ID for custom email
        $this->id = 'eot_gateway_email';

        // Is a customer email
        $this->customer_email = false;

        // Title field in WooCommerce Email settings
        $this->title = __('EOT Gateway Email', 'woocommerce');

        // Description field in WooCommerce email settings
        $this->description = __('This email is sent to an EOT when a new order is placed with the EOT payment gateway.', 'woocommerce');

        // Default heading and subject lines in WooCommerce email settings
        $this->subject = apply_filters('eot_gateway_email_default_subject', __('New EOT Request', 'woocommerce'));
        $this->heading = apply_filters('eot_gateway_email_default_heading', __('An EOT request is waiting for your approval.', 'woocommerce'));

        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar

        $this->template_base  = dirname(__FILE__) . '/templates/';	// Fix the template base lookup for use on admin screen template path display
        $this->template_html  = 'eot-gateway-email.php';
        $this->template_plain = 'plain-eot-gateway-email.php';

        $this->group_id = '';
        // Trigger email EOT submits invitation form
        //		add_action( 'woocommerce_payment_complete', array( $this, 'trigger' ) );
        //		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'trigger' ) );
        //		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'trigger' ) );
        //		add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'trigger' ) );
        //		add_action( 'woocommerce_order_status_failed_to_completed', array( $this, 'trigger' ) );
        //		add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'trigger' ) );

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();
    }


    /**
     * Prepares email content and triggers the email
     *
     * @param int $order_id
     */
    public function trigger($order_id, $recipient_email)
    {

        //* Maybe include an additional check to make sure that the online training program account was created
        /* Uncomment and add your own conditional check
        $online_training_account_created = get_post_meta( $this->object->id, '_crwc_user_account_created', 1 );

        if ( ! empty( $online_training_account_created ) && false === $online_training_account_created ) {
            return;
        }
        */

        /* Proceed with sending email */
        // $encrypter = new AphEncrypter(CRYPT_KEY);

        // $this->group_id = urlencode($group_id);

        // $this->recipient = $encrypter->decryptString($recipient_email);
        $this->recipient = $recipient_email;

        // All well, send the email
        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

        // add order note about the same
        // $this->object->add_order_note( sprintf( __( '%s email sent to the customer.', 'woocommerce' ), $this->title ) );
    }

    /**
     * get_content_html function.
     *
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html($this->template_html, array(
            'order'					=> $this->object,
            'email_heading'			=> $this->heading,
            'sent_to_admin'			=> false,
            'plain_text'			=> false,
            'email'					=> $this
            // 'group_id'              => $this->group_id
        ));
    }


    /**
     * get_content_plain function.
     *
     * @return string
     */
    public function get_content_plain()
    {
        return wc_get_template_html($this->template_plain, array(
            'order'					=> $this->object,
            'email_heading'			=> $this->heading,
            'sent_to_admin'			=> false,
            'plain_text'			=> true,
            'email'					=> $this
        ));
    }


    /**
     * Initialize settings form fields
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __('Enable/Disable', 'woocommerce'),
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes'
            ),
            'subject'    => array(
                'title'       => __('Subject', 'woocommerce'),
                'type'        => 'text',
                'description' => sprintf('This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject),
                'placeholder' => '',
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => __('Email Heading', 'woocommerce'),
                'type'        => 'text',
                'description' => sprintf(__('This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.'), $this->heading),
                'placeholder' => '',
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => __('Email type', 'woocommerce'),
                'type'        => 'select',
                'description' => __('Choose which format of email to send.', 'woocommerce'),
                'default'       => 'html',
                'class'         => 'email_type wc-enhanced-select',
                'options'     => array(
                    'plain'	    => __('Plain text', 'woocommerce'),
                    'html' 	    => __('HTML', 'woocommerce'),
                    'multipart' => __('Multipart', 'woocommerce'),
                )
            )
        );
    }
}
