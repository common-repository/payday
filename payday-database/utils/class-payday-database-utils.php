<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Payday_Database_Utils
{
	/**
	 * Check if table exists.
	 * 
	 * @param string $table_name The name of the table
	 * @return bool True if table exists, false otherwise
	 */
	public static function table_exists($table_name)
	{
		global $wpdb;

		return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
	}
}
