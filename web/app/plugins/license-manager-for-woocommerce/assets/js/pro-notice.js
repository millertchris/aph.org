jQuery(document).ready(function ($) {
    'use strict';

    // Dismiss notice
    $(document).on('click', '.lmfwc-pro-conversion-notice .notice-dismiss', function (e) {
        e.preventDefault();
        
        var $notice = $(this).closest('.lmfwc-pro-conversion-notice');
        var action = $notice.data('dismiss-action');

        $.ajax({
            url: lmfwcProNotice.ajax_url,
            type: 'POST',
            data: {
                action: action,
                nonce: lmfwcProNotice.nonce
            },
            success: function (response) {
                if (response.success) {
                    $notice.fadeOut(300, function () {
                        $(this).remove();
                    });
                }
            }
        });
    });

    // Copy coupon code functionality
    $(document).on('click', '.lmfwc-coupon-code-btn', function (e) {
        e.preventDefault();
        
        var $btn = $(this);
        var couponCode = $btn.data('coupon') || 'LMFW25';
        
        // Create temporary textarea to copy text
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(couponCode).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Visual feedback
        $btn.addClass('copied');
        setTimeout(function() {
            $btn.removeClass('copied');
        }, 2000);
    });
});

