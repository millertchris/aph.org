<?php

// =========================================================================
// THIS FILE EXTENDS WOOCOMMERCE TO SUPPORT CSR
// =========================================================================

// =========================================================================
// Hide 'Delete Note' from CSR
// =========================================================================
function csr_hide_delete_note() {
	if (is_user_role('copy_of_csr')) { ?>
		<style>
			.row-actions span.switch_to_user {
				display: none;
			}
		</style>
	<?php }
    if (is_user_role('copy_of_csr') || is_user_role('administrator')) { ?>
		<style>
			.delete_note {
				display: none;
			}
			#woocommerce-order-items tbody tr .wc-order-edit-line-item-actions {
				visibility: visible;
			}
		</style>
	<?php }
}

// =========================================================================
// Hide 'Move to trash' from CSR
// =========================================================================
function csr_hide_move_to_trash() {
    if (is_user_role('copy_of_csr')) { ?>
		<style>
			#delete-action {
				display: none;
			}			
		</style>
	<?php }
}

// =========================================================================
// Hide Order Line Items from CSR Until The Order Is Created
// =========================================================================
function csr_hide_line_items_before_creation() {
    if (is_user_role('copy_of_csr') || is_user_role('administrator')) { ?>
		<style>
			body.hide-line-items #woocommerce-order-items {
				opacity: 0.5;
				pointer-events: none;
			}			
		</style>
	<?php }
}

// =========================================================================
// ADD LOGOUT LINK TO CSR ADMIN BAR
// =========================================================================
function csr_add_logout($admin_bar) {
    if (is_user_role('copy_of_csr') || is_user_role('eot') || is_user_role('eot-assistant')) {
        $admin_bar->add_menu([
            'id'    => 'csr-logout',
            'title' => 'Logout',
            'href'  => wp_logout_url(get_home_url()),
            'meta'  => [
                'title' => __('Logout'),
            ],
        ]);
    }
}

// =========================================================================
// DISABLING ORDER STATUS OPTIONS
// =========================================================================

function csr_remove_processing_status( $wc_statuses_arr ){
	if (is_user_role('copy_of_csr')) {
		$new_statuses_arr = array(
			'wc-pending' => $wc_statuses_arr['wc-pending'],
			'wc-processing' => $wc_statuses_arr['wc-processing'],
			'wc-completed' => $wc_statuses_arr['wc-completed'],
			'wc-failed' => $wc_statuses_arr['wc-failed'],
			'wc-cancelled' => $wc_statuses_arr['wc-cancelled']
		);
	
		return $new_statuses_arr;
	} else {
		$new_statuses_arr = array(
			'wc-pending'    => _x( 'Pending payment', 'Order status', 'woocommerce' ),
			'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ),
			'wc-on-hold'    => _x( 'On hold', 'Order status', 'woocommerce' ),
			'wc-completed'  => _x( 'Completed', 'Order status', 'woocommerce' ),
			'wc-cancelled'  => _x( 'Cancelled', 'Order status', 'woocommerce' ),
			'wc-refunded'   => _x( 'Refunded', 'Order status', 'woocommerce' ),
			'wc-failed'     => _x( 'Failed', 'Order status', 'woocommerce' ),
		);
		return $new_statuses_arr;

	}
}

// =========================================================================
// Update Payment Page Link
// =========================================================================
function csr_update_customer_payment_link() {
    if (is_user_role('copy_of_csr')) {
        ?>
		<script>
			jQuery(document).ready(function(){
				if(jQuery('body').hasClass('post-type-shop_order')){
					if(jQuery('p.wc-order-status label a').length > 0){
						jQuery('p.wc-order-status label a').attr('onclick',
							"window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=800,height=600');return false;"
						);
					}
					if(jQuery('#_shipping_address_1').length > 0){
						jQuery('#_shipping_address_1').attr('placeholder', 'No PO box allowed');
					}
				}
			});
		</script>
	<?php
    }
}

// =========================================================================
// Hide Private Note Option
// =========================================================================
function csr_hide_private_note_option() {
    if (!is_user_role('administrator')) {
        ?>
		<script>
			jQuery(document).ready(function(){
				if(jQuery('#order_note_type').length > 0){
					jQuery('#order_note_type').find('option').first().remove();
					jQuery('#order_note_type').val('customer');
				}
			});
		</script>
	<?php
    }
}

// =========================================================================
// Update Pseduo Order Status Meta Box
// =========================================================================
function csr_update_pseudo_order_status() { ?>
	<script>
		jQuery(document).ready(function(){
			var pseudo_order_status_select = jQuery('#pseduo-order-status-select');
			var real_order_status_select = jQuery('#order_status');
			if(jQuery('#pseduo-order-status-select').length > 0){
				// Set up the event handlers
				pseudo_order_status_select.change(function(){
					real_order_status_select.val(jQuery(this).val()).trigger('change');
				});
				real_order_status_select.change(function() {
					pseudo_order_status_select.val(jQuery(this).val());
				});
				// Set up the options for the pseudo select
				//alert(real_order_status_select.html());
				pseudo_order_status_select.html(real_order_status_select.html());
			}
		});
	</script>
	<?php
}

// =========================================================================
// Update Order Edit Screen with Quote Text
// =========================================================================
function csr_update_quote_status() { ?>
	<script>
		jQuery(document).ready(function(){
			var order_heading = jQuery('.woocommerce-order-data__heading');
			var quote_age = jQuery('.woocommerce-order-quote-age');
			var order_status = jQuery('#order_status');
			function updateQuoteUI(){
				// Change heading
				order_heading.text(order_heading.text().replace('Order', 'Quote'));
				// Show quote age
				quote_age.show();
			}
			function updateOrderUI(){
				// Change heading
				order_heading.text(order_heading.text().replace('Quote', 'Order'));
				// Hide quote age
				quote_age.hide();
			}
			function checkOrderStatus(){
				if(order_status.val() == 'wc-quote'){
					updateQuoteUI();
				} else {
					updateOrderUI();
				}
			}
			if(order_heading.length > 0){
				checkOrderStatus();
				order_status.on('change', function(){
					checkOrderStatus();
				});
			}
		});
	</script>
	<?php
}

// =========================================================================
// Making some changes to the dom for better accessibility
// =========================================================================
function csr_accessibility_enhancements() { ?>
	<script>
		jQuery(document).ready(function(){
			// Add aria labels on load and after items are added to order list
			jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
			jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');			
			document.querySelector("#woocommerce-order-items").addEventListener("addProductEvent", (event) => {
				jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
				jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
			});
			document.querySelector("#woocommerce-order-items").addEventListener("addShippingEvent", (event) => {
				jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
				jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
			});
			document.querySelector("#woocommerce-order-items").addEventListener("deleteItemEvent", (event) => {
				jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
				jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
			});			
			document.querySelector("#woocommerce-order-items").addEventListener("addTaxEvent", (event) => {
				jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
				jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
			});
			document.querySelector("#woocommerce-order-items").addEventListener("deleteTaxEvent", (event) => {
				jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
				jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
			});					
			jQuery(document.body).on('order-totals-recalculate-complete', function(){
				jQuery('#woocommerce-order-items a.edit-order-item').attr('aria-label', 'Edit Order Item');
				jQuery('#woocommerce-order-items a.delete-order-item').attr('aria-label', 'Delete Order Item');
			});
			// Add aria labels for billing and shipping address
			jQuery('a.edit_address').first().attr('aria-label', 'Edit Billing Address');
			jQuery('a.edit_address').last().attr('aria-label', 'Edit Shipping Address');				
		});
	</script>
	<?php
}

// =========================================================================
// Require certain fields before order can be created
// =========================================================================
function csr_order_validation() { ?>
	<?php if (is_user_role('copy_of_csr')) : ?>
	<script>
		jQuery(document).ready(function(){
			function checkIfValid(){
				var orderButton = jQuery('.button.save_order.button-primary');
				var inputsValid = true;
				// Remove required span from our three primary fields
				jQuery('p._billing_email_field label span').remove();
				jQuery('p.po_number_field label span').remove();
				jQuery('p.fq_account_field label span').remove();
				if(wc_enhanced_select_params.current_customer_role == 'eot' || wc_enhanced_select_params.current_customer_role == 'eot-assistant' || wc_enhanced_select_params.current_customer_role == 'teacher'){
					var requiredInputs = jQuery('#_billing_email, #po_number, #fq_account');
					// Add required label
					jQuery('p._billing_email_field label').append('<span> (Required)</span>');
					jQuery('p.po_number_field label').append('<span> (Required)</span>');
					jQuery('p.fq_account_field label').append('<span> (Required)</span>');
				} else {
					var requiredInputs = jQuery('#_billing_email');
					// Add required label
					jQuery('p._billing_email_field label').append('<span> (Required)</span>');					
				}
				requiredInputs.each(function(){
					if(jQuery(this).val() == '' || jQuery(this).val() == '-1'){
						inputsValid = false;
					}
				});
				if(inputsValid){
					orderButton.removeAttr('disabled');
				} else {
					orderButton.attr('disabled', 'true');
				}
			}
			// wc_enhanced_select_params.current_customer_role
			if((jQuery('body').hasClass('post-new-php') || jQuery('body').hasClass('post-php')) && jQuery('body').hasClass('post-type-shop_order')){
				checkIfValid();
				jQuery('#customer_user, #_billing_email, #po_number, #fq_account').on('focus blur change', function(){
					// Have to set timeout so global var has time to change
					setTimeout(function(){
						checkIfValid();
					}, 100);
				});
			}	
		});
	</script>
	<?php endif; ?>
	<?php
}

function csr_order_note_templates() { ?>
	<?php if (is_user_role('copy_of_csr') || is_user_role('administrator')) : ?>
		<script>

			jQuery(document).ready(function(){
				if(jQuery('body').hasClass('post-type-shop_order') && jQuery('button.add_note.button').length > 0){
					var orderNoteTemplates = [
						{
							label: 'Discontinued Item',
							text: 'The following product(s) has been discontinued and was removed from your order. If you have any questions, please contact our Customer Service Team at 1.800.223.1839.'
						},
						{
							label: 'FQ Ineligibility',
							text: 'The following product(s) is not eligible for purchase with Federal Quota funding and was removed from your order. If you have any questions, please contact our Customer Service Team at 1.800.223.1839.'
						},	
						{
							label: 'App Store Download',
							text: 'The following product(s) is available for free, immediate download from the App Store. If you have any questions, please contact our Customer Service Team at 1.800.223.1839.'
						},
						{
							label: 'Free Download ',
							text: 'The following product(s) is available for free, immediate use at the website listed below. If you have any questions, please contact our Customer Service Team at 1.800.223.1839.'
						},
						{
							label: 'Processed Separately',
							text: 'The following product(s) was processed separately and will not appear on your Order Dashboard. If you have any questions, please contact our Customer Service Team at 1.800.223.1839.'
						},															
					];		
					
					jQuery(document).on('click', 'a.add_note_template', function(){
						var thisText = jQuery(this).data('message');
						jQuery('#add_order_note').val(thisText);						
					});

					jQuery.each(orderNoteTemplates, function( index, value ) {
						jQuery('button.add_note.button').after('<a class="add_note_template" href="#insertMessage" role="button" data-message="' + value.text + '">' + value.label + '</a><br />');
					});

					jQuery('button.add_note.button').after('<p>Order Note Templates</p>');

				}
			});
		</script>
	<?php endif; ?>
	<?php
}

?>
