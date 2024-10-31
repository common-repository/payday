<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/class-payday-gateway-error.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/enums/enum-payday-gateway-error-code.php';

/**
 * Class Payday_Request_Manager
 * Handles API requests.
 */
class Payday_Request_Manager
{
	use Payday_Singleton_Trait;

	/**
	 * @var string|null $api_endpoint API endpoint URL
	 */
	protected $api_endpoint = null;

	/**
	 * @var string|null $auth_token Authorization token
	 */
	protected $auth_token = null;

	/**
	 * @var int $timeout Request timeout in seconds
	 */
	protected $timeout = 5; // System default is 5 seconds

	/**
	 * Marked as private to enforce the singleton pattern.
	 */
	private function __construct()
	{
	}

	/**
	 * Initialize API endpoint and authentication token.
	 * 
	 * @param string $api_endpoint
	 * @param string|null $auth_token
	 * @param int $timeout Request timeout in seconds, default is 30 seconds
	 */
	public function initialize(string $api_endpoint, ?string $auth_token = null, int $timeout = 30)
	{
		$this->api_endpoint = $api_endpoint;
		$this->auth_token = $auth_token;
		$this->timeout = $timeout;
	}

	/**
	 * Sends a GET request to the API.
	 *
	 * @param string $url API endpoint
	 * @param mixed $data Request data
	 * @param bool $require_token Whether an auth token is required
	 *
	 * @return array|null The response data or null on failure
	 */
	public function get(string $url, bool $require_token = true)
	{
		$response = wp_remote_get($this->api_endpoint . $url, array(
			'headers' => $this->get_headers($require_token),
			'timeout' => $this->timeout
		));
		return $this->handle_response($response, $url, null, $require_token, 'GET');
	}

	/**
	 * Sends a POST request to the API.
	 *
	 * @param string $url API endpoint
	 * @param mixed $data Request data
	 * @param bool $require_token Whether an auth token is required
	 *
	 * @return array|null The response data or null on failure
	 */
	public function post(string $url, mixed $data, bool $require_token = true)
	{
		$json_data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		if ($json_data === false) {
			Payday_Logger::log("Payday_Request_Manager:post() - JSON encoding failed: " . json_last_error_msg() . ")", 'error');

			throw new Exception("JSON encoding failed: " . json_last_error_msg());
		}

		$response = wp_remote_post($this->api_endpoint . $url, array(
			'headers' => $this->get_headers($require_token),
			'timeout' => $this->timeout,
			'body' => $json_data
		));
		return $this->handle_response($response, $url, $data, $require_token, 'POST');
	}


	/**
	 * Sends a PUT request to the API.
	 * 
	 * @param string $url API endpoint
	 * @param mixed $data Request data
	 * @param bool $require_token Whether an auth token is required
	 * 
	 * @return array|null The response data or null on failure
	 */
	public function put(string $url, mixed $data, bool $require_token = true)
	{
		$response = wp_remote_request($this->api_endpoint . $url, array(
			'method'    => 'PUT',
			'headers'   => $this->get_headers($require_token),
			'timeout'   => $this->timeout,
			'body'      => json_encode($data)
		));
		return $this->handle_response($response, $url, $data, $require_token, 'PUT');
	}

	/**
	 * Sends a GET request to the API and returns the raw response.
	 *
	 * @param string $url API endpoint
	 * @param bool $require_token Whether an auth token is required
	 *
	 * @return array The raw response data
	 */
	public function get_raw(string $url, bool $require_token = true)
	{
		$response = wp_remote_get($this->api_endpoint . $url, array(
			'headers' => $this->get_headers($require_token),
			'timeout' => $this->timeout
		));

		if (is_wp_error($response)) {
			$error_code = $response->get_error_code();
			$error_message = $this->generate_error_message('GET', $url, null, $require_token, $error_code, $response->get_error_message());
			throw new Payday_Gateway_Error($error_code, $error_message);
		}

		return $response;
	}


	/**
	 * Returns request headers.
	 *
	 * @param bool $require_token Whether an auth token is required
	 *
	 * @return array The request headers
	 */
	private function get_headers(bool $require_token = true)
	{
		return $require_token ? array(
			'Content-Type' => 'application/json',
			'Api-Version' => 'alpha',
			'User-Agent' => 'payday-wordpress-plugin:' . PAYDAY_VERSION,
			'Authorization' => 'Bearer ' . $this->auth_token
		) : array(
			'Content-Type' => 'application/json',
			'Api-Version' => 'alpha',
			'User-Agent' => 'payday-wordpress-plugin:' . PAYDAY_VERSION
		);
	}

	/**
	 * Handles the API response.
	 *
	 * @param mixed $response API response
	 * @param string $url API endpoint
	 * @param mixed $data Request data
	 * @param bool $require_token Whether an auth token is required
	 * @param string $requestType Request type (GET or POST)
	 *
	 * @return array|null The response data or null on failure
	 *
	 * @throws Payday_Gateway_Error on a response error
	 */
	private function handle_response(mixed $response, string $url, mixed $data, bool $require_token, string $requestType)
	{
		// If the response is a WordPress error, handle it
		if (is_wp_error($response)) {
			// Retrieve error code and message
			$error_code = $response->get_error_code();
			$error_message = $response->get_error_message();

			// Check for cURL error 7 specifically (cannot connect to host)
			if ($error_code === 'http_request_failed' && strpos($error_message, 'cURL error 7:') !== false) {
				// set error_code to a http error code instead of http_request_failed
				$error_code = 503; // Service Unavailable
				$error_message = 'Could not connect to the server at ' . $this->api_endpoint . '. Please check your network connection and the server status.';
			}

			// Generate a full error message
			$error_message = $this->generate_error_message($requestType, $url, $data, $require_token, $error_code, $error_message);

			// Log the error
			Payday_Logger::log($error_message, 'error');

			// Throw an exception to be handled by the calling code
			throw new Payday_Gateway_Error($error_code, $error_message);
		}

		// Retrieve the HTTP status code and message from the response
		$response_code = wp_remote_retrieve_response_code($response);
		$response_error_message = wp_remote_retrieve_response_message($response);

		// If the HTTP status code is within the range of success codes (200-299), handle the successful response
		if ($response_code >= 200 && $response_code < 300) {
			// Log the successful request
			Payday_Logger::log('Request successful: ' . $requestType . ' ' . $url) . '. Response data: ' . json_encode($response);
			// Retrieve the body of the response
			$response_body = wp_remote_retrieve_body($response);

			// If there's no response body, return null
			if (!$response_body) {
				return null;
			}

			// Decode the JSON response
			$response_data = json_decode($response_body, true);

			// If there's no response body return null
			if (!$response_body) {
				return null;
			}

			// Decode the JSON response
			$response_data = json_decode($response_body, true);

			// If the response body cannot be decoded as JSON, log a message and return null
			if ($response_data === null && json_last_error() !== JSON_ERROR_NONE) {
				Payday_Logger::log('Could not decode JSON response for: ' . $requestType . ' ' . $url . ' Error: ' . json_last_error_msg(), 'error');
				return null;
			}

			// If everything was successful, return the decoded response data
			return $response_data;
		} else {
			// If the HTTP status code was not a success code, handle the error

			// Use the HTTP status code as the error code
			$error_code = $response_code;

			// Generate a full error message
			$error_message = $this->generate_error_message($requestType, $url, $data, $require_token, $error_code, $response_error_message);

			// Log the error
			Payday_Logger::log('HTTP error: ' . $error_code . ' ' . $response_error_message, 'error');

			// Throw an exception to be handled by the calling code
			throw new Payday_Gateway_Error($error_code, $error_message);
		}
	}

	/**
	 * Generates an error message.
	 *
	 * @param string $requestType Request type (GET or POST)
	 * @param string $url API endpoint
	 * @param mixed $data Request data
	 * @param bool $require_token Whether an auth token is required
	 * @param int|string|null $errorCode Error code
	 * @param string $errorMessage Error message
	 *
	 * @return string The error message
	 */
	private function generate_error_message(string $requestType, string $url, mixed $data, bool $require_token, $errorCode, string $errorMessage)
	{
		$message = $requestType . ' request to Payday External API failed.';
		$message .= ' Error code: ' . $errorCode;
		$message .= ' Error message: ' . $errorMessage;
		$message .= " | URL: $url";
		$message .= " | Was the data object set: " . (isset($data) && $data !== null && $data !== [] ? "True" : "False");
		$message .= " | Was the auth token set: " . ($require_token ? "True" : "False");

		return $message;
	}
}
