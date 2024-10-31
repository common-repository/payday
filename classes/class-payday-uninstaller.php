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

class Payday_Uninstaller
{
	// Make this class a singleton
	use Payday_Singleton_Trait;

	/**
	 * The constructor is private to prevent initiation with outer code.
	 */
	private function __construct()
	{
	}

	/**
	 * Fired during plugin uninstallation
	 */
	public static function uninstall()
	{
		Payday_Logger::log('The Payday plugin is being uninstalled...', 'info');

		// delete all tables
		self::delete_tables();

		// delete all options
		self::delete_options();

		// delete all transients
		self::delete_transients();

		// delete all cron jobs
		self::delete_cron_jobs();

		Payday_Logger::log('The Payday plugin was uninstalled successfully.', 'info');
	}

	/**
	 * Delete all tables
	 */
	private static function delete_tables()
	{
		// Delete auth tokens table
		try {
			if (Payday_Auth_Tokens_Provider::delete_table()) {
				Payday_Logger::log('The auth tokens table was deleted.', 'info');
			} else {
				Payday_Logger::log('The auth tokens table was not deleted.', 'info');
			}
		} catch (Exception $e) {
			Payday_Logger::log('The auth tokens table could not be deleted. Error: ' . $e->getMessage(), 'error');
		}

		// Delete invoice meta table
		try {
			if (Payday_Invoice_Meta_Provider::delete_table()) {
				Payday_Logger::log('The invoice meta table was deleted.', 'info');
			} else {
				Payday_Logger::log('The invoice meta table was not deleted.', 'info');
			}
		} catch (Exception $e) {
			Payday_Logger::log('The invoice meta table could not be deleted. Error: ' . $e->getMessage(), 'error');
		}

		// Delete payment types table
		try {
			if (Payday_Payment_Types_Provider::delete_table()) {
				Payday_Logger::log('The payment types table was deleted.', 'info');
			} else {
				Payday_Logger::log('The payment types table was not deleted.', 'info');
			}
		} catch (Exception $e) {
			Payday_Logger::log('The payment types table could not be deleted. Error: ' . $e->getMessage(), 'error');
		}

		// Delete order job status table
		try {
			if (Payday_Order_Job_Status_Provider::delete_table()) {
				Payday_Logger::log('The order job status table was deleted.', 'info');
			} else {
				Payday_Logger::log('The order job status table was not deleted.', 'info');
			}
		} catch (Exception $e) {
			Payday_Logger::log('The order job status table could not be deleted. Error: ' . $e->getMessage(), 'error');
		}
	}

	/**
	 * Delete all options
	 */
	private static function delete_options()
	{
		require_once PAYDAY_DIR_PATH . 'models/class-payday-settings-model.php';
		$settings_model = new Payday_Settings_Model();
		$settings_model->delete_all_settings();
	}

	/**
	 * Delete all transients
	 */
	private static function delete_transients()
	{
		require_once PAYDAY_DIR_PATH . 'models/class-payday-auth-model.php';
		$auth_model = new Payday_Auth_Model();
		$auth_model->delete_all_transients();
	}

	/**
	 * Delete all cron jobs
	 */
	private static function delete_cron_jobs()
	{
		require_once PAYDAY_DIR_PATH . 'classes/class-payday-cron-manager.php';
		$cron_manager = Payday_Cron_Manager::instance();
		$cron_manager->remove_all_cron_jobs();
	}
}
