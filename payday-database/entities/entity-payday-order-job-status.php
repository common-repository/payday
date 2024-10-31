<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Payday_Order_Job_Status_Entity
{
    /**
     * Order job status ID.
     *
     * @var int
     */
    public $order_job_status_id;

    /**
     * WooCommerce order ID.
     *
     * @var string
     */
    public $woocommerce_order_id;

    /**
     * Payday invoice number.
     *
     * @var string|null
     */
    public $payday_invoice_number;

    /**
     * Job status.
     *
     * @var string
     */
    public $job_status;

    /**
     * Creation date.
     *
     * @var ?DateTime
     */
    public $created_at;

    public function __construct(
        int $order_job_status_id,
        string $woocommerce_order_id,
        ?string $payday_invoice_number,
        string $job_status,
        ?DateTime $created_at = null
    ) {
        $this->order_job_status_id = $order_job_status_id;
        $this->woocommerce_order_id = $woocommerce_order_id;
        $this->payday_invoice_number = $payday_invoice_number;
        $this->job_status = $job_status;
        $this->created_at = $created_at;
    }
}
