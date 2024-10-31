<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-database/utils/class-payday-database-utils.php';

class Payday_Base_Provider
{
	public const TABLE_PREFIX = 'payday_';
	public const TABLE_NAME = '';

	public static function get_table_name()
	{
		global $wpdb;
		return $wpdb->prefix . static::TABLE_PREFIX . static::TABLE_NAME;
	}

	/**
	 * Remove all entries in a table. Use this function wisely, as it is a destructive operation.
	 * 
	 * @return bool True if entries are removed successfully, false otherwise
	 */
	public static function delete_all_entries()
	{
		global $wpdb;

		$table_name = static::get_table_name();

		// Check if table exists
		if (!Payday_Database_Utils::table_exists($table_name)) {
			throw new Exception("Error: Table does not exist.");
		}

		$query = "TRUNCATE TABLE " . $table_name;

		$wpdb->query($query);

		// check for database errors
		if ($wpdb->last_error) {
			return false;
		}

		return true;
	}

	/**
	 * Delete the table from the database. Use this function wisely, as it is a destructive operation.
	 *
	 * @return bool True on successful deletion, false on failure
	 * @throws Exception If deleting the table fails
	 */
	public static function delete_table()
	{
		global $wpdb;

		$table_name = static::get_table_name();

		// Check if the table exists before attempting to delete it
		if (!Payday_Database_Utils::table_exists($table_name)) {
			throw new Exception("Error: Table does not exist.");
		}

		$query = "DROP TABLE $table_name";

		$result = $wpdb->query($query);

		if ($result === false) {
			throw new Exception("Error: Failed to delete table from the database. Table name: {$table_name}");
		}

		return true;
	}
}
