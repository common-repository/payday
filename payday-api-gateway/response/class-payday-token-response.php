<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Token_Response
 *
 * Represents the response from Payday External API of a token.
 */
class Payday_Token_Response
{
    /** @var string|null $access_token The access token. */
    public $access_token;

    /** @var string|null $token_type The type of the token. */
    public $token_type;

    /** @var int|null $expires_in The duration before the token expires, in seconds. */
    public $expires_in;

    /** @var int|null $created_at The timestamp when the token was created. */
    public $created_at;

    /**
     * Token_Response constructor.
     *
     * @param string|null $access_token
     * @param string|null $token_type
     * @param int|null $expires_in
     * @param int|null $created_at
     */
    public function __construct(
        ?string $access_token,
        ?string $token_type,
        ?int $expires_in,
        ?int $created_at
    ) {
        $this->access_token = $access_token;
        $this->token_type = $token_type;
        $this->expires_in = $expires_in;
        $this->created_at = $created_at;
    }
}

