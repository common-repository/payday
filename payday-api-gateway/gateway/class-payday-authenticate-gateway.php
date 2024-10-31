<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-request-manager.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-gateway-error.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/enums/enum-payday-gateway-error-code.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-token-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-token-response.php';

class Payday_Authenticate_Gateway
{
	/**
	 * Create a Bearer token.
	 * 
	 * @param string $clientId
	 * @param string $clientSecret
	 * 
	 * @return Payday_Token_Response
	 * @throws Payday_Gateway_Error
	 */
	public static function create_token(string $clientId, string $clientSecret, string $api_endpoint = null)
	{
		if (empty($clientId)) {
			$errorCode = Payday_Gateway_Error_Code::ARGUMENT_EMPTY;
			throw new Payday_Gateway_Error($errorCode, 'Unable to create auth token. The client ID is not set.');
		}

		if (empty($clientSecret)) {
			throw new Payday_Gateway_Error(Payday_Gateway_Error_Code::ARGUMENT_EMPTY, 'Unable to create auth token. The client secret is not set.');
		}

		if (empty($api_endpoint)) {
			throw new Payday_Gateway_Error(Payday_Gateway_Error_Code::ARGUMENT_EMPTY, 'Unable to create auth token. The API endpoint is not set.');
		}

		// Initialize the request manager.
		$request_manager = Payday_Request_Manager::instance();
		$request_manager->initialize($api_endpoint);

		$url = '/auth/token';

		$payload = array(
			"clientId" => $clientId,
			"clientSecret" => $clientSecret
		);

		try {
			$response_data = $request_manager->post($url, $payload, false);
			$token_response = Payday_Token_Factory::create_response_from_data($response_data);
			return $token_response;
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
			throw $e;
		}
	}
}
