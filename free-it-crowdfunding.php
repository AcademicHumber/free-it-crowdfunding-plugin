<?php

/**
 * Plugin Name:     Free-It CrowdFunding plugin
 * Plugin URI:      https://github.com/AcademicHumber/free-it-crowdfunding-plugin
 * Description:     Free-It customizations to woocommerce and WPCrowdFunding.
 * Author:          Adrian Fernandez - Foco Azul 
 * Author URI:      https://www.linkedin.com/in/adrian-fernandez-1701/
 * Text Domain:     free-it
 * Domain Path:     /languages
 * Version:         1.0
 *
 * @package         Free-it
 *
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . '/wp-admin/includes/plugin.php');
}


/**
 * Check for the existence of WooCommerce and any other requirements
 */
function freeit_check_requirements()
{
    if (is_plugin_active('woocommerce/woocommerce.php')) {
        return true;
    } else {
        add_action('admin_notices', 'freeit_missing_wc_notice');
        return false;
    }
}

/**
 * Display a message advising WooCommerce is required
 */
function freeit_missing_wc_notice()
{
    $class = 'notice notice-error';
    $message = __('Galactica requires WooCommerce to be installed.', 'p3k-galactica');

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

add_action('after_setup_theme', 'freeit_check_requirements');


function setup_plugin()
{
    require_once plugin_dir_path(__FILE__) . 'free-it-rewards.php';
}

add_action('init', 'setup_plugin');
