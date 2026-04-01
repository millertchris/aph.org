/*!
 * dosomething
 * Fiercely quick and opinionated front-ends
 * https://HosseinKarami.github.io/fastshell
 * @author Hossein Karami
 * @version 1.0.5
 * Copyright 2022. MIT licensed.
 */
function disable_invite_form(){
  jQuery('#shopper-invite-loader').show();
  jQuery('#send-shopper-invite').attr('disabled', true);
}

function enable_invite_form(){
  jQuery('#shopper-invite-loader').hide();
  jQuery('#send-shopper-invite').removeAttr('disabled');
}

function invite_shopper(email_address, group_ids) {

  $.ajax({
    url: invite_shopper_ajax_obj.ajaxurl, // or ajax_obj.ajaxurl if using on frontend
    data: {
      'action': 'invite_shopper',
      'method': 'invite_shopper',
      'security': invite_shopper_ajax_obj.invite_shopper_ajax_nonce,
      'email_address': email_address,
      'group_id': group_ids
    },
    success: function (data) {
      alert('Shopper invitation sent!');
    },
    error: function (errorThrown) {
      alert('There was an error sending your invitation. Please try again.');
    },
    complete: function(){
      enable_invite_form();
    }
  });

}

function check_shopper(email_address, group_ids) {

  $.ajax({
    url: invite_shopper_ajax_obj.ajaxurl, // or ajax_obj.ajaxurl if using on frontend
    data: {
      'action': 'invite_shopper',
      'method': 'check_shopper',
      'security': invite_shopper_ajax_obj.invite_shopper_ajax_nonce,
      'email_address': email_address,
      'group_id': group_ids
    },
    success: function (data) {
      // check_shopper returns string TRUE or FALSE
      if(data == 'TRUE'){
        var choice = confirm("This email address is already a shopper. Would you still like to send an invitation?");
        if(choice){
          invite_shopper(email_address, group_ids);
        } else {
          enable_invite_form();
        }
      } else {
        invite_shopper(email_address, group_ids);
      }
    },
    error: function (errorThrown) {
      alert('There was an error sending your invitation. Please try again.');
      enable_invite_form();
    }
  });

}

jQuery(document).ready(function ($) {
  jQuery( "#generate-invite" ).submit(function( event ) {
    event.preventDefault();
    var group_id_array = [];
    var email_address = jQuery('#invite-shopper-email').val();
    var group_id = jQuery('#invite-shopper-group').val();

    $.each($("input[name='group-id']:checked"), function() {
      group_id_array.push($(this).val());
    });

    if(group_id_array.length > 0){
      group_ids = group_id_array.join('||');
      disable_invite_form();
      check_shopper(email_address, group_ids);
      console.log(group_ids);
    } else {
      alert('Please select an FQ Account.');
    }
  });  
});