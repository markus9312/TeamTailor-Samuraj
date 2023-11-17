<?php
/**
 * Plugin Name: CoSwift
 * Description: A WordPress plugin to fetch job listings from TeamTailor and create Custom Post Types.
 * Version: 0.1
 * Author: dotMavriQ
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'settings-page.php';
require_once plugin_dir_path(__FILE__) . 'settings-register.php';
require_once plugin_dir_path(__FILE__) . 'api-functions.php';
require_once plugin_dir_path(__FILE__) . 'helpers.php';
require_once plugin_dir_path(__FILE__) . 'styles-and-scripts.php';
require_once plugin_dir_path(__FILE__) . 'job-listings.php';
