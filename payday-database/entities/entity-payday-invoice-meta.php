<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Payday_Invoice_Meta_Entity
{
    /**
     * Invoice meta ID.
     *
     * @var int
     */
    public $id;

    /**
     * WooCommerce order ID.
     *
     * @var string
     */
    public $woocommerce_order_id;

    /**
     * WooCommerce customer ID.
     *
     * @var string
     */
    public $woocommerce_customer_id;

    /**
     * Payday customer ID.
     *
     * @var string
     */
    public $payday_customer_id;

    /**
     * Payday invoice ID.
     *
     * @var string
     */
    public $payday_invoice_id;

    public function __construct(
        int $id,
        string $woocommerce_order_id,
        string $woocommerce_customer_id,
        string $payday_customer_id,
        string $payday_invoice_id
    ) {
        $this->id = $id;
        $this->woocommerce_order_id = $woocommerce_order_id;
        $this->woocommerce_customer_id = $woocommerce_customer_id;
        $this->payday_customer_id = $payday_customer_id;
        $this->payday_invoice_id = $payday_invoice_id;
    }
}
