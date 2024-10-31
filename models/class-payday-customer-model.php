<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/gateway/class-payday-customer-gateway.php';
require_once PAYDAY_DIR_PATH . 'utils/class-payday-utils.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-claim-service-payment-gateway.php';

class Payday_Customer_Model
{

	/**
	 * Create or retrieve a customer in Payday from a WC_Order
	 * 
	 * @param WC_Order $order
	 * 
	 * @return Payday_Customer_Response The response from Payday if the customer was created successfully, null otherwise
	 * @throws Exception If the order is not valid
	 */
	public function create_or_get_payday_customer_from_wc_order($order)
	{
		if (!$this->is_valid_order($order)) {
			throw new Exception('Cannot create or get Payday customer, the wc_order provided is not valid');
		}

		$billing_data = $this->get_billing_data($order);
		$billing_ssn = $this->get_ssn($order);

		$order_data = $order->get_data();
		$order_billing_data = $order_data['billing'];

		// Check customer by SSN
		$billing_ssn = $this->get_ssn($order);
		if (isset($billing_ssn) && !Payday_Utils::is_null_or_empty_string($billing_ssn)) {
			$existing_customer = $this->get_customer_by_ssn($billing_ssn);
			if ($existing_customer != null) {
				return $existing_customer;
			}
			$customer = $this->create_customer_array($billing_data, $billing_ssn);
			$created_customer = $this->create_customer($customer);
			if ($created_customer != null) {
				return $created_customer;
			}
		}

		// Check customer by Email
		$order_billing_email = $order->get_billing_email();
		if (!Payday_Utils::is_null_or_empty_string($order_billing_email)) {
			$existing_customer = $this->get_customer_by_email($order_billing_email);
			if ($existing_customer != null) {
				return $existing_customer;
			}
		}

		// OpenPos order data
		$op_order = $order->get_meta('_op_order');

		// Check OpenPos customer
		if (!isset($payday_customer) && $op_order != null) {
			// Check if customer was added to OpenPos order
			if (isset($op_order['customer'])) {
				$op_order_customer_email = $op_order['customer']['email'];

				// Check customer by Email, ignore emails that end with @open-pos.com, generated my OpenPos
				if (!Payday_Utils::is_null_or_empty_string($op_order_customer_email) && !Payday_Utils::endsWith($op_order_customer_email, '@open-pos.com')) {
					$existing_customer = $this->get_customer_by_email($op_order_customer_email);
					if ($existing_customer != null) {
						return $existing_customer;
					}
				}

				if (!isset($payday_customer) && isset($op_order['customer']['name']) && !Payday_Utils::is_null_or_empty_string($op_order['customer']['name'])) {
					// Create a new customer with given OpenPos order customer data
					$op_order_customer_billing_address = $op_order['customer']['shipping_address'][0];
					$customer = $this->create_customer_array($op_order_customer_billing_address);
					$customer['email'] = $op_order_customer_email;
					// Send Customer to Payday
					$created_customer = $this->create_customer($customer);
					if ($created_customer != null) {
						return $created_customer;
					}
				}
			}

			// Check for OpenPos placeholder customer 
			$existing_customer = $this->get_customer_by_email("openpos@example.com");
			if ($existing_customer != null) {
				return $existing_customer;
			}

			// Create OpenPos placeholder customer in Payday
			$customer = array(
				'address' => '',
				'city' => '',
				'comment' => __('OpenPos customer created by Payday WooCommerce plugin. Please do not edit this customer', 'payday'),
				'contact' => '',
				'country' => '',
				'email' => 'openpos@example.com',
				'language' => (get_locale() == 'is_IS' ? 'is' : ''),
				'name' => "OpenPos",
				'phone' => '',
				'ssn' => null,
				'zipCode' => '',
			);
			$created_op_customer = $this->create_customer($customer);
			if ($created_op_customer != null) {
				return $created_op_customer;
			}
		}

		// create a new customer with given order billing data
		$customer = $this->create_customer_array($order_billing_data, !Payday_Utils::is_null_or_empty_string($billing_ssn)  ? $billing_ssn : null);
		// Send Customer to Payday
		$created_customer = $this->create_customer($customer);
		return $created_customer;
	}

	public function get_customer_by_ssn(string $ssn)
	{
		$customer_gateway = new Payday_Customer_Gateway();
		try {
			$customer = $customer_gateway->get_customer_by_number($ssn);
		} catch (Exception $e) {

			$customer = null;
		}
		return $customer;
	}

	public function get_customer_by_email(string $email)
	{
		$customer_gateway = new Payday_Customer_Gateway();

		// TODO: urlencode email
		$email = urlencode($email);

		$customers = $customer_gateway->get_customers_by_query($email);

		if (count($customers) > 0)
			return $customers[0];
		else
			return null;
	}

	public function create_customer(array $data = [])
	{
		$customer_gateway = new Payday_Customer_Gateway();
		$request = new Payday_Customer_Request($data);
		$response = $customer_gateway->create_customer($request);
		return $response;
	}

	public function update_customer(array $data = [])
	{
		$customer_gateway = new Payday_Customer_Gateway();
		$request = new Payday_Customer_Request($data);
		$response = $customer_gateway->update_customer($request);
		return $response;
	}

	public function is_valid_order($order)
	{
		return isset($order) && is_a($order, 'WC_Order');
	}

	public function get_billing_data($order)
	{
		$order_data = $order->get_data();
		return $order_data['billing'];
	}

	public function create_customer_array($data, $ssn = null)
	{
		return array(
			'address' => $this->get_address($data),
			'city' => sanitize_text_field($data['city']),
			'comment' => '',
			'contact' => $this->get_contact($data),
			'country' => $this->get_country($data),
			'email' => sanitize_text_field($data['email']),
			'language' => $this->get_language(),
			'name' => $this->get_name($data),
			'phone' => sanitize_text_field($data['phone']),
			'ssn' => $ssn,
			'zipCode' => trim($data['postcode']),
		);
	}

	public function get_address($billing_data)
	{
		// Check if $billing_data contains an address (OpenPos order data)
		if (!Payday_Utils::is_null_or_empty_string($billing_data['address'])) {
			return sanitize_text_field($billing_data['address']) .
				(Payday_Utils::is_null_or_empty_string($billing_data['address_2']) ? '' : ', ') . sanitize_text_field($billing_data['address_2']);
		}

		return sanitize_text_field($billing_data['address_1']) .
			(Payday_Utils::is_null_or_empty_string($billing_data['address_2']) ? '' : ', ') . sanitize_text_field($billing_data['address_2']);
	}

	public function get_contact($billing_data)
	{
		if (!Payday_Utils::is_null_or_empty_string($billing_data['company'])) {
			return "";
		}

		if (!Payday_Utils::is_null_or_empty_string($billing_data['name'])) {
			$name = sanitize_text_field($billing_data['name']);
			return trim($name);
		}

		$name = sanitize_text_field($billing_data['first_name']) . ' ' . sanitize_text_field($billing_data['last_name']);
		return trim($name);
	}

	public function get_country($billing_data)
	{
		return ($billing_data['country'] === 'IS' ? 'Ísland' : sanitize_text_field($billing_data['country']));
	}

	public function get_language()
	{
		return get_locale() == 'is_IS' ? 'is' : '';
	}

	public function get_name($billing_data)
	{
		$name = "";
		// Check if $billing_data contains a company name
		if (!Payday_Utils::is_null_or_empty_string($billing_data['company'])) {
			$name = sanitize_text_field($billing_data['company']);
		}
		// Check if $billing_data contains a name (OpenPos order data)
		else if (!Payday_Utils::is_null_or_empty_string($billing_data['name'])) {
			$name = sanitize_text_field($billing_data['name']);
		} else {
			$name = sanitize_text_field($billing_data['first_name']) . ' ' . sanitize_text_field($billing_data['last_name']);
		}

		return trim($name);
	}

	/**
	 * Get the SSN of the customer from the order
	 * 
	 * @param WC_Order $order
	 * @return string The SSN of the customer
	 */
	public function get_ssn($order)
	{
		return $order->get_meta(Payday_Claim_Service_Payment_Gateway::instance()->get_field_id());
	}
}
