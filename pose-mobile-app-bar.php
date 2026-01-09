<?php
/**
 * Plugin Name: Pose Mobile App Bar
 * Description: A premium, native-app like bottom navigation bar for WordPress. Features Glassmorphism design and easy customization.
 * Version: 3.2.0
 * Author: Pose Media
 * Author URI: https://posemedia.sa
 * Text Domain: pose-mobile-app-bar
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define Constants
define('PMAB_VERSION', '3.2.0');
define('PMAB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PMAB_PLUGIN_URL', plugin_dir_url(__FILE__));

// Require Classes
require_once PMAB_PLUGIN_DIR . 'includes/class-pmab-activator.php';
require_once PMAB_PLUGIN_DIR . 'includes/class-pmab-settings.php';
require_once PMAB_PLUGIN_DIR . 'includes/class-pmab-display.php';

/**
 * The code that runs during plugin activation.
 */
function run_pmab_activator()
{
    Pmab_Activator::activate();
}
register_activation_hook(__FILE__, 'run_pmab_activator');

/**
 * Initialize the plugin classes
 */
function run_pose_mobile_app_bar()
{
    $plugin_settings = new Pmab_Settings();
    $plugin_settings->init();

    $plugin_display = new Pmab_Display();
    $plugin_display->init();
}
add_action('plugins_loaded', 'run_pose_mobile_app_bar');
