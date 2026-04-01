<?php

namespace LicenseManagerForWooCommerce\Settings;

defined('ABSPATH') || exit;

class General
{
    /**
     * @var array
     */
    private $settings;

    /**
     * General constructor.
     */
    public function __construct()
    {
        $this->settings = get_option('lmfwc_settings_general', array());

        /**
         * @see https://developer.wordpress.org/reference/functions/register_setting/#parameters
         */
        $args = array(
            'sanitize_callback' => array($this, 'sanitize')
        );
    

        // Register the initial settings group.
        register_setting('lmfwc_settings_group_general', 'lmfwc_settings_general', $args) ;

        // Initialize the individual sections
        $this->initSectionLicenseKeys();
        $this->initSectionGracePeriod();
        $this->initSectionAPI();
        $this->initSectionWebHooks();

        // QRCode and Traceback sections (teasers and log toggles)
        $this->initSectionQRCode();
        $this->initSectionTraceback();
    }

    /**
     * Sanitizes the settings input.
     *
     * @param array $settings
     *
     * @return array
     */
    public function sanitize($settings)
    {
    
        return $settings;
    }

    

    /**
     * Initializes the "lmfwc_license_keys" section.
     *
     * @return void
     */
    private function initSectionLicenseKeys()
    {
        // Add the settings sections.
        add_settings_section(
            'license_keys_section',
            __('License keys', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_license_keys'
        );

        // lmfwc_security section fields.
        add_settings_field(
            'lmfwc_hide_license_keys',
            __('Obscure licenses', 'license-manager-for-woocommerce'),
            array($this, 'fieldHideLicenseKeys'),
            'lmfwc_license_keys',
            'license_keys_section'
        );

        add_settings_field(
            'lmfwc_allow_duplicates',
            __('Allow duplicates', 'license-manager-for-woocommerce'),
            array($this, 'fieldAllowDuplicates'),
            'lmfwc_license_keys',
            'license_keys_section'
        );
        add_settings_field(
            'lmfwc_product_downloads',
            __('Product downloads', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_license_keys',
            'license_keys_section',
            array(
                'label' => __('Enable product download management for digital / virtual products e.g. WordPress themes, plugins & more.', 'license-manager-for-woocommerce'),
                'type' => 'checkbox'
            )
        );
        add_settings_field(
            'lmfwc_download_expires',
            __('Download expires', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_license_keys',
            'license_keys_section',
            array(
                'label' => __('Automatically set download expiration date in orders to the license expiration date.', 'license-manager-for-woocommerce'),
                'type' => 'checkbox'
            )
        );
        add_settings_field(
            'lmfwc_expire_format',
            __('License expiration format', 'license-manager-for-woocommerce'),
            array($this, 'fieldExpireFormat'),
            'lmfwc_license_keys',
            'license_keys_section'
        );
    }

    public function fieldExpireFormat()
    {
        $field = 'lmfwc_expire_format';
        $value = isset($this->settings[$field]) ? $this->settings[$field] : '';
        $html = '<fieldset>';
        $html .= sprintf(
            '<input type="text" id="%s" name="lmfwc_settings_general[%s]" value="%s" >',
            esc_attr($field), // Escape field ID
            esc_attr($field), // Escape field name
            esc_attr($value)  // Escape field value
        );
        $html .= '<br><br>';
        $html .= sprintf(
            /* translators: %1$s: date format merge code, %2$s: time format merge code, %3$s: general settings URL, %4$s: link to date and time formatting documentation */
            __(
                '<code>%1$s</code> and <code>%2$s</code> will be replaced by formats from <a href="%3$s">Administration > Settings > General</a>. %4$s',
                'license-manager-for-woocommerce'
            ),
            '{{DATE_FORMAT}}',
            '{{TIME_FORMAT}}',
            esc_url(admin_url('options-general.php')), // Escape admin URL
            __(
                '<a href="https://wordpress.org/support/article/formatting-date-and-time/">Documentation on date and time formatting</a>.'
            )
        );
        $html .= '</fieldset>';
        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }
    

    
    

    /**
     * Initializes the "lmfwc_rest_api" section.
     *
     * @return void
     */
    private function initSectionAPI()
    {
        // Add the settings sections.
        add_settings_section(
            'lmfwc_rest_api_section',
            __('REST API', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_rest_api'
        );

        add_settings_field(
            'lmfwc_disable_api_ssl',
            __('API & SSL', 'license-manager-for-woocommerce'),
            array($this, 'fieldEnableApiOnNonSsl'),
            'lmfwc_rest_api',
            'lmfwc_rest_api_section'
        );

        add_settings_field(
            'lmfwc_enabled_api_routes',
            __('Enable/disable API routes', 'license-manager-for-woocommerce'),
            array($this, 'fieldEnabledApiRoutes'),
            'lmfwc_rest_api',
            'lmfwc_rest_api_section'
        );
    }

    /**
     * Callback for the "hide_license_keys" field.
     *
     * @return void
     */
    public function fieldHideLicenseKeys()
    {
        $field = 'lmfwc_hide_license_keys';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf('<span>%s</span>', __('Hide license keys in the admin dashboard.', 'license-manager-for-woocommerce'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('All license keys will be hidden and only displayed when the \'Show\' action is clicked.', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    /**
     * Callback for the "lmfwc_allow_duplicates" field.
     *
     * @return void
     */
    public function fieldAllowDuplicates()
    {
        $field = 'lmfwc_allow_duplicates';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow duplicate license keys inside the licenses database table.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';

        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

   
    /**
     * Callback for the "lmfwc_disable_api_ssl" field.
     *
     * @return void
     */
    public function fieldEnableApiOnNonSsl()
    {
        $field = 'lmfwc_disable_api_ssl';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Enable the plugin API routes over insecure HTTP connections.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('This should only be activated for development purposes.', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    /**
     * Callback for the "lmfwc_enabled_api_routes" field.
     *
     * @return void
     */
    public function fieldEnabledApiRoutes()
    {
        $field = 'lmfwc_enabled_api_routes';
        $value = array();
        $routes = array(
            array(
                'id'         => '010',
                'name'       => 'v2/licenses',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '011',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '012',
                'name'       => 'v2/licenses',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '013',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'PUT',
                'deprecated' => false,
            ),
             array(
                'id'         => '014',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'DELETE',
                'deprecated' => false,
            ),
            array(
                'id'         => '015',
                'name'       => 'v2/licenses/activate/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '016',
                'name'       => 'v2/licenses/deactivate/{activation_token}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '017',
                'name'       => 'v2/licenses/validate/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '018',
                'name'       => 'v2/generators',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '019',
                'name'       => 'v2/generators/{id}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '020',
                'name'       => 'v2/generators',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '021',
                'name'       => 'v2/generators/{id}',
                'method'     => 'PUT',
                'deprecated' => false,
            ),
             array(
                'id'         => '022',
                'name'       => 'v2/generators/{id}',
                'method'     => 'DELETE',
                'deprecated' => false,
            ),
            array(
                'id'         => '023',
                'name'       => 'v2/generators/{id}/generate',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '024',
                'name'       => 'v2/customers/{customer_id}/licenses',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '025',
                'name'       => 'v2/products/update/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '026',
                'name'       => 'v2/products/download/latest/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '027',
                'name'       => 'v2/products/ping',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '028',
                'name'       => 'v2/application/{application_id}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '029',
                'name'       => 'v2/application/download/{activation_token}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
        );
        $classList = array(
            'GET'    => 'text-success',
            'PUT'    => 'text-primary',
            'POST'   => 'text-primary',
            'DELETE' => 'text-danger '
        );

        // Routes that belong to the Pro version and should be shown as disabled teasers
        $proRoutes = array('023', '024', '025', '026', '027', '028', '029');
        if (array_key_exists($field, $this->settings)) {
            $value = $this->settings[$field];
        }

        $html = '<fieldset>';

        foreach ($routes as $route) {
            $checked = false;

            if (array_key_exists($route['id'], $value) && $value[$route['id']] === '1') {
                $checked = true;
            }

            $is_pro = in_array($route['id'], $proRoutes, true);

            $html .= sprintf('<label for="%s-%s">', $field, $route['id']);

            if ($is_pro) {
                // Render Pro endpoints as disabled checkboxes but wrap them in a clickable teaser
                $html .= '<span class="lmfwc-pro-setting" style="display:inline-block;cursor:pointer;">';
                $html .= sprintf(
                    '<input id="%s-%s" type="checkbox" name="lmfwc_settings_general[%s][%s]" value="1" %s disabled>',
                    $field,
                    $route['id'],
                    $field,
                    $route['id'],
                    checked(true, $checked, false)
                );

                $html .= sprintf('<code><b class="%s">%s</b> - %s</code>', $classList[$route['method']], $route['method'], $route['name']);
                $html .= ' <span class="lmfwc-pro-badge-small">PRO</span>';
                $html .= '</span>';
            } else {
                $html .= sprintf(
                    '<input id="%s-%s" type="checkbox" name="lmfwc_settings_general[%s][%s]" value="1" %s>',
                    $field,
                    $route['id'],
                    $field,
                    $route['id'],
                    checked(true, $checked, false)
                );

                $html .= sprintf('<code><b class="%s">%s</b> - %s</code>', $classList[$route['method']], $route['method'], $route['name']);
            }

            if (true === $route['deprecated']) {
                $html .= sprintf(
                    '<code class="text-info"><b>%s</b></code>',
                    strtoupper(__('Deprecated', 'license-manager-for-woocommerce'))
                );
            }

            $html .= '</label>';
            $html .= '<br>';
        }

        $html .= sprintf(
            '<p class="description" style="margin-top: 1em;">%s</p>',
            sprintf(
                 /* translators: %1$s: date format merge code, %2$s: time format merge code, %3$s: general settings URL, %4$s: link to date and time formatting documentation */
                __('The complete <b>API documentation</b> can be found <a href="%s" target="_blank" rel="noopener">here</a>.', 'license-manager-for-woocommerce'),
                'https://www.licensemanager.at/docs/rest-api/getting-started/api-keys'
            )
        );
        
        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    /**
     * Initializes the "lmfwc_grace_period" section.
     *
     * @return void
     */
    private function initSectionGracePeriod()
    {
        add_settings_section(
            'lmfwc_grace_period_section',
            __('Grace Period', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_license_keys'
        );

        add_settings_field(
            'lmfwc_grace_period',
            __('Grace Period', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_license_keys',
            'lmfwc_grace_period_section',
            array(
                'label' => __('Day(s)', 'license-manager-for-woocommerce'),
                'description' => __('Time interval before renewable License Key(s) expire', 'license-manager-for-woocommerce'),
                'type' => 'select'
            )
        );
    }

    /**
     * Initializes the "lmfwc_webhooks" section.
     *
     * @return void
     */
    private function initSectionWebHooks()
    {
        add_settings_section(
            'lmfwc_webhooks_section',
            __('Enable/disable Web Hooks', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_rest_api'
        );

        $webhooks = array(
            'Add license' => 'https://example.com/api/add_license',
            'Activations' => 'https://example.com/api/activations',
            'Deactivations' => 'https://example.com/api/deactivations',
            'Update License' => 'https://example.com/api/update_license',
            'Delete License' => 'https://example.com/api/delete_license',
            'Delete Activations' => 'https://example.com/api/delete_activations',
            'Reactivate License' => 'https://example.com/api/reactivate_license',
            'Get all licenses' => 'https://example.com/api/get_licenses',
            'Get all generators' => 'https://example.com/api/get_all_generators',
            'Valid License' => 'https://example.com/api/valid_license'
        );

        foreach ($webhooks as $label => $placeholder) {
            add_settings_field(
                'lmfwc_webhook_' . sanitize_title($label),
                $label,
                array($this, 'fieldProFeature'),
                'lmfwc_rest_api',
                'lmfwc_webhooks_section',
                array(
                    'placeholder' => $placeholder,
                    'type' => 'text_with_checkbox'
                )
            );
        }
    }

    /**
     * QR Code and License Activation Page section (teasers)
     */
    private function initSectionQRCode()
    {
        add_settings_section(
            'lmfwc_qrcode_section',
            __('QRCode', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_qrcode'
        );

        add_settings_field(
            'lmfwc_qrcode',
            __('QRCode', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_qrcode',
            'lmfwc_qrcode_section',
            array(
                'label' => __('License Activation QRCode', 'license-manager-for-woocommerce'),
                'description' => __('Generate QR codes for license activation that users can scan to activate licenses.', 'license-manager-for-woocommerce'),
                'type' => 'select'
            )
        );

        add_settings_field(
            'lmfwc_license_activation_page',
            __('License Activation Page', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_qrcode',
            'lmfwc_qrcode_section',
            array(
                'label' => __('Select a License Activation Page', 'license-manager-for-woocommerce'),
                'description' => __('Select a page to use for license activation. The page must include this shortcode:
[license_activation success_msg="Your License key is activated now" error_msg="License key is invalid."]
This page will be used when "License Activation QRCode" is enabled in the QR Code settings.', 'license-manager-for-woocommerce'),
                'type' => 'text'
            )
        );
    }

    public function fieldQRCode()
    {
        $field = 'lmfwc_qrcode';
        $value = isset($this->settings[$field]) ? $this->settings[$field] : 'disable';
        $html = '<select id="' . esc_attr($field) . '" name="lmfwc_settings_general[' . esc_attr($field) . ']">';
        $options = array('disable' => __('Disable', 'license-manager-for-woocommerce'), 'enable' => __('Enable', 'license-manager-for-woocommerce'));
        foreach ($options as $k => $v) {
            $html .= '<option value="' . esc_attr($k) . '"' . selected($value, $k, false) . '>' . esc_html($v) . '</option>';
        }
        $html .= '</select>';
        echo $html;
    }

    public function fieldLicenseActivationPage()
    {
        $field = 'lmfwc_license_activation_page';
        $value = isset($this->settings[$field]) ? $this->settings[$field] : '';
        
        $html = '<select id="' . esc_attr($field) . '" name="lmfwc_settings_general[' . esc_attr($field) . ']">';
        $html .= '<option value="">' . esc_html__('Select a License Activation Page', 'license-manager-for-woocommerce') . '</option>';
   
        $html .= '</select>';
        $html .= '<p class="description">' . esc_html__('Select a page to use for license activation. The page must include this shortcode:', 'license-manager-for-woocommerce') . '<br><code>[license_activation success_msg="Your License key is activated now" error_msg="License key is invalid."]</code><br>' . esc_html__('This page will be used when "License Activation QRCode" is enabled in the QR Code settings.', 'license-manager-for-woocommerce') . '</p>';
        echo $html;
    }

    /**
     * Traceback (API logs) section (Pro teasers)
     */
    private function initSectionTraceback()
    {
        add_settings_section(
            'lmfwc_traceback_section',
            __('Traceback', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_traceback'
        );

        add_settings_field(
            'lmfwc_api_activity_log',
            __('API Activity Log', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_traceback',
            'lmfwc_traceback_section',
            array(
                'label' => __('API Activity Log', 'license-manager-for-woocommerce'),
                'description' => __('Enable to log API activities (Pro).', 'license-manager-for-woocommerce'),
                'type' => 'toggle'
            )
        );

        add_settings_field(
            'lmfwc_api_exception_log',
            __('API Exception Log', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_traceback',
            'lmfwc_traceback_section',
            array(
                'label' => __('API Exception Log', 'license-manager-for-woocommerce'),
                'description' => __('Enable to log API exceptions (Pro).', 'license-manager-for-woocommerce'),
                'type' => 'toggle'
            )
        );

        add_settings_field(
            'lmfwc_api_output_log',
            __('API Output Log', 'license-manager-for-woocommerce'),
            array($this, 'fieldProFeature'),
            'lmfwc_traceback',
            'lmfwc_traceback_section',
            array(
                'label' => __('API Output Log', 'license-manager-for-woocommerce'),
                'description' => __('Enable to log API output (Pro).', 'license-manager-for-woocommerce'),
                'type' => 'toggle'
            )
        );
    }

    public function fieldApiActivityLog()
    {
        $field = 'lmfwc_api_activity_log';
        $value = isset($this->settings[$field]) && $this->settings[$field] ? true : false;
        $logfile = sprintf('lmfwc-api-activity-%s.log', date('Y-m-d'));
        $logurl = LMFWC_PLUGIN_URL . 'logs/' . $logfile;
        $html = '<fieldset>';

        // Row: label on the left, toggle on the right
        $html .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:20px;">';
        $html .= sprintf('<div><strong>%s</strong></div>', esc_html__('API Activity Log', 'license-manager-for-woocommerce'));
        $html .= sprintf('<div><label class="lmfwc-toggle" style="display:inline-flex;align-items:center;gap:8px;"><input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/> <span>%s</span></label></div>', $field, $field, checked(true, $value, false), '');
        $html .= '</div>';

        // Description with filename and view link
        $html .= sprintf('<p class="description" style="margin-top:8px;">%s <code style="background:#f4f4f4;padding:2px 6px;border-radius:3px;">%s</code><br /><a href="%s" target="_blank">%s</a></p>', esc_html__('Enable to log api activitites inside', 'license-manager-for-woocommerce'), esc_html($logfile), esc_url($logurl), esc_html__('View Log', 'license-manager-for-woocommerce'));

        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    public function fieldApiExceptionLog()
    {
        $field = 'lmfwc_api_exception_log';
        $value = isset($this->settings[$field]) && $this->settings[$field] ? true : false;
        $logfile = sprintf('lmfwc-api-exceptions-%s.log', date('Y-m-d'));
        $logurl = LMFWC_PLUGIN_URL . 'logs/' . $logfile;
        $html = '<fieldset>';

        // Row: label on the left, toggle on the right
        $html .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:20px;">';
        $html .= sprintf('<div><strong>%s</strong></div>', esc_html__('API Exception Log', 'license-manager-for-woocommerce'));
        $html .= sprintf('<div><label class="lmfwc-toggle" style="display:inline-flex;align-items:center;gap:8px;"><input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/> <span>%s</span></label></div>', $field, $field, checked(true, $value, false), '');
        $html .= '</div>';

        // Description with filename and view link
        $html .= sprintf('<p class="description" style="margin-top:8px;">%s <code style="background:#f4f4f4;padding:2px 6px;border-radius:3px;">%s</code><br /><a href="%s" target="_blank">%s</a></p>', esc_html__('Enable to log api exceptions inside', 'license-manager-for-woocommerce'), esc_html($logfile), esc_url($logurl), esc_html__('View Log', 'license-manager-for-woocommerce'));

        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    public function fieldApiOutputLog()
    {
        $field = 'lmfwc_api_output_log';
        $value = isset($this->settings[$field]) && $this->settings[$field] ? true : false;
        $logfile = sprintf('lmfwc-api-output-%s.log', date('Y-m-d'));
        $logurl = LMFWC_PLUGIN_URL . 'logs/' . $logfile;
        $html = '<fieldset>';

        // Row: label on the left, toggle on the right
        $html .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:20px;">';
        $html .= sprintf('<div><strong>%s</strong></div>', esc_html__('API Output Log', 'license-manager-for-woocommerce'));
        $html .= sprintf('<div><label class="lmfwc-toggle" style="display:inline-flex;align-items:center;gap:8px;"><input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/> <span>%s</span></label></div>', $field, $field, checked(true, $value, false), '');
        $html .= '</div>';

        // Description with filename and view link
        $html .= sprintf('<p class="description" style="margin-top:8px;">%s <code style="background:#f4f4f4;padding:2px 6px;border-radius:3px;">%s</code><br /><a href="%s" target="_blank">%s</a></p>', esc_html__('Enable to log api output inside', 'license-manager-for-woocommerce'), esc_html($logfile), esc_url($logurl), esc_html__('View Log', 'license-manager-for-woocommerce'));

        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    /**
     * Generic callback for Pro features.
     *
     * @param array $args
     * @return void
     */
    public function fieldProFeature($args)
    { 
        $aria = esc_attr__('Upgrade to Pro', 'license-manager-for-woocommerce');
        $args = is_array($args) ? $args : array();
        $html = '<div class="lmfwc-pro-setting" role="button" tabindex="0" aria-label="' . $aria . '">';
        
        $type = isset($args['type']) ? $args['type'] : '';
        $label = isset($args['label']) ? $args['label'] : '';
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';

        if ($type === 'select') {
            $html .= '<select disabled class="regular-text"><option>' . esc_html($label) . '</option></select>';
        } elseif ($type === 'text_with_checkbox') {
            $html .= '<input type="checkbox" disabled> ';
            $html .= '<input type="text" disabled class="regular-text" placeholder="' . esc_attr($placeholder) . '">';
        } elseif ($type === 'text') {
            $html .= '<input type="text" disabled class="regular-text" placeholder="' . esc_attr( $label) . '">';
        } elseif (isset($args['type']) && $args['type'] === 'checkbox') {
            $html .= '<label><input type="checkbox" disabled> <span>' . esc_html($label) . '</span></label>';
        } elseif (isset($args['type']) && $args['type'] === 'toggle') {
            // Simple toggle representation using a disabled checkbox
            $html .= '<label class="lmfwc-toggle" style="display:inline-flex;align-items:center;gap:8px;"><input type="checkbox" disabled> <span>' . esc_html($label) . '</span></label>';
        } elseif (isset($args['type']) && $args['type'] === 'button') {
            $html .= '<button type="button" class="button" disabled>' . esc_html($label ? $label : __('Action', 'license-manager-for-woocommerce')) . '</button>';
        } else {
            $html .= '<input type="text" disabled class="regular-text">';
        }

        $html .= ' <span class="lmfwc-pro-badge-small">PRO</span>';
        
        if (isset($args['description'])) {
            $html .= '<p class="description">' . esc_html($args['description']) . '</p>';
        }

        $html .= '</div>';

        echo $html;
    }
}