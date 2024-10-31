<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-claim-service-payment-gateway.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'enums/enum-payday-order-job-status.php';
require_once PAYDAY_DIR_PATH . 'enums/enum-payday-order-status.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/enums/enum-payday-invoice-status.php';
require_once PAYDAY_DIR_PATH . 'payday-database/entities/entity-payday-order-job-status.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-order-job-status-provider.php';
require_once PAYDAY_DIR_PATH . 'payday-database/provider/class-payday-payment-type-provider.php';

class Payday_Order_Model
{
    /** Constructor function */
    public function __construct()
    {
    }

    /**
     * Handle WooCommerce order status changes.
     * Depending on the status transition and the selected settings,
     * this method may create a new invoice in Payday with a specific status.
     *
     * @param int      $order_id                  The ID of the order.
     * @param string   $invoice_status_transition_from    The status before the transition.
     * @param string   $invoice_status_transition_to      The status after the transition.
     * @param WC_Order $order                     The order object.
     */
    public function handle_wc_order_status_changed(int $order_id, string $invoice_status_transition_from, string $invoice_status_transition_to, WC_Order $order)
    {
        // Lets check if the order is being being paid with bank claim service
        $is_bank_claim_service = $order->get_payment_method() == Payday_Claim_Service_Payment_Gateway::instance()->get_id();

        // We only care about transitions to Processing or Completed status.
        // If the new status is not one of these, there's nothing to do.
        if ($invoice_status_transition_to !== Payday_Order_Status::PROCESSING && $invoice_status_transition_to !== Payday_Order_Status::COMPLETED && !$is_bank_claim_service) {
            return;
        }

        // Get the payment method for the order.
        $payment_method = $order->get_payment_method($context = 'view');
        $payment_method_title = $order->get_payment_method_title();


        // Get the selected settings for when to create an invoice and what payment type to use.
        // We supply default values in case the options are not set.
        $selected_status_to_create_invoice = get_option('payday_payment_method_' . $payment_method . '_create_invoice_on_action');

        // If the setting is set to 'Do not send', we do nothing. We just add add it to the logs.
        if ($selected_status_to_create_invoice === Payday_Order_Status::DO_NOT_SEND) {
            Payday_Logger::log("Invoice will not be created in Payday for order {$order_id} with status '{$invoice_status_transition_to}' and payment method '{$payment_method_title}' because the setting 'Create invoice on action' is set to 'Do not send'.", "info");
            return;
        }

        $selected_status_to_create_invoice = $selected_status_to_create_invoice === false || Payday_Utils::is_null_or_empty_string($selected_status_to_create_invoice) ? Payday_Order_Status::NONE : $selected_status_to_create_invoice;

        $selected_payday_payment_type_id = get_option('payday_payment_method_' . $payment_method);
        $selected_payday_payment_type_id = $selected_payday_payment_type_id === false || Payday_Utils::is_null_or_empty_string($selected_payday_payment_type_id) ? 'None' : $selected_payday_payment_type_id;

        if ($is_bank_claim_service) {
            $this->create_invoice_with_status($order_id, $order, Payday_Invoice_Status::SENT, $selected_payday_payment_type_id);
        }
        // If the new status is Processing...
        else if ($invoice_status_transition_to === Payday_Order_Status::PROCESSING) {
            // ...and the settings indicate to create an invoice when Processing...
            if ($selected_status_to_create_invoice === Payday_Order_Status::PROCESSING) {
                // ...and a payment type is selected, create a Paid invoice.
                // If no payment type is selected, we do nothing and wait until the order is Completed.
                if ($selected_payday_payment_type_id !== 'None') {
                    $this->create_invoice_with_status($order_id, $order, Payday_Invoice_Status::PAID, $selected_payday_payment_type_id);
                }
            }
        }
        // If the new status is Completed...
        else if ($invoice_status_transition_to === Payday_Order_Status::COMPLETED) {
            // ...and the settings indicate to create an invoice when Completed or are not set...
            if ($selected_status_to_create_invoice === Payday_Order_Status::NONE || $selected_status_to_create_invoice === Payday_Order_Status::COMPLETED) {
                // ...and a payment type is selected, create a Paid invoice.
                // If no payment type is selected, create a Sent invoice.
                if ($selected_payday_payment_type_id === 'None') {
                    $this->create_invoice_with_status($order_id, $order, Payday_Invoice_Status::SENT);
                } else {
                    $this->create_invoice_with_status($order_id, $order, Payday_Invoice_Status::PAID, $selected_payday_payment_type_id);
                }
            }
        }
    }


    /**
     * Creates an invoice in Payday with a given status.
     * 
     * @param int   $order_id   The ID of the order.
     * @param WC_Order  $order  The WooCommerce order object.
     * @param string    $invoice_status The status to set for the invoice.
     * @param string|null   $payment_type_id    The payment type ID. Must be supplied if the invoice status is PAID.
     * 
     * @throws Exception If the payment type ID is null when the invoice status is PAID.
     */
    public function create_invoice_with_status(int $order_id, WC_Order $order, string $invoice_status, ?string $payment_type_id = null)
    {
        // Check if payment type ID is set when the invoice status is PAID
        if ($invoice_status == Payday_Invoice_Status::PAID && $payment_type_id === null) {
            throw new Exception("Payment type ID cannot be null when creating an invoice with status PAID");
        }

        // Get the order job status if it exists
        $order_job_status_entity = self::get_order_job_status($order_id);

        // If the order job status is not found, create a new one
        if ($order_job_status_entity === null) {
            self::create_order_job_status($order_id, null, Payday_Order_Job_Status::PROCESSING);
        } else {
            // If the order is already being processed or has been completed
            if (
                $order_job_status_entity->job_status === Payday_Order_Job_Status::PROCESSING
                || $order_job_status_entity->job_status === Payday_Order_Job_Status::SUCCESSFUL
            ) {
                return;
            }
            // Update the order job status to Processing
            $this->update_order_job_status($order_id, null, Payday_Order_Job_Status::PROCESSING);
        }

        // Create invoice in Payday
        try {
            // Here, depending on your payday invoice API, you should add a field or parameter to specify the status
            $invoiceModel = new Payday_Invoice_Model();
            $created_invoice = $invoiceModel->create_invoice_in_payday_from_wc_order($order, $invoice_status, $payment_type_id);

            if ($created_invoice === null) {
                $this->update_order_job_status($order_id, null, Payday_Order_Job_Status::UNSUCCESSFUL);
                $order->add_order_note(
                    __("Invoice was not created in Payday. Please check the logs for more information.", 'payday')
                );
                return;
            } else {
                // Getting the payment method of the order
                $order_payment_method = $order->get_payment_method($context = 'view');

                // Getting the SSN of the customer from the order meta
                $claim_service_payment_gateway = Payday_Claim_Service_Payment_Gateway::instance();
                $billing_ssn = $order->get_meta($claim_service_payment_gateway->get_field_id());

                // If the SSN has value and the payday claim service is enabled and matches the order payment method
                if (!Payday_Utils::is_null_or_empty_string($billing_ssn) && $claim_service_payment_gateway->enabled && $order_payment_method === $claim_service_payment_gateway->get_id()) {
                    // Logging the creation of the invoice in Payday
                    Payday_Logger::log("Invoice has been created in Payday and a bank claim has been sent to customer {$order->get_formatted_billing_full_name()} with SSN: {$billing_ssn}", "info");
                    // Adding a note to the order stating the invoice was created using Payday Claim Service
                    $order->add_order_note(
                        sprintf(__("Invoice was created in Payday. A bank claim was been sent to SSN: %s", 'payday'), $billing_ssn)
                    );
                } else {
                    // Logging the creation of the invoice in Payday
                    Payday_Logger::log("Invoice has been created in Payday for customer {$order->get_formatted_billing_full_name()} with the following payment method: {$order_payment_method}", "info");
                    // Adding a note to the order stating the invoice was created in Payday
                    $order->add_order_note(
                        __("Invoice was created in Payday", 'payday')
                    );
                }
            }
            $this->update_order_job_status($order_id, null, Payday_Order_Job_Status::SUCCESSFUL);
        } catch (Exception $e) {
            Payday_Logger::log("Error creating invoice in Payday: " . $e->getMessage(), "error");
            $this->update_order_job_status($order_id, null, Payday_Order_Job_Status::UNSUCCESSFUL);
        }
    }

    /**
     * Fetches all orders.
     *
     * @return array The orders.
     */
    public function get_orders()
    {
        // Fetch all orders.
        $orders = wc_get_orders(['status' => 'any']);

        // Convert the orders to an array format that's more suitable for a REST API response.
        $orders_data = array_map(function (WC_Order $order) {
            return $order->get_data();
        }, $orders);

        return $orders_data;
    }


    /**
     * Fetch an order job status by its ID.
     * 
     * @param int $order_id The ID of the order.
     * 
     * @return Payday_Order_Job_Status_Entity|null The order job status entity.
     */
    public function get_order_job_status($order_id)
    {
        $woocommerce_order_id = (string)$order_id;
        return Payday_Order_Job_Status_Provider::get_order_job_status($woocommerce_order_id);
    }

    /**
     * Creates a new order job status.
     * 
     * @param int $order_id The ID of the order.
     * @param string|null $payday_invoice_number The invoice number from Payday.
     * @param string $job_status The status of the job.
     * 
     * @return Payday_Order_Job_Status_Entity The order job status entity.
     */
    public function create_order_job_status(int $order_id, ?string $payday_invoice_number, string $job_status)
    {
        $woocommerce_order_id = (string)$order_id;
        return Payday_Order_Job_Status_Provider::create_order_job_status($woocommerce_order_id, $payday_invoice_number, $job_status);
    }

    /**
     * Update an existing order job status.
     * 
     * @param int $order_id The ID of the order.
     * @param string|null $payday_invoice_number The invoice number from Payday.
     * @param string $job_status The status of the job.
     * 
     * @return Payday_Order_Job_Status_Entity The order job status entity.
     */
    public function update_order_job_status(int $order_id, ?string $payday_invoice_number, string $job_status)
    {
        $woocommerce_order_id = (string)$order_id;
        return Payday_Order_Job_Status_Provider::update_order_job_status($woocommerce_order_id, $payday_invoice_number, $job_status);
    }
}
