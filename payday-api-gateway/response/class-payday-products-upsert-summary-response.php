<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Product_Upsert_Summary_Response
 *
 * Represents the response from Payday External API of a product.
 */
class Payday_Products_Upsert_Summary_Response
{
    public $created;
    public $updated;
    public $unsuccessful;

    /**
     * Payday_Product_Upsert_Summary_Response constructor.
     * @param $created
     * @param $updated
     * @param $unsuccessful
     */
    public function __construct(
        $created,
        $updated,
        $unsuccessful
    ) {
        $this->created = $created;
        $this->updated = $updated;
        $this->unsuccessful = $unsuccessful;
    }
}
