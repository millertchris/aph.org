jQuery(document).ready(function ($) {
    'use strict';

    // Countdown Timer
    function initCountdown() {
        $('.lmfwc-bf-countdown').each(function () {
            var $countdown = $(this);
            var endDate = $countdown.data('end-date');
            
            if (!endDate) {
                return;
            }

            var end = new Date(endDate).getTime();
            var $days = $countdown.find('[data-unit="days"]');
            var $hours = $countdown.find('[data-unit="hours"]');
            var $minutes = $countdown.find('[data-unit="minutes"]');
            var $seconds = $countdown.find('[data-unit="seconds"]');

            function updateTimer() {
                var now = new Date().getTime();
                var distance = end - now;

                if (distance < 0) {
                    $days.text('00');
                    $hours.text('00');
                    $minutes.text('00');
                    $seconds.text('00');
                    return;
                }

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var secs = Math.floor((distance % (1000 * 60)) / 1000);

                $days.text(String(days).padStart(2, '0'));
                $hours.text(String(hours).padStart(2, '0'));
                $minutes.text(String(minutes).padStart(2, '0'));
                $seconds.text(String(secs).padStart(2, '0'));
            }

            updateTimer();
            setInterval(updateTimer, 1000);
        });
    }

    // Dismiss notice
    $(document).on('click', '.lmfwc-blackfriday-notice .notice-dismiss', function (e) {
        e.preventDefault();
        
        var $notice = $(this).closest('.lmfwc-blackfriday-notice');
        var action = $notice.data('dismiss-action');

        $.ajax({
            url: lmfwcBF.ajax_url,
            type: 'POST',
            data: {
                action: action,
                nonce: lmfwcBF.nonce
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

    // Copy coupon code
    $(document).on('click', '.lmfwc-bf-coupon', function (e) {
        e.preventDefault();
        var code = 'LMFW25';
        var $coupon = $(e.currentTarget);
        var originalText = $coupon.find('.lmfwc-bf-coupon-text').html();
        
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function () {
                $coupon.find('.lmfwc-bf-coupon-text').html('Copied!');
                
                setTimeout(function () {
                    $coupon.find('.lmfwc-bf-coupon-text').html(originalText);
                }, 2000);
            });
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = code;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                $coupon.find('.lmfwc-bf-coupon-text').html('Copied!');
                setTimeout(function () {
                    $coupon.find('.lmfwc-bf-coupon-text').html(originalText);
                }, 2000);
            } catch (err) {
                console.error('Failed to copy:', err);
            }
            document.body.removeChild(textArea);
        }
    });

    // Initialize countdown
    initCountdown();
});

