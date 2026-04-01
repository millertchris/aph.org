// Loading vendors
@@include('js/vendors/glide.js')
@@include('js/vendors/accessable-slick.js')
@@include('js/vendors/tilt.js')
@@include('js/vendors/rellax.js')
@@include('js/vendors/aos.js')
@@include('js/vendors/1_micromodal.js')
@@include('js/vendors/2_micromodal.js')

// Loading modules
@@include('js/modules/accordions.js')
@@include('js/modules/autocomplete.js')
@@include('js/modules/cards.js')
@@include('js/modules/glideSlide.js')
@@include('js/modules/slickSlide.js')
@@include('js/modules/mobileMenu.js')
@@include('js/modules/layoutFilter.js')
@@include('js/modules/accessibleTabbing.js')
@@include('js/modules/donateForm.js')
@@include('js/modules/searchForm.js')
@@include('js/modules/notice.js')
@@include('js/modules/emailCheck.js')
@@include('js/modules/kerning.js')
@@include('js/modules/cartPreview.js')
@@include('js/modules/scrollTo.js')
@@include('js/modules/tables.js')
@@include('js/modules/adminOrderScreen.js')

jQuery(document).ready(function () {

  mobileMenu.init();
  accordions.init();
  accordions.init();
  LayoutCard.init();
  carbonTables.init();
  LayoutFilter.init();
  donateForm.init();
  emailCheck.init();
  glideSlide.init();
  slickSlide.init();
  MicroModal.init();
  manualKern.init();
  cartPreview.init();
  scrollTo.init();
  adminOrderScreen.init();

  CarbonComponents.Accordion.init();
  CarbonComponents.Modal.init();

  AOS.init({
    duration: 1000,
    delay: 200,
    mirror: true,
    easing: 'easeInOut'
  });


  // Animating the accordions
  // $('.accordion-wrapper .bx--accordion__heading').on('click', function() {
  //   $(this).parent().toggleClass('bx--accordion__item');
  //   $(this).parent().toggleClass('bx--accordion__item--active');
  //   //$(this).next('.bx--accordion__content').slideToggle();
  // });


  var urlParams = new URLSearchParams(window.location.search);
  // console.log(urlParams.has('shopper_email_address'));
  if (urlParams.has('shopper_email_address') || urlParams.has('shopper_invite_open')) {
    MicroModal.show('invite-shopper');
  }

  accessibleTabbing.init();
  notice.init();

  if (jQuery('.rellax').length) {
    // Center all the things!
    var rellax = new Rellax('.rellax', {
      center: true
    });
  }

  // magicalUnderline.init();
  // formLabels.init();

  searchForm.init();

  if (jQuery('body').hasClass('role-teacher')) {
    jQuery('ul.ajax-sub-menu li.checkout a').text('Proceed to Request');
  }

  // This disables the select dropdown for users on the checkout screen
  jQuery('select[readonly="readonly"] option:not(:selected)').attr('disabled', true);

  // Replace string in state dropdown with 'select a state' instead of 'select an option'
  function replaceStatePlaceholder() {
    if(jQuery('#billing_country').val() == 'US'){
      // console.log('us was selected');
      jQuery("#billing_state option, #billing_state_field .select2-selection__placeholder").each(function(){
        // console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select an option…/g,'Select a state…'));
      });
    } else {
      jQuery("#billing_state option, #billing_state_field .select2-selection__placeholder").each(function(){
        // console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select a state…/g,'Select an option…'));
      });
    }

    if(jQuery('#shipping_country').val() == 'US'){
      // console.log('us was selected');
      jQuery("#shipping_state option, #shipping_state_field .select2-selection__placeholder").each(function(){
        // console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select an option…/g,'Select a state…'));
      });
    } else {
      jQuery("#shipping_state option, #shipping_state_field .select2-selection__placeholder").each(function(){
        // console.log(jQuery(this).html());
        jQuery(this).html(jQuery(this).html().replace(/Select a state…/g,'Select an option…'));
      });
    }    
  }
  if(jQuery('#billing_state_field').length > 0 || jQuery('#shipping_state_field').length > 0){
    setTimeout(function(){
        replaceStatePlaceholder();
    }, 500);
    $('#billing_country').on('change', function(){
        setTimeout(function(){
            replaceStatePlaceholder();
        }, 50);
    });
    $('#shipping_country').on('change', function(){
      setTimeout(function(){
          replaceStatePlaceholder();
      }, 50);
  });    
  } 


  // Disables default Woocommerce Orderby form functionality, and adds a submit buttons
  jQuery('.woocommerce-ordering').unbind();

  // // Allows click on WooCommerce tab links
  jQuery('body').off('click', '.wc-tabs li a, ul.tabs li a');

  jQuery('body').on('click', '.wc-tabs li a.tab-link, ul.tabs li a.tab-link', function (e) {
    e.preventDefault ? e.preventDefault() : (e.returnValue = false);
    // e.preventDefault();

    var $tab = $(this);
    var $tabs_wrapper = $tab.closest('.wc-tabs-wrapper, .woocommerce-tabs');
    var $tabs = $tabs_wrapper.find('.wc-tabs, ul.tabs');

    $tabs.find('li').removeClass('active');
    $tabs_wrapper.find('.wc-tab, .panel:not(.panel .panel)').hide();

    $tab.closest('li').addClass('active');
    $tabs_wrapper.find($tab.attr('href')).show();
  });

  // Controls layout view toggle button on List Of Items
  var layoutButtons = jQuery('.layout-button');

  layoutButtons.on('click', function (evt) {
    evt.preventDefault ? evt.preventDefault() : (evt.returnValue = false);
    // evt.preventDefault();
    jQuery(this).toggleClass('active');
    jQuery(this).siblings('.layout-button').toggleClass('active');
    var layoutWrapper = jQuery(this).closest('.list-of-items');
    var clickedView = jQuery(this).data('view');
    if (clickedView == 'grid') {
      layoutWrapper.removeClass('list-view');
    } else if (clickedView == 'list') {
      layoutWrapper.addClass('list-view');
    }
  });

  // Add target="_blank" attribute to download links
  if (jQuery('.woocommerce-MyAccount-downloads-file').length > 0) {
    jQuery('.woocommerce-MyAccount-downloads-file').attr('target', '_blank');
  }

  // Remove aria-describedby attribute from wp-caption figure elements
  if (jQuery('figure.wp-caption').length > 0) {
    jQuery('figure.wp-caption').removeAttr('aria-describedby');
  }

  jQuery('#wl-wrapper select.select-move').on('change', function () {
    //window.location.href = this.value;
    var thisEl = this;
    postData = JSON.parse(thisEl.value);
    // console.log(postData);
    $(thisEl).parents('.select-url-wrapper').find('img').show();
    jQuery.post(postData.url, postData).done(function (data) {
      $(thisEl).parents('tr.cart_table_item').remove();
    });
  });

  jQuery('#wl-wrapper select.select-privacy').on('change', function () {
    //window.location.href = this.value;
    var thisEl = this;
    postData = JSON.parse(thisEl.value);
    // console.log(postData);
    $(thisEl).parents('.select-url-wrapper').find('img').show();
    jQuery.post(postData.url, postData).done(function (data) {
      $(thisEl).parents('.select-url-wrapper').find('img').hide();
    });
  });

  var prevQty = 1;
  var newQty = 1;
  jQuery('#wl-wrapper input.qty').on('focus', function(evt){
    prevQty = $(this).val();
    // console.log('prev: ' + prevQty);
  });
  jQuery('#wl-wrapper input.qty').on('blur', function (evt) {
    //window.location.href = this.value;
    var thisEl = this;
    newQty = $(thisEl).val();
    // If qty changes then proceed
    if(prevQty != newQty){
      itemKey = $(thisEl).data('key');
      postData = $(thisEl).data('query');
      postData.cart[itemKey].qty = newQty;
      $(thisEl).parents('div.quantity').find('img').show();
      jQuery.post(postData.url, postData).done(function (data) {
        $(thisEl).parents('div.quantity').find('img').hide();
      });
    }
  });
  $(document.body).on( 'checkout_error', function(){
    if($('.woocommerce-error').length > 0){
      $('.woocommerce-error').attr('tabindex', '0');
      $('.woocommerce-error').focus();
    }
  });

});


