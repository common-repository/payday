<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/interface/interface-payday-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-payment-type-response.php';

class Payday_Payment_Type_Factory implements Payday_Factory
{
	/**
	 * Placeholder for a new Payday_Customer_Request.
	 * 
	 * @throws Exception Not implemented.
	 */
	public static function create_data_array_from_request($request)
	{
		throw new Exception('Not implemented');
	}

	/**
	 * Create a Payday_Payment_Type_Response from an array of data.
	 * 
	 * @param array $data
	 * @return Payday_Payment_Type_Response
	 */
	public static function create_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		$response = new Payday_Payment_Type_Response(
			$data['id'],
			$data['title'],
			$data['description'],
			$data['created']
		);

		return $response;
	}
}
