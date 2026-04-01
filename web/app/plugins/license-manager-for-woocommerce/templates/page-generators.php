<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc lmfwc-modern-dashboard">
    <hr class="wp-header-end">
    
    <div class="lmfwc-main-layout">
        <div class="lmfwc-content">
            <?php
                if ($action === 'list'
                    || $action === 'delete'
                ) {
                    include_once('generators/page-list.php');
                } elseif ($action === 'add') {
                    include_once('generators/page-add.php');
                } elseif ($action === 'edit') {
                    include_once('generators/page-edit.php');
                } elseif ($action === 'generate') {
                    include_once ('generators/page-generate.php');
                }
            ?>
        </div>
        
        <?php include_once LMFWC_TEMPLATES_DIR . 'sidebar-pro.php'; ?>
    </div>
</div>