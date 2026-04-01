<?php defined('ABSPATH') || exit; ?>

<div class="lmfwc-card">
    <div class="lmfwc-card-header">
        <h2 class="lmfwc-card-title"><?php esc_html_e('License keys', 'license-manager-for-woocommerce'); ?></h2>
        <div class="lmfwc-header-actions">
            <a class="lmfwc-btn lmfwc-btn-primary" href="<?php echo esc_url($addLicenseUrl); ?>">
                <?php esc_html_e('Add new', 'license-manager-for-woocommerce');?>
            </a>
            <a class="lmfwc-btn lmfwc-btn-secondary" href="<?php echo esc_url($importLicenseUrl); ?>">
                <?php esc_html_e('Import', 'license-manager-for-woocommerce');?>
            </a>
        </div>
    </div>

    <form method="post" id="lmfwc-license-table">
        <?php
            $licenses->prepare_items();
            $licenses->views();
            $licenses->search_box(__( 'Search license key', 'license-manager-for-woocommerce' ), 'license_key');
            $licenses->display();
        ?>
    </form>
</div>

<span class="lmfwc-txt-copied-to-clipboard" style="display: none"><?php esc_html_e('Copied to clipboard', 'license-manager-for-woocommerce'); ?></span>