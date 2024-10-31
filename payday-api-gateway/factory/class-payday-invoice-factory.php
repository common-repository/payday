<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/interface/interface-payday-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-customer-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/factory/class-payday-invoice-line-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-invoice-response.php';

class Payday_Invoice_Factory implements Payday_Factory
{
	/**
	 * Create a data array from a Payday_Invoice_Request.
	 * 
	 * @param Payday_Invoice_Request $request
	 * @return array
	 */
	public static function create_data_array_from_request($request)
	{
		$data = array();

		if ($request->id !== null)
			$data['id'] = $request->id;

		if ($request->customer !== null)
			$data['customer'] = Payday_Customer_Factory::create_data_array_from_request($request->customer);

		if ($request->payor !== null)
			$data['payor'] = Payday_Customer_Factory::create_data_array_from_request($request->payor);

		if ($request->description !== null)
			$data['description'] = $request->description;

		if ($request->reference !== null)
			$data['reference'] = $request->reference;

		if ($request->number !== null)
			$data['number'] = $request->number;

		if ($request->status !== null)
			$data['status'] = $request->status;

		if ($request->claim_created !== null)
			$data['claimCreated'] = $request->claim_created;

		if ($request->claim_final_due_date !== null)
			$data['claimFinalDueDate'] = $$request->claim_final_due_date;

		if ($request->invoice_date !== null)
			$data['invoiceDate'] = $request->invoice_date;

		if ($request->due_date !== null)
			$data['dueDate'] = $request->due_date;

		if ($request->final_due_date !== null)
			$data['finalDueDate'] = $request->final_due_date;

		if ($request->paid_date !== null)
			$data['paidDate'] = $request->paid_date;

		if ($request->cancelled_date !== null)
			$data['cancelledDate'] = $request->cancelled_date;

		if ($request->refund_date !== null)
			$data['refundDate'] = $request->refund_date;

		if ($request->credit_date !== null)
			$data['creditDate'] = $request->credit_date;

		if ($request->sent_date !== null)
			$data['sentDate'] = $request->sent_date;

		if ($request->claim_cancelled_date !== null)
			$data['claimCancelledDate'] = $request->claim_cancelled_date;

		if ($request->claim_cancelled !== null)
			$data['claimCancelled'] = $request->claim_cancelled;

		if ($request->amount_excluding_vat !== null)
			$data['amountExcludingVat'] = $request->amount_excluding_vat;

		if ($request->amount_including_vat !== null)
			$data['amountIncludingVat'] = $request->amount_including_vat;

		if ($request->amount_vat !== null)
			$data['amountVat'] = $request->amount_vat;

		if ($request->foreign_amount_excluding_vat !== null)
			$data['foreignAmountExcludingVat'] = $$request->foreign_amount_excluding_vat;

		if ($request->foreign_amount_including_vat !== null)
			$data['foreignAmountIncludingVat'] = $$request->foreign_amount_including_vat;

		if ($request->foreign_amount_vat !== null)
			$data['foreignAmountVat'] = $request->foreign_amount_vat;

		if ($request->currency_code !== null)
			$data['currencyCode'] = $request->currency_code;

		if ($request->currency_rate !== null)
			$data['currencyRate'] = $request->currency_rate;

		if ($request->vat_number !== null)
			$data['vatNumber'] = $request->vat_number;

		if ($request->create_claim !== null)
			$data['createClaim'] = $request->create_claim;

		if ($request->create_electronic_invoice !== null)
			$data['createElectronicInvoice'] = $request->create_electronic_invoice;

		if ($request->electronic_invoice_party_id !== null)
			$data['electronicInvoicePartyId'] = $$request->electronic_invoice_party_id;

		if ($request->accounting_cost !== null)
			$data['accountingCost'] = $request->accounting_cost;

		if ($request->ocr !== null)
			$data['ocr'] = $request->ocr;

		if ($request->send_email !== null)
			$data['sendEmail'] = $request->send_email;

		if ($request->default_interest !== null)
			$data['defaultInterest'] = $request->default_interest;

		if ($request->capital_gains_tax !== null)
			$data['capitalGainsTax'] = $request->capital_gains_tax;

		if ($request->lines !== null) {
			$data['lines'] = [];

			foreach ($request->lines as $line) {
				$data['lines'][] = Payday_Invoice_Line_Factory::create_data_array_from_request($line);
			}
		}

		if ($request->payment_type !== null)
			$data['paymentType'] = $request->payment_type;

		if ($request->source !== null)
			$data['source'] = $request->source;

		return $data;
	}


	/**
	 * Create a Payday_Invoice_Response from an array of data.
	 * 
	 * @param array $data
	 * @return Payday_Invoice_Response
	 */
	public static function create_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		$customer = Payday_Customer_Factory::create_response_from_data($data['customer'] ?? null);
		$payor = Payday_Customer_Factory::create_response_from_data($data['payor'] ?? null);

		/** @var Payday_Invoice_Line_Response[] $lines */
		$lines = [];
		if (isset($data['lines']) && is_array($data['lines'])) {
			foreach ($data['lines'] as $line_data) {
				// append to the $lines array
				$lines[] = Payday_Invoice_Line_Factory::create_response_from_data($line_data);
			}
		}

		$response = new Payday_Invoice_Response(
			$data['id'] ?? null,
			$customer,
			$payor,
			$data['description'] ?? null,
			$data['reference'] ?? null,
			$data['number'] ?? null,
			$data['status'] ?? null,
			$data['created'] ?? null,
			$data['claimCreated'] ?? null,
			$data['claimFinalDueDate'] ?? null,
			$data['invoiceDate'] ?? null,
			$data['dueDate'] ?? null,
			$data['finalDueDate'] ?? null,
			$data['paidDate'] ?? null,
			$data['cancelledDate'] ?? null,
			$data['refundDate'] ?? null,
			$data['creditDate'] ?? null,
			$data['sentDate'] ?? null,
			$data['claimCancelledDate'] ?? null,
			$data['claimCancelled'] ?? null,
			$data['amountExcludingVat'] ?? null,
			$data['amountIncludingVat'] ?? null,
			$data['amountVat'] ?? null,
			$data['foreignAmountExcludingVat'] ?? null,
			$data['foreignAmountIncludingVat'] ?? null,
			$data['foreignAmountVat'] ?? null,
			$data['currencyCode'] ?? null,
			$data['currencyRate'] ?? null,
			$data['vatNumber'] ?? null,
			$data['createClaim'] ?? null,
			$data['createElectronicInvoice'] ?? null,
			$data['electronicInvoicePartyId'] ?? null,
			$data['accountingCost'] ?? null,
			$data['ocr'] ?? null,
			$data['sendEmail'] ?? null,
			$data['defaultInterest'] ?? null,
			$data['capitalGainsTax'] ?? null,
			$lines,
			$data['paymentType'] ?? null,
			$data['source'] ?? null
		);

		return $response;
	}
}
