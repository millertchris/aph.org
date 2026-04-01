<?php

defined('ABSPATH') || exit;
?>
<div class="wrap lmfwc custom-settings-page">
<style>
    .lmfwc-settings-grid { display:flex; gap:30px; align-items:flex-start; }
    .lmfwc-settings-main { flex:1 1 0; }
    .lmfwc-sidebar { width:320px; }
    form#mainform { padding: 0px 0px 0px !important; }
    .lmfwc-sidebar {top: 115px;}
    body.woocommerce_page_wc-settings #mainform nav {margin: 0 0px 16px 0px;}
    .wrap.woocommerce { background-color: #f0f0f1; }
    .wrap.lmfwc.custom-settings-page {
    padding: 0px 30px;
    padding-bottom: 30px;
    }
    .lmfwc-pro-notice-banner {
    width: 96%;
    margin: 0px 22px;
}
</style>
    <?php settings_errors(); ?>
    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url($urlGeneral); ?>" class="<?= $section === 'general' ? 'current' : '';?>">
                <span><?php esc_html_e('General', 'license-manager-for-woocommerce');?></span>
            </a>
        | </li>
        <li>
            <a href="<?php echo esc_url($urlWooCommerce); ?>" class="<?= $section === 'woocommerce' ? 'current' : ''; ?>">
                <span><?php esc_html_e('WooCommerce', 'license-manager-for-woocommerce');?></span>
            </a>
        | </li>
        <li>
            <a href="<?php echo esc_url($urlRestApi); ?>" class="<?= $section === 'rest_api' ? 'current' : '';?>">
                <span><?php esc_html_e('REST API', 'license-manager-for-woocommerce');?></span>
            </a>
        | </li>
        <li>
            <a href="<?php echo esc_url($urlTools); ?>" class="<?= $section === 'tools' ? 'current' : '';?>">
                <span><?php esc_html_e('Tools', 'license-manager-for-woocommerce');?></span>
            </a>
        </li>
    </ul>
    <br class="clear">

    <div class="lmfwc-settings-grid">
        <div class="lmfwc-settings-main">
    <?php if ($section == 'general'): ?>

        <form action="<?php echo esc_url(admin_url('options.php')); ?>" method="POST">
            <?php settings_fields('lmfwc_settings_group_general'); ?>
            <?php do_settings_sections('lmfwc_license_keys'); ?>
            <?php do_settings_sections('lmfwc_qrcode'); ?>
            <?php do_settings_sections('lmfwc_rest_api'); ?>
            <?php do_settings_sections('lmfwc_traceback'); ?>
            <?php submit_button(); ?>
        </form>

    <?php elseif ($section === 'woocommerce'): ?>

        <form action="<?php echo esc_url(admin_url('options.php')); ?>" method="POST">
            <?php settings_fields('lmfwc_settings_group_woocommerce'); ?>
            <?php do_settings_sections('lmfwc_license_key_delivery'); ?>
            <?php do_settings_sections('lmfwc_branding'); ?>
            <?php do_settings_sections('lmfwc_my_account'); ?>
            <?php submit_button(); ?>
        </form>

    <?php elseif ($section === 'rest_api'): ?>

        <?php if ($action === 'list'): ?>

            <?php include_once 'settings/rest-api-list.php'; ?>

        <?php elseif ($action === 'show'): ?>

            <?php include_once 'settings/rest-api-show.php'; ?>

        <?php else: ?>

            <?php include_once 'settings/rest-api-key.php'; ?>

        <?php endif; ?>

    <?php elseif ($section === 'tools'): ?>

        <form action="<?php echo esc_url(admin_url('options.php')); ?>" method="POST">
            <?php settings_fields('lmfwc_settings_group_tools'); ?>
            <?php do_settings_sections('lmfwc_export'); ?>
            <?php submit_button(); ?>
        </form>

         <?php include_once 'settings/data-tools.php'; ?>

    <?php endif; ?>
        </div>

        <aside class="lmfwc-sidebar">
            <?php include_once 'sidebar-pro.php'; ?>
        </aside>
    </div>

</div>
