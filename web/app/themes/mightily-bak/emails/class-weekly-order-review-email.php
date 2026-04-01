<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Weekly Email Update for EOTs/OOAs showing the week's orders
 *
 * @extends \WC_Email
 */

class Weekly_Order_Review_Email extends WC_Email {
	
	/**
	 * Set email defaults
	 */
	public function __construct() {

		// Unique ID for custom email
		$this->id = 'weekly_order_review_email';
	
		// Is a customer email
		$this->customer_email = true;
		
		// Title field in WooCommerce Email settings
		$this->title = __( 'Weekly Order Review Email', 'woocommerce' );

		// Description field in WooCommerce email settings
		$this->description = __( 'This email is sent weekly to EOTs/OOAs, giving them a summary of the orders over the previous quota year.', 'woocommerce' );

		// Default heading and subject lines in WooCommerce email settings
		$this->subject = apply_filters( 'weekly_order_review_email_default_subject', __( 'Weekly Order Review', 'woocommerce' ) );
		$this->heading = apply_filters( 'weekly_order_review_email_default_heading', __( 'Weekly Order Review', 'woocommerce' ) );
		
		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		
		$this->template_base  = dirname(__FILE__) . '/templates/';	// Fix the template base lookup for use on admin screen template path display
		$this->template_html  = 'weekly-order-review-email.php';
		$this->template_plain = 'plain-weekly-order-review-email.php';

		$this->user = null;
		
		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

	}


	/**
	 * Prepares email content and triggers the email
	 *
	 * @param int $order_id
	 */
	public function trigger($user, $email_address) {

		$this->user = $user;
		$this->recipient = $email_address;
		
		// All well, send the email
		$this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

	
	}
	
	/**
	 * get_content_html function.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'email_heading'			=> $this->heading,
			'sent_to_admin'			=> false,
			'plain_text'			=> false,
			'email'					=> $this,
			'user'                  => $this->user
		) );
	}


	/**
	 * get_content_plain function.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'email_heading'			=> $this->heading,
			'sent_to_admin'			=> false,
			'plain_text'			=> true,
			'email'					=> $this,
			'user'                  => $this->user
		) );
	}


	/**
	 * Initialize settings form fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes'
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'woocommerce' ),
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'woocommerce' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
				'default'       => 'html',
				'class'         => 'email_type wc-enhanced-select',
				'options'     => array(
					'plain'	    => __( 'Plain text', 'woocommerce' ),
					'html' 	    => __( 'HTML', 'woocommerce' ),
					'multipart' => __( 'Multipart', 'woocommerce' ),
				)
			)
		);
	}
}

?>