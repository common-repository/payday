<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

// No need to include as this class is an edge case with no request object. 
// require_once PAYDAY_DIR_PATH . 'payday-api-gateway/interface/interface-payday-factory.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-token-response.php';

/**
 * Class Payday_Token_Factory
 *
 * Factory for creating Payday_Token_Response objects.
 */
class Payday_Token_Factory
{
    /**
     * Create a Payday_Token_Response from an array of data.
     * 
     * @param array $data
     * @return Payday_Token_Response
     */
    public static function create_response_from_data(array $data)
    {
        if (!$data) {
            return null;
        }

        $response = new Payday_Token_Response(
            $data['accessToken'] ?? null,
            $data['tokenType'] ?? null,
            $data['expiresIn'] ?? null,
            $data['createdAt'] ?? null
        );

        return $response;
    }
}
