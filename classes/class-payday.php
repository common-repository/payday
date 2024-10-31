<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Include dependencies
require_once PAYDAY_DIR_PATH . 'admin/class-payday-admin-menu.php';
require_once PAYDAY_DIR_PATH . 'admin/class-payday-admin.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-i18n.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-settings.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-webhooks.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-product-sync.php';
require_once PAYDAY_DIR_PATH . 'pages/class-payday-settings-page.php';
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';


/**
 * Class Payday
 *
 * Main plugin class.
 */
class Payday
{
    use Payday_Singleton_Trait;  // Make this class a singleton

    protected $plugin_name;      // The unique identifier of this plugin.
    protected $version;          // The current version of the plugin.

    const TEXT_DOMAIN = 'payday';

    /**
     * Constructor: Initializes the plugin and defines hooks.
     */
    private function __construct()
    {
        $this->initialize_plugin_info();
        $this->define_hooks();
    }

    /**
     * Initializes the version and name for this plugin.
     */
    private function initialize_plugin_info()
    {
        $this->version = defined('PAYDAY_VERSION') ? PAYDAY_VERSION : '1.0.0';
        $this->plugin_name = PAYDAY_NAME;
    }

    /**
     * Registers various WordPress hooks.
     */
    public function define_hooks()
    {
        // Plugins loaded related hooks
        add_action('plugins_loaded', array($this, 'plugins_loaded'), 12);
        add_action('plugins_loaded', array(Payday_i18n::instance(), 'load_plugin_textdomain'), 11);

        // Admin menu related hooks
        add_action('admin_menu', array(Payday_Admin_Menu::instance(), 'admin_menu'), 20);

        // Admin related hooks
        $payday_admin = Payday_Admin::instance();
        add_filter('plugin_action_links_' . PAYDAY_BASE_NAME, array($payday_admin, 'plugin_action_links'), 10, 2);
        add_filter('plugin_row_meta', array($payday_admin, 'plugin_row_meta'), 10, 2);
        add_action('admin_footer_text', array($payday_admin, 'admin_footer_text'), 11);

        // General hooks
        $payday_webhooks = Payday_Webhooks::instance();
        add_action('woocommerce_order_status_changed', array($payday_webhooks, 'action_woocommerce_order_status_changed'), 10, 4);
        add_action('woocommerce_new_order', array($payday_webhooks, 'action_woocommerce_new_order'), 10, 1);

        // Settings related hooks
        $payday_settings = Payday_Settings::instance();
        $payday_settings_page = Payday_Settings_Page::instance();
        add_action('admin_init', array($payday_settings, 'register_settings'));
        add_action("admin_post_payday_settings_refresh", array($payday_settings_page, "admin_post_payday_settings_refresh"));
        add_action("admin_post_payday_settings_disconnect", array($payday_settings_page, "admin_post_payday_settings_disconnect"));
        add_action('admin_post_payday_sync_all_products', array($payday_settings_page, 'admin_post_payday_sync_all_products'));

        $payday_product_sync = Payday_Product_Sync::instance();
        add_filter('bulk_actions-edit-product', array($payday_product_sync, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-product', array($payday_product_sync, 'bulk_action_handler'), 10, 3);
        add_action('admin_post_sync_inventory_with_payday', array($payday_product_sync, 'handle_sync_inventory_with_payday'));
        add_action('admin_notices', array($payday_product_sync, 'bulk_action_admin_notice'));
        add_filter('post_row_actions', array($payday_product_sync, 'add_custom_button_to_product_list'), 10, 2);
        add_filter('manage_edit-product_columns', array($payday_product_sync, 'add_custom_product_column'), 10, 1);
        add_action('manage_product_posts_custom_column', array($payday_product_sync, 'add_custom_product_column_content'), 10, 2);
        add_action('admin_enqueue_scripts', array($payday_product_sync, 'my_plugin_enqueue_admin_styles'));
    }

    /**
     * Defines hooks related to the "claim service payment gateway" 
     * after all plugins have been loaded.
     */
    public function plugins_loaded()
    {
        $this->define_claim_service_payment_gateway_hooks();
    }

    /**
     * Defines and registers various hooks related to the payment gateway.
     */
    public function define_claim_service_payment_gateway_hooks()
    {
        // We require the payment gateway class here because it will break unless woocommerce is loaded first
        require_once PAYDAY_DIR_PATH . 'classes/class-payday-claim-service-payment-gateway.php';

        // Payment Gateway related hooks
        $payment_gateway = Payday_Claim_Service_Payment_Gateway::instance();

        // Adding the payment gateway class to WooCommerce's list of payment gateways
        add_filter("woocommerce_payment_gateways", array($payment_gateway, "add_gateway_class"));

        // When the gateway settings are updated 
        add_action('woocommerce_update_options_payment_gateways_' . $payment_gateway->get_id(), array($payment_gateway, 'process_admin_options'));

        // Adding custom fields to the WooCommerce checkout form
        add_action('woocommerce_checkout_fields', array($payment_gateway, 'checkout_fields'));

        // Adding a custom function to the checkout process in WooCommerce
        add_action('woocommerce_checkout_process', array($payment_gateway, 'checkout_process'));

        // Updating the user's metadata with the custom fields from the checkout form
        add_action('woocommerce_checkout_update_user_meta', array($payment_gateway, 'checkout_update_user_meta'));

        // Updating the order's metadata with the custom fields from the checkout form
        add_action('woocommerce_checkout_update_order_meta', array($payment_gateway, 'checkout_update_order_meta'), 10, 2);

        // Adding a custom function to the save_post_shop_order action in WooCommerce
        add_action('save_post_shop_order', array($payment_gateway, 'action_save_post_shop_order'), 10, 1);

        // Adding a custom SSN field to the admin billing fields in WooCommerce
        add_filter('woocommerce_admin_billing_fields', array($payment_gateway, 'add_ssn_field_to_admin_billing_fields'));

        // Adding the custom fields to the email that is sent out with the order information
        add_filter('woocommerce_email_order_meta_keys', array($payment_gateway, 'email_order_meta_keys'));
    }
}
