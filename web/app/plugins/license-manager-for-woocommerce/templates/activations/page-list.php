<?php
/**
 * Some of the code written, maintained by Darko Gjorgjijoski
 */
use LicenseManagerForWooCommerce\Lists\ActivationsList;

defined( 'ABSPATH' ) || exit;

/**
 * @var Activations $activations
 */
?>

<div class="lmfwc-card">
    <div class="lmfwc-card-header">
        <h2 class="lmfwc-card-title"><?php esc_html_e( 'Activations', 'license-manager-for-woocommerce' ); ?></h2>
    </div>

    <form method="post">
        <?php
        $activations->prepare_items();
        $activations->views();
        $activations->search_box(__( 'Search activations', 'license-manager-for-woocommerce' ), 'license_key');
        $activations->display();
        ?>
    </form>
</div>
