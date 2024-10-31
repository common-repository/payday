<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/gateway/class-payday-invoice-gateway.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-customer-model.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-customer-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-customer-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-invoice-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-invoice-response.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-invoice-line-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/response/class-payday-invoice-line-response.php';

class Payday_Invoice_Model
{
	const INVOICE_DESCRIPTION_SEPARATOR = ' | ';

	/**
	 * Create an invoice in Payday from a WC_Order
	 * 
	 * @param WC_Order $order
	 * @param string $invoice_status
	 * @param string $payment_type_id
	 * 
	 * @return Payday_Invoice_Response|null The response from Payday if the invoice was created successfully, null otherwise
	 */
	public function create_invoice_in_payday_from_wc_order($order, string $invoice_status, string $payment_type_id = null)
	{
		$customer_model = new Payday_Customer_Model();
		$payday_customer_response = $customer_model->create_or_get_payday_customer_from_wc_order($order);

		if ($payday_customer_response == null) {
			// Add a order not and log the error
			Payday_Logger::log("Unable to create an invoice in Payday due to failure to create customer in Payday", "error");
			$message = sprintf(__("Unable to create an invoice in Payday due to failure to create customer in Payday", 'payday'));
			$order->add_order_note($message);
			return null;
		}

		// Update the customer address if it has changed
		$customer_address = $payday_customer_response->address;
		$customer_zip = $payday_customer_response->zip_code;
		$customer_city = $payday_customer_response->city;
		$customer_country = $payday_customer_response->country;
		$customer_due_date_default_days_after = $payday_customer_response->due_date_default_days_after;
		$customer_final_due_date_default_days_after = $payday_customer_response->final_due_date_default_days_after;


		$billing_data = $customer_model->get_billing_data($order);
		$billing_address = $customer_model->get_address($billing_data);
		$billing_zip = sanitize_text_field($order->get_billing_postcode());
		$billing_city = sanitize_text_field($order->get_billing_city());
		$billing_country = $customer_model->get_country($billing_data);

		if ($customer_address != $billing_address || $customer_zip != $billing_zip || $customer_city != $billing_city || $customer_country != $billing_country) {

			$payday_customer_response = $customer_model->update_customer([
				'id' => $payday_customer_response->id,
				'address' => $billing_address,
				'zip_code' => $billing_zip,
				'city' => $billing_city,
				'country' => $billing_country
			]);
		}

		if ($payday_customer_response == null) {
			// Add a order not and log the error
			Payday_Logger::log("Unable to create an invoice in Payday due to failure to updating customer address in Payday", "error");
			$message = sprintf(__("Unable to create an invoice in Payday due to failure to updating customer address in Payday", 'payday'));
			$order->add_order_note($message);
			return null;
		}

		$payday_customer = new Payday_Customer_Request([
			'id' => $payday_customer_response->id
		]);

		$data = $order->get_data();
		$currency_code = $order->get_currency();
		$payment_method = $order->get_payment_method($context = 'view');
		$payment_method_title = $order->get_payment_method_title($context = 'view');

		// Check if Claim is Enabled in the Payday Payment Gateway
		$claim_enabled = $payment_method === Payday_Claim_Service_Payment_Gateway::instance()->get_id() && Payday_Claim_Service_Payment_Gateway::instance()->get_option('enabled') == "yes";
		$billing_ssn = $order->get_meta(Payday_Claim_Service_Payment_Gateway::instance()->get_field_id());

		// Generate the invoice description
		$site_title = get_bloginfo('name', 'raw');
		$invoice_description = $site_title;
		$invoice_description .= self::INVOICE_DESCRIPTION_SEPARATOR . 'Pöntunarnúmer: ' . $order->get_order_number();
		if (!Payday_Utils::is_null_or_empty_string($data['customer_note'])) {
			$invoice_description .= self::INVOICE_DESCRIPTION_SEPARATOR . 'Athugasemdir aðila: ' . sanitize_text_field($data['customer_note']);
		}
		$invoice_description .= self::INVOICE_DESCRIPTION_SEPARATOR . 'Greiðslumáti: ' . $payment_method_title;

		$invoice_description = substr($invoice_description, 0, 1024); // Truncate the description if it exceeds 1024 characters

		// Get Order Dates
		$date_paid = $order->get_date_paid();
		$date_created = $order->get_date_created();

		$payday_invoice_date_option = get_option('payday_invoice_date_option');
		$invoice_date = new DateTime();

		if ($payday_invoice_date_option === "1") {
			// The invoice date set to the date when the request is made to create an invoice
			// $invoice_date is already set to today's date by default
		} else {
			// The invoice date set to the date when the order was created
			// WC_DateTime extends DateTime
			if (isset($date_created) && $date_created instanceof DateTime) {
				$invoice_date = clone $date_created;
			}
		}

		// Set the due date, start of with the invoice date
		$invoice_due_date = clone $invoice_date;

		if ($customer_due_date_default_days_after) {
			$invoice_due_date->modify("+$customer_due_date_default_days_after days");
		}

		// Set final due date, start of with the due date
		$final_due_date = clone $invoice_due_date;

		if ($customer_final_due_date_default_days_after) {
			$final_due_date->modify("+$customer_final_due_date_default_days_after days");
		} else if ($payment_method === Payday_Claim_Service_Payment_Gateway::instance()->get_id()) {
			$days_until_final_due_date = Payday_Claim_Service_Payment_Gateway::instance()->get_option('days_until_final_due_date');
			if (intval($days_until_final_due_date) > 0) {
				$final_due_date->modify("+$days_until_final_due_date days");
			}
		}

		// Check if the customer has provided an email address and the setting is set to send email on invoice create
		$send_email_option = get_option('payday_send_email_on_invoice_create');
		$invoice_send_email = false;
		if ($send_email_option === 'yes' && !Payday_Utils::is_null_or_empty_string($payday_customer_response->email) && $payday_customer_response->email !== 'openpos@example.com') {
			// If so, set the send email flag to true
			$invoice_send_email = true;
		}

		// Create an array of invoice line request objects from the order
		$invoice_lines = $this->create_invoice_lines_from_order($order);

		$order_grand_total =  $order->get_total() - $order->get_total_refunded();
		$order_subtotal = $order->get_subtotal();
		$calculated_order_grand_total = 0;

		// Calculate order subtotal
		foreach ($invoice_lines as $invoice_line) {
			$calculated_order_grand_total += Payday_Utils::calculate_grand_total(
				$invoice_line->unit_price_including_vat,
				$invoice_line->discount_percentage,
				$invoice_line->quantity,
				$currency_code === "ISK" ? 0 : 2
			);
		}

		// Adjust for minor differences caused by rounding errors
		$order_grand_total = round($order_grand_total, 0);
		$calculated_order_grand_total = round($calculated_order_grand_total, 0);
		$rounding_difference = round($order_grand_total - $calculated_order_grand_total, 0);

		// Compare the totals
		if ($order_grand_total != 0 && $rounding_difference != 0) {

			// Get the highest tax rate from the invoice lines 
			$highest_tax_rate = 0.0;
			if (get_option('woocommerce_calc_taxes') === 'yes') {
				foreach ($invoice_lines as $invoice_line) {
					// Check if vat_percentage is set and is not null
					if (isset($invoice_line->vat_percentage) && $invoice_line->vat_percentage !== null) {
						if ($invoice_line->vat_percentage > $highest_tax_rate) {
							$highest_tax_rate = $invoice_line->vat_percentage;
						}
					}
				}
			}

			$rounding_difference = round($order_grand_total - $calculated_order_grand_total, 0);

			// If the totals are not equal, handle the discrepancy here
			$invoice_lines[] = new Payday_Invoice_Line_Request([
				'position' => count($invoice_lines) + 1,
				'description' =>  __('Rounding Amount', 'payday'),
				'quantity' => 1.0,
				'unit_price_including_vat' => round($rounding_difference, 4),
				'vat_percentage' => $highest_tax_rate,
				'discount_percentage' => 0.0,
			]);
		}

		// Create an invoice in Payday
		$invoice_request = new Payday_Invoice_Request([
			'customer' =>  $payday_customer,
			'description' => $invoice_description,
			'invoice_date' => $invoice_date->format('Y-m-d'),
			'due_date' => $invoice_due_date->format('Y-m-d'),
			'final_due_date' => $final_due_date->format('Y-m-d'),
			'source:' => '3',
			'send_email' => $invoice_send_email,
			'lines' => $invoice_lines,
			'status' => $invoice_status,
		]);

		// Checking if currency code is set and assigning it to the invoice
		if (isset($currency_code)) {
			$invoice_request->currency_code = $currency_code;
		} else {
			$invoice_request->currency_code = 'ISK';
		}

		// Checking if createClaim should be true
		if (isset($billing_ssn) && $billing_ssn !== "" && $claim_enabled) {
			$invoice_request->create_claim = true;
		}

		// Determining the status and paid_date of the invoice
		if (isset($date_paid) && $date_paid != null) {
			$invoice_request->status = Payday_Invoice_Status::PAID;
			$invoice_request->paid_date = $date_paid->date('Y-m-d');
		} else {
			$invoice_request->status = Payday_Invoice_Status::SENT;
			$invoice_request->paid_date = $date_created->date('Y-m-d');
		}

		// Determining the payment type of the invoice
		if (isset($billing_ssn) && $billing_ssn !== "" && $claim_enabled) {
			$invoice_request->payment_type = null;
		} else {
			$invoice_request->payment_type = $payment_type_id;
		}

		$invoice_gateway = new Payday_Invoice_Gateway();
		$invoice_response = $invoice_gateway->create_invoice($invoice_request);

		return $invoice_response;
	}


	/** 
	 * Create an array of invoice line requests from a WooCommerce order.
	 * 
	 * @param WC_Order $order The WooCommerce order object.
	 * 
	 * @return Payday_Invoice_Line_Request[] Array of generated invoice line requests.
	 */
	private function create_invoice_lines_from_order($order)
	{
		// Initialize the array of invoice lines
		$invoice_lines = [];

		// Initialize the position index
		$index = 1;

		// Iterate through each item in the order
		foreach ($order->get_items() as $item_id => $item) {
			// Depending on the type of item, handle it differently
			if ($item->is_type('line_item')) {
				// Get the order meta data
				$op_order = $order->get_meta('_op_order');
				if ($op_order != null) {
					// If the order meta data exists, generate an invoice line from the op line item
					$invoice_lines[] = $this->create_invoice_line_request_from_op_line_item($item, $index, $order);
				} else {
					// If the order meta data doesn't exist, generate an invoice line from the product line item
					$invoice_lines[] = $this->create_invoice_line_request_from_product_line_item($item, $index, $order);
				}

				// Increment the position index
				$index++;
			}
		}

		// Add all shipping methods as invoice lines
		$shipping_methods = $order->get_shipping_methods();
		foreach ($shipping_methods as $shipping) {
			$invoice_lines[] = $this->create_invoice_line_request_from_shipping_line_item($shipping, $index, $order);
			$index++;
		}

		/** @var WC_Order_item_Fee $fees */
		$fees = $order->get_fees();

		if (!empty($fees)) {
			foreach ($fees as $fee) {
				// Skip POS Cart Discount fee
				if ($fee->get_name() === 'POS Cart Discount') {
					continue;
				}
				$invoice_lines[] = $this->create_invoice_line_request_from_fee_line_item($fee, $index, $order);
				$index++;
			}
		}

		// Add all refunds as invoice lines
		$refunds = $order->get_refunds();
		foreach ($refunds as $refund) {
			$invoice_lines[] = $this->create_invoice_line_request_from_refund($refund, $index, $order);
			$index++;
		}

		// Return the array of invoice lines
		return $invoice_lines;
	}

	/**
	 * Create an invoice line request from a WooCommerce product line item.
	 *
	 * @param WC_Order_Item_Product $item The product line item.
	 * @param int $invoice_line_position The position of the line item in the invoice.
	 * @param WC_Order $order The order the line item belongs to.
	 * @return Payday_Invoice_Line_Request The SKU of the product associated with the line item.
	 */
	private function create_invoice_line_request_from_product_line_item($item, $invoice_line_position, $order)
	{
		// Get the product from the item
		/** @var WC_Product $product */
		$product = $item->get_product();

		// Get product SKU
		$sku = $product->get_sku();

		// Get tax rate
		$tax_rate = 0.0;
		if (get_option('woocommerce_calc_taxes') === 'yes') {
			$tax_rate = floatval($this->get_tax_rate($item));
		}

		// Get quantity
		$quantity = $item->get_quantity();

		// Get currency code
		$currency_code = $order->get_currency();

		// Calculate the total price of the line item
		$total_price_excluding_tax = $item->get_total();
		$total_tax = $item->get_total_tax();
		$total_price_including_tax = $total_price_excluding_tax + $total_tax;

		$final_unit_price_including_tax = $total_price_including_tax / $quantity;
		$final_unit_price_including_tax = round($final_unit_price_including_tax, 4);

		// Get discount percentage
		if (!isset($currency_code) || $currency_code === "ISK") {
			// Get regular and sales price
			// Always as ISK
			$unit_regular_price_including_tax = floatval($product->get_regular_price());
			// Incase the regular price is lower than the sales price, set the regular price to the sales price
			if ($unit_regular_price_including_tax < $final_unit_price_including_tax) {
				$unit_regular_price_including_tax = $final_unit_price_including_tax;
			}
			$final_discount_percentage = $this->calculate_discount_percentage($unit_regular_price_including_tax, $final_unit_price_including_tax);
		} else {
			$line_discount = $item->get_subtotal() - $item->get_total();
			$line_discount_per_unit = round($line_discount / $quantity, 4); // TODO: Find a way to get the currency rate and replae with unit_regular_price_including_tax * $currency_rate
			$unit_regular_price_including_tax = $final_unit_price_including_tax + $line_discount_per_unit;
			// Incase the regular price is lower than the sales price, set the regular price to the sales price
			if ($unit_regular_price_including_tax < $final_unit_price_including_tax) {
				$unit_regular_price_including_tax = $final_unit_price_including_tax;
			}
			$final_discount_percentage = $this->calculate_discount_percentage($unit_regular_price_including_tax, $final_unit_price_including_tax);
		}

		$description = $this->generate_invoice_line_description($item);

		// Create the invoice line request
		$invoice_line_request = new Payday_Invoice_Line_Request([
			'position' => $invoice_line_position,
			'description' => $description,
			'quantity' => floatval($quantity),
			'unit_price_including_vat' => $unit_regular_price_including_tax,
			'vat_percentage' => $tax_rate,
			'sku' => $sku,
			'discount_percentage' => $final_discount_percentage
		]);

		return $invoice_line_request;
	}

	/**
	 * Create an invoice line request from an OpenPOS product line item.
	 *
	 * @param WC_Order_Item_Product $item The product line item.
	 * @return Payday_Invoice_Line_Request The SKU of the product associated with the line item.
	 */
	private function create_invoice_line_request_from_op_line_item($item, $index, $order)
	{
		$op_order = $order->get_meta('_op_order');
		$op_order_final_discount_amount = $op_order['final_discount_amount'];
		$op_order_sub_total_incl_tax = $op_order['sub_total_incl_tax'];
		$op_chart_discount_percentage = $op_order_sub_total_incl_tax == 0 ? 0 : ($op_order_final_discount_amount / $op_order_sub_total_incl_tax) * 100;
		$op_chart_discount_percentage_rounded = round($op_chart_discount_percentage, 2);

		$op_total_incl_tax = $op_order['total_incl_tax'];

		$line_item = $item->get_data();
		$line_item_name = $line_item['name'];
		$line_item_quantity = $line_item['quantity'];

		$isRefunding = $line_item_quantity < 0;
		$line_item_quantity_rounded = round($line_item_quantity, 2); // Payday compatible decimal format

		$price_ex_tax = $line_item['subtotal'];
		if (!Payday_Utils::is_null_or_empty_string($line_item['subtotal_tax'])) {
			$price_inc_tax = ($line_item['subtotal'] + $line_item['subtotal_tax']) / $line_item_quantity;
		} else {
			$price_inc_tax = $line_item['subtotal'] / $line_item_quantity;
		}

		$price_inc_tax = round($price_inc_tax, 4); // Payday compatible sql money format

		// GET SKU
		$item_sku = "";
		$product = wc_get_product($item->get_product_id());
		if ($product->product_type == 'variable' && $line_item['variation_id'] != 0) {
			$variation = new WC_Product_Variation($line_item['variation_id']);
			$item_sku = $variation->get_sku();
		} else {
			$p = $product->get_data();
			$item_sku = $p["sku"];
		}


		$op_item_data_details = $item->get_meta("_op_item_data_details");
		$op_item_price_excl_tax = $op_item_data_details["final_price"];
		$op_item_price_excl_tax_rounded = round($op_item_price_excl_tax, 4); // Payday compatible decimal format
		$op_item_final_discount_amount = $op_item_data_details["final_discount_amount"];

		// Calculate Tax Rate
		$tax = $line_item['total_tax'];
		$line_item_total = $line_item['subtotal'];

		$tax_rate = 0.0;
		if (get_option('woocommerce_calc_taxes') === 'yes') {
			$tax_rate = Payday_Utils::round_to_closest_vat_percentage($line_item_total == 0 ? 0 : round(($tax / $line_item_total) * 100, 0));
			$tax_rate = abs($tax_rate);
			$tax_rate = floatval($tax_rate);
		}

		// Calculate discount amount set on line
		$line_discount = $op_item_final_discount_amount / $line_item_quantity;

		// Calculate discount amount set on line plus discount amount set on order
		$final_discount_amount = $line_discount + (($op_item_price_excl_tax - $line_discount) * ($op_chart_discount_percentage / 100));

		// Calculate the final discount percentage
		$final_discount_amount_percentage = $price_inc_tax == 0 ? 0 : ($final_discount_amount / $op_item_price_excl_tax) * 100;
		$final_discount_amount_percentage_rounded = round($final_discount_amount_percentage, 2); // Payday compatible decimal format

		// Add line item to request data
		return new Payday_Invoice_Line_Request([
			'position' => $index,
			'description' => $line_item_name,
			'quantity' => $isRefunding ? $line_item_quantity_rounded * -1.0 : $line_item_quantity_rounded,
			// 'unit_price_excluding_vat' => $isRefunding ? $op_item_price_excl_tax * -1 : $op_item_price_excl_tax,
			'unit_price_including_vat' => $isRefunding ? $price_inc_tax * -1.0 : $price_inc_tax,
			'vat_percentage' => $tax_rate,
			'sku' => $item_sku,
			'discount_percentage' => $final_discount_amount_percentage_rounded
		]);
	}


	/**
	 * Create an invoice line request from a WooCommerce shipping line item.
	 *
	 * @param WC_Order_Item_Shipping $item The shipping line item.
	 * @param int $invoice_line_position The position of the line item in the invoice.
	 * @param WC_Order $order The order the line item belongs to.
	 * @return Payday_Invoice_Line_Request The generated invoice line request.
	 */
	private function create_invoice_line_request_from_shipping_line_item($item, $invoice_line_position, $order)
	{
		// Get shipping method title
		$shipping_method_title = $item->get_method_title();

		// Get total shipping cost (including tax)
		$total_price_excluding_tax = floatval($item->get_total());
		$total_tax = floatval($item->get_total_tax());
		$total_shipping_cost_including_tax = $total_price_excluding_tax + $total_tax;

		// Get tax rate (assuming you have a method to get tax rate from tax amount and total)
		$tax_rate = 0.0;
		if (get_option('woocommerce_calc_taxes') === 'yes') {
			$taxes = $item->get_taxes();
			if (!empty($taxes['total'])) {
				foreach ($taxes['total'] as $tax_rate_id => $tax_amount) {
					$tax_rate = WC_Tax::get_rate_percent_value($tax_rate_id);
				}
			}
		}

		// Initialize discount amount
		$discount_amount = 0.0;

		// Get applied coupons
		$coupons = $order->get_coupons();


		foreach ($coupons as $coupon) {
			$coupon_obj = new WC_Coupon($coupon->get_code());

			if ($coupon_obj->get_free_shipping()) {
				$discount_amount += $total_price_excluding_tax;  // Free shipping or percent discount
			} elseif ($coupon_obj->get_discount_type() == 'percent') {
				$discount_amount += ($coupon_obj->get_amount() / 100) * $total_price_excluding_tax;  // Percent discount
			} elseif ($coupon_obj->get_discount_type() == 'fixed_cart') {
				// Fixed cart discount do not apply to shipping, but can be used to free shipping
				if ($coupon_obj->get_free_shipping()) {
					$discount_amount += $total_price_excluding_tax;
				}
			}
		}

		$discount_percentage = 0.0;
		// Calculate discount percentage
		if ($total_price_excluding_tax > 0) {
			$discount_percentage = round(($discount_amount / $total_price_excluding_tax) * 100, 2);
		}

		$description = substr($shipping_method_title, 0, 1024); // Truncate the description if it exceeds 1024 characters

		// Create the invoice line request
		$invoice_line_request = new Payday_Invoice_Line_Request([
			'position' => $invoice_line_position,
			'description' => $description,
			'quantity' => 1.0,  // Quantity is always 1 for shipping cost
			'unit_price_including_vat' => $total_shipping_cost_including_tax,
			'vat_percentage' => $tax_rate,
			'sku' => '',  // SKU is typically not applicable for a shipping line
			'discount_percentage' => $discount_percentage  // Discount is typically not applicable for a shipping line
		]);

		return $invoice_line_request;
	}


	/**
	 * Create an invoice line request from a WooCommerce fee line item.
	 *
	 * @param WC_Order_Item_Fee $item The fee line item.
	 * @param int $invoice_line_position The position of the line item in the invoice.
	 * @param WC_Order $order The order the line item belongs to.
	 * @return Payday_Invoice_Line_Request The invoice line request.
	 */
	private function create_invoice_line_request_from_fee_line_item($item, $invoice_line_position, $order)
	{
		// Get fee name
		$fee_name = $item->get_name();

		// Get fee amount
		$fee_amount = floatval($item->get_total());

		// Get fee tax amount
		$fee_tax_amount = floatval($item->get_total_tax());

		// Calculate total fee amount including tax
		$fee_total_amount_including_tax = $fee_amount + $fee_tax_amount;

		$description = substr($fee_name, 0, 1024); // Truncate the description if it exceeds 1024 characters

		// Create the invoice line request
		$invoice_line_request = new Payday_Invoice_Line_Request([
			'position' => $invoice_line_position,
			'description' => $description,
			'quantity' => 1.0,
			'unit_price_including_vat' => $fee_total_amount_including_tax,
		]);

		return $invoice_line_request;
	}


	/**
	 * Create an invoice line request from a WooCommerce refund.
	 * 
	 * @param WC_Order_Refund $refund The refund.
	 * @param int $invoice_line_position The position of the line item in the invoice.
	 * @param WC_Order $order The order the line item belongs to.
	 * @return Payday_Invoice_Line_Request The invoice line request.
	 */
	private function create_invoice_line_request_from_refund($refund, $invoice_line_position, $order)
	{
		// Refund item details
		$total_refund_including_tax = $refund->get_total(); // amount is negative
		$total_refund_tax = $refund->get_total_tax();

		// Calculate total refund excluding tax
		$total_refund_excluding_tax = abs($total_refund_including_tax) - abs($total_refund_tax);

		// Calculate tax rate
		$tax_rate = 0;
		if (get_option('woocommerce_calc_taxes') === 'yes') {
			if ($total_refund_excluding_tax != 0) {
				$tax_rate = (abs($total_refund_tax) / $total_refund_excluding_tax) * 100;
			}
		}

		// Generate the description
		// get the refund ID
		$refund_id = $refund->get_id();

		// get the refund date and format it
		$refund_date = $refund->get_date_created();
		$refund_date_formatted = $refund_date->date_i18n('F j, Y, g:i a');

		// get the user who made the refund
		$refunded_by = get_userdata($refund->get_refunded_by());
		$refunded_by_name = $refunded_by ? $refunded_by->display_name : '';

		// get the refund reason
		$refund_reason = $refund->get_reason();
		$reason_text = $refund_reason ? sprintf(' - %s', $refund_reason) : '';

		$description = sprintf('%s%s', __("Refund", 'payday'), $reason_text);

		$description = substr($description, 0, 1024); // Truncate the description if it exceeds 1024 characters

		$invoice_line_request = new Payday_Invoice_Line_Request([
			'position' => $invoice_line_position,
			'description' => $description,
			'quantity' => 1.0,
			'unit_price_including_vat' => -1 * abs($total_refund_including_tax), // The amount is negative
			'vat_percentage' => Payday_Utils::round_to_closest_vat_percentage($tax_rate), // Adjust this if the refund includes tax
			'discount_percentage' => 0.0
		]);

		return $invoice_line_request;
	}

	/**
	 * Retrieve the tax rate for an order line item.
	 *
	 * @param WC_Order_Item_Product $item
	 * @return float
	 */
	private function get_tax_rate($item)
	{
		// Try to get the actual applied tax rate from the line item
		/** @var WC_Order_Item_Tax $taxes */
		$taxes = $item->get_taxes();
		if (!empty($taxes['total'])) {
			$tax_amount = array_sum($taxes['total']);
			$line_total = $item->get_total();
			if ($line_total != 0) {
				$tax_rate = ($tax_amount / $line_total) * 100;
				$tax_rate = Payday_Utils::round_to_closest_vat_percentage($tax_rate);
				$tax_rate = abs($tax_rate);
				$tax_rate = floatval($tax_rate);
				return $tax_rate;
			}
		}

		// If no tax rate found, return 0
		return 0;
	}

	/**
	 * Calculate the discount percentage.
	 *
	 * @param float $unit_regular_price_excluding_tax
	 * @param float $final_unit_price_including_vat
	 * @return float
	 */
	private function calculate_discount_percentage($unit_regular_price_including_tax, $final_unit_price_including_vat)
	{
		if ($unit_regular_price_including_tax > 0) {
			return abs(round((1 - ($final_unit_price_including_vat / $unit_regular_price_including_tax)) * 100, 2));
		}
		return 0;
	}

	/**
	 * Generate a description for an invoice line item.
	 *
	 * @param WC_Order_Item_Product $item
	 * @return string
	 */
	private function generate_invoice_line_description($item)
	{
		// Start with the product name
		/** @var string $description */
		$description = $item->get_name();
		$description = substr($description, 0, 1024); // Truncate the description if it exceeds 1024 characters
		return $description;
	}
}
