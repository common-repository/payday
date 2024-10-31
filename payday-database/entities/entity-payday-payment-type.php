<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Payday_Payment_Types_Entity
{
    /**
     * Payment type ID. The primary key in the database.
     *
     * @var int
     */
    public $payment_type_id;

    /**
     * ID. The GUID of the payment type in the Payday API.
     *
     * @var string
     */
    public $id;

    /**
     * Title.
     *
     * @var string
     */
    public $title;

    /**
     * Creation date.
     *
     * @var DateTime
     */
    public $created_at;

    public function __construct(
        int $payment_type_id,
        string $id,
        string $title,
        DateTime $created_at
    ) {
        $this->payment_type_id = $payment_type_id;
        $this->id = $id;
        $this->title = $title;
        $this->created_at = $created_at;
    }
}
