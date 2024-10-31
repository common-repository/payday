<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-plugin-api/class-payday-permissions.php';

/**
 * Class Payday_Base_Controller
 *
 * Base class for API controllers in the Payday plugin.
 */
abstract class Payday_Base_Controller
{

	/**
	 * Ensure that the API key is valid and that WooCommerce is active.
	 *
	 * @param WP_REST_Request $request The request to validate.
	 *
	 * @return bool|WP_Error True if the request is valid, or a WP_Error if not.
	 */
	protected function validate_request(WP_REST_Request $request)
	{
		// Validate the API key.
		if (!Payday_API_Permissions::validate_api_key($request)) {
			return new WP_Error('invalid_api_key', 'Invalid API key', ['status' => 403]);
		}

		// Check if WooCommerce is active.
		if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			return new WP_Error('woocommerce_not_active', 'WooCommerce must be active to use this endpoint', ['status' => 403]);
		}

		// If we've gotten this far, the request is valid.
		return true;
	}


	// In your Payday_Controller_Trait trait:
	abstract public static function register_routes();
}
