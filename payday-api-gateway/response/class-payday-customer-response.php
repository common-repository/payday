<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Customer_Response
 *
 * Represents the response from Payday External API of a customer.
 */
class Payday_Customer_Response
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

    /** @var int|null $due_date_default_days_after The default number of days after the invoice issue date when the due date is set. */
    public $due_date_default_days_after;

    /** @var int|null $final_due_date_default_days_after The default number of days after which the final due date occurs. */
    public $final_due_date_default_days_after;

    /** @var string|null $created The date when the customer was created. */
    public $created;

    /** @var string|null $edited The date when the customer was last edited. */
    public $edited;

    /**
     * Payday_Customer_Response constructor.
     *
     * @param array $data The data to initialize the response with.
     */
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->language = $data['language'] ?? null;
        $this->ssn = $data['ssn'] ?? null;
        $this->foreign_ssn = $data['foreignSsn'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->zip_code = $data['zipCode'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->country = $data['country'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->contact = $data['contact'] ?? null;
        $this->comment = $data['comment'] ?? null;
        $this->invoice_notes = $data['invoiceNotes'] ?? null;
        $this->additional_header_info = $data['additionalHeaderInfo'] ?? null;
        $this->invoice_line_discount = $data['invoiceLineDiscount'] ?? null;
        $this->phone = $data['phone'] ?? null;
        $this->send_electronic_invoices = $data['sendElectronicInvoices'] ?? null;
        $this->due_date_default_days_after = $data['dueDateDefaultDaysAfter'] ?? null;
        $this->final_due_date_default_days_after = $data['finalDueDateDefaultDaysAfter'] ?? null;
        $this->created = $data['created'] ?? null;
        $this->edited = $data['edited'] ?? null;
    }
}
