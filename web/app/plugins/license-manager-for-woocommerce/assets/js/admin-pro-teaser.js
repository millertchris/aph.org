jQuery(document).ready(function ($) {
    'use strict';

    // Handle clicks on Pro settings
    $(document).on('click', '.lmfwc-pro-setting', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openProPopup();
    });

    // Handle keyboard activation (Enter / Space) for accessibility
    $(document).on('keydown', '.lmfwc-pro-setting', function (e) {
        var code = e.which || e.keyCode;
        // Enter (13) or Space (32)
        if (code === 13 || code === 32) {
            e.preventDefault();
            e.stopPropagation();
            openProPopup();
        }
    });

    // Create and append the popup to the body
    function createProPopup() {
        if ($('#lmfwc-pro-popup').length > 0) {
            return;
        }

        // Get plugin image URL from script source
        var scriptSrc = $('script[src*="admin-pro-teaser.js"]').attr('src') || '';
        var pluginBaseUrl = scriptSrc.substring(0, scriptSrc.lastIndexOf('/js/'));
        var rocketImageUrl = pluginBaseUrl + '/img/rocket.png';

        var popupHtml = `
            <div id="lmfwc-pro-popup" class="lmfwc-pro-popup-overlay" style="display:none;">
                <div class="lmfwc-pro-popup-content">
                    <button type="button" class="lmfwc-pro-popup-close" aria-label="Close">❌</button>
                    <div class="lmfwc-pro-popup-inner">
                        <div class="lmfwc-hero-icon" aria-hidden="true">
                            <img src="${rocketImageUrl}" alt="Rocket Launch" width="64" height="64" class="lmfwc-rocket-img">
                        </div>

                        <h2 class="lmfwc-popup-title">Unlock Your Pro Access</h2>
                        <p class="lmfwc-popup-subtitle">Your premium license is ready — activate now and enjoy full access!</p>

                        <div class="lmfwc-offer-pill-group" role="group" aria-label="Product selection">
                            <button type="button" class="lmfwc-pill-btn lmfwc-pill-btn-active" data-pill="license-manager">
                                <span class="lmfwc-pill-badge">LICENSE MANAGER</span>
                            </button>
                            <button type="button" class="lmfwc-pill-btn" data-pill="activate-premium">
                                <span class="lmfwc-pill-label">ACTIVATE PREMIUM</span>
                            </button>
                        </div>

                        <p class="lmfwc-offer-note">
                            <span class="lmfwc-sparkle-icon" aria-hidden="true">✨</span>
                            <span>Special intro offer — limited time only</span>
                        </p>

                        <a href="https://licensemanager.at/pricing/?utm_source=plugin&utm_medium=settings_popup&utm_campaign=upgrade" target="_blank" class="shine-button lmfwc-upgrade-btn" rel="noopener noreferrer">
                            <span>Upgrade to Pro</span>
                            <span class="lmfwc-arrow-icon">→</span>
                        </a>

                        <button type="button" class="lmfwc-dismiss-btn">No thanks, maybe later.</button>

                        <div class="lmfwc-divider"></div>

                        <div class="lmfwc-trust-badges">
                            <div class="lmfwc-trust-badge">
                                <div class="lmfwc-trust-icon-wrapper">
                                    <span class="lmfwc-trust-icon dashicons dashicons-admin-site" aria-hidden="true"></span>
                                </div>
                                <div class="lmfwc-trust-text">
                                    <div class="lmfwc-trust-text-primary">Trusted by</div>
                                    <div class="lmfwc-trust-text-secondary">6K+ website owners</div>
                                </div>
                            </div>
                            <div class="lmfwc-trust-badge" style="max-width: 150px;">
                                <div class="lmfwc-trust-icon-wrapper">
                                    <span class="lmfwc-trust-icon dashicons dashicons-star-filled" aria-hidden="true"></span>
                                </div>
                                <div class="lmfwc-trust-text">
                                    <div class="lmfwc-trust-text-primary">Rated 4.8/5</div>
                                    <div class="lmfwc-trust-text-secondary">by customers</div>
                                </div>
                            </div>
                            <div class="lmfwc-trust-badge">
                                <div class="lmfwc-trust-icon-wrapper">
                                    <span class="lmfwc-trust-icon dashicons dashicons-shield" aria-hidden="true"></span>
                                </div>
                                <div class="lmfwc-trust-text">
                                    <div class="lmfwc-trust-text-primary">14-day</div>
                                    <div class="lmfwc-trust-text-secondary">money-back guarantee</div>
                                </div>
                            </div>
                        </div>
                    <p class="lmfwc-footer-text">Thank you for choosing License Manager!<br> 
                    Upgrading helps <a href="https://licensemanager.at/docs/?utm_source=plugin&amp;utm_medium=sidebar&amp;utm_campaign=docs" target="_blank"> support development</a> and <a href="https://licensemanager.at/pricing/?utm_source=plugin&amp;utm_medium=settings_popup&amp;utm_campaign=upgrade" target="_blank"> unlocks</a> the full power of the plugin.</p>
                       
                    </div>
                </div>
            </div>
        `;

        $('body').append(popupHtml);

        // mark the dialog for accessibility
        $('#lmfwc-pro-popup .lmfwc-pro-popup-content')
            .attr('role', 'dialog')
            .attr('aria-modal', 'true')
            .attr('tabindex', '-1');

        // Toggle pill buttons
        $(document).on('click.lmfwcProPopup', '.lmfwc-pill-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.lmfwc-pill-btn').removeClass('lmfwc-pill-btn-active');
            $(this).addClass('lmfwc-pill-btn-active');
        });

        // Close popup handlers (overlay, close button, and dismiss button)
        // namespace the click handler so we can remove it when closing
        $('.lmfwc-pro-popup-close, .lmfwc-pro-popup-overlay, .lmfwc-dismiss-btn').on('click.lmfwcProPopup', function (e) {
            if (e.target === this) {
                closeProPopup();
            }
        });

        // Key handling: ESC to close & focus trap
        $(document).on('keydown.lmfwcProPopup', function (e) {
            var code = e.which || e.keyCode;
            if (code === 27) { // ESC
                if ($('#lmfwc-pro-popup').is(':visible')) {
                    closeProPopup();
                }
            }
        });
    }

    var lmfwc_last_focused = null;

    function openProPopup() {
        createProPopup();
        lmfwc_last_focused = document.activeElement;
        $('#lmfwc-pro-popup').fadeIn(200, function () {
            // set focus into the dialog for accessibility
            var $close = $('#lmfwc-pro-popup').find('.lmfwc-pro-popup-close');
            if ($close.length) {
                $close.focus();
            }
        });

        // simple focus trap inside dialog
        $(document).on('focusin.lmfwcProPopup', function (e) {
            var $popup = $('#lmfwc-pro-popup');
            if ($popup.length && $popup.is(':visible') && !$popup[0].contains(e.target)) {
                // move focus back to close button if focus leaves
                $popup.find('.lmfwc-pro-popup-close').focus();
            }
        });
    }

    function closeProPopup() {
        $('#lmfwc-pro-popup').fadeOut(200, function () {
            // restore previous focus
            if (lmfwc_last_focused && typeof lmfwc_last_focused.focus === 'function') {
                try { lmfwc_last_focused.focus(); } catch (e) { }
            }
        });
        // cleanup namespaced handlers and remove popup from DOM
        $(document).off('focusin.lmfwcProPopup keydown.lmfwcProPopup click.lmfwcProPopup');
        $('.lmfwc-pro-popup-close, .lmfwc-pro-popup-overlay, .lmfwc-dismiss-btn').off('click.lmfwcProPopup');
        // remove popup when fully hidden
        setTimeout(function () {
            $('#lmfwc-pro-popup').remove();
        }, 260);
    }
});
