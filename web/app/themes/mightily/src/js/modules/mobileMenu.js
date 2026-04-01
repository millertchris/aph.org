var s,
mobileMenu = {

    settings: {
        hamburger: jQuery('.hamburger'),
        header: jQuery('.header'),
        search: jQuery('.header .mobile-search'),
        searchSubmit: jQuery('.header form .search-submit'),
    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        // console.log('mobileMenu loaded!');
    },

    bindUIActions: function() {

      // Setting up Mobile Menu with drop-down sub-menus, and Cart link
      if (jQuery(window).width() < 769) {
        // Setting up cart link
        jQuery('a.cart-preview-link').attr('href', '/cart/');
        // Adding parent links to sub-menus
        jQuery('ul.main-menu li.menu-item-has-children > a').on('click', function(event) {
          event.preventDefault();
          jQuery(this).addClass("no-link")
          jQuery(this).next().slideToggle();
          // console.log("no click for you!");
        });
        // Adding toggle to sub-menu when parent link is clicked
        jQuery('ul.main-menu li.menu-item-has-children > a').each(function() {
          var subMenu = jQuery(this).siblings('.sub-menu');
          jQuery(this).clone().prependTo(subMenu).wrap('<li class="menu-item menu-item-type-post_type menu-item-object-page"></li>');
        });
      }


      s.hamburger.on('click', function() {
          jQuery('.hamburger').toggleClass('is-active');
          jQuery('.header').toggleClass('reveal');
          jQuery('#main').toggleClass('hide');
          jQuery('body').toggleClass('no-scroll');
      });



        // s.search.on('click', function() {
        //     jQuery('.site-search').toggleClass('is-active');
        //     jQuery('header form').removeClass('move');
        //     jQuery('header .search-results').hide();
        // });
        // s.searchSubmit.on('click', function(e) {
        //     e.preventDefault();
        //     jQuery('header form').addClass('move');
        //     jQuery('header .search-results').fadeIn('slow', function() {
        //         // Animation complete
        //     });
        // });
    }

};
