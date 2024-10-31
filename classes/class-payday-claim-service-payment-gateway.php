<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Include required files
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-order-model.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-invoice-model.php';

class Payday_Claim_Service_Payment_Gateway extends WC_Payment_Gateway
{
	use Payday_Singleton_Trait;

	protected $field_id;
	protected $field_label;

	/**
	 * The constructor cannot be private because it is called by woocommerce
	 */
	public function __construct()
	{
		$this->id                 = 'payday';
		$this->has_fields         = true;
		$this->method_title       = __("Bank claim service", 'payday');
		$this->method_description = __("Create a bank claim in the customer's online bank", "payday");

		$this->supports = array('products');

		$this->init_form_fields();
		$this->init_settings();

		// Get options
		$this->title        = $this->get_option('title');
		$this->description  = $this->get_option('description');
		$this->enabled      = $this->get_option('enabled');
		$this->field_label  = $this->get_option('field_label');
		$this->field_id     = $this->get_option('field_id');
	}

	public function get_id()
	{
		return $this->id;
	}

	public function get_field_id()
	{
		return $this->get_option('field_id');
	}

	public function add_gateway_class($gateways)
	{
		$gateways[] = self::class; // your class name is here
		return $gateways;
	}

	public function init_form_fields()
	{
		$this->form_fields = $this->get_form_fields();
	}

	public function process_admin_options()
	{
		parent::process_admin_options();
	}

	public function get_form_fields()
	{
		$order_statuses = wc_get_order_statuses();
		return array(
			'enabled' => array(
				'title' => esc_html__('Enable/Disable', 'payday'),
				'label' => esc_html__('Activate bank claim service', 'payday'),
				'type' => 'checkbox',
				'description' => '',
				'default' => 'no'
			),
			'title' => array(
				'title' => esc_html__('Payment method title', "payday"),
				'type' => 'text',
				'description' => esc_html__('This title will be shown to customers during checkout. Choose a name that clearly indicates what this payment method involves.', "payday"),
				'default' => esc_html__('Bank claim service', "payday"),
				'desc_tip' => true,
			),
			'description' => array(
				'title' => esc_html__('Payment method description', "payday"),
				'type' => 'text',
				'description' => esc_html__('Provide details on how this payment method works. This will be visible to customers during checkout.', 'payday'),
				'default' => esc_html__('Receive a claim in my online banking', "payday"),
				'desc_tip' => true,
			),
			'field_label' => array(
				'title' => esc_html__('SSN field label', "payday"),
				'type' => 'text',
				'description' => esc_html__('This will be the name of the SSN field in the checkout form.', "payday"),
				'default' => esc_html__('Social security number', "payday"),
				'desc_tip' => true,
			),
			'field_id' => array(
				'title' => esc_html__('SSN field identifier', "payday"),
				'type' => 'text',
				'description' => esc_html__('This is the unique identifier of the SSN field. If you have an existing SSN field, ensure that the IDs match.', "payday"),
				'default' => 'payday_ssn',
				'desc_tip' => true,
			),
			'display_field_checkout' => array(
				'title' => esc_html__('Show SSN field on checkout page', 'payday'),
				'label' => ' ',
				'type' => 'checkbox',
				'description' => esc_html__('Tick this box if you want the SSN field to appear during checkout. Note: If this payment gateway is active, the SSN field will automatically be displayed.', "payday"),
				'default' => 'no',
				'desc_tip' => true,
			),
			'require_field_checkout' => array(
				'title' => esc_html__('Require SSN during checkout', 'payday'),
				'label' => ' ',
				'type' => 'checkbox',
				'description' => esc_html__('Enable this option if you want to require customers to provide their SSN during checkout.', "payday"),
				'default' => 'no',
				'desc_tip' => true,
			),
			'display_field_email' => array(
				'title' => esc_html__('Include SSN field in the confirmation email', 'payday'),
				'label' => ' ',
				'type' => 'checkbox',
				'description' => esc_html__("Enable this to have the customer's SSN included in the confirmation email sent by WooCommerce. Note: If this payment gateway is active, the SSN will be automatically included.", "payday"),
				'default' => 'no',
				'desc_tip' => true,
			),
			'status_after_process' => array(
				'title' => esc_html__('Order status after checkout', "payday"),
				'type' => 'select',
				'description' => esc_html__('Select the status that the order transitions to after the checkout has been processed.', "payday"),
				'default' => 'pending',
				'desc_tip' => true,
				'options' => array(
					'pending' => __($order_statuses['wc-pending']),
					'processing' => __($order_statuses['wc-processing']),
					'on-hold' => __($order_statuses['wc-on-hold']),
					'completed' => __($order_statuses['wc-completed']),
					'cancelled' => __($order_statuses['wc-cancelled']),
					'refunded' => __($order_statuses['wc-refunded']),
					'failed' => __($order_statuses['wc-failed']),
				)
			),
			"days_until_final_due_date" => array(
				'title' => esc_html__('Days until final due date', "payday"),
				'type' => 'number',
				'description' => esc_html__('Number of additional days for the final due date on the invoice.', "payday"),
				'default' => 7,
				'desc_tip' => true,
			),
		);
	}

	// Add fields to checkout form
	public function checkout_fields($fields)
	{
		if ($this->get_option("display_field_checkout") == "yes") {
			$fields['billing'][$this->field_id] = array(
				'label' => $this->field_label,
				'required' => $this->get_option("require_field_checkout") == "yes",
				'class' =>
				array(
					0 => 'form-row-wide',
				),
				'autocomplete' => 'given-ssn',
				'priority' => 22, // Add field underneath billing last name index range     21 - 29
			);
		}

		return $fields;
	}

	// Process the checkout, validate fields
	public function checkout_process()
	{
		// If this payment method is selected, validate the field
		if ($_POST['payment_method'] === $this->id) {
			if (!$_POST[$this->field_id] || !preg_match('/[0-7]\d[01]\d{3}[-]*\d{3}[09]/', $_POST[$this->field_id])) {
				wc_add_notice(esc_html__('Validation error: Please enter a valid "' . $this->get_option("field_label"), 'payday') . '"', 'error');
			}
		}
		// If the field is required and the field is empty, display an error message
		if ($this->get_option("require_field_checkout") == "yes" && !$_POST[$this->field_id]) {
			wc_add_notice(esc_html__('Validation error: Please enter a valid "' . $this->get_option("field_label"), 'payday') . '"', 'error');
		}
		// If the field is not required and the field is not empty, validate the field
		if ($this->get_option("require_field_checkout") == "yes" && $_POST[$this->field_id]) {
			if (!preg_match('/[0-7]\d[01]\d{3}[-]*\d{3}[09]/', $_POST[$this->field_id])) {
				wc_add_notice(esc_html__('Validation error: Please enter a valid "' . $this->get_option("field_label"), 'payday') . '"', 'error');
			}
		}
	}

	// Update the user meta with field value
	public function checkout_update_user_meta($user_id)
	{
		if ($user_id && $_POST[$this->field_id])
			update_user_meta($user_id, $this->field_id, esc_attr($_POST[$this->field_id]));
	}

	public function checkout_update_order_meta($order_id, $data)
	{
		if (isset($data[$this->field_id])) {
			$field_value = $data[$this->field_id];
			$order = wc_get_order($order_id);
			$order->update_meta_data($this->field_id, $field_value);
			$order->save();
		}
	}

	// Process payment and create a claim in Payday
	public function process_payment($order_id)
	{
		global $woocommerce;
		// $order = new WC_Order($order_id);
		$order = wc_get_order($order_id);

		// Check if the order is being processed or has already been processed
		// If so, redirect to the order confirmation page
		$order_model = new Payday_Order_Model();
		$order_job_status = $order_model->get_order_job_status($order_id);

		if ($order_job_status === null) {
			Payday_Logger::log("Order #" . $order_id . " does not have a job status. Creating one with status 'Processing'", "info");
			$order_model->create_order_job_status($order_id, null, 'Processing');
		} else {
			if ($order_job_status->job_status === "Processing" || $order_job_status->job_status === "Successful") {
				Payday_Logger::log('Order #' . $order_id . ' is already being processed or has already been processed. Job status: ' . $order_job_status->job_status, "info");
				return array(
					'result' => 'success',
					'redirect' => $this->get_return_url($order)
				);
			}
			Payday_Logger::log("Order #" . $order_id . " has a job status. Job status: " . $order_job_status->job_status . ". Updating to 'Processing'", "info");
			$order_model->update_order_job_status($order_id, null, 'Processing');
		}

		try {
			// Create invoice in Payday
			$invoice_model = new Payday_Invoice_Model();
			$payment_method = $order->get_payment_method($context = 'view');
			$selected_payday_payment_type_id = get_option('payday_payment_method_' . $payment_method, 'None');
			$selected_payday_payment_type_id = Payday_Utils::is_null_or_empty_string($selected_payday_payment_type_id) ? 'None' : $selected_payday_payment_type_id;

			if ($selected_payday_payment_type_id !== 'None') {
				$created_invoice = $invoice_model->create_invoice_in_payday_from_wc_order($order, Payday_Invoice_Status::PAID, $selected_payday_payment_type_id);
			} else {
				$created_invoice = $invoice_model->create_invoice_in_payday_from_wc_order($order, Payday_Invoice_Status::SENT);
			}

			if ($created_invoice === null) {
				// If unsuccessful
				// Set Order Job Status to Unsuccessful
				Payday_Logger::log("Order #" . $order_id . " was not successfully created in Payday. Updating job status to 'Unsuccessful' and displaying error message to customer.", "info");
				$order_model->update_order_job_status($order_id, null, 'Unsuccessful');

				// Display an error message to customer
				$error_message = __("Unable to process order using: ", 'payday') . self::get_title();
				wc_add_notice(__('Payment error:', 'payday') . $error_message, 'error');

				// Redirect back to checkout by returning null
				return null;
			}

			// Remove action so the on order_status_changed wont be called
			// Priority may be different for different environments.
			$priority = has_action('woocommerce_order_status_changed', array(Payday_Webhooks::instance(), 'action_woocommerce_order_status_changed'));
			if ($priority !== false) {
				remove_action("woocommerce_order_status_changed", array(Payday_Webhooks::instance(), 'action_woocommerce_order_status_changed'), $priority);
			}

			// Set Order Job Status to Successful
			Payday_Logger::log("Invoice for order #" . $order_id . " was successfully created in Payday. Updating job status to 'Successful'", "info");
			// No longer will we receive payday_invoice_number, so we put null instead
			$order_model->update_order_job_status($order_id, null, 'Successful');

			// Add Order Note
			$billing_ssn = $order->get_meta($this->get_field_id());
			if (!Payday_Utils::is_null_or_empty_string($billing_ssn) && $this->enabled && $order->get_payment_method($context = 'view') === $this->get_id()) {
				$message = sprintf(__("Invoice was created in Payday.\nClaim was sent to SSN: %s", 'payday'), $billing_ssn);
			} else {
				$message = __("Invoice was created in Payday", 'payday');
			}
			$order->add_order_note($message);
			Payday_Logger::log("Invoice for order #" . $order_id . " was successfully created in Payday. Added order note: " . $message, "info");


			// Update Order Status
			$new_status = $this->get_option("status_after_process");
			Payday_Logger::log("Invoice for order #" . $order_id . " was successfully created in Payday. Updating order status to '" . $new_status . "'", "info");
			$order->update_status($new_status, esc_html__('Awaiting payment', 'payday'));
			$order->save();

			// Set back to previous priority
			add_action("woocommerce_order_status_changed", array(Payday_Webhooks::instance(), 'action_woocommerce_order_status_changed', $priority, 4));

			// Remove items from cart
			$woocommerce->cart->empty_cart();
		} catch (Exception $e) {
			$order_job_status = $order_model->get_order_job_status($order_id);

			if ($order_job_status === null) {
				Payday_Logger::log("Order #" . $order_id . " was successfully created in Payday. Updating job status to 'Unsuccessful' and displaying error message to customer.", "info");
				$order_model->create_order_job_status($order_id, null, 'Unsuccessful');
			} else {
				Payday_Logger::log("Order #" . $order_id . " was not successfully created in Payday. Updating job status to 'Unsuccessful' and displaying error message to customer.", "info");
				$order_model->update_order_job_status($order_id, null, 'Unsuccessful');
			}
		}


		// Redirect to Thank you Page
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url($order)
		);
	}

	/**
	 * Handles the save_post_shop_order action.
	 * Save the custom field value during admin order update
	 * 
	 * @param int $post_id
	 */
	public function action_save_post_shop_order($post_id)
	{
		if (defined('DOING_AJAX') && DOING_AJAX)
			return $post_id;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $post_id;

		if (!current_user_can('edit_shop_order', $post_id))
			return $post_id;

		$order = wc_get_order($post_id);

		if (!isset($_POST['_billing_' . $this->get_field_id()])) {
			return $post_id;
		}

		$ssn = sanitize_text_field($_POST['_billing_' . $this->get_field_id()]);
		$payment_method = $_POST['_payment_method'];

		if ($payment_method == $this->id && !preg_match('/[0-7]\d[01]\d{3}[-]*\d{3}[09]/', $ssn)) {
			// TODO: Send error message
			return $post_id;
		}
		$order->update_meta_data($this->get_field_id(), $ssn);
		unset($_POST['_billing_' . $this->get_field_id()]);
		// $order->save_meta_data();
		$order->save();
	}

	/**
	 * Handles the woocommerce_admin_billing_fields filter
	 * 
	 * @param array $fields
	 */
	public function add_ssn_field_to_admin_billing_fields($fields)
	{
		global $post;
		if (isset($post)) {
			$order = wc_get_order($post->ID);
			// $order_id = $order->get_id();

			$sorted_fields = [];
			$field_id = $this->field_id;
			$ssn = $order->get_meta($field_id, true);
			// $ssn = get_post_meta($order_id, $field_id, true);
			if (!isset($ssn) || $ssn === "") {
				// $ssn = get_post_meta($order_id, '_billing_' . $field_id, true);
				$ssn = $order->get_meta('_billing_' . $field_id, true);
			}

			foreach ($fields as $key => $values) {
				$sorted_fields[$key] = $values;
				if ($key === 'phone') {
					$sorted_fields[$field_id] = array(
						'label' => __('SSN', 'payday'),
						'show' => true,
						// 'value' => get_post_meta($order_id, $field_id, true),
						'value' => $order->get_meta($field_id, true)
					);
				}
			}

			return $sorted_fields;
		} else {
			return $fields;
		}
	}

	/**
	 * Handels the woocommerce_email_order_meta_keys filter
	 * 
	 * @param array $keys
	 */
	public function email_order_meta_keys($keys)
	{
		if ($this->get_option("enabled") == "yes" || $this->get_option("display_field_email") == "yes") {
			$keys[] = $this->field_id;
		}

		return $keys;
	}
}

function payday_claim_service_payment_gateway_init()
{
	// Only include if WooCommerce is active
	if (class_exists('WC_Payment_Gateway')) {
		add_filter('woocommerce_payment_gateways', array('Payday_Claim_Service_Payment_Gateway', 'add_gateway_class'));
	}
}

add_action('plugins_loaded', 'payday_claim_service_payment_gateway_init', 0);
