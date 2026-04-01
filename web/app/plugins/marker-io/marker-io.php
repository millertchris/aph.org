<?php
/*
Plugin Name: Marker.io
Plugin URI:  https://marker.io/wordpress-website-feedback-plugin
Description: Collect visual website feedback from colleagues and clients directly in your WordPress site.
Version:     1.2.2
Author:      Marker.io
Author URI:  https://marker.io
License:     GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.6
Requires at least: 4.7
Tested up to: 6.9
*/

// Config automatic pull
if (file_exists(__DIR__ . '/cfg.php')) {
  include('cfg.php');
} else {
  include('dev.cfg.php');
}


function markerio_enqueue_admin_script($hook)
{
  wp_register_style('markerio_icons', plugin_dir_url(__FILE__) . 'markerio-icons.css');
  wp_enqueue_style('markerio_icons');

  if ('settings_page_marker-io-plugin' != $hook && 'toplevel_page_marker-io-plugin' != $hook ) {
    return;
  }

  wp_enqueue_style('markerio_style', plugin_dir_url(__FILE__) . 'dist/styles.css', [], '1.2.2');
  wp_enqueue_script('markerio_script', plugin_dir_url(__FILE__) . 'dist/scripts.js', [], '1.2.2');
}
add_action('admin_enqueue_scripts', 'markerio_enqueue_admin_script');

function markerio_check_user_role($roles, $user_id = null)
{
  if ($user_id) $user = get_userdata($user_id);
  else $user = wp_get_current_user();
  if (empty($user)) return false;
  if (empty($roles)) return false;
  foreach ($user->roles as $role) {
    if (in_array($role, $roles)) {
      return true;
    }
  }
  return false;
}

function markerio_insert_code_snippet()
{
  $display_button = true;
  $reason = '';

  $options = get_option('markerio_plugin_options');

  // General
  $destination_id = $options['destination_id'];
  $enable_widget = $options['enable_widget'] === 'false' ? false : true;

  // User role
  $user_role_filter = $options['user_role_filter'];
  $enabled_user_roles = $options['enabled_user_roles'];

  // Post type
  $post_type_filter = $options['post_type_filter'];
  $enabled_post_types = $options['enabled_post_types'];
  $post_type = get_post_type();

  // Status
  $post_status_filter = $options['post_status_filter'];
  $status = get_post_status(get_the_id());

  if (is_admin()) {
    $enable_admin = $options['enable_admin'] === false || $options['enable_admin'] === 'false' ? false : true;

    if (!$enable_admin) {
      $display_button = false;
      $reason = 'Disabled in admin';
    } elseif (!$destination_id) {
      $display_button = false;
      $reason = 'Missing project ID';
    } elseif (!$enable_widget) {
      $display_button = false;
      $reason = 'Widget disabled';
    } elseif ($user_role_filter === 'specific' && !markerio_check_user_role($enabled_user_roles)) {
      $display_button = false;
      $reason = 'User has no enabled role';
    }
  } else {
    if (!$destination_id) {
      $display_button = false;
      $reason = 'Missing project ID';
    } elseif (!$enable_widget) {
      $display_button = false;
      $reason = 'Widget disabled';
    } elseif ($user_role_filter === 'authed' && !is_user_logged_in()) {
      $display_button = false;
      $reason = 'User not authenticated';
    } elseif ($user_role_filter === 'specific' && !markerio_check_user_role($enabled_user_roles)) {
      $display_button = false;
      $reason = 'User has no enabled role';
    } elseif ($post_type_filter === 'pages' && $post_type !== 'page') {
      $display_button = false;
      $reason = 'Not a page';
    } elseif ($post_type_filter === 'posts' && $post_type !== 'post') {
      $display_button = false;
      $reason = 'Not a blog post';
    } elseif ($post_type_filter === 'specific' && !empty($enabled_post_types) && !in_array($post_type, $enabled_post_types)) {
      $display_button = false;
      $reason = 'Post type "' . $post_type . '" not enabled';
    } elseif ($post_status_filter === 'published' && $status !== 'publish') {
      $display_button = false;
      $reason = 'Status not published';
    } elseif ($post_status_filter === 'unpublished' && !in_array($status, ['pending', 'draft'])) {
      $display_button = false;
      $reason = 'Status not draft/pending';
    }
  }

  if (!$display_button) {
    // Use this for debugging:
    // console.log('Marker.io widget not loaded: " . $reason . "');

    echo "<script>
    // Block presence of other buttons
    window.__Marker = {};
    </script>";
  }

  if ($display_button) {
    $passUserInfo = false;
    $user = wp_get_current_user();

    if (is_user_logged_in()) {
      $passUserInfo = true;

      if ($user->first_name && $user->last_name) {
        $username = $user->first_name . ' ' . $user->last_name;
      } elseif ($user->first_name) {
        $username = $user->first_name;
      } elseif ($user->display_name) {
        $username = $user->display_name;
      } else {
        $username = $user->user_login;
      }
    }

    echo "<script>(function (window) {
    console.log('Loading marker.io plugin...');

    if (window.__Marker || window !== window.top) { return; }
    window.__Marker = {};

    window.markerConfig = {
      project: '" . esc_js($options['destination_id']) . "',
      source: 'wordpress',
      ";

    if ($passUserInfo) {
      echo "reporter: {
          email: '" . esc_js($user->user_email) . "',
          fullName: '" . esc_js($username) . "',
          createdAt: '" . esc_js($user->user_registered) . "',
        },";
    }
    echo "
    };

    const script = document.createElement('script');
    script.async = 1;
    script.src = '" . esc_url(MARKER_EDGE_URL) . "/shim.js';
    document.head.appendChild(script);
  })(window);</script>";
  }
}
add_action('wp_head', 'markerio_insert_code_snippet');
add_action('admin_head', 'markerio_insert_code_snippet');

function markerio_add_menu_page()
{
  if (current_user_can('manage_options')) {
    add_options_page('Marker.io settings', 'Marker.io', 'manage_options', 'marker-io-plugin', 'markerio_render_plugin_settings_page');
  } else {
    add_menu_page('Marker.io settings', 'Marker.io', 'read', 'marker-io-plugin', 'markerio_render_plugin_settings_page', 'dashicons-markerio', 99);
  }

  // Use this later once we need submenu
  // add_submenu_page('marker-io-plugin', 'Settings', 'Settings', 'manage_options', 'marker-io-plugin', 'markerio_render_plugin_settings_page');
}
add_action('admin_menu', 'markerio_add_menu_page');

// Activation hook
function markerio_activation()
{
  do_action('markerio_default_options');
}
register_activation_hook(__FILE__, 'markerio_activation');


// Set default options
function markerio_default_values()
{
  // Form settings
  $options = array(
    'destination_id' => '',
    'destination_name' => '',
    'destination_display_name' => '',
    'destination_platform' => '',
    'enable_widget' => true,
    'enable_admin' => false,
    'user_role_filter' => 'all',
    'enabled_user_roles' => [],
    'post_type_filter' => 'all',
    'enabled_post_types' => [],
    'post_status_filter' => 'all',
  );
  add_option('markerio_plugin_options', $options);
}
add_action('markerio_default_options', 'markerio_default_values');

function markerio_render_plugin_settings_page()
{
  $plugin_options = get_option('markerio_plugin_options');
  $available_user_role = get_editable_roles();
  $available_post_types = get_post_types();
  $site_url = get_site_url();
  $can_manage_options = current_user_can('manage_options');
  $nonce = wp_create_nonce('markerio_nonce');

  unset($available_post_types['custom_css']);
  unset($available_post_types['customize_changeset']);
  unset($available_post_types['nav_menu_item']);
  unset($available_post_types['oembed_cache']);
  unset($available_post_types['revision']);
  unset($available_post_types['user_request']);
  unset($available_post_types['wp_block']);

  echo '<div id="app"></div>';
  echo '<script>
      window.markerioPluginOptions = ' . json_encode($plugin_options) . ';
      window.markerioAvailableUserRoles = ' . json_encode($available_user_role) . ';
      window.markerioAvailablePostTypes = ' . json_encode($available_post_types) . ';
      window.markerioCanManageOptions = ' . json_encode($can_manage_options) . ';
      window.markerioSiteUrl = "' . $site_url . '";
      window.markerioNonce = "' . $nonce . '";
    </script>';
}

function markerio_register_settings()
{
  register_setting('markerio_plugin_options', 'markerio_plugin_options', 'markerio_plugin_options_validate');

  // General

  add_settings_field('destination_id', 'Project ID', 'markerio_plugin_setting_destination_id', 'markerio_plugin');
  add_settings_field('destination_name', 'Project name', 'markerio_plugin_setting_destination_name', 'markerio_plugin');
  add_settings_field('destination_display_name', 'Project display name', 'markerio_plugin_setting_destination_display_name', 'markerio_plugin');
  add_settings_field('destination_platform', 'Project platform', 'markerio_plugin_setting_destination_platform', 'markerio_plugin');
  add_settings_field('enable_widget', 'Enable Marker.io', 'markerio_plugin_setting_enable_widget', 'markerio_plugin');
  add_settings_field('enable_admin', 'Enable in admin', 'markerio_plugin_setting_enable_admin', 'markerio_plugin');

  // User roles

  add_settings_field('user_role_filter', 'Filter on users', 'markerio_plugin_setting_user_role_filter', 'markerio_plugin');
  add_settings_field('enabled_user_roles', 'User roles', 'markerio_plugin_setting_enabled_user_roles', 'markerio_plugin');

  // Post types

  add_settings_field('post_type_filter', 'Filter on post types', 'markerio_plugin_setting_post_type_filter', 'markerio_plugin');
  add_settings_field('enabled_post_types', 'Post types', 'markerio_plugin_setting_enabled_post_types', 'markerio_plugin');

  // Status

  add_settings_field('post_status_filter', 'Post status', 'markerio_plugin_setting_post_status_filter', 'markerio_plugin');
}
add_action('admin_init', 'markerio_register_settings');

function markerio_plugin_options_validate($input)
{
  // IMPROVEMENT: validation

  // $newinput['destination_id'] = trim( $input['destination_id'] );
  // if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['destination_id'] ) ) {
  //     $newinput['destination_id'] = '';
  // }

  // return $newinput;

  return $input;
}

add_filter('plugin_action_links_marker-io/marker-io.php', 'markerio_settings_link');
function markerio_settings_link($links)
{
  $url = esc_url(add_query_arg(
    'page',
    'marker-io-plugin',
    get_admin_url() . 'options-general.php?page=marker-io-plugin'
  ));

  // Use this when using a top-level page again:
  // get_admin_url() . 'admin.php?page=marker-io-plugin'

  $settings_link = "<a href='$url'>" . __('Settings') . '</a>';

  array_push(
    $links,
    $settings_link
  );

  return $links;
}

function markerio_save_option()
{
  // Do NOT remove this line !!!
  if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'markerio_nonce')) {
    // Invalid nonce: return an error.
    wp_send_json_error('Forbidden', 403);
  }

  // Do NOT remove this line !!!
  if (!current_user_can('manage_options')) {
    wp_send_json_error('Forbidden', 403);
  }

  $markerio_plugin_options = get_option('markerio_plugin_options');

  // General
  $markerio_plugin_options['enable_widget'] = sanitize_text_field($_POST["enableWidget"]);
  $markerio_plugin_options['enable_admin'] = sanitize_text_field($_POST["enableAdmin"]);

  // User roles
  $markerio_plugin_options['user_role_filter'] = sanitize_text_field($_POST["userRoleFilter"]);
  $enabled_user_roles = sanitize_text_field($_POST["enabledUserRoles"]);
  $markerio_plugin_options['enabled_user_roles'] = explode(',', $enabled_user_roles);

  // Post type
  $markerio_plugin_options['post_type_filter'] = sanitize_text_field($_POST["postTypeFilter"]);
  $enabled_post_types = sanitize_text_field($_POST["enabledPostTypes"]);
  $markerio_plugin_options['enabled_post_types'] = explode(',', $enabled_post_types);

  // Status
  $markerio_plugin_options['post_status_filter'] = sanitize_text_field($_POST["postStatusFilter"]);

  update_option('markerio_plugin_options', $markerio_plugin_options);

  echo json_encode($markerio_plugin_options);
  die();
}
add_action('wp_ajax_' . 'markerio_save_action', 'markerio_save_option');


function markerio_save_destination()
{
  // Do NOT remove this line !!!
  if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'markerio_nonce')) {
    // Invalid nonce: return an error.
    wp_send_json_error('Forbidden', 403);
  }

  // Do NOT remove this line !!!
  if (!current_user_can('manage_options')) {
    wp_send_json_error('Forbidden', 403);
  }

  $markerio_plugin_options = get_option('markerio_plugin_options');
  $markerio_plugin_options['destination_id'] = sanitize_key($_POST["destinationId"]);
  $markerio_plugin_options['destination_name'] = sanitize_text_field($_POST["destinationName"]);
  $markerio_plugin_options['destination_display_name'] = sanitize_text_field($_POST["destinationDisplayName"]);
  $markerio_plugin_options['destination_platform'] = sanitize_text_field($_POST["destinationPlatform"]);

  update_option('markerio_plugin_options', $markerio_plugin_options);

  echo json_encode($markerio_plugin_options);
  die();
}
add_action('wp_ajax_' . 'markerio_save_destination', 'markerio_save_destination');