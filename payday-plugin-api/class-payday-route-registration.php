<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-plugin-api/controllers/class-payday-products-controller.php';
require_once PAYDAY_DIR_PATH . 'payday-plugin-api/controllers/class-payday-orders-controller.php';

class Payday_Route_Registration
{
	public static function register_all_routes()
	{
		// Register the routes for the products controller.
		Payday_Products_Controller::register_routes();

		// Register the routes for the orders controller.
		Payday_Orders_Controller::register_routes();
	}
}
