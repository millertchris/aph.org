var ec,
emailCheck = {

    settings: {

    },

    init: function() {
        ec = this.settings;
        this.bindUIActions();
        // console.log('emailCheck loaded!');
    },

    bindUIActions: function() {
      // var username_state = false;
      // 
      // var email_state = false;
      //
      // jQuery('#reg_email').on('blur', function(){
      //   var email = jQuery('#reg_email').val();
      //   console.log('blur');
      //   if (email == '') {
      //    email_state = false;
      //    return;
      //   }
      //   $.ajax({
      //     url: '../app/themes/mightily/woocommerce/myaccount/form-login.php',
      //     type: 'POST',
      //     data: {
      //       'email_check' : 1,
      //       'user_email' : email,
      //     },
      //     success: function(response){
      //       if (response == 'taken' ) {
      //         email_state = false;
      //         jQuery('#reg_email').parent().removeClass();
      //         jQuery('#reg_email').parent().addClass("form_error");
      //         jQuery('#reg_email').siblings("span").text('Sorry... Email already taken');
      //       }else if (response == 'not_taken') {
      //         email_state = true;
      //         jQuery('#reg_email').parent().removeClass();
      //         jQuery('#reg_email').parent().addClass("form_success");
      //         jQuery('#reg_email').siblings("span").text('Email available');
      //       }
      //     }
      //   });
      // });




    }
};
