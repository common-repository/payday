<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Include dependencies
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-product-model.php';

/**
 * Class Payday_Product_Sync
 * 
 * Handles syncing of WooCommerce products with Payday.
 */
class Payday_Product_Sync
{
	use Payday_Singleton_Trait;

	/**
	 * Constructor function
	 */
	private function __construct()
	{
		// Initialization code here
	}

	public function register_bulk_actions($bulk_actions)
	{
		// Check if the user is authenticated if so add the bulk actions to the array
		$auth_model = new Payday_Auth_Model();
		if ($auth_model->is_connected()) {
			$bulk_actions['sync_inventory_with_payday'] = __('Sync products with Payday', 'payday');
		}
		return $bulk_actions;
	}

	public function bulk_action_handler($redirect_to, $action, $product_ids)
	{
		if ($action !== 'sync_inventory_with_payday') {
			return $redirect_to;
		}

		$product_model = new Payday_Product_Model();
		$response = $product_model->upsert_payday_products($product_ids);

		set_transient('payday_upsert_products_response', $response, 1 * 60); // 5 minutes

		// $products_response = $response['products_response'];
		// $productsMissingSKU = $response['productsMissingSKU'];
		// $variationsMissingSKU = $response['variationsMissingSKU'];

		// // TODO: Replace count($product_ids) with the number of products that were successfully created in Payday, etc.
		// $redirect_to = add_query_arg('payday_upsert_created', $products_response->summary->created, $redirect_to);
		// $redirect_to = add_query_arg('payday_upsert_updated', $products_response->summary->updated, $redirect_to);
		// $redirect_to = add_query_arg('payday_upsert_unsuccessful', $products_response->summary->unsuccessful, $redirect_to);
		// $redirect_to = add_query_arg('payday_upsert_products_missing_sku', count($productsMissingSKU), $redirect_to);
		// $redirect_to = add_query_arg('payday_upsert_variations_missing_sku', count($variationsMissingSKU), $redirect_to);

		return $redirect_to;
	}

	public function bulk_action_admin_notice()
	{
		$response = get_transient('payday_upsert_products_response');

		if (!$response) {
			return;
		}

		// Clear the transient
		delete_transient('payday_upsert_products_response');

		// Extract the data from the response
		$products_response = $response['products_response'];
		$productsMissingSKU = $response['productsMissingSKU'];
		$variationsMissingSKU = $response['variationsMissingSKU'];

		// Extract the inventory sync results
		$created = $products_response->summary->created;
		$updated = $products_response->summary->updated;
		$unsuccessful = $products_response->summary->unsuccessful + count($productsMissingSKU) + count($variationsMissingSKU);

		// Extract the products that had errors
		$payday_products_upsert_response = $products_response->products;

		// Check if we are on the products list page
		$screen = get_current_screen();

		// Start outputting the table only if there is data to show
		if ($created > 0 || $updated > 0 || $unsuccessful > 0) {


?>

			<?php if ($screen->id === 'edit-product') { ?>
				<div class="notice">
				<?php
			} ?>
				<div class="wrap">
					<h2><?php echo esc_html__('Product sync results', 'payday') ?></h2>
					<table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
						<thead>
							<tr>
								<th><?php echo esc_html__('Status', 'payday') ?></th>
								<th><?php echo esc_html__('Count', 'payday') ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if ($created > 0) {
							?>
								<tr>
									<td><span class="dashicons dashicons-yes-alt" style="color: green;"></span> <?php echo esc_html__('Created', 'payday') ?></td>
									<td><?php echo number_format_i18n($created); ?></td>
								</tr>
							<?php
							} ?>
							<?php if ($updated > 0) {
							?>
								<tr>
									<td><span class="dashicons dashicons-update"></span> <?php echo esc_html__('Updated', 'payday') ?></td>
									<td><?php echo number_format_i18n($updated); ?></td>
								</tr>
							<?php
							} ?>
							<?php if ($unsuccessful > 0) {
							?>
								<tr>
									<td><span class="dashicons dashicons-warning" style="color: orange;"></span> <?php echo esc_html__('Unsuccessful', 'payday') ?></td>
									<td><?php echo number_format_i18n($unsuccessful); ?></td>
								</tr>
							<?php
							} ?>
						</tbody>
					</table>
				</div>
				<?php if ($screen->id === 'edit-product') { ?>
				</div>
			<?php
				} ?>
			<?php
		}
		if ($payday_products_upsert_response && count($payday_products_upsert_response->products)) {
			$products = $payday_products_upsert_response->products;
			// filter only the products that had errors
			$error_products = array_filter($products, function ($product) {
				return isset($product->status) && $product->status === 'error';
			});

			if (count($error_products) > 0 || count($productsMissingSKU) > 0 || count($variationsMissingSKU) > 0) {
			?>

				<?php if ($screen->id === 'edit-product') { ?>
					<div class="notice notice-error">
					<?php
				} ?>
					<div class="wrap">
						<h2><?php echo esc_html__('Errors', 'payday') ?></h2>
						<table class="wp-list-table widefat fixed striped" style="margin-bottom: 20px;">
							<thead>
								<tr>
									<th><?php echo esc_html__('Product', 'payday') ?></th>
									<th><?php echo esc_html__('Error', 'payday') ?></th>
								</tr>
							</thead>
							<tbody>

								<?php foreach ($error_products as $product) {
									if (!isset($product->status) || $product->status !== 'error') {
										continue;
									}

									$sku = $product->sku;
									$wc_product_id = wc_get_product_id_by_sku($sku);
									$product_edit_url = "#";
									if ($wc_product_id) {
										$product_edit_url = admin_url('post.php?post=' . $wc_product_id . '&action=edit');
									}

									$error = $product->error;
									$product_name = $product->name;
								?>
									<tr>
										<!-- <td><?php echo esc_html($product_name); ?></td> -->
										<td><a href="<?php echo esc_url($product_edit_url); ?>" target="_blank"><?php echo esc_html($product_name); ?></a></td>
										<td><?php echo esc_html($error); ?></td>
									</tr>
								<?php
								} ?>
								<?php foreach ($productsMissingSKU as $product) {
									$product_edit_url = "#";
									$wc_product_id = $product->get_id();
									if ($product) {
										$product_edit_url = admin_url('post.php?post=' . $wc_product_id . '&action=edit');
									}

								?>
									<tr>
										<td><a href="<?php echo esc_url($product_edit_url); ?>" target="_blank"><?php echo esc_html($product->name); ?></a></td>
										<td><?php echo esc_html__('Product is missing SKU', 'payday'); ?></td>
									</tr>
								<?php } ?>
								<?php foreach ($variationsMissingSKU as $variation) {
									$wc_product_id = $variation->get_parent_id();
									$product_edit_url = "#";
									if ($variation) {
										$product_edit_url = admin_url('post.php?post=' . $wc_product_id . '&action=edit');
									}
								?>
									<tr>
										<td><a href="<?php echo esc_url($product_edit_url); ?>" target="_blank"><?php echo esc_html($variation->name); ?></a></td>
										<td><?php echo esc_html__('Variation is missing SKU', 'payday'); ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<?php if ($screen->id === 'edit-product') { ?>
					</div>
			<?php
					}
				} ?>
<?php
		}
	}


	public function add_custom_button_to_product_list($actions, $post)
	{
		// Check if the post type is 'product'
		if ($post->post_type === 'product') {
			// Add your custom action
			$actions['sync_inventory_with_payday'] = '<a href="' . esc_url(admin_url('admin-post.php?action=sync_inventory_with_payday&product_id=' . $post->ID)) . '" class="my-custom-action">' . __('Sync products with Payday', 'payday') . '</a>';
		}

		return $actions;
	}



	public function handle_sync_inventory_with_payday()
	{
		$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

		if ($product_id) {
			$product_model = new Payday_Product_Model();
			$response = $product_model->upsert_payday_products([$product_id]);

			set_transient('payday_upsert_products_response', $response, 1 * 60); // 5 minutes
		}

		$redirect_to = admin_url('edit.php?post_type=product');
		// Redirect back to the products page
		wp_redirect($redirect_to);
		exit;
	}


	public function add_custom_product_column($columns)
	{
		$auth_model = new Payday_Auth_Model();
		if (!$auth_model->is_connected()) {
			return $columns;
		}

		$new_columns = [];
		foreach ($columns as $key => $title) {
			$new_columns[$key] = $title;

			// Inserting the new column right after the 'Stock' column
			if ('is_in_stock' === $key) {
				$new_columns['unique_skus'] = __('Variants with SKUs', 'payday');
			}
		}
		return $new_columns;
	}

	public function add_custom_product_column_content($column, $post_id)
	{
		if ('unique_skus' === $column) {
			$product = wc_get_product($post_id);
			if ($product && $product->is_type('variable')) {
				$unique_sku_count = Payday_Product_Sync::get_unique_sku_count($product);
				echo $unique_sku_count;
			} else {
				// For non-variable products
				$sku = $product->get_sku();
				echo (!empty($sku) ? '1' : '0') . '/1';
			}
		}
	}


	// Function to count unique SKUs
	function get_unique_sku_count($product)
	{
		$variations = $product->get_children();
		$unique_skus = [];

		foreach ($variations as $variation_id) {
			$variation = wc_get_product($variation_id);
			$sku = $variation->get_sku();

			// Check if SKU is unique and not the same as the parent product
			if (!empty($sku) && !in_array($sku, $unique_skus) && $sku !== $product->get_sku()) {
				$unique_skus[] = $sku;
			}
		}

		return count($unique_skus) . '/' . count($variations);
	}

	function my_plugin_enqueue_admin_styles()
	{
		// Ensure the stylesheet is only loaded on the WooCommerce Products page
		global $pagenow;
		if ($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'product') {
			wp_enqueue_style('payday-products-style', plugin_dir_url(__FILE__) . '../assets/css/products.css', array(), '1.0.0');
		}
	}
}
