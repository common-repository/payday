<?php

// Exit if accessed directly.
if (!defined('ABSPATH'))
    exit;

final class  Payday_Utils
{
    /** Private constructor to prevent instantiation */
    private  function __construct()
    {
        // The constructor is empty since there is no need to initialize anything in a static class
    }

    /**
     * Check if the input string is null or an empty string
     * 
     * @param $str The string to check.
     * @return bool
     */
    public static function is_null_or_empty_string($str)
    {
        return (!isset($str) || trim($str) === '');
    }

    /**
     * Check if a string ends with a specific substring
     * 
     * @param string $string The main string.
     * @param string $endString The ending substring to check for.
     * @return bool
     */
    public static function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len === 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }

    /**
     * Check if all keys from an array exist in another array
     * 
     * @param array $keys The keys to check.
     * @param array $arr The array to search in.
     * @return bool
     */
    public static  function array_keys_exists(array $keys, array $arr)
    {
        return !array_diff_key(array_flip($keys), $arr);
    }

    /** 
     * Generate a new order note with specific details to be added to a order 
     *
     * @param object $order The order object.
     * @param string $payday_invoice_number The Payday invoice number.
     * @param string|null $billing_ssn The billing Social Security Number (optional).
     * @return string
     */
    public static function generate_order_note($order, $payday_invoice_number, $billing_ssn = null)
    {
        if (!self::is_null_or_empty_string($billing_ssn)) {
            return sprintf(__("Invoice was created in Payday #%s\nClaim was sent to SSN: %s", 'payday'), $payday_invoice_number, $billing_ssn);
        }

        return sprintf(__("Invoice was created in Payday #%s", 'payday'), $payday_invoice_number);
    }

    /** 
     * Calculate total amount including tax with given values
     *
     * @param float $amount_excl_tax The amount excluding tax.
     * @param float $discount_percentage The discount percentage.
     * @param float $quantity The quantity of the item.
     * @param float $tax_rate The tax rate.
     * @return float
     */
    public static function calculate_amount_incl_tax($amount_excl_tax, $discount_percentage = 0.0, $quantity = 1.0, $tax_rate = 0.0)
    {
        return $amount_excl_tax * $quantity * (1 + ($tax_rate / 100)) * (1 - ($discount_percentage / 100));
    }

    /**
     * Calculates the grand total based on the unit price including VAT, discount percentage, quantity, and currency decimal.
     *
     * @param float $unit_price_including_vat The unit price including VAT.
     * @param float $discount_percentage The discount percentage. Default is 0.0.
     * @param float $quantity The quantity. Default is 1.0.
     * @param float $currency_decimal The currency decimal. Default is 2.0.
     *
     * @return float The calculated grand total.
     */
    public static function calculate_grand_total($unit_price_including_vat, $discount_percentage = 0.0, $quantity = 1.0, $currency_decimal = 2.0)
    {
        return round($unit_price_including_vat * $quantity * (1 - ($discount_percentage / 100)), $currency_decimal);
    }

    /**
     * Check if a string is a valid GUID
     * 
     * @param string $str The string to check.
     * @return bool
     */
    public static function is_valid_guid($str)
    {
        return preg_match('/^[0-9a-f]{12}[4][0-9a-f]{19}$/', $str);
    }

    /**
     * Round VAT percentage to the closest valid rate if it's within +-2% of a valid rate.
     *
     * @param float $vat_percentage The original VAT percentage.
     * @return int The closest valid VAT rate if it's within +-2% of the original rate, otherwise the standard VAT rate.
     */
    public static function round_to_closest_vat_percentage($vat_percentage)
    {
        require_once PAYDAY_DIR_PATH . 'enums/enum-payday-vat-percentage-rate.php';
        $vatRates = [
            Payday_VAT_PERCENTAGE_RATE::STANDARD,
            Payday_VAT_PERCENTAGE_RATE::REDUCED,
            Payday_VAT_PERCENTAGE_RATE::ZERO
        ];

        asort($vatRates); // Sort rates in ascending order

        $closest = null;
        foreach ($vatRates as $rate) {
            if ($closest === null || abs($vat_percentage - $closest) > abs($rate - $vat_percentage)) {
                $closest = $rate;
            }
        }

        // If the closest rate is within +-2% of the original rate, return it
        if (abs($closest - $vat_percentage) <= 2) {
            return $closest;
        }

        // If no suitable rate was found, return the standard rate if taxes are enabled, otherwise return 0%
        if (get_option('woocommerce_calc_taxes') === 'yes') {
            return Payday_VAT_PERCENTAGE_RATE::STANDARD;
        } else {
            return Payday_VAT_PERCENTAGE_RATE::ZERO;
        }
    }
}
