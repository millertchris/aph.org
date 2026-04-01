<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wrap lmfwc lmfwc-modern-dashboard">
    <hr class="wp-header-end">
    
    <div class="lmfwc-main-layout">
        <div class="lmfwc-content">
            <?php
            if ( $action === 'list' || $action === 'activate' || $action === 'deactivate' || $action === 'delete' ) {
                include_once( 'activations/page-list.php' );
            }
        
            ?>
        </div>
        
        <?php include_once LMFWC_TEMPLATES_DIR . 'sidebar-pro.php'; ?>
    </div>
</div>