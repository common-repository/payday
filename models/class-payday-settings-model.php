<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/gateway/class-payday-payment-type-gateway.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-invoice-meta-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-payment-type-provider.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'enums/enum-payday-api-endpoint.php';
require_once PAYDAY_DIR_PATH . 'mapper/class-payday-payment-type-mapper.php';

class Payday_Settings_Model
{
	private static function _get_option($option_name)
	{
		$option = get_option($option_name, false);

		if ($option === false) {
			self::_set_option($option_name, '');
			return '';
		}

		return $option;
	}

	private static function _set_option($option_name, $new_value)
	{
		$current_value = get_option($option_name);

		// Check if the option already exists.
		if ($current_value !== false) {
			// Only update the option if the new value is different.
			if ($current_value !== $new_value) {
				$success = update_option($option_name, $new_value);
			} else {
				// No need to update the option because the value hasn't changed.
				return;
			}
		} else {
			$success = add_option($option_name, $new_value);
		}

		// If the option update/create failed, throw an exception.
		if ($success === false) {
			throw new Exception("Failed to update/create the {$option_name} option. The value was {$new_value}.");
		}
	}

	/**
	 * Gets the client ID from the options.
	 *
	 * @return string
	 * @throws Exception If the client ID is not set.
	 */
	public static function get_client_id()
	{
		return self::_get_option('payday_client_id');
	}

	/**
	 * Sets the client ID in the options.
	 *
	 * @param string $client_id The client ID to set.
	 * @throws Exception If the client ID is not set or fails to update/create.
	 */
	public static function set_client_id($client_id)
	{
		self::_set_option('payday_client_id', $client_id);
	}


	/**
	 * Gets the client secret from the options.
	 *
	 * @return string The client secret.
	 * @throws Exception If the client secret is not set.
	 */
	public static function get_client_secret()
	{
		return self::_get_option('payday_client_secret');
	}

	/**
	 * Sets the client secret in the options.
	 *
	 * @param string $client_secret The client secret to set.
	 * @throws Exception If failed to update/create the client secret option.
	 */
	public static function set_client_secret($client_secret)
	{
		self::_set_option('payday_client_secret', $client_secret);
	}


	/**
	 * Gets the API endpoint from the options.
	 *
	 * @return string The API endpoint.
	 * @throws Exception If the API endpoint is not set.
	 */
	public static function get_api_endpoint()
	{
		return self::_get_option('payday_api_endpoint');
	}

	/**
	 * Sets the API endpoint in the options.
	 *
	 * @param string $api_endpoint The API endpoint to set.
	 * @throws Exception If failed to update/create the API endpoint option.
	 */
	public static function set_api_endpoint($api_endpoint)
	{
		self::_set_option('payday_api_endpoint', $api_endpoint);
	}

	/**
	 * Gets the API endpoint title from the options.
	 *
	 * @return string The API endpoint title.
	 * @throws Exception If the API endpoint title is not set.
	 */
	public static function get_api_endpoint_title()
	{
		$reflection = new ReflectionClass(Payday_API_Endpoint::class);
		$constants = $reflection->getConstants();

		if ($constants === NULL) {
			return "Not set";
		}

		$constants = array_change_key_case($constants, CASE_LOWER);
		$flippedConstants = array_flip($constants);

		$api_endpoint = self::_get_option('payday_api_endpoint');

		if (Payday_Utils::is_null_or_empty_string($api_endpoint)) {
			return "Not set";
		}

		$desiredKeyLower = strtolower($api_endpoint);
		if (isset($flippedConstants[$desiredKeyLower])) {
			$constantTitleCase = ucwords($flippedConstants[$desiredKeyLower], "_");
			$constantTitleCase = str_replace("_", " ", $constantTitleCase);
			return $constantTitleCase;
		} else {
			return "Not set";
		}
	}


	/**
	 * Get the plugin setting for the specified payment method.
	 * 
	 * @param string $wc_payment_method The WooCommerce payment method.	
	 * 
	 * @return string Returns the order status when an invoice should be created. Possible values are processing, completed, or None.
	 */
	public static function get_create_invoice_on_action_option($wc_payment_method)
	{
		// Get the settings.
		// processing, completed, or None. False if the setting doesn't exist.
		/** @var string|bool $settings */
		$settings = get_option("payday_payment_method_{$wc_payment_method}_create_invoice_on_action");

		if ($settings === false) {
			// Set the default value.
			$settings = 'None';
		}

		return $settings;
	}

	/**
	 * Set the plugin setting for the specified payment method.
	 * 
	 * @param string $wc_payment_method The WooCommerce payment method.
	 * @param string $value The value to set.
	 */
	public static function set_create_invoice_on_action_option($wc_payment_method, $order_status)
	{
		// Set the settings.
		$success = update_option("payday_payment_method_{$wc_payment_method}_create_invoice_on_action", $order_status);

		if ($success === false) {
			Payday_Logger::log("Failed to update the create invoice on action option for the {$wc_payment_method} payment method. The value was {$order_status}.", 'error');
		}
	}


	/**
	 * Get all payment types from the database.
	 * 
	 * @return Payday_Payment_Types_Entity[] List of all payment types.
	 */
	public function get_all_payment_types()
	{
		$payment_types_provider = new Payday_Payment_Types_Provider();
		$existing_payment_types = $payment_types_provider::get_all_payment_types();
		return $existing_payment_types;
	}

	/**
	 * Retrieve Payday payment types and update the database accordingly.
	 *
	 * @return array Updated list of all payment types.
	 */
	public function retrieve_and_update_payday_payment_types()
	{
		$existing_payment_types = $this->get_all_payment_types();

		// Get Payday payment types.
		$payment_type_gateway = new Payday_Payment_Type_Gateway();
		$payday_payment_types = array();
		try {
			$payday_payment_types = $payment_type_gateway->get_sales_payment_types();
			$payday_payment_types = Payday_Payment_Type_Mapper::toEntityArray($payday_payment_types);
		} catch (Payday_Gateway_Error $e) {
			Payday_Logger::log($e->getMessage(), 'error');
		}

		// Convert lists to dictionaries for easy lookup
		$existing_dict = $this->convert_to_dict($existing_payment_types, 'id');
		$payday_dict = $this->convert_to_dict($payday_payment_types, 'id');

		// Find types to save and delete by comparing the keys in payday_dict and existing_dict
		$types_to_save = array_diff_key($payday_dict, $existing_dict);
		$types_to_delete = array_diff_key($existing_dict, $payday_dict);

		// If there are types to save
		if (count($types_to_save) > 0) {
			// create the payment types using the types_to_save
			$this->create_payment_types($types_to_save);
		}

		// If there are types to delete
		if (count($types_to_delete) > 0) {
			// delete the payment types using the types_to_delete
			$this->delete_payment_types($types_to_delete);

			// Get all available payment gateways
			$payment_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			// Loop through each payment gateway
			foreach ($payment_gateways as $payment_gateway) {
				// Get the id of the current payment gateway
				$payment_method = $payment_gateway->id;

				// Get the currently selected payment type id for this payment method
				$selected_payment_type_id = get_option("payday_payment_method_{$payment_method}");

				// Get all ids of the types to be deleted
				$deleted_type_ids = array_map(function ($type) {
					// Returns the id of the type
					return $type->id;
				}, $types_to_delete);

				// Check if the selected_payment_type_id is one of the ids to be deleted
				if ($selected_payment_type_id !== false && in_array($selected_payment_type_id, $deleted_type_ids)) {
					// If it is, update the selected payment type id to 'None'
					update_option("payday_payment_method_{$payment_method}", 'None');
				}
			}
		}

		// Get all payment types.
		$payment_types =  $this->get_all_payment_types();

		// Return all payment types.
		return $payment_types;
	}

	public function delete_all_payment_types()
	{
		$payment_types_provider = new Payday_Payment_Types_Provider();
		try {
			$payment_types_provider::delete_all_entries();
		} catch (Exception $e) {
			Payday_Logger::log("Failed to delete all payment types. {$e->getMessage()}", 'error');
		}

		// set the selected value to 'None'
		$all_options = wp_load_alloptions();
		$setting_prefix = 'payday_payment_method_';
		if (isset($all_options)) {
			foreach ($all_options as $option_name => $option_value) {
				if (substr($option_name, 0, strlen($setting_prefix)) === $setting_prefix) {
					update_option($option_name, "None");
				}
			}
		}
	}

	public function delete_all_invoice_meta()
	{
		$invoice_meta_provider = new Payday_Invoice_Meta_Provider();
		try {
			$invoice_meta_provider::delete_all_entries();
		} catch (Exception $e) {
			Payday_Logger::log("Failed to delete all invoice meta. {$e->getMessage()}", 'error');
		}
	}

	/**
	 * Empties the value of all settings that start with the prefix defined by PAYDAY_NAME.
	 *
	 * @return void
	 */
	public function empty_all_settings()
	{
		$all_options = wp_load_alloptions();
		if (isset($all_options)) {
			foreach ($all_options as $option_name => $option_value) {
				if (substr($option_name, 0, strlen(PAYDAY_NAME)) === PAYDAY_NAME) {
					update_option($option_name, "");
				}
			}
		}
	}

	/**
	 * Deletes all settings that start with the prefix defined by PAYDAY_NAME.
	 *
	 * @return void
	 */
	public function delete_all_settings()
	{
		$all_options = wp_load_alloptions();
		if (isset($all_options)) {
			foreach ($all_options as $option_name => $option_value) {
				if (substr($option_name, 0, strlen(PAYDAY_NAME)) === PAYDAY_NAME) {
					delete_option($option_name);
				}
			}
		}
	}

	/**
	 * Convert an array of objects to an associative array.
	 *
	 * @param array $objects Array of objects.
	 * @param string $keyProperty Name of the property to use as the key.
	 * @return array Associative array of objects indexed by the key property.
	 */
	private function convert_to_dict($objects, $keyProperty)
	{
		if (!is_array($objects) && !is_object($objects)) {
			// $objects is not an array or object, handle this case appropriately
			return [];  // return an empty array, for example
		}

		$dict = [];
		foreach ($objects as $object) {
			$dict[$object->$keyProperty] = $object;
		}
		return $dict;
	}

	/**
	 * Save new payment types to the database.
	 *
	 * @param Payday_Payment_Types_Entity[] $types_to_save List of payment types to save.
	 * @return void
	 */
	public function create_payment_types($types_to_save)
	{
		$payment_types_provider = new Payday_Payment_Types_Provider();

		foreach ($types_to_save as $payment_type_to_save) {
			$payment_types_provider->create_payment_type(
				$payment_type_to_save->id,
				$payment_type_to_save->title
			);
		}
	}

	/**
	 * Delete existing payment types from the database.
	 *
	 * @param Payday_Payment_Types_Entity[] $types_to_delete List of payment types to delete.
	 * @return void
	 */
	public function delete_payment_types($types_to_delete)
	{
		$payment_types_provider = new Payday_Payment_Types_Provider();

		foreach ($types_to_delete as $payment_type_to_delete) {
			$payment_types_provider->delete_payment_type($payment_type_to_delete->id);
		}
	}
}
