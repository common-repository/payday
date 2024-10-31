<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/interface/interface-payday-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-product-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-product-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-products-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-bulk-product-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-bulk-products-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-products-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-products-upsert-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-products-upsert-summary-response.php';

class Payday_Product_Factory implements Payday_Factory
{
	/**
	 * Create an array of data from a Payday_Product_Request.
	 * 
	 * @param Payday_Product_Request $request
	 * @return array
	 */
	public static function create_data_array_from_request($request)
	{
		$data = [];

		if (!$request) {
			$data;
		}

		if ($request->name !== null)
			$data['name'] = $request->name;

		if ($request->description !== null)
			$data['description'] = $request->description;

		if ($request->sku !== null)
			$data['sku'] = $request->sku;

		if ($request->quantity !== null)
			$data['quantity'] = $request->quantity;

		if ($request->sales_unit_price_excluding_vat !== null)
			$data['salesUnitPriceExcludingVAT'] = $request->sales_unit_price_excluding_vat;

		if ($request->sales_unit_price_including_vat !== null)
			$data['salesUnitPriceIncludingVAT'] = $request->sales_unit_price_including_vat;

		if ($request->sales_ledger_account_id !== null)
			$data['salesLedgerAccountId'] = $request->sales_ledger_account_id;

		if ($request->vat_percentage !== null)
			$data['vatPercentage'] = $request->vat_percentage;

		if ($request->tags !== null)
			$data['tags'] = $request->tags;

		if ($request->archived !== null)
			$data['archived'] = $request->archived;


		return $data;
	}

	/**
	 * Create a Payday_Product_Response from an array of data.
	 * 
	 * @param array $data
	 * @return Payday_Product_Response
	 */
	public static function create_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		$response = new Payday_Product_Response(
			$data['id'] ?? null,
			$data['name'] ?? null,
			$data['description'] ?? null,
			$data['sku'] ?? null,
			$data['quantity'] ?? null,
			$data['salesUnitPriceExcludingVAT'] ?? null,
			$data['salesUnitPriceIncludingVAT'] ?? null,
			$data['salesLedgerAccountId'] ?? null,
			$data['vatPercentage'] ?? null,
			$data['tags'] ?? null,
			$data['archived'] ?? null,
			$data['created'] ?? null
		);

		return $response;
	}

	public static function create_bulk_product_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		$response = new Payday_Bulk_Product_Response(
			$data['id'] ?? null,
			$data['name'] ?? null,
			$data['description'] ?? null,
			$data['sku'] ?? null,
			$data['quantity'] ?? null,
			$data['salesUnitPriceExcludingVAT'] ?? null,
			$data['salesUnitPriceIncludingVAT'] ?? null,
			$data['salesLedgerAccountId'] ?? null,
			$data['vatPercentage'] ?? null,
			$data['tags'] ?? null,
			$data['archived'] ?? null,
			$data['created'] ?? null,
			$data['status'] ?? null,
			$data['error'] ?? null
		);

		return $response;
	}

	/**
	 * Create an array of data from a Payday_Product_Request.
	 * 
	 * @param Payday_Products_Request $request
	 * @return array
	 */
	public static function create_data_array_from_products_request($request)
	{
		$data = [];

		if (!$request) {
			$data;
		}

		if ($request->products !== null) {
			$data['products'] = [];

			foreach ($request->products as $product) {
				$data['products'][] = Payday_Product_Factory::create_data_array_from_request($product);
			}
		}

		return $data;
	}

	/**
	 * Create a Payday_Product_Response from an array of data.
	 * 
	 * @param array $data
	 * @return Payday_Products_Response
	 */
	public static function create_products_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		/** @var Payday_Product_Response[] $products */
		$products = [];
		if (isset($data) && is_array($data)) {
			foreach ($data as $product_data) {
				// append to the $products array
				$products[] = Payday_Product_Factory::create_response_from_data($product_data);
			}
		}

		$response = new Payday_Products_Response(
			$products
		);

		return $response;
	}



	/**
	 * Create a Payday_Bulk_Product_Response from an array of data.
	 * 
	 * @param array $data
	 * @return Payday_Bulk_Product_Response
	 */
	public static function create_bulk_products_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		/** @var Payday_Bulk_Product_Response[] $products */
		$products = [];
		if (isset($data) && is_array($data)) {
			foreach ($data as $product_data) {
				// append to the $products array
				$products[] = Payday_Product_Factory::create_bulk_product_response_from_data($product_data);
			}
		}

		$response = new Payday_Bulk_Products_Response(
			$products
		);

		return $response;
	}

	public static function create_upsert_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		/** @var Payday_Products_Upsert_Summary_Response $summary */
		$summary = Payday_Product_Factory::create_upsert_summary_response_from_data($data['summary']);


		/** @var Payday_Bulk_Products_Response $products */
		$products = Payday_Product_Factory::create_bulk_products_response_from_data($data['products']);

		$response = new Payday_Products_Upsert_Response(
			$summary,
			$products
		);

		return $response;
	}

	public static function create_upsert_summary_response_from_data($data)
	{
		if (!$data) {
			return null;
		}

		$response = new Payday_Products_Upsert_Summary_Response(
			$data['created'],
			$data['updated'],
			$data['unsuccessful']
		);

		return $response;
	}
}
