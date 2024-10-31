<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-request-manager.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-gateway-error.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-customer-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-customer-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-customer-response.php';

class Payday_Customer_Gateway
{
	/**
	 * Create a new customer.
	 * 
	 * @param Payday_Customer_Request $customer_request
	 * 
	 * @return Payday_Customer_Response
	 */
	public static function create_customer(Payday_Customer_Request $customer_request)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = '/customers';

		$payload = Payday_Customer_Factory::create_data_array_from_request($customer_request);

		try {
			$response_data = $request_manager->post($url, $payload);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$customer_response = Payday_Customer_Factory::create_response_from_data($response_data);

		return $customer_response;
	}


	public static function update_customer(Payday_Customer_Request $customer_request)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = '/customers/' . $customer_request->id;

		$payload = Payday_Customer_Factory::create_data_array_from_request($customer_request);

		try {
			$response_data = $request_manager->put($url, $payload);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$customer_response = Payday_Customer_Factory::create_response_from_data($response_data);

		return $customer_response;
	}

	/**
	 * Get a customer by ID.
	 * 
	 * @param string $customer_id
	 * 
	 * @return Payday_Customer_Response
	 */
	public function get_customer_by_id(string $customer_id)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/customers/{$customer_id}";

		try {
			$response_data = $request_manager->get($url);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$customer_response = Payday_Customer_Factory::create_response_from_data($response_data);

		return $customer_response;
	}

	/**
	 * Get all customers.
	 *
	 * @param int $perpage
	 * @param int $page
	 * 
	 * @return array of Payday_Customer_Response
	 */
	public static function get_all_customers(int $perpage = 100, int $page = 1)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();

		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/customers?perpage={$perpage}&page={$page}";

		try {
			$response_data = $request_manager->get($url);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		// Assuming the customer list is under 'customers' key
		$customers = [];
		if (isset($response_data['customers'])) {
			foreach ($response_data['customers'] as $customer_data) {
				$customers[] = Payday_Customer_Factory::create_response_from_data($customer_data);
			}
		}

		return $customers;
	}

	/**
	 * Get customer by SSN (number).
	 * 
	 * @param string $number
	 * 
	 * @return Payday_Customer_Response
	 */
	public static function get_customer_by_number(string $number)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/customers/number/{$number}";


		try {
			$response_data = $request_manager->get($url);
		} catch (Payday_Gateway_Error $e) {
			// If the customer is not found, return null.
			if ($e->getErrorCode() === 404) {
				return null;
			}

			// Otherwise, throw the error.
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$customer_response = Payday_Customer_Factory::create_response_from_data($response_data);

		return $customer_response;
	}


	/**
	 * Get a customer by query.
	 *
	 * @param string $query
	 *
	 * @return Payday_Customer_Response[]
	 */
	public function get_customers_by_query(string $query)
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/customers/search/?query={$query}";

		try {
			$response_data = $request_manager->get($url);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		$customers = Payday_Customer_Factory::create_responses_from_data($response_data);

		return $customers;
	}

	/**
	 * Get all invoice for a customer
	 *
	 * @param string $customer_id
	 * @param int $perpage
	 * @param int $page
	 * @param string $include
	 *
	 * @return array of Payday_Invoice_Response
	 */
	public function get_customer_invoices(string $customer_id, int $perpage = 10, int $page = 1, string $include = 'lines')
	{
		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$settings_model = new Payday_Settings_Model();
		$api_endpoint = $settings_model->get_api_endpoint();
		$auth_model = new Payday_Auth_Model();
		$auth_token = $auth_model->get_auth_token();
		$request_manager->initialize($api_endpoint, $auth_token);

		$url = "/customers/{$customer_id}/invoice?perpage={$perpage}&page={$page}&include={$include}";

		try {
			$response_data = $request_manager->get($url);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}

		// Assuming the invoice list is under 'invoices' key
		$invoices = [];
		if (isset($response_data['invoices'])) {
			foreach ($response_data['invoices'] as $invoice_data) {
				// Append the invoice to the invoices array
				$invoices[] = Payday_Invoice_Factory::create_response_from_data($invoice_data);
			}
		}

		return $invoices;
	}
}
