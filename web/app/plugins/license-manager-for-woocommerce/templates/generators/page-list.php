<?php

use LicenseManagerForWooCommerce\Lists\GeneratorsList;

defined('ABSPATH') || exit;

/**
 * @var string         $addGeneratorUrl
 * @var string         $generateKeysUrl
 * @var GeneratorsList $generators
 */

?>

<div class="lmfwc-card">
    <div class="lmfwc-card-header">
        <h2 class="lmfwc-card-title"><?php esc_html_e('Generators', 'license-manager-for-woocommerce'); ?></h2>
        <div class="lmfwc-header-actions">
            <a href="<?php echo esc_url($addGeneratorUrl); ?>" class="lmfwc-btn lmfwc-btn-primary">
                <?php esc_html_e('Add new', 'license-manager-for-woocommerce');?>
            </a>
            <a href="<?php echo esc_url($generateKeysUrl); ?>" class="lmfwc-btn lmfwc-btn-secondary">
                <?php esc_html_e('Generate', 'license-manager-for-woocommerce');?>
            </a>
        </div>
    </div>

    <p>
        <b><?php esc_html_e('Important', 'license-manager-for-woocommerce');?>:</b>
        <span><?php esc_html_e('You can not delete generators which are still assigned to active products! To delete those, please remove the generator from all of its assigned products first.', 'license-manager-for-woocommerce');?></span>
    </p>

    <form method="post">
        <?php
            $generators->prepare_items();
            $generators->display();
        ?>
    </form>
</div>
