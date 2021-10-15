<?php
/*
Plugin Name: Motivar Post Search
Plugin URI: https://motivar.io
Description: Simple stylish search powered with awm
Version: 1.1
Author: Giannopoulos Nikolaos
Author URI: https://motivar.io
Text Domain:       mtv-search
 */

if (!defined('WPINC')) {
 die;
}

define('mtv_search_url', plugin_dir_url(__FILE__));
define('mtv_search_path', plugin_dir_path(__FILE__));
define('mtv_search_relative_path', dirname(plugin_basename(__FILE__)));
if (class_exists('AWM_Meta')) {
 require_once 'includes/init.php';
}
