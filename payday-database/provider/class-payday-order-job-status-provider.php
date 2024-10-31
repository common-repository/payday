<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-base-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/utils/class-payday-database-utils.php';
require_once PAYDAY_DIR_PATH . 'payday-database/entities/entity-payday-order-job-status.php';

class Payday_Order_Job_Status_Provider extends Payday_Base_Provider
{
	public const TABLE_NAME = 'order_job_status';

	/**
	 * Create order_job_status table.
	 * 
	 * @param string $prefix The prefix for table names
	 * @return bool True if table created successfully, false otherwise
	 */
	public static function create_order_job_status_table()
	{
		global $wpdb;

		$table_name = self::get_table_name();

		// Check if table already exists
		if (Payday_Database_Utils::table_exists($table_name)) {
			return true;
		}

		$query = "CREATE TABLE " . $table_name . " (
            order_job_status_id INT NOT NULL AUTO_INCREMENT,
            woocommerce_order_id VARCHAR(36) NOT NULL,  
            payday_invoice_number VARCHAR(36) NULL,
            job_status VARCHAR(36) NOT NULL, 
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (order_job_status_id)
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
	 * Insert a new order_job_status entry into the database.
	 *
	 * @param string $woocommerce_order_id The order id
	 * @param string $payday_invoice_number The invoice number
	 * @param string $job_status The status of the job
	 * @return Payday_Order_Job_Status_Entity The order_job_status entry created
	 * @throws Exception If insertion into the database fails
	 */
	public static function create_order_job_status($woocommerce_order_id, $payday_invoice_number, $job_status)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		$created_at_timestamp = current_time('mysql');

		$data = array(
			'woocommerce_order_id' => $woocommerce_order_id,
			'payday_invoice_number' => $payday_invoice_number,
			'job_status' => $job_status,
			'created_at' => $created_at_timestamp
		);

		$format = array('%s', '%s', '%s', '%s');

		$result = $wpdb->insert($table_name, $data, $format);

		if ($result === false) {
			if ($wpdb->last_error === '') {
				throw new Exception("Error: Failed to insert data into the database.");
			}
			throw new Exception("Error: Failed to insert data into the database. Details: " . $wpdb->last_error);
		}

		// Get the ID of the entry that was just inserted
		$order_job_status_id = $wpdb->insert_id;

		// Create a new Payday_Order_Job_Status_Entity object with the data that was inserted
		$order_job_status = new Payday_Order_Job_Status_Entity(
			$order_job_status_id,
			$woocommerce_order_id,
			$payday_invoice_number,
			$job_status,
			new DateTime($created_at_timestamp)
		);

		return $order_job_status;
	}

	/**
	 * Retrieve the order_job_status entry from the database for the given order.
	 * 
	 * @param string $woocommerce_order_id The order id
	 * @return Payday_Order_Job_Status_Entity|null The order_job_status entry, or null if not found
	 */
	public static function get_order_job_status($woocommerce_order_id)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		$query = "SELECT * FROM " . $table_name . " WHERE woocommerce_order_id = %s";

		$prepared_query = $wpdb->prepare($query, $woocommerce_order_id);

		$result = $wpdb->get_row($prepared_query);

		if ($result === null) {
			return null;
		}

		$order_job_status = new Payday_Order_Job_Status_Entity(
			$result->order_job_status_id,
			$result->woocommerce_order_id,
			$result->payday_invoice_number,
			$result->job_status,
			new DateTime($result->created_at)
		);

		return $order_job_status;
	}

	/**
	 * Update an existing order_job_status entry in the database and return the updated entry.
	 *
	 * @param string $woocommerce_order_id The order id
	 * @param string $payday_invoice_number The updated invoice number
	 * @param string $job_status The updated job status
	 * @return Payday_Order_Job_Status_Entity|false The updated order_job_status entry, or false on error
	 */
	public static function update_order_job_status($woocommerce_order_id, $payday_invoice_number, $job_status)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		$data = array(
			'payday_invoice_number' => $payday_invoice_number,
			'job_status' => $job_status,
		);

		$where = array('woocommerce_order_id' => $woocommerce_order_id);
		$format = array('%s', '%s'); // All are strings.
		$where_format = array('%s'); // The woocommerce_order_id is a string.

		try {
			$result = $wpdb->update($table_name, $data, $where, $format, $where_format);

			if ($result === false) {
				throw new Exception("Error: Failed to update data in the database. Details: " . $wpdb->last_error);
			}

			// Retrieve the updated entry
			$updated_entry = self::get_order_job_status($woocommerce_order_id);

			if ($updated_entry === null) {
				if ($wpdb->last_error === '') {
					throw new Exception("Error: Failed to retrieve updated data from the database.");
				}
				throw new Exception("Error: Failed to retrieve updated data from the database. Details: " . $wpdb->last_error);
			}

			return $updated_entry;
		} catch (Exception $e) {
			Payday_logger::log($e->getMessage(), 'error');
			return false;
		}
	}

	/**
	 * Delete an order_job_status entry in the database.
	 *
	 * @param string $woocommerce_order_id The order id
	 * @return bool True on successful deletion, false on failure
	 * @throws Exception If deleting the entry fails
	 */
	public static function delete_order_job_status($woocommerce_order_id)
	{
		global $wpdb;

		$table_name = self::get_table_name();

		$where = array('woocommerce_order_id' => $woocommerce_order_id);
		$where_format = array('%s'); // The woocommerce_order_id is a string.

		$result = $wpdb->delete($table_name, $where, $where_format);

		if ($result === false) {
			if ($wpdb->last_error === '') {
				throw new Exception("Error: Failed to delete data from the database.");
			}
			throw new Exception("Error: Failed to delete data from the database. Details: " . $wpdb->last_error);
		}

		return true;
	}
}
