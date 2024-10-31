<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-products-upsert-summary-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-bulk-products-response.php';

/**
 * Class Payday_Products_Upsert_Response
 *
 * Represents the response from Payday External API of a product.
 */
class Payday_Products_Upsert_Response
{
    /** @var Payday_Products_Upsert_Summary_Response $summary */
    public $summary;

    /** @var Payday_Bulk_Products_Response $products The list of products */
    public $products;

    /**
     * Payday_Product_Upsert Response constructor.
     * @param $summary
     * @param $products
     *
     */
    public function __construct(
        $summary,
        $products
    ) {
        $this->summary = $summary;
        $this->products = $products;
    }
}
