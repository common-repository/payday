<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Product_Response
 *
 * Represents the response from Payday External API of a product.
 */
class Payday_Product_Response
{
    /** @var string|null $id The product ID. */
    public $id;

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

    /** @var string|null $created The date the product was created. */
    public $created;

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
        ?string $created
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
    }
}

