<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-customer-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-invoice-line-request.php';

/**
 * Class Payday_Invoice_Request
 *
 * Represents the request object of an invoice to be submitted to the Payday External API.
 */
class Payday_Invoice_Request
{
    /** @var string|null $id The invoice ID. */
    public $id;

    /** @var Payday_Customer_Request|null $customer The customer information. */
    public $customer;

    /** @var Payday_Customer_Request|null $payor The payor information. */
    public $payor;

    /** @var string|null $description The invoice description. */
    public $description;

    /** @var string|null $reference The invoice reference. */
    public $reference;

    /** @var int|null $number The invoice number. */
    public $number;

    /** @var string|null $status The invoice status. */
    public $status;

    /** @var string|null $claim_created The date the claim was created. */
    public $claim_created;

    /** @var string|null $claim_final_due_date The final due date of the claim. */
    public $claim_final_due_date;

    /** @var string|null $invoice_date The date of the invoice. */
    public $invoice_date;

    /** @var string|null $due_date The due date of the invoice. */
    public $due_date;

    /** @var string|null $final_due_date The final due date of the invoice. */
    public $final_due_date;

    /** @var string|null $paid_date The date the invoice was paid. */
    public $paid_date;

    /** @var string|null $cancelled_date The date the invoice was cancelled. */
    public $cancelled_date;

    /** @var string|null $refund_date The date the invoice was refunded. */
    public $refund_date;

    /** @var string|null $credit_date The date the invoice was credited. */
    public $credit_date;

    /** @var string|null $sent_date The date the invoice was sent. */
    public $sent_date;

    /** @var string|null $claim_cancelled_date The date the claim was cancelled. */
    public $claim_cancelled_date;

    /** @var bool|null $claim_cancelled Indicates if the claim was cancelled. */
    public $claim_cancelled;

    /** @var float|null $amount_excluding_vat The amount excluding VAT. */
    public $amount_excluding_vat;

    /** @var float|null $amount_including_vat The amount including VAT. */
    public $amount_including_vat;

    /** @var float|null $amount_vat The amount of VAT. */
    public $amount_vat;

    /** @var float|null $foreign_amount_excluding_vat The foreign amount excluding VAT. */
    public $foreign_amount_excluding_vat;

    /** @var float|null $foreign_amount_including_vat The foreign amount including VAT. */
    public $foreign_amount_including_vat;

    /** @var float|null $foreign_amount_vat The foreign amount of VAT. */
    public $foreign_amount_vat;

    /** @var string|null $currency_code The currency code. */
    public $currency_code;

    /** @var float|null $currency_rate The currency rate. */
    public $currency_rate;

    /** @var string|null $vat_number The VAT number. */
    public $vat_number;

    /** @var bool|null $create_claim Indicates if a claim should be created. */
    public $create_claim;

    /** @var bool|null $create_electronic_invoice Indicates if an electronic invoice should be created. */
    public $create_electronic_invoice;

    /** @var string|null $electronic_invoice_party_id The electronic invoice party ID. */
    public $electronic_invoice_party_id;

    /** @var string|null $accounting_cost The accounting cost. */
    public $accounting_cost;

    /** @var string|null $ocr The OCR. */
    public $ocr;

    /** @var bool|null $send_email Indicates if an email should be sent. */
    public $send_email;

    /** @var float|null $default_interest The default interest rate. */
    public $default_interest;

    /** @var float|null $capital_gains_tax The capital gains tax. */
    public $capital_gains_tax;

    /** @var Payday_Invoice_Line_Request[]|null $lines The invoice lines. */
    public $lines;

    /** @var string|null $payment_type The type of payment. */
    public $payment_type;

    /** @var int|null $source The source ID. */
    public $source = 3; // WordPress

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->customer = $data['customer'] ?? null;
        $this->payor = $data['payor'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->reference = $data['reference'] ?? null;
        $this->number = $data['number'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->claim_created = $data['claim_created'] ?? null;
        $this->claim_final_due_date = $data['claim_final_due_date'] ?? null;
        $this->invoice_date = $data['invoice_date'] ?? null;
        $this->due_date = $data['due_date'] ?? null;
        $this->final_due_date = $data['final_due_date'] ?? null;
        $this->paid_date = $data['paid_date'] ?? null;
        $this->cancelled_date = $data['cancelled_date'] ?? null;
        $this->refund_date = $data['refund_date'] ?? null;
        $this->credit_date = $data['credit_date'] ?? null;
        $this->sent_date = $data['sent_date'] ?? null;
        $this->claim_cancelled_date = $data['claim_cancelled_date'] ?? null;
        $this->claim_cancelled = $data['claim_cancelled'] ?? null;
        $this->amount_excluding_vat = $data['amount_excluding_vat'] ?? null;
        $this->amount_including_vat = $data['amount_including_vat'] ?? null;
        $this->amount_vat = $data['amount_vat'] ?? null;
        $this->foreign_amount_excluding_vat = $data['foreign_amount_excluding_vat'] ?? null;
        $this->foreign_amount_including_vat = $data['foreign_amount_including_vat'] ?? null;
        $this->foreign_amount_vat = $data['foreign_amount_vat'] ?? null;
        $this->currency_code = $data['currency_code'] ?? null;
        $this->currency_rate = $data['currency_rate'] ?? null;
        $this->vat_number = $data['vat_number'] ?? null;
        $this->create_claim = $data['create_claim'] ?? null;
        $this->create_electronic_invoice = $data['create_electronic_invoice'] ?? null;
        $this->electronic_invoice_party_id = $data['electronic_invoice_party_id'] ?? null;
        $this->accounting_cost = $data['accounting_cost'] ?? null;
        $this->ocr = $data['ocr'] ?? null;
        $this->send_email = $data['send_email'] ?? null;
        $this->default_interest = $data['default_interest'] ?? null;
        $this->capital_gains_tax = $data['capital_gains_tax'] ?? null;
        $this->lines = $data['lines'] ?? null;
        $this->payment_type = $data['payment_type'] ?? null;
    }
}
