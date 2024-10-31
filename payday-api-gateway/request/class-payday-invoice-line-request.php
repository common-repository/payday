<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Invoice_Line_Request
 *
 * Represents the request object of an invoice line to be submitte to the Payday External API.
 */
class Payday_Invoice_Line_Request
{
	/** @var string|null $invoice_id The GUID of the invoice this line belongs to. */
	public $invoice_id;

	/** @var int|null $position The position of this line in the invoice. */
	public $position;

	/** @var string|null $description The invoice line description. */
	public $description;

	/** @var string|null $product_id The product ID. */
	public $product_id;

	/** @var string|null $sku The product SKU. */
	public $sku;

	/** @var float|null $quantity The quantity of the product. */
	public $quantity;

	/** @var float|null $unit_price_excluding_vat The unit price excluding VAT. */
	public $unit_price_excluding_vat;

	/** @var float|null $unit_price_including_vat The unit price including VAT. */
	public $unit_price_including_vat;

	/** @var float|null $foreign_unit_price_excluding_vat The foreign unit price excluding VAT. */
	public $foreign_unit_price_excluding_vat;

	/** @var float|null $foreign_unit_price_including_vat The foreign unit price including VAT. */
	public $foreign_unit_price_including_vat;

	/** @var float|null $vat_percentage The VAT percentage. */
	public $vat_percentage;

	/** @var float|null $discount_percentage The discount percentage. */
	public $discount_percentage;

	public function __construct(array $data = [])
	{
		$this->invoice_id = $data['invoice_id'] ?? null;
		$this->position = $data['position'] ?? null;
		$this->description = $data['description'] ?? null;
		$this->product_id = $data['product_id'] ?? null;
		$this->sku = $data['sku'] ?? null;
		$this->quantity = $data['quantity'] ?? null;
		$this->unit_price_excluding_vat = $data['unit_price_excluding_vat'] ?? null;
		$this->unit_price_including_vat = $data['unit_price_including_vat'] ?? null;
		$this->foreign_unit_price_excluding_vat = $data['foreign_unit_price_excluding_vat'] ?? null;
		$this->foreign_unit_price_including_vat = $data['foreign_unit_price_including_vat'] ?? null;
		$this->vat_percentage = $data['vat_percentage'] ?? null;
		$this->discount_percentage = $data['discount_percentage'] ?? null;
	}
}
