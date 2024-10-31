<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

interface Payday_Factory {
    /**
     * Create a data array from a request object.
     *
     * @param mixed $request The request object.
     *
     * @return array The data array.
     */
    public static function create_data_array_from_request($request);

    /**
     * Create a response object from a data array.
     *
     * @param array $data The data array.
     *
     * @return mixed The response object.
     */
    public static function create_response_from_data(array $data);
}
