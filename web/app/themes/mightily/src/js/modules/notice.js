var s,
notice = {

    settings: {
        notice: jQuery('.notice.cookie'),
        noticeClose: jQuery('.notice.cookie .cookie-notice-close'),
        sitenotice: jQuery('.notice.site-wide.notice-small'),
        sitenoticeClose: jQuery('.notice.site-wide .site-wide-notice-close'),
        sitenoticeToggle: true
    },

    init: function() {
        s = this.settings;
        this.bindUIActions();
        // console.log('notice loaded!');
    },

    bindUIActions: function() {
        jQuery(document).ready(function(){
            notice.checkNoticeCookie();
            notice.adjustForNotice();
        });
        jQuery(window).resize(function(){
            notice.adjustForNotice();
        });
        s.noticeClose.on('click', function() {
            document.cookie = "cookie_notice=accepted; expires=Thu, 18 Dec 2020 12:00:00 UTC";
            jQuery('.notice.cookie').toggleClass('disable');
        });
        s.sitenoticeClose.on('click', function(evt) {
            evt.preventDefault(jQuery(this));
            notice.removeNotice(jQuery(this));
        });        
    },
    setCookie: function(name,value,days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    },
    getCookie: function(name) {
        var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    },
    removeNotice: function(el){
        notice.setCookie('sitewide_notice','accepted',1);
        el.parents('.notice').remove();
        notice.adjustForNotice();    
        s.sitenoticeToggle = false;
    },
    adjustForNotice: function(){
        var allChildHeights = 0;
        var noticeHeights = 0;
        var navHeights = 0;
        var topNavHeight = jQuery('header.header .nav-top').outerHeight() + jQuery('header.header .header-btns').outerHeight()
        jQuery('header.header > *:not(.skip-nav)').each(function(){
            allChildHeights += $(this).outerHeight();
        });
        jQuery('header.header > .notice').each(function(){
            noticeHeights += $(this).outerHeight();
        });
        jQuery('header.header > *:not(.notice)').each(function(){
            navHeights += $(this).outerHeight();
        });        
             
        //jQuery('header.header').css('height', allChildHeights);
        jQuery('#main').css('padding-top', allChildHeights);
        // If we're on smaller screen sizes, we need to add padding to the top of the header to make room for the notices
        if(jQuery(window).width() <= 1100){
            jQuery('header.header').css('padding-top', topNavHeight);
        } else {
            jQuery('header.header').css('padding-top', 0);
        }
    },
    checkNoticeCookie: function(){
        // console.log('the cookie');
        // console.log(notice.getCookie('sitewide_notice'));
        if(notice.getCookie('sitewide_notice') && notice.getCookie('sitewide_notice') == 'accepted'){
            s.sitenoticeToggle = false;
        }
    }
};