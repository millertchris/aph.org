/*!
 * dosomething
 * Fiercely quick and opinionated front-ends
 * https://HosseinKarami.github.io/fastshell
 * @author Hossein Karami
 * @version 1.0.5
 * Copyright 2023. MIT licensed.
 */
function check_order_status(email_address, order_number) {
    // console.log('form submitted');
    if (email_address == '' || order_number == '') {
        alert('Email address and order number are required.');
        return false;
    }
    // Disable the form
    jQuery('#order-status-form').addClass('is-loading');
    // Clear the previous results
    jQuery('#order-status-result').empty();
    $.ajax({
        url: order_status_obj.ajaxurl, // or ajax_obj.ajaxurl if using on frontend
        data: {
            'action': 'check_order_status',
            'security': order_status_obj.order_status_ajax_nonce,
            'email_address': email_address,
            'order_number': order_number
        },
        success: function (data) {
            var resultEl = jQuery('#order-status-result');
            if (data == 'NO_ORDER') {
                resultEl.html(
                    '<h2 class="h4">Order not found.</h2>'
                );
            }
            else if (data == 'NO_MATCH') {
                resultEl.html(
                    '<h2 class="h4">Email address does not match the order on file.</h2>'
                );
            }
            else {
                data = JSON.parse(data);
                var order_products_string = '';
                var order_po_string = '';
                jQuery.each(data.order_products, function (index, value) {
                    order_products_string += '<li>' + value + '</li>';
                });
                if (data.order_po && data.order_po != '') {
                    order_po_string = '<span class="order-po">PO Number: ' + data.order_po + '</span><br />';
                }
                resultEl.html(
                    '<h2 class="h4">Details for Order #' + data.order_number + '</h2>' +
                    '<span class="order-status">Status: ' + data.order_status + '</span><br />' +
                    order_po_string +
                    '<span class="order-total">Order Total: ' + data.order_total + '</span><br />' +
                    '<span class="order-products-title">Order Items:</span>' +
                    '<ul class="order-products">' + order_products_string + '</ul>'
                );
            }
        },
        error: function (errorThrown) {
            //$('#po_number_validate').after('<div class="validate-po-error woocommerce-error">There was a problem checking this PO Number.</div>');
        },
        complete: function () {
            jQuery('#order-status-form').removeClass('is-loading');
        }
    });
}
jQuery(document).ready(function ($) {
    jQuery('#order-status-form').submit(function (evt) {
        evt.preventDefault();
        check_order_status(jQuery('#order-status-field-email-input').val(), jQuery('#order-status-field-number-input').val());
    });
    // if ($('#po_number').length > 0) {
    //     $('#po_number').after('<a id="po_number_validate" href="#" title="Check this PO Number for potential duplicates" onclick="validate_po(event)" style="display: inline-block; padding: 5px 0;">Check PO Number</a><img id="po_number_loader" style="display: none; width: 18px; margin-left: 5px; position: relative; top: 5px;" src="' + stylesheet_directory_uri + '/app/assets/img/loader.gif" alt="Loading"/>');
    // }
});