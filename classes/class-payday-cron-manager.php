<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Include the Payday_Base class
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';

class Payday_Cron_Manager
{
	// Make this class a singleton
	use Payday_Singleton_Trait;

	/**
	 * The constructor is private to prevent initiation with outer code.
	 */
	private function __construct()
	{
	}

	const MY_CRON_OPTION = PAYDAY_NAME . '_cron_jobs';

	/**
	 * Adds a cron job to the WordPress cron system.
	 *
	 * @param string $schedule The schedule for the cron job.
	 * @param object $component A reference to the instance of the object on which the action is defined.
	 * @param string $callback The name of the function definition on the $component.
	 * 
	 * @return void
	 * @throws InvalidArgumentException If the parameters are invalid.
	 * @throws Exception If an error occurs.
	 */
	public static function add_cron_job(string $schedule, object $component, string $callback): void
	{
		// Validate and sanitize input
		if (empty($schedule) || !is_string($schedule)) {
			throw new InvalidArgumentException('Invalid schedule.');
		}

		$schedule = sanitize_text_field($schedule);

		if (!self::is_valid_schedule($schedule)) {
			throw new InvalidArgumentException('Invalid schedule.');
		}

		// Check if the component and callback are valid
		if (!is_object($component) || !is_callable([$component, $callback])) {
			throw new InvalidArgumentException('Invalid component or callback.');
		}

		// Store the scheduled job information
		$scheduled_jobs = get_option(self::MY_CRON_OPTION, array());

		// Check if the job already exists in the array
		$job_identifier = self::get_job_identifier($component, $callback);
		if (isset($scheduled_jobs[$job_identifier])) {
			return; // Job already exists, no need to add it again
		}

		// Code to add a cron job
		$success = wp_schedule_event(time(), $schedule, array($component, $callback));
		if (!$success) {
			self::log('Failed to add cron job: ' . $job_identifier);
			throw new Exception('Failed to add cron job.');
		}

		// Append the new job to the array
		$scheduled_jobs[$job_identifier] = array(
			'component' => $component,
			'callback' => $callback,
		);

		// Store the updated array
		update_option(self::MY_CRON_OPTION, $scheduled_jobs);
	}

	/**
	 * Removes a cron job from the WordPress cron system.
	 *
	 * @param object $component A reference to the instance of the object on which the action is defined.
	 * @param string $callback The name of the function definition on the $component.
	 * 
	 * @return void
	 */
	public static function remove_cron_job(object $component, string $callback): void
	{
		// Code to remove a cron job
		$job_identifier = self::get_job_identifier($component, $callback);
		$scheduled_jobs = get_option(self::MY_CRON_OPTION, array());

		if (isset($scheduled_jobs[$job_identifier])) {
			unset($scheduled_jobs[$job_identifier]);
			wp_clear_scheduled_hook(array($component, $callback));
			update_option(self::MY_CRON_OPTION, $scheduled_jobs);
		}
	}

	/**
	 * Removes all cron jobs from the WordPress cron system.
	 *
	 * @return void
	 */
	public static function remove_all_cron_jobs(): void
	{
		// Retrieve the stored scheduled jobs
		$scheduled_jobs = get_option(self::MY_CRON_OPTION, array());

		// Return early if the option is not found
		if (!$scheduled_jobs) {
			return;
		}

		// Loop through the scheduled jobs and remove them
		foreach ($scheduled_jobs as $job_identifier => $job) {
			wp_clear_scheduled_hook(array($job['component'], $job['callback']));
		}

		// Remove the stored scheduled jobs
		delete_option(self::MY_CRON_OPTION);
	}

	/**
	 * Check if a given schedule is valid.
	 *
	 * @param string $schedule The cron schedule to check.
	 * 
	 * @return bool Whether the schedule is valid or not.
	 */
	private static function is_valid_schedule(string $schedule): bool
	{
		$schedules = wp_get_schedules();
		return isset($schedules[$schedule]);
	}

	/**
	 * Generate a unique identifier for a cron job.
	 *
	 * @param object $component A reference to the instance of the object on which the action is defined.
	 * @param string $callback The name of the function definition on the $component.
	 * 
	 * @return string The unique identifier for the cron job.
	 */
	private static function get_job_identifier(object $component, string $callback): string
	{
		$class_name = get_class($component);
		return $class_name . '::' . $callback;
	}

	/**
	 * Logs a message using WordPress logging functionality.
	 *
	 * @param string $message The message to log.
	 *
	 * @return void
	 */
	private static function log(string $message): void
	{
		if (!function_exists('error_log')) {
			return;
		}

		$log_message = 'Payday Cron Manager: ' . $message;
		error_log($log_message);
	}
}
