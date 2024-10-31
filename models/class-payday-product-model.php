<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'payday-api-gateway/gateway/class-payday-product-gateway.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-product-request.php';
require_once PAYDAY_DIR_PATH . 'payday-api-gateway/request/class-payday-products-request.php';

class Payday_Product_Model
{

    /**
     * Fetches all products.
     *
     * @return array The products.
     */
    public function get_products()
    {
        // Fetch all products.
        $products = wc_get_products(['status' => 'any']);

        // Convert the products to an array format that's more suitable for a REST API response.
        $products_data = array_map(function (WC_Product $product) {
            return $product->get_data();
        }, $products);

        return $products_data;
    }

    /**
     * Updates the inventory of a product.
     * 
     * @param int $product_id The ID of the product to update.
     * @param int $quantity The new quantity of the product.
     * 
     * @return void
     */
    public function update_inventory($product_id, $quantity)
    {
        $product = wc_get_product($product_id);
        $product->set_stock_quantity($quantity);
        $product->save();
    }

    /**
     * Upserts the products in Payday.
     * 
     * @param int[] $product_ids The IDs of the woocommerce products to upsert.
     * 
     * @return array 
     */
    public function upsert_payday_products($product_ids)
    {
        $products = [];
        $productsMissingSKU = [];
        $variationsMissingSKU = [];


        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);

            // $product_type = $product->get_type();

            // Check if the product is a variable product
            if ($product->is_type('variable')) {

                $parent_product_sku = $product->get_sku();

                // Get all variations
                $variations = $product->get_children();
                $variation_ids = $product->get_children();
                foreach ($variation_ids as $variation_id) {
                    // Handle variations
                    $variation = wc_get_product($variation_id);
                    if ($variation == false || $variation == null) continue;
                    if ($variation->get_sku() == null || $variation->get_sku() == '') {
                        $productsMissingSKU[] = $variation;
                        continue;
                    }
                    $tagIds = $variation->get_tag_ids();
                    $tagNames = [];
                    foreach ($tagIds as $tagId) {
                        // Get the term by its ID
                        $term = get_term_by('id', $tagId, 'product_tag');
                        // Check if term exists
                        if ($term) {
                            // Add the tag name to the array
                            $tagNames[] = $term->name;
                        }
                    }

                    $variant_name = $variation->get_name();
                    if ($variant_name) {
                        $variant_name = substr($variant_name, 0, 256);
                    }

                    $variant_description = $variation->get_description();
                    if ($variant_description) {
                        $variant_description = substr($variant_description, 0, 1024);
                    }

                    $variation_sku = $variation->get_sku();

                    // If the variation SKU is the same as the parent product SKU, then we add it to productsMissingSKU
                    if ($variation_sku == $parent_product_sku) {
                        $variationsMissingSKU[] = $variation;
                        continue;
                    }

                    if ($variation_sku) {
                        $variation_sku = substr($variation_sku, 0, 100);
                    }

                    $products[] = new Payday_Product_Request([
                        'name' => $variant_name,
                        'description' => $variant_description,
                        'sku' => $variation_sku,
                        'quantity' => $variation->get_stock_quantity(),
                        'sales_unit_price_excluding_vat' => wc_get_price_excluding_tax($variation),
                        'sales_unit_price_including_vat' => wc_get_price_including_tax($variation),
                        'sales_ledger_account_id' => null,
                        'vat_percentage' => $this->get_tax_rate($variation),
                        'tags' => count($tagNames) > 0 ? $tagNames : null,
                        'archived' => false,
                    ]);
                }
            } else {
                // Handle simple products
                if ($product == false || $product == null) continue;

                if ($product->get_sku() == null || $product->get_sku() == '') {
                    $productsMissingSKU[] = $product;
                    continue;
                }

                $tagIds = $product->get_tag_ids();
                $tagNames = [];

                foreach ($tagIds as $tagId) {
                    // Get the term by its ID
                    $term = get_term_by('id', $tagId, 'product_tag');

                    // Check if term exists
                    if ($term) {
                        // Add the tag name to the array
                        $tagNames[] = $term->name;
                    }
                }

                $product_name = $product->get_name();
                if ($product_name) {
                    $product_name = substr($product_name, 0, 256);
                }

                $product_description = $product->get_description();
                if ($product_description) {
                    $product_description = substr($product_description, 0, 1024);
                }

                $product_sku = $product->get_sku();
                if ($product_sku) {
                    $product_sku = substr($product_sku, 0, 100);
                }

                $products[] = new Payday_Product_Request([
                    'name' => $product_name,
                    'description' => $product_description,
                    'sku' => $product->get_sku(),
                    'quantity' => $product->get_stock_quantity(),
                    'sales_unit_price_excluding_vat' => wc_get_price_excluding_tax($product),
                    'sales_unit_price_including_vat' => wc_get_price_including_tax($product),
                    'sales_ledger_account_id' => null,
                    'vat_percentage' => $this->get_tax_rate($product),
                    'tags' => count($tagNames) > 0 ? $tagNames : null,
                    'archived' => false,
                ]);
            }
        }

        if (count($products) > 0) {
            $products_request = new Payday_Products_Request($products);

            $product_gateway = new Payday_Product_Gateway();
            $products_response = $product_gateway->upsert_products($products_request);
        } else {
            $products_response = [
                'summary' => [
                    'created' => 0,
                    'updated' => 0,
                    'unchanged' => 0
                ],
                'products' => []
            ];
        }


        $response =  [
            'products_response' => $products_response,
            'productsMissingSKU' => $productsMissingSKU,
            'variationsMissingSKU' => $variationsMissingSKU
        ];

        return $response;
    }

    /**
     * Retrieve the tax rate for a product.
     *
     * @param WC_Product $product
     * @return string
     */
    private function get_tax_rate($product)
    {
        $tax_rates = WC_Tax::get_rates($product->get_tax_class());

        if (!empty($tax_rates)) {
            return array_shift($tax_rates)['rate']; // Get Tax Rate (Assuming a single tax rate for simplicity)
        }
        return 0;
    }
}
