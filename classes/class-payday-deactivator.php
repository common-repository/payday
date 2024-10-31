<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-auth-tokens-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-invoice-meta-provider.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-cron-manager.php';


// Check if the Payday_Deactivator class exists
class Payday_Deactivator
{
    // Make this class a singleton
    use Payday_Singleton_Trait;

    /**
     * The constructor is private to prevent initiation with outer code.
     */
    private function __construct()
    {
    }

    // Fired during plugin deactivation.
    public function deactivate()
    {
        Payday_Logger::log('The Payday plugin is being deactivated...', 'info');

        add_filter('woocommerce_payment_gateways', array($this, 'unregister_payday_claim_service_payment_gateway'));

        // Delete tables for this plugin
        try {
            if (Payday_Auth_Tokens_Provider::delete_table()) {
                Payday_Logger::log('The auth tokens table was deleted.', 'info');
            } else {
                Payday_Logger::log('The auth tokens table was not deleted.', 'info');
            }
        } catch (Exception $e) {
            Payday_Logger::log('The auth tokens table could not be deleted. Error: ' . $e->getMessage(), 'error');
        }

        try {
            if (Payday_Invoice_Meta_Provider::delete_table()) {
                Payday_Logger::log('The invoice meta table was deleted.', 'info');
            } else {
                Payday_Logger::log('The invoice meta table was not deleted.', 'info');
            }
        } catch (Exception $e) {
            Payday_Logger::log('The invoice meta table could not be deleted. Error: ' . $e->getMessage(), 'error');
        }

        Payday_Cron_Manager::remove_all_cron_jobs();
        Payday_Logger::log('The Payday plugin was deactivated. All cron jobs were removed.', 'info');
    }

    public function unregister_payday_claim_service_payment_gateway($gateways)
    {
        foreach ($gateways as $key => $gateway) {
            if ($gateway == 'WC_Gateway_Payday') {
                unset($gateways[$key]);
            }
        }
        return $gateways;
    }
}
