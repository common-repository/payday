<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'payday-database/entities/entity-payday-auth-token.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/gateway/class-payday-authenticate-gateway.php';

class Payday_Auth_Model
{
    const TRANSIENT_TOKEN_NAME = PAYDAY_NAME . '_bearer_token';
    const TOKEN_EXPIRATION_SECONDS = 86400; // 24 hours in seconds


    public function verify_credentials($client_id, $client_secret, $api_endpoint)
    {
        Payday_Logger::log('Login Attempt: Verifying credentials...', 'info');
        // Create an instance of the Payday authentication gateway
        $payday_auth_gateway = new Payday_Authenticate_Gateway();

        try {
            // Get the token from the Payday authentication gateway
            $payday_token_response = $payday_auth_gateway->create_token($client_id, $client_secret, $api_endpoint);

            if ($payday_token_response == null) {
                return false;
            }

            // Check if the token is valid
            if (!Payday_Utils::is_null_or_empty_string($payday_token_response->access_token)) {
                // save the token 
                set_transient(self::TRANSIENT_TOKEN_NAME, $payday_token_response->access_token, self::TOKEN_EXPIRATION_SECONDS);
                return true;
            } else {
                return false;
            }
        } catch (Payday_Gateway_Error $e) {
            // In case of 401 error (Unauthorized), assume connection is not valid
            if ($e->getErrorCode() == 401) {
                // Optionally, delete the transient here
                delete_transient(self::TRANSIENT_TOKEN_NAME);
                throw $e;
            }
            throw $e;
            // return false;
        }
    }

    public function get_auth_token()
    {
        // Try to get the token from the transient
        $token = get_transient(self::TRANSIENT_TOKEN_NAME);

        if ($token === false || Payday_Utils::is_null_or_empty_string($token)) {
            // Transient has expired or does not exist, get a new token
            $token = $this->get_new_token();

            if ($token === false || Payday_Utils::is_null_or_empty_string($token)) {
                return null;
            }

            // Save the new token in a transient
            set_transient(self::TRANSIENT_TOKEN_NAME, $token, self::TOKEN_EXPIRATION_SECONDS);
        }

        return $token;
    }

    public function delete_auth_token()
    {
        delete_transient(self::TRANSIENT_TOKEN_NAME);
    }

    public function is_connected()
    {
        // Check if the client settings are present
        $settings_model = new Payday_Settings_Model();
        $clientId = $settings_model->get_client_id();
        $clientSecret = $settings_model->get_client_secret();
        $api_endpoint = $settings_model->get_api_endpoint();

        if (empty($clientId) || empty($clientSecret) || empty($api_endpoint)) {
            return false;
        }

        // Check if a valid transient exists
        $token = get_transient(self::TRANSIENT_TOKEN_NAME);

        if ($token === false || Payday_Utils::is_null_or_empty_string($token)) {
            // Let try to renew it
            $token = $this->get_new_token();
            if ($token === false || Payday_Utils::is_null_or_empty_string($token)) {
                return false;
            } else {
                // Save the new token in a transient
                set_transient(self::TRANSIENT_TOKEN_NAME, $token, self::TOKEN_EXPIRATION_SECONDS);
                return true;
            }
        } else {
            return true;
        }

        return true;
    }

    /**
     * Deletes all transients that start with the prefix defined by PAYDAY_NAME.
     *
     * @return void
     */
    public function delete_all_transients()
    {
        global $wpdb;

        $prefix = esc_sql(PAYDAY_NAME);
        $options = $wpdb->options;

        $t  = "_transient_timeout_{$prefix}%";
        $sql = $wpdb->prepare("SELECT option_name FROM $options WHERE option_name LIKE %s", $t);
        $transients = $wpdb->get_col($sql);

        // For each transient...
        foreach ($transients as $transient) {
            // Strip away the WordPress prefix in order to arrive at the transient key.
            $key = str_replace('_transient_timeout_', '', $transient);
            // Now that we have the key, use WordPress core to the delete the transient.
            delete_transient($key);
        }
    }


    protected function get_new_token()
    {
        // Get client ID, client secret, and API endpoint from the settings model
        $settings_model = new Payday_Settings_Model();
        $client_id = $settings_model->get_client_id();
        $client_secret = $settings_model->get_client_secret();
        $api_endpoint = $settings_model->get_api_endpoint();

        // Create an instance of the Payday authentication gateway
        $payday_auth_gateway = new Payday_Authenticate_Gateway();

        try {
            // Get the token from the Payday authentication gateway
            $payday_token_response = $payday_auth_gateway->create_token($client_id, $client_secret, $api_endpoint);

            return $payday_token_response->access_token;
        } catch (Payday_Gateway_Error $e) {
            // In case of 401 error (Unauthorized), assume connection is not valid
            if ($e->getErrorCode() == 401) {
                // Optionally, delete the transient here
                delete_transient(self::TRANSIENT_TOKEN_NAME);
                return false;
            }
            // Handle other errors as necessary
        }
    }
}
