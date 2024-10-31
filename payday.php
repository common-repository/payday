<?php

/**
 * Plugin Name:       Payday
 * Description:       Integrates your WooCommerce store with Payday allowing new orders to be automatically sent as invoices.
 * Author:            Payday ehf.
 * Version:           3.3.9
 * Requires PHP:      8.0
 * Author URI:        https://payday.is/
 * Plugin URI:        https://payday.is/
 * Text Domain:       payday
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Tested up to:      6.6.2
 * WC requires at least: 3.0.0
 * WC tested up to: 9.3.2
 */
if (!defined('ABSPATH')) exit;

if (!function_exists('is_woocommerce_active')) {
    function is_woocommerce_active()
    {
        $active_plugins = (array)get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
    }
}

if (is_woocommerce_active()) {
    if (!defined('PAYDAY_PLUGIN_FILE')) {
        define('PAYDAY_PLUGIN_FILE', __FILE__);
    }

    if (!defined('PAYDAY_VERSION')) {
        define('PAYDAY_VERSION', '3.3.9');
    }

    if (!defined('PAYDAY_NAME')) {
        define('PAYDAY_NAME', 'payday');
    }

    if (!defined('PAYDAY_BASE_NAME')) {
        define('PAYDAY_BASE_NAME', plugin_basename(__FILE__));
    }

    if (!defined('PAYDAY_DIR_PATH')) {
        define('PAYDAY_DIR_PATH', plugin_dir_path(__FILE__));
    }

    if (!defined('PAYDAY_MINIMUM_PHP_VERSION')) {
        define('PAYDAY_MINIMUM_PHP_VERSION', '8.0');
    }

    if (!defined('PAYDAY_LOGS_DIR_PATH')) {
        $upload_dir_info = wp_upload_dir();
        $log_dir = trailingslashit($upload_dir_info['basedir']) . 'payday-logs/';
        if (!file_exists($log_dir)) {
            if (wp_mkdir_p($log_dir)) { // Directory created successfully
                define('PAYDAY_LOGS_DIR_PATH', $log_dir);
            } else { // Directory creation failed
                // Log this error somewhere else, like a system error log
                error_log("Could not create directory: $log_dir");
            }
        } else {
            define('PAYDAY_LOGS_DIR_PATH', $log_dir);
        }
    }

    register_activation_hook(__FILE__, 'activate_payday');
    register_deactivation_hook(__FILE__, 'deactivate_payday');
    register_uninstall_hook(__FILE__, 'uninstall_payday');

    require_once PAYDAY_DIR_PATH . 'classes/class-payday.php';
    function run_payday()
    {
        $plugin = Payday::instance();
    }

    run_payday();
}

/**
 * Display an error notice about the PHP version requirement.
 */
function payday_php_version_error_notice()
{
?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                esc_html__('The Payday plugin requires PHP version %s or later. You are running version %s. Please upgrade your PHP version or deactivate the plugin.', 'payday'),
                PAYDAY_MINIMUM_PHP_VERSION,
                PHP_VERSION
            );
            ?>
        </p>
    </div>
<?php
}

/**
 * The code that runs during plugin activation.
 * 
 * @return void
 */
function activate_payday()
{
    // Compare the current PHP version with the minimum required version.
    if (version_compare(PHP_VERSION, PAYDAY_MINIMUM_PHP_VERSION, '<')) {
        // Deactivate the plugin.
        deactivate_plugins(plugin_basename(__FILE__));

        // Display an error notice.
        add_action('admin_notices', 'payday_php_version_error_notice');

        // Remove the "Plugin activated" notice.
        unset($_GET['activate']);
        return;
    }

    // Check if the current user has the capability to activate plugins.
    if (!current_user_can('activate_plugins'))
        return;

    // Get the plugin request. If not set, assign an empty string.
    $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';

    // Verify that the request to activate the plugin was a valid request from an administrator.
    check_admin_referer("activate-plugin_{$plugin}");

    // Include the payday activator class.
    require_once PAYDAY_DIR_PATH . 'classes/class-payday-activator.php';

    // Create an instance of the payday activator class.
    $activator = Payday_Activator::instance();

    // Run the activate function from the payday activator class.
    $activator->activate();

    // Check if the required tables for the plugin exist.
    $activator->check_if_tables_exists();
}

/**
 * The code that runs during plugin deactivation.
 * 
 * @return void
 */
function deactivate_payday()
{
    // Check if the current user has the capability to deactivate plugins.
    if (!current_user_can('activate_plugins'))
        return;

    // Get the plugin request. If not set, assign an empty string.
    $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';

    // Verify that the request to deactivate the plugin was a valid request from an administrator.
    check_admin_referer("deactivate-plugin_{$plugin}");

    // Include the payday deactivator class.
    require_once PAYDAY_DIR_PATH . 'classes/class-payday-deactivator.php';

    // Create an instance of the payday deactivator class.
    $deactivator = Payday_Deactivator::instance();

    // Run the deactivate function from the payday deactivator class.
    $deactivator->deactivate();
}

/**
 * The code that runs during plugin uninstallation.
 * 
 * @return void
 */
function uninstall_payday()
{
    // Check if the current user has the capability to uninstall plugins.
    if (!current_user_can('activate_plugins'))
        return;

    // Include the payday uninstaller class.
    require_once PAYDAY_DIR_PATH . 'classes/class-payday-uninstaller.php';

    // Create an instance of the payday uninstaller class.
    $uninstaller = Payday_Uninstaller::instance();

    // Run the uninstall function from the payday uninstaller class.
    $uninstaller->uninstall();
}


/**
 * Checks the PHP version before updating the plugin.
 *
 * This function runs the PHP version check before the plugin is updated. 
 * If the required PHP version is not met, it cancels the update, 
 * keeps the previous version activated, and displays an error message.
 *
 * @param mixed $return The filtered value of the WP_Upgrader::install_package() result.
 * @param array $plugin An array of plugin data including the plugin to update.
 * 
 * @return mixed Returns the filtered value of the WP_Upgrader::install_package() result, 
 *               or a WP_Error object if the PHP version requirement is not met.
 */
function payday_pre_update_check($return, $plugin)
{
    // If another plugin is being updated, return early.
    if ($plugin['plugin'] !== plugin_basename(__FILE__)) {
        return $return;
    }

    // Compare the current PHP version with the minimum required version.
    if (version_compare(PHP_VERSION, PAYDAY_MINIMUM_PHP_VERSION, '<')) {
        // Display an error notice.
        add_action('admin_notices', 'payday_php_version_error_notice');

        // Cancel the update and keep the previous version activated.
        return new WP_Error(
            'update_error',
            sprintf(
                __('The Payday plugin requires PHP version %s or later. You are running version %s. Please upgrade your PHP version or deactivate the plugin.', 'payday'),
                PAYDAY_MINIMUM_PHP_VERSION,
                PHP_VERSION
            )
        );
    }

    return $return;
}

add_filter('upgrader_pre_install', 'payday_pre_update_check', 10, 2);

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage
 * 
 * @param string $plugin_file The plugin file.
 * @param bool $positive_compatibility Whether the plugin is compatible with WooCommerce High-Performance Order Storage.
 * @return void
 */
function declare_compatibility_with_wc_hpos($plugin_file, $positive_compatibility)
{
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', $plugin_file, $positive_compatibility);
    }
}

add_action('before_woocommerce_init', function () {
    declare_compatibility_with_wc_hpos(__FILE__, true);
});
