var cp,
cartPreview = {

    settings: {

    },

    init: function() {
        cp = this.settings;
        this.bindUIActions();
        // console.log('cartPreview loaded!');
    },

    bindUIActions: function() {
      
      var cart_link = $('a.cart-preview-link');
      var cart_submenu = $('ul.ajax-sub-menu.sub-menu');

      if(cart_submenu.css('display') == 'none') {
        cart_link.attr('href', '/cart/');
      } else {
        cart_link.attr('href', '#');
      }

      $(window).resize(function() {
        if(cart_submenu.css('display') == 'none') {
        cart_link.attr('href', '/cart/');
      } else {
        cart_link.attr('href', '#');
      }
      });
      
    }

};
