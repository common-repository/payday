<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-request-manager.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-gateway-error.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-payment-type-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/gateway/class-payday-payment-type-gateway.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-payment-type-response.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-auth-model.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-settings-model.php';

class Payday_Payment_Type_Gateway
{
		/**
	 * Get an invoice by ID.
	 * 
	 * @param string $invoice_id
	 * @param bool $include
	 * 
	 * @return Payday_Payment_Type_Response[]|Payday_Gateway_Error
	 */
	public static function get_sales_payment_types()
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);
		
		$url = "/sales/paymenttypes";
		
		try{
			$response_data = $request_manager->get($url);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$sales_payment_types = array_map(function($payment_type_data) {
			return Payday_Payment_Type_Factory::create_response_from_data($payment_type_data);
		}, $response_data);
		
		return $sales_payment_types;
	}
}
