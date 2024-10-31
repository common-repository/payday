<?php

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/enums/enum-payday-gateway-error-code.php';

/**
 * Represents a custom error for the Payday gateway.
 */
class Payday_Gateway_Error extends Exception
{
    /** @var int The error code. */
    protected $error_code;

    /** @var mixed Additional data associated with the error. */
    protected $data;

    /** @var int The timestamp when the error occurred. */
    protected $timestamp;

    /**
     * Constructs a new Payday_Gateway_Error instance.
     *
     * @param int $error_code The error code.
     * @param string $message The error message.
     * @param mixed $data Additional data associated with the error.
     */
    public function __construct($error_code, $message, $data = null)
    {
        parent::__construct($message, 0);
        $this->error_code = $error_code;
        $this->data = $data;
        $this->timestamp = time();
    }

    /**
     * Get the error code.
     *
     * @return int The error code.
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * Get the additional data associated with the error.
     *
     * @return mixed The additional data associated with the error.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the timestamp when the error occurred.
     *
     * @return int The timestamp when the error occurred.
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}