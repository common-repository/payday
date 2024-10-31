<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Payday_Logger
{
	public static $LOG_FILE = PAYDAY_LOGS_DIR_PATH . 'payday-log.txt';
	const DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Log a message.
	 *
	 * @param string $message The message to log.
	 * @param string $level The log level (optional).
	 * @return bool True on success, false on failure.
	 */
	public static function log($message, $level = 'info')
	{
		$level = strtoupper($level);
		$log_entry = '[' . date(self::DATE_FORMAT) . '] [' . $level . '] ' . $message . PHP_EOL;

		// Check if the log folder exists and is writable, if not, attempt to create it, if that fails, log to error_log
		if (!is_dir(PAYDAY_LOGS_DIR_PATH) && !mkdir(PAYDAY_LOGS_DIR_PATH, 0755, true)) {
			return self::log_to_error_log($message, $level);
		}

		// Check if the log file exists and is writable, if not, attempt to create it, if that fails, log to error_log
		if (!is_writable(self::$LOG_FILE) && (!touch(self::$LOG_FILE) || !chmod(self::$LOG_FILE, 0644))) {
			return self::log_to_error_log($message, $level);
		}

		// Attempt to append the log entry to the log file, if that fails, log to error_log
		if (file_put_contents(self::$LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX) === false) {
			return self::log_to_error_log($message, $level);
		}

		return true;
	}

	public static function create_empty_log_file()
	{
		if (!is_dir(PAYDAY_LOGS_DIR_PATH) && !mkdir(PAYDAY_LOGS_DIR_PATH, 0755, true)) {
			return false;
		}

		if (!is_writable(self::$LOG_FILE) && (!touch(self::$LOG_FILE) || !chmod(self::$LOG_FILE, 0644))) {
			return false;
		}

		return true;
	}

	/**
	 * Log a message to the error_log.
	 *
	 * @param string $message The message to log.
	 * @param string $level The log level (optional).
	 * @return bool Always true.
	 */
	public static function log_to_error_log($message, $level = 'info')
	{
		$level = strtoupper($level);
		$log_entry = '[' . date(self::DATE_FORMAT) . '] [' . $level . '] ' . $message . PHP_EOL;
		return error_log('[Payday Logger] ' . $log_entry);
	}
}
