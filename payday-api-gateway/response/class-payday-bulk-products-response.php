<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-bulk-product-response.php';

/**
 * Class Payday_Bulk_Products_Response
 *
 * Represents the bulk response object of a product to be submitted to the Payday External API.
 */
class Payday_Bulk_Products_Response
{
	/** @var Payday_Bulk_Product_Response[]|null $products The list of products */
	public $products;

	/**
	 * Payday_Bulk_Product_Response constructor.
	 *
	 * @param Payday_Bulk_Product_Response[]|null $products
	 */
	public function __construct(
		?array $products
	) {
		$this->products = $products;
	}
}
