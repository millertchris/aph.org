<?php defined('ABSPATH') || exit; ?>

<div class="lmfwc-card">
    <div class="lmfwc-card-header">
        <h2 class="lmfwc-card-title"><?php esc_html_e('Add a single license key', 'license-manager-for-woocommerce'); ?></h2>
    </div>

    <form method="post" action="<?php echo esc_html(admin_url('admin-post.php'));?>">
        <input type="hidden" name="action" value="lmfwc_add_license_key">
        <?php wp_nonce_field('lmfwc_add_license_key'); ?>

        <!-- LICENSE KEY -->
        <div class="lmfwc-form-group">
            <label for="single__license_key" class="lmfwc-label"><?php esc_html_e('License key', 'license-manager-for-woocommerce');?></label>
            <input name="license_key" id="single__license_key" class="lmfwc-input" type="text" required>
            <p class="description"><?php esc_html_e('The license key will be encrypted before it is stored inside the database.', 'license-manager-for-woocommerce');?></p>
        </div>

        <!-- VALID FOR -->
        <div class="lmfwc-form-group">
            <label for="single__valid_for" class="lmfwc-label"><?php esc_html_e('Valid for (days)', 'license-manager-for-woocommerce');?></label>
            <input name="valid_for" type="number" id="single__valid_for" class="lmfwc-input">
            <p class="description"><?php esc_html_e('Number of days for which the license key is valid after purchase. Leave blank if the license key does not expire. Cannot be used at the same time as the "Expires at" field.', 'license-manager-for-woocommerce');?></p>
        </div>

        <!-- EXPIRES AT -->
        <div class="lmfwc-form-group">
            <label for="single__expires_at" class="lmfwc-label"><?php esc_html_e('Expires at', 'license-manager-for-woocommerce');?></label>
            <input name="expires_at" id="single__expires_at" class="lmfwc-input" type="text">
            <p class="description"><?php esc_html_e('The exact date this license key expires on. Leave blank if the license key does not expire. Cannot be used at the same time as the "Valid for (days)" field.', 'license-manager-for-woocommerce');?></p>
        </div>

        <!-- TIMES ACTIVATED MAX -->
        <div class="lmfwc-form-group">
            <label for="single__times_activated_max" class="lmfwc-label"><?php esc_html_e('Maximum activation count', 'license-manager-for-woocommerce');?></label>
            <input name="times_activated_max" id="single__times_activated_max" class="lmfwc-input" type="number">
            <p class="description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'license-manager-for-woocommerce');?></p>
        </div>

        <!-- STATUS -->
        <div class="lmfwc-form-group">
            <label for="edit__status" class="lmfwc-label"><?php esc_html_e('Status', 'license-manager-for-woocommerce');?></label>
            <select id="edit__status" name="status" class="lmfwc-input">
                <?php foreach($statusOptions as $option): ?>
                    <option value="<?php echo esc_html($option['value']); ?>"><?php echo esc_html($option['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- ORDER -->
        <div class="lmfwc-form-group">
            <label for="single__order" class="lmfwc-label"><?php esc_html_e('Order', 'license-manager-for-woocommerce');?></label>
            <select name="order_id" id="single__order" class="lmfwc-input"></select>
            <p class="description"><?php esc_html_e('The order to which the license keys will be assigned.', 'license-manager-for-woocommerce');?></p>
        </div>

        <!-- PRODUCT -->
        <div class="lmfwc-form-group">
            <label for="single__product" class="lmfwc-label"><?php esc_html_e('Product', 'license-manager-for-woocommerce');?></label>
            <select name="product_id" id="single__product" class="lmfwc-input"></select>
            <p class="description"><?php esc_html_e('The product to which the license keys will be assigned.', 'license-manager-for-woocommerce');?></p>
        </div>

        <!-- CUSTOMER -->
        <div class="lmfwc-form-group">
            <label for="single__user" class="lmfwc-label"><?php esc_html_e('Customer', 'license-manager-for-woocommerce');?></label>
            <select name="user_id" id="single__user" class="lmfwc-input"></select>
            <p class="description"><?php esc_html_e('The user to which the license keys will be assigned.', 'license-manager-for-woocommerce');?></p>
        </div>

        <div class="lmfwc-form-group">
            <input name="submit" id="single__submit" class="lmfwc-btn lmfwc-btn-primary" value="<?php esc_html_e('Add' ,'license-manager-for-woocommerce');?>" type="submit">
        </div>
    </form>
</div>
