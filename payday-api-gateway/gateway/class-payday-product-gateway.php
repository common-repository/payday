<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-request-manager.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-gateway-error.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-product-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-product-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-product-response.php';

class Payday_Product_Gateway
{

	public static function create_product(Payday_Product_Request $request)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/products";

		$data = Payday_Product_Factory::create_data_array_from_request($request);

		try {
			$response_data = $request_manager->post($url, $data);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$product = Payday_Product_Factory::create_response_from_data($response_data);

		return $product;
	}

	public static function upsert_products(Payday_Products_Request $request)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/products/upsert";

		$data = Payday_Product_Factory::create_data_array_from_products_request($request);

		try {
			$response_data = $request_manager->post($url, $data);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$products = Payday_Product_Factory::create_upsert_response_from_data($response_data);

		return $products;
	}
}
