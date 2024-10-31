<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Invoice_Line_Response
 *
 * Represents the response from Payday External API of an invoice line.
 */
class Payday_Invoice_Line_Response
{
    /** @var string|null $id The invoice line ID. */
    public $id;

    /** @var string|null $invoice_id The ID of the invoice this line belongs to. */
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

    /** @var string|null $created The invoice line creation date. */
    public $created;

    public function __construct(
        ?string $id,
        ?string $invoice_id,
        ?int $position,
        ?string $description,
        ?string $product_id,
        ?string $sku,
        ?float $quantity,
        ?float $unit_price_excluding_vat,
        ?float $unit_price_including_vat,
        ?float $foreign_unit_price_excluding_vat,
        ?float $foreign_unit_price_including_vat,
        ?float $vat_percentage,
        ?float $discount_percentage,
        ?string $created
    ) {
        $this->id = $id;
        $this->invoice_id = $invoice_id;
        $this->position = $position;
        $this->description = $description;
        $this->product_id = $product_id;
        $this->sku = $sku;
        $this->quantity = $quantity;
        $this->unit_price_excluding_vat = $unit_price_excluding_vat;
        $this->unit_price_including_vat = $unit_price_including_vat;
        $this->foreign_unit_price_excluding_vat = $foreign_unit_price_excluding_vat;
        $this->foreign_unit_price_including_vat = $foreign_unit_price_including_vat;
        $this->vat_percentage = $vat_percentage;
        $this->discount_percentage = $discount_percentage;
        $this->created = $created;
    }
}
