<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-base-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/utils/class-payday-database-utils.php';

class Payday_Payment_Types_Provider extends Payday_Base_Provider
{
	public const TABLE_NAME = 'payment_types';

	/**
	 * Create payment_types table.
	 * 
	 * @param string $prefix The prefix for table names
	 * @return bool True if table created successfully, false otherwise
	 */
	public static function create_payment_types_table()
	{
		global $wpdb;

		$table_name = self::get_table_name();

		$query = "CREATE TABLE " . $table_name . " (
            payment_type_id INT NOT NULL AUTO_INCREMENT,
            id VARCHAR(256) NOT NULL,
            title VARCHAR(256) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (payment_type_id)
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
	 * Remove all entries from payment_types table.
	 * 
	 * @return bool True if entries removed successfully, false otherwise
	 */
	public static function delete_all_entries_in_payment_types_table()
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

	/**
	 * Get payment type by payment_type_id.
	 *
	 * @param int $payment_type_id The payment type ID.
	 * @return Payday_Payment_Types_Entity|null Payment type entity on success, null on failure.
	 */
	public static function get_payment_type_by_id($payment_type_id)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		// Prepare SQL statement
		$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE payment_type_id = %d", $payment_type_id);

		// Execute SQL statement and fetch the result
		$result = $wpdb->get_row($sql, ARRAY_A);

		// If there was an error or no result, return null
		if ($wpdb->last_error || !$result) {
			return null;
		}

		// Convert 'created_at' to DateTime object
		$result['created_at'] = new DateTime($result['created_at']);

		// Create and return a new Payday_Payment_Types_Entity
		return new Payday_Payment_Types_Entity(
			$result['payment_type_id'],
			$result['id'],
			$result['title'],
			$result['created_at']
		);
	}


	/**
	 * Get all payment types.
	 *
	 * @return Payday_Payment_Types_Entity[]|null Array of payment type entities on success, null on failure.
	 */
	public static function get_all_payment_types()
	{
		global $wpdb;

		$table_name = self::get_table_name();

		// Prepare SQL statement
		$sql = "SELECT * FROM $table_name";

		// Execute SQL statement and fetch the results
		$results = $wpdb->get_results($sql, ARRAY_A);

		// If there was an error or no result, return null
		if ($wpdb->last_error || !$results) {
			return null;
		}

		// Array to hold our entities
		$entities = [];

		foreach ($results as $result) {
			// Convert 'created_at' to DateTime object
			$result['created_at'] = new DateTime($result['created_at']);

			// Create a new Payday_Payment_Types_Entity and add it to the array
			$entities[] = new Payday_Payment_Types_Entity(
				$result['payment_type_id'],
				$result['id'],
				$result['title'],
				$result['created_at']
			);
		}

		// Return the array of entities
		return $entities;
	}

	/**
	 * Create a new payment type.
	 *
	 * @param string $id The ID of the payment type.
	 * @param string $title The title of the payment type.
	 * @return Payday_Payment_Types_Entity|null The created payment type entity on success, null on failure.
	 */
	public static function create_payment_type($id, $title)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		// Prepare data for insertion
		$data = [
			'id' => $id,
			'title' => $title
		];

		// Insert the data into the database
		$inserted = $wpdb->insert($table_name, $data);

		// If there was an error, return null
		if (!$inserted) {
			return null;
		}

		// Fetch the newly created payment type
		$created_payment_type = self::get_payment_type_by_id($id);

		// Return the created payment type
		return $created_payment_type;
	}

	/**
	 * Delete payment type by id.
	 *
	 * @param string $id The payment type ID.
	 * @return bool True on successful delete, false on failure.
	 */
	public static function delete_payment_type($id)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		// Prepare SQL statement
		$sql = $wpdb->prepare("DELETE FROM $table_name WHERE id = %s", $id);

		// Execute SQL statement
		$wpdb->query($sql);

		// If there was an error, return false
		if ($wpdb->last_error) {
			return false;
		}

		// If no rows affected, return false
		if ($wpdb->rows_affected == 0) {
			return false;
		}

		return true;
	}
}
