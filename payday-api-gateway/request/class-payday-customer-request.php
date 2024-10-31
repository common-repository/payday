<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Customer_Response
 *
 * Represents the request object of a customer to be submitted to the Payday External API.
 */
class Payday_Customer_Request
{
	/** @var string|null $id The customer ID. */
	public $id;

	/** @var string|null $language The customer's language. */
	public $language;

	/** @var string|null $ssn The customer's social security number. */
	public $ssn;

	/** @var string|null $foreign_ssn The customer's foreign social security number. */
	public $foreign_ssn;

	/** @var string|null $name The customer's name. */
	public $name;

	/** @var string|null $address The customer's address. */
	public $address;

	/** @var string|null $zip_code The customer's zip code. */
	public $zip_code;

	/** @var string|null $city The customer's city. */
	public $city;

	/** @var string|null $country The customer's country. */
	public $country;

	/** @var string|null $email The customer's email. */
	public $email;

	/** @var string|null $contact The customer's contact info. */
	public $contact;

	/** @var string|null $comment The customer's comment. */
	public $comment;

	/** @var string|null $invoice_notes The customer's invoice notes. */
	public $invoice_notes;

	/** @var string|null $additional_header_info Additional header info for the customer. */
	public $additional_header_info;

	/** @var float|null $invoice_line_discount The customer's invoice line discount. */
	public $invoice_line_discount;

	/** @var string|null $phone The customer's phone number. */
	public $phone;

	/** @var bool|null $send_electronic_invoices If true, send electronic invoices to the customer. */
	public $send_electronic_invoices;

	/** @var int|null $due_date_default_days_after The default number of days after which the due date occurs after the invoice issue date. */
	public $due_date_default_days_after;

	/** @var int|null $final_due_date_default_days_after The default number of days after the due date which the final due date is set. */
	public $final_due_date_default_days_after;

	/** @var bool|null $refetch If true, the customer data will be retrieved from the national registry. */
	public $refetch;

	/**
	 * Payday_Customer_Response constructor.
	 *
	 * @param array $data The data to initialize the customer request with.
	 */
	public function __construct($data = [])
	{
		$this->id = $data['id'] ?? null;
		$this->language = $data['language'] ?? null;
		$this->ssn = $data['ssn'] ?? null;
		$this->foreign_ssn = $data['foreign_ssn'] ?? null;
		$this->name = $data['name'] ?? null;
		$this->address = $data['address'] ?? null;
		$this->zip_code = $data['zip_code'] ?? null;
		$this->city = $data['city'] ?? null;
		$this->country = $data['country'] ?? null;
		$this->email = $data['email'] ?? null;
		$this->contact = $data['contact'] ?? null;
		$this->comment = $data['comment'] ?? null;
		$this->invoice_notes = $data['invoice_notes'] ?? null;
		$this->additional_header_info = $data['additional_header_info'] ?? null;
		$this->invoice_line_discount = $data['invoice_line_discount'] ?? null;
		$this->phone = $data['phone'] ?? null;
		$this->send_electronic_invoices = $data['send_electronic_invoices'] ?? null;
		$this->due_date_default_days_after = $data['due_date_default_days_after'] ?? null;
		$this->final_due_date_default_days_after = $data['final_due_date_default_days_after'] ?? null;
		$this->refetch = $data['refetch'] ?? null;
	}
}
