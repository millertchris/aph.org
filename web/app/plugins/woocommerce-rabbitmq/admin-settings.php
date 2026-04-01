<?php
add_action('admin_menu', 'woocommerce_rabbitmq_add_admin_menu');
add_action('admin_init', 'woocommerce_rabbitmq_settings_init');

/**
 * Rabbitmq adding settings page in admin site
 *
 * @name   woocommerce_rabbitmq_add_admin_menu
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_add_admin_menu() {

    add_submenu_page(null, 'woocommerce-rabbitmq', 'woocommerce-rabbitmq', 'manage_options', 'woocommerce-rabbitmq', 'woocommerce_rabbitmq_options_page');
}

/**
 * Rabbitmq settings rendering page
 *
 * @name   woocommerce_rabbitmq_settings_init
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_settings_init() {

    register_setting('pluginPage', 'woocommerce_rabbitmq_settings');

    add_settings_section(
        'woocommerce_rabbitmq_pluginPage_section', __('', 'wordpress'), '', 'pluginPage'
    );

    add_settings_field(
        'woocommerce_rabbitmq_host_name', __('Please enter host name', 'wordpress'), 'woocommerce_rabbitmq_host_name_render', 'pluginPage', 'woocommerce_rabbitmq_pluginPage_section'
    );

    add_settings_field(
        'woocommerce_rabbitmq_user_name', __('Please enter user name', 'wordpress'), 'woocommerce_rabbitmq_user_name_render', 'pluginPage', 'woocommerce_rabbitmq_pluginPage_section'
    );

    add_settings_field(
        'woocommerce_rabbitmq_password', __('Please enter password', 'wordpress'), 'woocommerce_rabbitmq_password_render', 'pluginPage', 'woocommerce_rabbitmq_pluginPage_section'
    );

    add_settings_field(
        'woocommerce_rabbitmq_port_numer', __('Please enter port number', 'wordpress'), 'woocommerce_rabbitmq_port_number_render', 'pluginPage', 'woocommerce_rabbitmq_pluginPage_section'
    );

    add_settings_field(
        'woocommerce_rabbitmq_queue_name', __('Please enter queue name', 'wordpress'), 'woocommerce_rabbitmq_queue_name_render', 'pluginPage', 'woocommerce_rabbitmq_pluginPage_section'
    );
	add_settings_field(
        'woocommerce_rabbitmq_salesforce_queue_name', __('Please enter salesforce queue name', 'wordpress'), 'woocommerce_rabbitmq_salesforce_queue_name_render', 'pluginPage', 'woocommerce_rabbitmq_pluginPage_section'
    );
}

/**
 * Rabbitmq host name rendering
 *
 * @name   woocommerce_rabbitmq_host_name_render
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_host_name_render() {

    $options = get_option('woocommerce_rabbitmq_settings');
    ?>
    <input type='text' name='woocommerce_rabbitmq_settings[woocommerce_rabbitmq_host_name]' value='<?php echo $options['woocommerce_rabbitmq_host_name']; ?>'>
    <?php
}

/**
 * Rabbitmq user name rendering
 *
 * @name   woocommerce_rabbitmq_user_name_render
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_user_name_render() {

    $options = get_option('woocommerce_rabbitmq_settings');
    ?>
    <input type='text' name='woocommerce_rabbitmq_settings[woocommerce_rabbitmq_user_name]' value='<?php echo $options['woocommerce_rabbitmq_user_name']; ?>'>
    <?php
}

/**
 * Rabbitmq password rendering
 *
 * @name   woocommerce_rabbitmq_password_render
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_password_render() {

    $options = get_option('woocommerce_rabbitmq_settings');
    ?>
    <input type='text' name='woocommerce_rabbitmq_settings[woocommerce_rabbitmq_password]' value='<?php echo $options['woocommerce_rabbitmq_password']; ?>'>
    <?php
}

/**
 * Rabbitmq port number rendering
 *
 * @name   woocommerce_rabbitmq_port_number_render
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_port_number_render() {

    $options = get_option('woocommerce_rabbitmq_settings');
    ?>
    <input type='text' name='woocommerce_rabbitmq_settings[woocommerce_rabbitmq_port_numer]' value='<?php echo $options['woocommerce_rabbitmq_port_numer']; ?>'>
    <?php
}


/**
 * Rabbitmq queue name rendering
 *
 * @name   woocommerce_rabbitmq_queue_name_render
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_queue_name_render() {

    $options = get_option('woocommerce_rabbitmq_settings');
    ?>
    <input type='text' name='woocommerce_rabbitmq_settings[woocommerce_rabbitmq_queue_name]' value='<?php echo $options['woocommerce_rabbitmq_queue_name']; ?>'>
    <?php
}

/**
 * Rabbitmq queue salesforce name rendering
 *
 * @name   woocommerce_rabbitmq_salesforce_queue_name_render
 * @author Vivek Bansal
 */
function woocommerce_rabbitmq_salesforce_queue_name_render() {

    $options = get_option('woocommerce_rabbitmq_settings');
    ?>
    <input type='text' name='woocommerce_rabbitmq_settings[woocommerce_rabbitmq_salesforce_queue_name]' value='<?php echo $options['woocommerce_rabbitmq_salesforce_queue_name']; ?>'>
    <?php
}

/**
 * Rabbitmq settings page
 *
 * @name   woocommerce_rabbitmq_options_page
 * @author Vivek Bansal
 */

function woocommerce_rabbitmq_options_page() {
    ?>
    <form action='options.php' method='post'>

        <h2>Woocommerce Rabbitmq Settings</h2>

        <?php
        settings_fields('pluginPage');
        do_settings_sections('pluginPage');
        submit_button();
        ?>

    </form>
    <?php
}
?>