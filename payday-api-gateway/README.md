# Payday API Gateway

The Payday API Gateway directory contains classes responsible for interacting with the Payday External API. These classes act as a bridge between your WordPress plugin and the Payday API, providing an abstraction layer for making API requests and handling responses.

## Purpose

The classes in this directory are designed to encapsulate the communication with the Payday API, allowing your plugin to perform various operations such as creating invoices, retrieving invoice data, and more. These classes help streamline the integration of Payday functionality into your WordPress plugin.

## Directory Structure

The Payday API Gateway directory has the following structure:

- `response/`: Contains classes that represent the response data from the Payday API.
- `request/`: Contains classes that represent the request data to be sent to the Payday API.
- `factory/`: Contains classes that assist in mapping API responses to corresponding response objects.
- `class-payday-request-manager.php`: Implements the `Payday_Request_Manager` class responsible for handling API requests and responses.

## How to Use

To use the Payday API Gateway classes in your plugin, follow these steps:

1. Include the necessary gateway classes in your plugin by requiring the appropriate files.

2. Instantiate the relevant request classes from the `request/` directory to create requests for the Payday API operations you want to perform.

3. Utilize the gateway classes (e.g., `Payday_Invoice_Gateway`) to execute the desired operations by passing the request objects as arguments.

4. The gateway classes will internally use the `Payday_Request_Manager` to make the necessary API requests and receive responses.

5. The responses from the Payday API will be mapped to the corresponding response objects using the factory defined in the `factory/` directory.

## Examples

Here are some example use cases:

```php
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/gateway/class-payday-invoice-gateway.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-invoice-request.php';

// Create an invoice
$invoice_request = new Payday_Invoice_Request($order);
/** @var Payday_Invoice_Response $created_invoice */
$created_invoice = Payday_Invoice_Gateway::create_invoice($invoice_request);

// Get an invoice by ID
$invoice_id = '12345';
/** @var Payday_Invoice_Response $invoice */
$invoice = Payday_Invoice_Gateway::get_invoice($invoice_id);
```