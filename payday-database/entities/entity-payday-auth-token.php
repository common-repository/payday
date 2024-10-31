<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

class Payday_Auth_Token_Entity
{
	/**
	 * Auth token ID.
	 * 
	 * @var int|null
	 */
	public $id;

	/**
	 * Auth token.
	 * 
	 * @var string
	 */
	public $token;

	/**
	 * Auth token creation date.
	 * 
	 * @var DateTime
	 */
	public $created_at;

	public function __construct(?int $id = null, string $token, DateTime $created_at)
	{
		$this->id = $id;
		$this->token = $token;
		$this->created_at = $created_at;
	}
}
