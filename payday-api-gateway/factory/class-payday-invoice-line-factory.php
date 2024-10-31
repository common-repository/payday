<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/interface/interface-payday-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-invoice-line-response.php';

class Payday_Invoice_Line_Factory implements Payday_Factory
{
    /**
     * Create a data array from a Payday_Invoice_Line_Request.
     * 
     * @param Payday_Invoice_Line_Request $request
     * @return array
     */
    public static function create_data_array_from_request($request)
    {
        $data = array();

        if ($request->invoice_id !== null) {
            $data['invoiceId'] = $request->invoice_id;
        }
        if ($request->position !== null) {
            $data['position'] = $request->position;
        }
        if ($request->description !== null) {
            $data['description'] = $request->description;
        }
        if ($request->product_id !== null) {
            $data['productId'] = $request->product_id;
        }
        if ($request->sku !== null) {
            $data['sku'] = $request->sku;
        }
        if ($request->quantity !== null) {
            $data['quantity'] = $request->quantity;
        }
        if ($request->unit_price_excluding_vat !== null) {
            $data['unitPriceExcludingVat'] = $request->unit_price_excluding_vat;
        }
        if ($request->unit_price_including_vat !== null) {
            $data['unitPriceIncludingVat'] = $request->unit_price_including_vat;
        }
        if ($request->foreign_unit_price_excluding_vat !== null) {
            $data['foreignUnitPriceExcludingVat'] = $request->foreign_unit_price_excluding_vat;
        }
        if ($request->foreign_unit_price_including_vat !== null) {
            $data['foreignUnitPriceIncludingVat'] = $request->foreign_unit_price_including_vat;
        }
        if ($request->vat_percentage !== null) {
            $data['vatPercentage'] = $request->vat_percentage;
        }
        if ($request->discount_percentage !== null) {
            $data['discountPercentage'] = $request->discount_percentage;
        }

        return $data;
    }



    /**
     * Create a Payday_Invoice_Line_Response from an array of data.
     * 
     * @param array $data
     * @return Payday_Invoice_Line_Response
     */
    public static function create_response_from_data($data)
    {
        if (!$data) {
            return null;
        }

        $response = new Payday_Invoice_Line_Response(
            $data['id'] ?? null,
            $data['invoiceid'] ?? null,
            $data['position'] ?? null,
            $data['description'] ?? null,
            $data['productId'] ?? null,
            $data['sku'] ?? null,
            $data['quantity'] ?? null,
            $data['unitPriceExcludingVat'] ?? null,
            $data['unitPriceIncludingVat'] ?? null,
            $data['foreignUnitPriceExcludingVat'] ?? null,
            $data['foreignUnitPriceIncludingVat'] ?? null,
            $data['vatPercentage'] ?? null,
            $data['discountPercentage'] ?? null,
            $data['created'] ?? null
        );

        return $response;
    }
}
