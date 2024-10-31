<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Product_Request
 *
 * Represents the request object of a product to be submitted to the Payday External API.
 */
class Payday_Product_Request
{
    /** @var string|null $name The product name. */
    public $name;

    /** @var string|null $description The product description. */
    public $description;

    /** @var string|null $sku The product SKU. */
    public $sku;

    /** @var int|null $quantity The product quantity. */
    public $quantity;

    /** @var float|null $sales_unit_price_excluding_vat The sales unit price excluding VAT. */
    public $sales_unit_price_excluding_vat;

    /** @var float|null $sales_unit_price_including_vat The sales unit price including VAT. */
    public $sales_unit_price_including_vat;

    /** @var string|null $sales_ledger_account_id The sales ledger account ID. */
    public $sales_ledger_account_id;

    /** @var float|null $vat_percentage The VAT percentage. */
    public $vat_percentage;

    /** @var array|null $tags The product tags. */
    public $tags;

    /** @var bool|null $archived Indicates if the product is archived. */
    public $archived;

    /**
     * Payday_Product_Request constructor.
     *
     * @param array $data The product data.
     */
    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->sku = $data['sku'] ?? null;
        $this->quantity = $data['quantity'] ?? null;
        $this->sales_unit_price_excluding_vat = $data['sales_unit_price_excluding_vat'] ?? null;
        $this->sales_unit_price_including_vat = $data['sales_unit_price_including_vat'] ?? null;
        $this->sales_ledger_account_id = $data['sales_ledger_account_id'] ?? null;
        $this->vat_percentage = $data['vat_percentage'] ?? null;
        $this->tags = $data['tags'] ?? null;
        $this->archived = $data['archived'] ?? null;
    }
}
