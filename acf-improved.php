<?php
/**
 * Plugin Name:       ACF Improved
 * Plugin URI:        https://gavdev.com/wordpress-plugins/acf-improved/
 * Description:       A series of performance improvements designed to work with Advanced Custom Fields to reduce database queries.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Gavin McDonald
 * Author URI:        https://gavdev.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       acf-improved
 * Domain Path:       /languages
 */

namespace ACFImproved;

// Prevent direct access to this file

defined('ABSPATH') || die;

// Setup plugin constants

define('ACF_IMPROVED_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
define('ACF_IMPROVED_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));
define('ACF_IMPROVED_PLUGIN_VERSION', '1.0.0');

// Include plugin classes

require_once ACF_IMPROVED_PLUGIN_DIR . '/includes/classes/Data.php';

// Set up plugin hooks

add_action('update_option', '\\ACFImproved\\Data::clearOptionGroupCache', 10, 1);