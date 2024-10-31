<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Class Payday_Payment_Type_Response
 *
 * Represents the response from Payday External API of a payment type.
 */
class Payday_Payment_Type_Response
{
	/** @var string|null $id The id. */
	public $id;

	/** @var string|null $title The title. */
	public $title;

	/** @var string|null $description The description. */
	public $description;

	/** @var string|null $created The timestamp when the payment type was created. */
	public $created;

	/**
	 * Payday_Payment_Type_Response constructor.
	 *
	 * @param string|null $id
	 * @param string|null $title
	 * @param string|null $description
	 * @param string|null $created
	 */
	public function __construct(
		?string $id,
		?string $title,
		?string $description,
		?string $created
	) {
		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
		$this->created = $created;
	}
}
