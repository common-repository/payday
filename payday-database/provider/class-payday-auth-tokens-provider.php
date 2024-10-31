<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-base-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/utils/class-payday-database-utils.php';

class Payday_Auth_Tokens_Provider extends Payday_Base_Provider
{
	public const TABLE_NAME = 'auth_tokens';

	/**
	 * Create auth_tokens table.
	 * 
	 * @return bool True if table created successfully, false otherwise
	 */
	public static function create_auth_tokens_table()
	{
		global $wpdb;

		$table_name = self::get_table_name();

		// Check if table already exists
		if (Payday_Database_Utils::table_exists($table_name)) {
			return true;
		}

		$query = "CREATE TABLE " . $table_name . " (
            id INT NOT NULL AUTO_INCREMENT,
            token VARCHAR(303) NOT NULL, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) " . $wpdb->get_charset_collate() . ";";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($query);

		// check for database errors
		if ($wpdb->last_error) {
			return false;
		}

		// check if table was created successfully
		if (!Payday_Database_Utils::table_exists($table_name)) {
			return false;
		}

		return true;
	}

	/**
	 * Remove all entries from auth_tokens table.
	 * 
	 * @return bool True if entries removed successfully, false otherwise
	 */
	public static function delete_all_entries_in_auth_tokens_table()
	{
		global $wpdb;

		$table_name = self::get_table_name();

		// Check if table exists
		if (!Payday_Database_Utils::table_exists($table_name)) {
			// Table doesn't exist, so nothing to delete
			return true;
		}

		$query = "TRUNCATE TABLE " . $table_name;

		$wpdb->query($query);

		// check for database errors
		if ($wpdb->last_error) {
			return false;
		}

		return true;
	}
}
