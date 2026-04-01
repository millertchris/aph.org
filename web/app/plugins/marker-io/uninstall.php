<?php

// Do NOT delete this line !!!
if (!defined('WP_UNINSTALL_PLUGIN')) {
  die;
}

$option_name = 'markerio_plugin_options';

delete_option($option_name);

?>