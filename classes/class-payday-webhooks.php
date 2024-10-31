<?php

// Exit if accessed directly.
if (!defined('ABSPATH'))
    exit;

// Include dependencies
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';

class Payday_Webhooks
{
    use Payday_Singleton_Trait;

    /** Constructor function */
    private function __construct()
    {
    }

    /**
     * Action handler that runs when an order is created or the order status is changed by the admin.
     *
     * @param int $order_id The ID of the order
     * @param string $status_transition_from The status from which the order is transitioning
     * @param string $status_transition_to The status to which the order is transitioning
     * @param WC_Order $order The order object
     * @return void
     */
    public function action_woocommerce_order_status_changed($order_id, $status_transition_from, $status_transition_to, $order)
    {
        $order_model = new Payday_Order_Model();

        try {
            $order_model->handle_wc_order_status_changed($order_id, $status_transition_from, $status_transition_to, $order);
        } catch (Exception $e) {
            Payday_Logger::log($e->getMessage(), 'error');
        }
    }

    public function action_woocommerce_new_order($order_id)
    {
        if (isset($_POST["save"]) && $_POST["save"] == "Create" && isset($_POST["order_status"])) {
            // For admin-created orders
            $order = wc_get_order($order_id);

            $line_items = $order->get_items();
            $customer = $order->get_customer_id();

            if (empty($line_items) || empty($customer)) {
                return;
            }

            $order_model = new Payday_Order_Model();

            try {
                // Use $_POST["order_status"] instead of $order->get_status()
                $order_model->handle_wc_order_status_changed($order_id, '', sanitize_text_field($_POST["order_status"]), $order);
            } catch (Exception $e) {
                Payday_Logger::log($e->getMessage(), 'error');
            }
        }
    }
}
