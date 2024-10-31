<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_API_Permissions
 *
 * Handles API permissions for the Payday plugin.
 */
class Payday_API_Permissions
{
	/**
	 * Generates a unique API key for a user, hashes it, and stores it in the user's meta data.
	 *
	 * @param int $user_id The user's ID.
	 *
	 * @return string The generated API key.
	 */
	public static function generate_api_key(int $user_id): string
	{
		// Generate a unique API key.
		$api_key = wp_generate_password(24, false);
		// Hash the API key.
		$hashed_api_key = wp_hash_password($api_key);
		// Save the hashed API key in the user's meta data.
		update_user_meta($user_id, 'payday_api_key', $hashed_api_key);
		return $api_key;
	}

	/**
	 * Validates an API key from a request.
	 *
	 * @param WP_REST_Request $request The request to validate.
	 *
	 * @return bool Whether the API key is valid.
	 */
	public static function validate_api_key(WP_REST_Request $request): bool
	{
		// Get the API key from the request headers.
		$api_key = $request->get_header('api_key');
		// Get the user's ID.
		$user_id = get_current_user_id();
		// Get the hashed API key from the user's meta data.
		$hashed_api_key = get_user_meta($user_id, 'payday_api_key', true);

		// Check if the API keys match.
		return wp_check_password($api_key, $hashed_api_key);
	}
}
