/*!
 * dosomething
 * Fiercely quick and opinionated front-ends
 * https://HosseinKarami.github.io/fastshell
 * @author Hossein Karami
 * @version 1.0.5
 * Copyright 2022. MIT licensed.
 */
function validate_po(event) {
  event = event || window.event;
  event.preventDefault();
  var poNumber = '';
  var customerId = '';
  // Set the value if the field is present
  if(jQuery('#po_number').length > 0 && jQuery('#po_number').val() !== '' && jQuery('#po_number').val() !== ' '){
    poNumber = jQuery('#po_number').val()
  } else {
    return false;
  }
  // Show the loader icon
  jQuery('#po_number_loader').show();
  // Remove the existing message box
  jQuery('.validate-po-error').remove();
  // Get the customer id if that field is present
  if(jQuery('#customer_user').length > 0){
    customerId = jQuery('#customer_user').val();
  }
  jQuery.ajax({
    url: ajax_obj.ajaxurl, // or ajax_obj.ajaxurl if using on frontend
    data: {
      'action': 'validate_po',
      'security': ajax_obj.po_ajax_nonce,
      'po_number': poNumber,
      'customer_id': customerId
    },
    success: function (data) {
      // This outputs the result of the ajax request
      // If this is admin screen, alert a message, else add to dom
      if(jQuery('body').hasClass('wp-admin')){
        if (data == 'TRUE') {
          alert('This is a duplicate PO Number.');
        } else {
          alert('This is a new PO Number.');
        }
      } else {
        if (data == 'TRUE') {
          jQuery('#po_number_validate').after('<div class="validate-po-error woocommerce-error">This is a duplicate PO Number.</div>');
        } else {
          jQuery('#po_number_validate').after('<div class="validate-po-error woocommerce-message">This is a new PO Number.</div>');
        }
      }
    },
    error: function (errorThrown) {
      jQuery('#po_number_validate').after('<div class="validate-po-error woocommerce-error">There was a problem checking this PO Number.</div>');
    },
    complete: function(){
      jQuery('#po_number_loader').hide();
    }
  });

}
jQuery(document).ready(function ($) {
  if(jQuery('#po_number').length > 0){
    jQuery('#po_number').after('<a id="po_number_validate" href="#" title="Check this PO Number for potential duplicates" onclick="validate_po(event)" style="display: inline-block; padding: 5px 0;">Check PO Number</a><img id="po_number_loader" style="display: none; width: 18px; margin-left: 5px; position: relative; top: 5px;" src="'+stylesheet_directory_uri+'/app/assets/img/loader.gif" alt="Loading"/>');
    jQuery('#po_number').blur(function(evt) {
      validate_po(evt);
    });
  }
});