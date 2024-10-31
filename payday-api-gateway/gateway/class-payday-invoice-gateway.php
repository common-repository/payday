<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-request-manager.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-gateway-error.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-customer-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-invoice-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-invoice-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-invoice-response.php';

class Payday_Invoice_Gateway
{
	/**
	 * Create a new invoice.
	 * 
	 * @param Payday_Invoice_Request $invoice_request
	 * 
	 * @return Payday_Invoice_Response|Payday_Gateway_Error
	 */
	public static function create_invoice(Payday_Invoice_Request $invoice_request)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = '/invoices';

		$payload = Payday_Invoice_Factory::create_data_array_from_request($invoice_request);

		try {
			$response_data = $request_manager->post($url, $payload);
			$invoice_response = Payday_Invoice_Factory::create_response_from_data($response_data);
			return $invoice_response;
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}
	}


	/**
	 * Get an invoice by ID.
	 * 
	 * @param string $invoice_id
	 * @param bool $include
	 * 
	 * @return Payday_Invoice_Response|Payday_Gateway_Error
	 */
	public static function get_invoice(string $invoice_id, bool $include_invoice_lines = true)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = $include_invoice_lines
			? "/invoices/{$invoice_id}?include=lines"
			: "/invoices/{$invoice_id}";

		try {
			$response_data = $request_manager->get($url);
			$invoice_response = Payday_Invoice_Factory::create_response_from_data($response_data);
			return $invoice_response;
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}
	}

	/**
	 * Download invoice PDF by ID.
	 * 
	 * @param string $invoice_id
	 * 
	 * @return string
	 */
	public static function download_invoice_pdf(string $invoice_id)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/invoices/{$invoice_id}/pdf";

		// Assuming this endpoint returns the PDF content as a string.
		try {
			$response = $request_manager->get_raw($url);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$pdf_content = $response['body'];

		return $pdf_content;
	}

	/**
	 * Fetch invoice attachments by ID.
	 * 
	 * @param string $invoice_id
	 * 
	 * @return string|Payday_Gateway_Error
	 */
	public static function get_invoice_attachments(string $invoice_id)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/invoices/{$invoice_id}/attachment";
		// Assuming this endpoint returns the ZIP content as a string.
		$zip_content = $request_manager->get($url);

		return $zip_content;
	}

	// /**
	//  * Get invoice history by ID.
	//  * 
	//  * @param string $invoice_id
	//  * 
	//  * @return array
	//  */
	// public static function get_invoice_history(string $invoice_id)
	// {
	// 	$url = "/invoices/{$invoice_id}/history";

	// 	$request_manager = new Payday_Request_Manager();
	// 	$request_manager->initialize();

	// 	$response_data = $request_manager->get($url);

	// 	// Assuming the invoice history is an array of events.
	// 	$history = [];
	// 	if (isset($response_data['events'])) {
	// 		foreach ($response_data['events'] as $event_data) {
	// 			$history[] = Payday_Event_Factory::create_response_from_data($event_data);
	// 		}
	// 	}

	// 	return $history;
	// }
}
