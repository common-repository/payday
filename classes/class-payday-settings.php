<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-auth-model.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-settings-model.php';
require_once PAYDAY_DIR_PATH . 'enums/enum-payday-order-status.php';

class Payday_Settings
{
	use Payday_Singleton_Trait;

	const PAGE = "payday-settings";
	const OPTION_GROUP = 'payday_option_group';

	const SETTINGS_SECTION_1 = 'payday_payment_methods_mapping_section';
	const SETTINGS_SECTION_2 = 'payday_create_invoice_on_order_status_section';
	const SETTINGS_SECTION_3 = 'payday_invoice_settings_section';
	const SETTINGS_SECTION_4 = 'payday_products_section';


	public function register_settings()
	{
		// Add a settings section for the payment methods mapping.
		$this->register_payment_methods_mapping();

		// Add a settings section for the order status mapping.
		$this->register_payment_methods_create_invoice_on_order_status_mapping();

		// Add a settings section for additional invoice settings.
		$this->register_additional_invoice_settings_section();
	}

	/**
	 * Adds a section to the settings page for mapping WooCommerce payment methods to Payday payment types.
	 *
	 * This function creates a new section on the settings page where each active WooCommerce payment method
	 * is listed with a corresponding select field. This select field allows the user to choose a matching
	 * Payday payment type for each payment method. The settings for these fields are registered and can be
	 * saved from the settings page.
	 *
	 * @return void
	 */
	public function register_payment_methods_mapping()
	{
		// Add a settings section.
		add_settings_section(
			self::SETTINGS_SECTION_1, // ID used to identify this section and with which to register options.
			__("Payment method to sales account mapping", 'payday'), // Title to be displayed on the administration page
			// Callback used to render the description of the section
			function () {
				echo '<p>' . __('Ensure each WooCommerce payment method is mapped to the appropriate sales account in Payday.', 'payday') . '</p>';
				echo '<p>' . __('If a matching sales account does not exist in Payday for a particular WooCommerce payment method, you may need to create a new one.', 'payday') . '</p>';
			},
			self::PAGE // The page where to display the section
		);

		// Get WooCommerce payment methods and Payday payment types.
		/** @var WC_Payment_Gateways $wc_payment_gateways */
		$wc_payment_gateways = WC_Payment_Gateways::instance();
		$woocommerce_payment_gateways = $wc_payment_gateways->payment_gateways();

		$settings_model = new Payday_Settings_Model();

		// Get Payday payment types.
		$payday_payment_types = $settings_model->get_all_payment_types();

		// Define the select options for the payment types.
		$payment_type_select_options = array();
		$payment_type_select_options[__('None', 'payday')] = 'None';
		if (isset($payday_payment_types)) {
			foreach ($payday_payment_types as &$payment_type) {
				// key => value
				if (isset($payment_type) and gettype($payment_type) === "object") {
					$payment_type = get_object_vars($payment_type);
					$payment_type_select_options[$payment_type['title']] = $payment_type['id'];
				}
				if (isset($payment_type) and gettype($payment_type) === "array") {
					$payment_type_select_options[$payment_type['title']] = $payment_type['id'];
				}
			}
		}

		foreach ($woocommerce_payment_gateways as $payment_gateway) {
			if ($payment_gateway->enabled == 'no') {
				continue;
			}
			$id = $payment_gateway->id;

			$title = $payment_gateway->get_method_title();
			if ($title == "") {
				try {
					$title = $payment_gateway->title;
				} catch (Exception $e) {
					$title = "";
				}
			}
			$field_id = 'payday_payment_method_' . $id;


			if ($payment_gateway->id === Payday_Claim_Service_Payment_Gateway::instance()->get_id()) {
				add_settings_field(
					$field_id,
					esc_html__($title, 'payday'),
					array($this, 'display_select_field'),
					self::PAGE,
					self::SETTINGS_SECTION_1,
					array(
						'id' => $field_id,
						'name' => esc_html__($field_id, 'payday'),
						'disabled' => true, // Disable the select field for the Payday payment gateway.
						'title' => esc_html__("For the bank claim service gateway, there's no need to map a sales account. Just make sure to manually record the payment in Payday once the customer has completed the transaction.", 'payday'),
						'options' => $payment_type_select_options
					)
				);
			} else {
				add_settings_field(
					$field_id,
					esc_html__($title, 'payday'),
					array($this, 'display_select_field'),
					self::PAGE,
					self::SETTINGS_SECTION_1,
					array(
						'id' => $field_id,
						'name' => esc_html__($field_id, 'payday'),
						'title' => esc_html__("Select the WooCommerce payment method that corresponds to this sales account in Payday.", 'payday'),
						'options' => $payment_type_select_options
					)
				);
			}
			register_setting(self::OPTION_GROUP, $field_id);
		}
	}

	/**
	 * Adds a section to the settings page for mapping WooCommerce order statuses to actions.
	 *
	 * This function creates a new section on the settings page where each active WooCommerce payment method
	 * is listed with a corresponding select field. This select field allows the user to choose a corresponding
	 * WooCommerce Order Status, which when achieved, will trigger an action to create a new invoice in Payday and mark it as paid.
	 * The settings for these fields are registered and can be saved from the settings page.
	 *
	 * @return void
	 */
	public function register_payment_methods_create_invoice_on_order_status_mapping()
	{
		// Add a settings section.
		add_settings_section(
			self::SETTINGS_SECTION_2, // ID used to identify this section and with which to register options.
			esc_html__('Invoice generation', 'payday'), // A more concise and self-explanatory title.
			function () {
				echo '<p>' . esc_html__('Assign the WooCommerce Order Status that initiates the following action:', 'payday') . '</p>';
				echo '<p>' . esc_html__('When the chosen status is reached, an invoice will be automatically generated in Payday and marked as paid.', 'payday') . '</p>';
			},
			self::PAGE // The page where to display the section
		);

		// Get WooCommerce payment gateways and Payday payment types.
		$wc_payment_gateways = WC_Payment_Gateways::instance();

		/** @var WC_Payment_Gateway[]  $woocommerce_payment_gateways*/
		$woocommerce_payment_gateways = $wc_payment_gateways->payment_gateways();

		foreach ($woocommerce_payment_gateways as $payment_gateway) {
			if ($payment_gateway->enabled == 'no') {
				continue;
			}

			$id = $payment_gateway->id;
			$title = $payment_gateway->get_method_title();
			if ($title == "") {
				try {
					$title = $payment_gateway->title;
				} catch (Exception $e) {
					$title = "";
				}
			}

			$field_id = 'payday_payment_method_' . $id . '_create_invoice_on_action';
			$order_statuses = wc_get_order_statuses();

			if ($payment_gateway->id === Payday_Claim_Service_Payment_Gateway::instance()->get_id()) {
				add_settings_field(
					$field_id,
					esc_html__($title, 'payday'),
					array($this, 'display_select_field'),
					self::PAGE,
					self::SETTINGS_SECTION_2,
					array(
						'id' => $field_id,
						'name' => $field_id,
						'disabled' => true, // This field is disabled because it is not editable.
						'title' => esc_html__('Invoices are automatically generated, marked as SENT, and a claim is created in the customer\'s bank when an order is made through the Payday Claim Service. This field is not editable.', 'payday'),
						'options' => [
							__("None", 'payday') => Payday_Order_Status::NONE,
							__('Do not send', 'payday') => Payday_Order_Status::DO_NOT_SEND,
							__($order_statuses["wc-processing"], 'woocommerce') => Payday_Order_Status::PROCESSING,
							__($order_statuses["wc-completed"], 'woocommerce') => Payday_Order_Status::COMPLETED
						]

					)
				);
			} else {
				add_settings_field(
					$field_id,
					esc_html__($title, 'payday'),
					array($this, 'display_select_field'),
					self::PAGE,
					self::SETTINGS_SECTION_2,
					array(
						'id' => $field_id,
						'name' => $field_id,
						'title' => esc_html__('Specify the WooCommerce Order Status that will initiate the action', 'payday'),
						'options' => [
							__("None", 'payday') => Payday_Order_Status::NONE,
							__('Do not send', 'payday') => Payday_Order_Status::DO_NOT_SEND,
							__($order_statuses["wc-processing"], 'payday') => Payday_Order_Status::PROCESSING,
							__($order_statuses["wc-completed"], 'payday') => Payday_Order_Status::COMPLETED,
						]
					)
				);
			}


			register_setting(self::OPTION_GROUP, $field_id);
		}
	}

	/**
	 * Adds an additional section for invoice settings to the settings page.
	 *
	 * This function creates a new section on the settings page where specific invoice related settings can be defined
	 * like the option to email the invoice as a PDF when it's created and setting invoice date and due date.
	 * The settings for these fields are registered and can be saved from the settings page.
	 *
	 * @return void
	 */
	public function register_additional_invoice_settings_section()
	{
		add_settings_section(
			self::SETTINGS_SECTION_3,
			esc_html__('Additional invoice settings', 'payday'),
			function () {
			},
			self::PAGE
		);

		$send_email_on_invoice_create_field_id = "payday_send_email_on_invoice_create";
		add_settings_field(
			$send_email_on_invoice_create_field_id,
			esc_html__("Send invoice as PDF to customer", "payday"),
			array($this, 'display_select_field'),
			self::PAGE,
			self::SETTINGS_SECTION_3,
			array(
				'id' => $send_email_on_invoice_create_field_id,
				'name' => $send_email_on_invoice_create_field_id,
				'title' => esc_html__('Choose whether to send the invoice as a PDF via email to the customer upon creation.', 'payday'),
				'options' => [
					esc_html__('Yes', 'payday') => "yes",
					esc_html__('No', 'payday') => "no",
				]
			)
		);

		register_setting(self::OPTION_GROUP, $send_email_on_invoice_create_field_id);

		$invoice_date_option = "payday_invoice_date_option";
		add_settings_field(
			$invoice_date_option,
			esc_html__("Setting for invoice creation and due dates", "payday"),
			array($this, 'display_select_field'),
			self::PAGE,
			self::SETTINGS_SECTION_3,
			array(
				'id' => $invoice_date_option,
				'name' => $invoice_date_option,
				'title' => esc_html__('Choose whether the invoice creation and due date should be set to the initial order date or the date the invoice is actually generated.', 'payday'),
				'options' => [
					esc_html__('Set dates to initial order date', 'payday') => "0",
					esc_html__('Set dates to the day the invoice is generated', 'payday') => "1",
				]
			)
		);

		register_setting(self::OPTION_GROUP, $invoice_date_option);
	}

	/**
	 * Displays a HTML form field with the given arguments.
	 *
	 * @param array $args Arguments to construct the HTML form field.
	 * @return void
	 */
	public function display_field($args)
	{
		if ($args['type'] == 'password') {
		}
		echo "<input class=" . esc_html($args['class']) . " id=" . esc_html($args['id']) . " name=" . esc_html($args['name']) . " type='" . esc_html($args['type']) . "' value='" . esc_html($args['value']) . "' />";
	}

	/**
	 * Displays a HTML select field with the given arguments.
	 *
	 * @param array $args Arguments to construct the HTML select field.
	 * @return void
	 */
	public function display_select_field($args)
	{
		// Determine if the select field should be disabled.
		$disabled = '';
		if (isset($args['disabled']) && $args['disabled']) {
			$disabled = ' disabled';
		}
?>
		<select id="<?php echo esc_html($args['id']) ?>" name="<?php echo esc_html($args['name']) ?>" title="<?php echo esc_html($args['title']) ?>" <?php echo $disabled; ?>>
			<?php
			foreach ($args['options'] as $key => $value) {
			?>
				<option value="<?php echo esc_html($value); ?>" <?php echo selected(get_option(esc_html($args['name'])), esc_html($value)); ?>><?php echo esc_html__($key, 'payday'); ?></option>
			<?php
			}
			?>
		</select>
<?php
	}
}
