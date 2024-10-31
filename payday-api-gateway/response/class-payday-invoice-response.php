<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-customer-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-invoice-line-response.php';

/**
 * Class Payday_Invoice_Response
 *
 * Represents the response from Payday External API of an invoice.
 */
class Payday_Invoice_Response
{
    /** @var string|null $id The invoice ID. */
    public $id;

    /** @var Payday_Customer_Response|null $customer The customer information. */
    public $customer;

    /** @var Payday_Customer_Response|null $payor The payor information. */
    public $payor;

    /** @var string|null $description The invoice description. */
    public $description;

    /** @var string|null $reference The invoice reference. */
    public $reference;

    /** @var int|null $number The invoice number. */
    public $number;

    /** @var string|null $status The invoice status. */
    public $status;

    /** @var string|null $created The invoice creation date. */
    public $created;

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

    /** @var Payday_Invoice_Line_Response[]|null $lines The invoice lines. */
    public $lines;

    /** @var string|null $payment_type The type of payment. */
    public $payment_type;

    /** @var int|null $source The source ID. */
    public $source;

    public function __construct(
        ?string $id,
        ?Payday_Customer_Response $customer,
        ?Payday_Customer_Response $payor,
        ?string $description,
        ?string $reference,
        ?int $number,
        ?string $status,
        ?string $created,
        ?string $claim_created,
        ?string $claim_final_due_date,
        ?string $invoice_date,
        ?string $due_date,
        ?string $final_due_date,
        ?string $paid_date,
        ?string $cancelled_date,
        ?string $refund_date,
        ?string $credit_date,
        ?string $sent_date,
        ?string $claim_cancelled_date,
        ?bool $claim_cancelled,
        ?float $amount_excluding_vat,
        ?float $amount_including_vat,
        ?float $amount_vat,
        ?float $foreign_amount_excluding_vat,
        ?float $foreign_amount_including_vat,
        ?float $foreign_amount_vat,
        ?string $currency_code,
        ?float $currency_rate,
        ?string $vat_number,
        ?bool $create_claim,
        ?bool $create_electronic_invoice,
        ?string $electronic_invoice_party_id,
        ?string $accounting_cost,
        ?string $ocr,
        ?bool $send_email,
        ?float $default_interest,
        ?float $capital_gains_tax,
        ?array $lines,
        ?string $payment_type,
        ?int $source
    ) {
        $this->id = $id;
        $this->customer = $customer;
        $this->payor = $payor;
        $this->description = $description;
        $this->reference = $reference;
        $this->number = $number;
        $this->status = $status;
        $this->created = $created;
        $this->claim_created = $claim_created;
        $this->claim_final_due_date = $claim_final_due_date;
        $this->invoice_date = $invoice_date;
        $this->due_date = $due_date;
        $this->final_due_date = $final_due_date;
        $this->paid_date = $paid_date;
        $this->cancelled_date = $cancelled_date;
        $this->refund_date = $refund_date;
        $this->credit_date = $credit_date;
        $this->sent_date = $sent_date;
        $this->claim_cancelled_date = $claim_cancelled_date;
        $this->claim_cancelled = $claim_cancelled;
        $this->amount_excluding_vat = $amount_excluding_vat;
        $this->amount_including_vat = $amount_including_vat;
        $this->amount_vat = $amount_vat;
        $this->foreign_amount_excluding_vat = $foreign_amount_excluding_vat;
        $this->foreign_amount_including_vat = $foreign_amount_including_vat;
        $this->foreign_amount_vat = $foreign_amount_vat;
        $this->currency_code = $currency_code;
        $this->currency_rate = $currency_rate;
        $this->vat_number = $vat_number;
        $this->create_claim = $create_claim;
        $this->create_electronic_invoice = $create_electronic_invoice;
        $this->electronic_invoice_party_id = $electronic_invoice_party_id;
        $this->accounting_cost = $accounting_cost;
        $this->ocr = $ocr;
        $this->send_email = $send_email;
        $this->default_interest = $default_interest;
        $this->capital_gains_tax = $capital_gains_tax;
        $this->lines = $lines;
        $this->payment_type = $payment_type;
        $this->source = $source;
    }
}
