<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Include the Payday_Base class
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'payday-database/utils/class-payday-database-utils.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-auth-tokens-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-invoice-meta-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-payment-type-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-order-job-status-provider.php';

class Payday_Activator
{
    // Make this class a singleton
    use Payday_Singleton_Trait;

    const TABLE_PREFIX = 'payday_';

    /**
     * The constructor is private to prevent initiation with outer code.
     */
    private function __construct()
    {
    }

    // Fired during plugin activation
    public static function activate()
    {
        Payday_Logger::log('The Payday plugin is being activated...', 'info');

        try {
            if (Payday_Invoice_Meta_Provider::create_invoice_meta_table()) {
                Payday_Logger::log('Invoice meta table was created', 'info');
            } else {
                Payday_Logger::log('The invoice meta table was not created.', 'info');
            }
        } catch (Exception $e) {
            Payday_Logger::log('The invoice meta table could not be created. Error: ' . $e->getMessage(), 'error');
            // cancel activation
            return;
        }

        try {
            if (Payday_Order_Job_Status_Provider::create_order_job_status_table()) {
                Payday_Logger::log('Order job status table was created', 'info');
            } else {
                Payday_Logger::log('The job status table was not created.', 'info');
            }
        } catch (Exception $e) {
            Payday_Logger::log('The job status table could not be created. Error: ' . $e->getMessage(), 'error');
        }

        try {
            if (Payday_Payment_Types_Provider::create_payment_types_table()) {
                Payday_Logger::log('Payment types table was created', 'info');
            } else {
                Payday_Logger::log('The payment types table was not created.', 'info');
            }
        } catch (Exception $e) {
            Payday_Logger::log('The payment types table could not be created. Error: ' . $e->getMessage(), 'error');
        }

        Payday_Logger::log('The Payday plugin was activated sucessfully and all necessary tables were created.', 'info');
    }

    /**
     * Check if all necessary tables exist. If not trigger an error.
     * 
     * @return void
     */
    public static function check_if_tables_exists()
    {
        // Including necessary file
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Accessing global wpdb object
        global $wpdb;

        // List of expected tables
        $tables = [
            Payday_Invoice_Meta_Provider::get_table_name(),
            Payday_Payment_Types_Provider::get_table_name(),
            Payday_Order_Job_Status_Provider::get_table_name()
        ];

        // Checking each table
        foreach ($tables as $table_name) {
            // If the table does not exist, trigger an error
            if (!Payday_Database_Utils::table_exists($table_name)) {
                $error_message = 'ESSENTIAL DATABASE TABLE MISSING: ' . $table_name;
                // TODO: Replace with something more user friendly, like admin_notice
                trigger_error($error_message, E_USER_ERROR); // Show error
                Payday_Logger::log($error_message, 'error');
            }
        }
    }
}
