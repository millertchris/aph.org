<?php defined('ABSPATH') || exit; ?>

<div class="lmfwc-card">
    <div class="lmfwc-card-header">
        <h2 class="lmfwc-card-title"><?php esc_html_e('Add new generator', 'license-manager-for-woocommerce'); ?></h2>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')) ;?>">
        <input type="hidden" name="action" value="lmfwc_save_generator">
        <?php wp_nonce_field('lmfwc_save_generator'); ?>

        <!-- NAME -->
        <div class="lmfwc-form-group">
            <label for="name" class="lmfwc-label">
                <?php esc_html_e('Name', 'license-manager-for-woocommerce');?> <span class="text-danger">*</span>
            </label>
            <input name="name" id="name" class="lmfwc-input" type="text" required>
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Required.', 'license-manager-for-woocommerce');?></b>
                <span><?php esc_html_e('A short name to describe the generator.', 'license-manager-for-woocommerce');?></span>
            </p>
        </div>

        <!-- TIMES ACTIVATED MAX -->
        <div class="lmfwc-form-group">
            <label for="times_activated_max" class="lmfwc-label"><?php esc_html_e('Maximum activation count', 'license-manager-for-woocommerce');?></label>
            <input name="times_activated_max" id="times_activated_max" class="lmfwc-input" type="number">
            <p class="description" id="tagline-description"><?php esc_html_e('Define how many times the license key can be marked as "activated" by using the REST API. Leave blank if you do not use the API.', 'license-manager-for-woocommerce');?></p>
        </div>

        <!-- CHARSET -->
        <div class="lmfwc-form-group">
            <label for="charset" class="lmfwc-label">
                <?php esc_html_e('Character map', 'license-manager-for-woocommerce');?> <span class="text-danger">*</span>
            </label>
            <input name="charset" id="charset" class="lmfwc-input" type="text" required>
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Required.', 'license-manager-for-woocommerce');?></b>
                <span><?php echo wp_kses( 'i.e. for "12-AB-34-CD" the character map is <kbd>ABCD1234</kbd>.', lmfwc_shapeSpace_allowed_html() ); ?></span>
            </p>
        </div>

        <!-- NUMBER OF CHUNKS -->
        <div class="lmfwc-form-group">
            <label for="chunks" class="lmfwc-label">
                <?php esc_html_e('Number of chunks', 'license-manager-for-woocommerce');?> <span class="text-danger">*</span>
            </label>
            <input name="chunks" id="chunks" class="lmfwc-input" type="text" required>
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Required.', 'license-manager-for-woocommerce');?></b>
                <span><?php echo wp_kses( 'i.e. for "12-AB-34-CD" the number of chunks is <kbd>4</kbd>.', lmfwc_shapeSpace_allowed_html() ); ?></span>
            </p>
        </div>

        <!-- CHUNK LENGTH -->
        <div class="lmfwc-form-group">
            <label for="chunk_length" class="lmfwc-label">
                <?php esc_html_e('Chunk length', 'license-manager-for-woocommerce');?> <span class="text-danger">*</span>
            </label>
            <input name="chunk_length" id="chunk_length" class="lmfwc-input" type="text" required>
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Required.', 'license-manager-for-woocommerce');?></b>
                <span><?php echo wp_kses( 'i.e. for "12-AB-34-CD" the chunk length is <kbd>2</kbd>.', lmfwc_shapeSpace_allowed_html() );?></span>
            </p>
        </div>

        <!-- SEPARATOR -->
        <div class="lmfwc-form-group">
            <label for="separator" class="lmfwc-label"><?php esc_html_e('Separator', 'license-manager-for-woocommerce');?></label>
            <input name="separator" id="separator" class="lmfwc-input" type="text">
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Optional.', 'license-manager-for-woocommerce');?></b>
                <span><?php echo wp_kses( 'i.e. for "12-AB-34-CD" the separator is <kbd>-</kbd>.', lmfwc_shapeSpace_allowed_html() );?></span>
            </p>
        </div>

        <!-- PREFIX -->
        <div class="lmfwc-form-group">
            <label for="prefix" class="lmfwc-label"><?php esc_html_e('Prefix', 'license-manager-for-woocommerce');?></label>
            <input name="prefix" id="prefix" class="lmfwc-input" type="text">
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Optional.', 'license-manager-for-woocommerce');?></b>
                <span><?php echo wp_kses( 'Adds a word at the start (separator <b>not</b> included), i.e. <kbd><b>PRE-</b>12-AB-34-CD</kbd>.', lmfwc_shapeSpace_allowed_html() ); ?></span>
            </p>
        </div>

        <!-- SUFFIX -->
        <div class="lmfwc-form-group">
            <label for="suffix" class="lmfwc-label"><?php esc_html_e('Suffix', 'license-manager-for-woocommerce');?></label>
            <input name="suffix" id="suffix" class="lmfwc-input" type="text">
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Optional.', 'license-manager-for-woocommerce');?></b>
                <span><?php echo wp_kses( 'Adds a word at the end (separator <b>not</b> included), i.e. <kbd>12-AB-34-CD<b>-SUF</b></kbd>.', lmfwc_shapeSpace_allowed_html() );?></span>
            </p>
        </div>

        <!-- EXPIRES IN -->
        <div class="lmfwc-form-group">
            <label for="expires_in" class="lmfwc-label"><?php esc_html_e('Expires in', 'license-manager-for-woocommerce');?></label>
            <input name="expires_in" id="expires_in" class="lmfwc-input" type="text">
            <p class="description" id="tagline-description">
                <b><?php esc_html_e('Optional.', 'license-manager-for-woocommerce');?></b>
                <span><?php esc_html_e('The number of days for which the license key is valid after purchase. Leave blank if it doesn\'t expire.', 'license-manager-for-woocommerce');?></span>
            </p>
        </div>

        <div class="lmfwc-form-group">
            <input name="submit" id="submit" class="lmfwc-btn lmfwc-btn-primary" value="<?php esc_html_e('Save' ,'license-manager-for-woocommerce');?>" type="submit">
        </div>
    </form>
</div>
