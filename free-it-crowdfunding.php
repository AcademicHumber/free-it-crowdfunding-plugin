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
 * @Type
 * @Version
 * @Directory URL
 * @Directory Path
 * @Plugin Base Name
 */
define('FREE_IT_FILE', __FILE__);
define('FREE_IT_DIR_URL', plugin_dir_url(FREE_IT_FILE));
define('FREE_IT_DIR_PATH', plugin_dir_path(FREE_IT_FILE));
define('FREE_IT_BASENAME', plugin_basename(FREE_IT_FILE));


/**
 * Check for the existence of WooCommerce and any other requirements
 */
function freeit_check_requirements()
{
    if (is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('wp-crowdfunding/wp-crowdfunding.php')) {
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
    $message = __('Free It plugin requires WooCommerce and WPCrowdfunding to be installed.', 'free-it');

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

add_action('after_setup_theme', 'freeit_check_requirements');



if (!function_exists('freeit_function')) {
    function freeit_functions()
    {
        require_once FREE_IT_DIR_PATH . 'includes/Functions.php';
        return new \FREE_IT\Functions();
    }
}

if (!class_exists('FreeIt_CrowdFunding')) {
    require_once FREE_IT_DIR_PATH . 'includes/free-it-rewards.php';
    new \FREE_IT\FreeIt_CrowdFunding();
    require_once FREE_IT_DIR_PATH . 'auto-creator.php';
}

// add_filter('woocommerce_add_cart_item', function ($array, $int) {
//     echo '<pre>';
//     print_r(WC()->session);
//     echo '</pre>';

//     wp_die();
// }, 15, 3);