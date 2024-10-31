<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-product-request.php';


/**
 * Class Payday_Product_Request
 *
 * Represents the request object of a product to be submitted to the Payday External API.
 */
class Payday_Products_Request
{
    /** @var Payday_Product_Request[]|null $products The list of products */
    public $products;

    /**
     * Payday_Products_Request constructor.
     *
     * @param Payday_Product_Request[]|null $products
     */
    public function __construct(
        ?array $products
    ) {
        $this->products = $products;
    }
}
