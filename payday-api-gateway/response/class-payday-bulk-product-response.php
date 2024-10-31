<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-product-response.php';


/**
 * Class Payday_Product_Response
 *
 * Represents the response from Payday External API of a product.
 */
class Payday_Bulk_Product_Response extends Payday_Product_Response
{
	/** @var string|null $status The bulk action status. */
	public $status;

	/** @var string|null $error The bulk action error. */
	public $error;

	/**
	 * Payday_Product_Response constructor.
	 *
	 * @param string|null $id
	 * @param string|null $name
	 * @param string|null $description
	 * @param string|null $sku
	 * @param int|null $quantity
	 * @param float|null $sales_unit_price_excluding_vat
	 * @param float|null $sales_unit_price_including_vat
	 * @param string|null $sales_ledger_account_id
	 * @param float|null $vat_percentage
	 * @param array|null $tags
	 * @param bool|null $archived
	 * @param string|null $created
	 * @param string|null $status
	 * @param string|null $error
	 */
	public function __construct(
		?string $id,
		?string $name,
		?string $description,
		?string $sku,
		?int $quantity,
		?float $sales_unit_price_excluding_vat,
		?float $sales_unit_price_including_vat,
		?string $sales_ledger_account_id,
		?float $vat_percentage,
		?array $tags,
		?bool $archived,
		?string $created,
		?string $status,
		?string $error
	) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->sku = $sku;
		$this->quantity = $quantity;
		$this->sales_unit_price_excluding_vat = $sales_unit_price_excluding_vat;
		$this->sales_unit_price_including_vat = $sales_unit_price_including_vat;
		$this->sales_ledger_account_id = $sales_ledger_account_id;
		$this->vat_percentage = $vat_percentage;
		$this->tags = $tags;
		$this->archived = $archived;
		$this->created = $created;
		$this->status = $status;
		$this->error = $error;
	}
}
