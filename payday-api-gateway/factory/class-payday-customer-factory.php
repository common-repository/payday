<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/interface/interface-payday-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-customer-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-customer-response.php';

class Payday_Customer_Factory implements Payday_Factory
{
	/**
	 * Create an array of data from a Payday_Customer_Request.
	 * 
	 * @param Payday_Customer_Request $request
	 * @return array
	 */
	public static function create_data_array_from_request($request)
	{
		$data = [];

		if (!$request) {
			$data;
		}

		if ($request->id !== null) {
			$data['id'] = $request->id;
		}
		if ($request->language !== null) {
			$data['language'] = $request->language;
		}
		if ($request->ssn !== null) {
			$data['ssn'] = $request->ssn;
		}
		if ($request->foreign_ssn !== null) {
			$data['foreignSsn'] = $request->foreign_ssn;
		}
		if ($request->name !== null) {
			$data['name'] = $request->name;
		}
		if ($request->address !== null) {
			$data['address'] = $request->address;
		}
		if ($request->zip_code !== null) {
			$data['zipCode'] = $request->zip_code;
		}
		if ($request->city !== null) {
			$data['city'] = $request->city;
		}
		if ($request->country !== null) {
			$data['country'] = $request->country;
		}
		if ($request->email !== null) {
			$data['email'] = $request->email;
		}
		if ($request->contact !== null) {
			$data['contact'] = $request->contact;
		}
		if ($request->comment !== null) {
			$data['comment'] = $request->comment;
		}
		if ($request->invoice_notes !== null) {
			$data['invoiceNotes'] = $request->invoice_notes;
		}
		if ($request->additional_header_info !== null) {
			$data['additionalHeaderInfo'] = $request->additional_header_info;
		}
		if ($request->invoice_line_discount !== null) {
			$data['invoiceLineDiscount'] = $request->invoice_line_discount;
		}
		if ($request->phone !== null) {
			$data['phone'] = $request->phone;
		}
		if ($request->send_electronic_invoices !== null) {
			$data['sendElectronicInvoices'] = $request->send_electronic_invoices;
		}
		if ($request->due_date_default_days_after !== null) {
			$data['dueDateDefaultDaysAfter'] = $request->due_date_default_days_after;
		}
		if ($request->final_due_date_default_days_after !== null) {
			$data['finalDueDateDefaultDaysAfter'] = $request->final_due_date_default_days_after;
		}
		if ($request->refetch !== null) {
			$data['refetch'] = $request->refetch;
		}

		return $data;
	}

	/**
	 * Create a Payday_Customer_Response from an array of data.
	 * 
	 * @param array $data
	 * @return Payday_Customer_Response
	 */
	public static function create_response_from_data($data)
	{
		if (!$data) {
			return null;
		}
		$response = new Payday_Customer_Response($data);
		return $response;
	}

	/**
	 * Create a Payday_Customer_Response from an array of data.
	 * 
	 * @param array $data
	 * @return Payday_Customer_Response[]
	 */
	public static function create_responses_from_data(array $data = [])
	{
		if (!$data) {
			return null;
		}

		$responses = [];

		if (array_key_exists('customers', $data)) {
			$customers = $data['customers'];

			// map the customers to responses
			foreach ($customers as $customer) {
				$response = self::create_response_from_data($customer);
				$responses[] = $response;
			}
		}

		return $responses;
	}
}
