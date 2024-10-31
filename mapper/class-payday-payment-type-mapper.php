<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-database/entities/entity-payday-payment-type.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-payment-type-response.php';

class Payday_Payment_Type_Mapper
{
	/**
	 * Convert a Payday_Payment_Type_Response to a Payday_Payment_Types_Entity.
	 *
	 * @param Payday_Payment_Type_Response $response
	 * @return Payday_Payment_Types_Entity
	 * @throws Exception
	 */
	public static function toEntity(Payday_Payment_Type_Response $response): Payday_Payment_Types_Entity
	{
		if ($response === null) {
			throw new Exception('Payday_Payment_Type_Response is null');
		}

		// Assuming 'created' field from response is a string that can be converted to a DateTime object
		$created_at = new DateTime($response->created);

		// Return a new Payday_Payment_Types_Entity
		return new Payday_Payment_Types_Entity(
			0,// id is set in the database
			$response->id,
			$response->title,
			$created_at
		);
	}

	/**
	 * Convert an array of Payday_Payment_Type_Response objects to an array of Payday_Payment_Types_Entity objects.
	 *
	 * @param array $responses
	 * @return Payday_Payment_Types_Entity[] An array of Payday_Payment_Types_Entity objects.
	 * @throws Exception
	 */
	public static function toEntityArray(array $responses): array
	{
		$entities = [];

		if ($responses === null) {
			return $entities;
		}

		foreach ($responses as $response) {
			$entities[] = self::toEntity($response);
		}

		return $entities;
	}
}
