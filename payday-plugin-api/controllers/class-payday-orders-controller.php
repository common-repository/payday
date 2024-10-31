<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Assuming these files are in the same directory as your controller
require_once PAYDAY_DIR_PATH . 'payday-plugin-api/controllers/class-payday-base-controller.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-order-model.php';

/**
 * Class Payday_Orders_Controller
 *
 * Handles API requests for orders.
 */
class Payday_Orders_Controller extends Payday_Base_Controller
{

	public static function register_routes()
	{
		// Register the route for getting orders.
		register_rest_route('payday/v1', '/orders', [
			'methods' => 'GET',
			'callback' => [__CLASS__, 'get_orders'],
			'permission_callback' => [__CLASS__, 'validate_request'],
		]);
	}

	/**
	 * Handle a request to get orders.
	 *
	 * @param WP_REST_Request $request The request to handle.
	 *
	 * @return WP_REST_Response|WP_Error The response, or an error if the API key is invalid.
	 */
	public static function get_orders(WP_REST_Request $request)
	{

		// Fetch orders using the Order model.
		$payday_order_model = new Payday_Order_Model();
		$wc_orders = $payday_order_model->get_orders();

		// Return the orders as a REST response.
		return new WP_REST_Response($wc_orders, 200);
	}
}
