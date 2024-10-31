<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-auth-model.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-settings-model.php';
require_once PAYDAY_DIR_PATH . 'classes/class-payday-settings.php';
require_once PAYDAY_DIR_PATH . 'pages/class-payday-login-page.php';
require_once PAYDAY_DIR_PATH . 'enums/enum-payday-order-status.php';

class Payday_Settings_Page
{
	use Payday_Singleton_Trait;
	const SETTINGS_SECTION_1 = Payday_Settings::SETTINGS_SECTION_1;
	const SETTINGS_SECTION_2 = Payday_Settings::SETTINGS_SECTION_2;
	const SETTINGS_SECTION_3 = Payday_Settings::SETTINGS_SECTION_3;

	// Refresh Values button handler
	public function admin_post_payday_settings_refresh()
	{
		if (isset($_POST['refresh'])) {
			self::refresh_settings_button_on_submit_handler();
			wp_safe_redirect(esc_url(admin_url('admin.php?page=' . Payday_Settings::PAGE)));
			exit;
		}
	}


	// Clear Cache button handler
	public function admin_post_payday_settings_disconnect()
	{
		if (isset($_POST['disconnect'])) {
			self::disconnect_button_on_submit_handler();
			wp_safe_redirect(esc_url(admin_url('admin.php?page=' . Payday_Settings::PAGE)));
			exit;
		}
	}

	// Sync all products button handler
	public function admin_post_payday_sync_all_products()
	{
		if (isset($_POST['payday_sync_all_products'])) {
			// Get all WooCommerce products IDs
			// $args = array(
			// 	'post_type' => 'product',
			// 	'posts_per_page' => -1
			// );

			$args = array(
				'limit' => -1
			);
			// $products = get_posts($args);

			$products = wc_get_products($args);
			$product_ids = array();
			foreach ($products as $product) {
				array_push($product_ids, $product->id);
			}

			$product_model = new Payday_Product_Model();
			$response = $product_model->upsert_payday_products($product_ids);

			set_transient('payday_upsert_products_response', $response, 1 * 60); // 5 minutes

			$redirect_to = esc_url(admin_url('admin.php?page=' . Payday_Settings::PAGE));

			// Get redirect URL from the request
			wp_safe_redirect($redirect_to);
		}
	}


	/**
	 * Updates the settings page with new sales payment types from payday. 
	 * 
	 * @return void
	 */
	public function refresh_settings_button_on_submit_handler()
	{
		$settings_model = new Payday_Settings_Model();
		$settings_model->retrieve_and_update_payday_payment_types();
	}

	/**
	 * Disconnects the plugin from the Payday API.
	 * 
	 * Deletes all payment types, invoice meta, and auth tokens.
	 * Set all settings to empty string if they have the payday prefix.
	 * 
	 * @return void
	 */
	public function disconnect_button_on_submit_handler()
	{
		$settings_model = new Payday_Settings_Model();
		$settings_model->delete_all_payment_types();
		$settings_model->delete_all_invoice_meta();

		$auth_model = new Payday_Auth_Model();
		$auth_model->delete_auth_token();

		// Empty all settings
		$settings_model->empty_all_settings();
	}

	public function display_page_content()
	{
		$api_endpoint = Payday_Settings_Model::get_api_endpoint();
		$api_endpoint_title = Payday_Settings_Model::get_api_endpoint_title();

		settings_errors(Payday_Settings::PAGE);
?>
		<div class="wrap">
			<!-- Botton row -->
			<h2><?php echo esc_html__("Settings", 'payday'); ?></h2>
			<span>
				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline-block;">
					<?php wp_nonce_field('payday_settings_refresh_nonce_action', 'payday_settings_refresh_nonce'); ?>
					<input type="hidden" name="action" value="payday_settings_refresh">
					<button type="submit" style="vertical-align: baseline;" id="refresh" name="refresh" class="button" title="<?php echo esc_attr__("Click to fetch and update the sales accounts from Payday", 'payday'); ?>">
						<span class="dashicons dashicons-update" style="vertical-align: text-bottom;"></span>
						<?php echo esc_html__("Sync sales accounts from Payday", 'payday'); ?>
					</button>
				</form>

				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline-block;">
					<?php wp_nonce_field('payday_sync_nonce_action', 'payday_sync_nonce'); ?>
					<input type="hidden" name="action" value="payday_sync_all_products">
					<!-- TODO add a Dashicon on this this button  -->
					<button type="submit" style="vertical-align: baseline;" id="payday_sync_all_products" name="payday_sync_all_products" class="button" title="<?php echo esc_attr__("Click to synchronize all product inventories with Payday", 'payday'); ?>">
						<span class="dashicons dashicons-archive" style="vertical-align: text-bottom;"></span>
						<?php echo esc_attr__("Sync all products inventory with Payday", 'payday'); ?>
					</button>
				</form>

				<span style="background-color:#52BB62; margin-left: 10px; padding: 10px 5px 10px 5px; border-radius: 15px;">
					<!-- <h2 style="color: #393939; font-weight: 700; display: inline-block; vertical-align: baseline;"><?php echo esc_html__("Active", 'payday') ?></h2> -->
					<p style="color: #393939; font-weight: 700; display: inline-block; vertical-align: baseline; margin-left: 10px;" title="<?php echo esc_html($api_endpoint); ?>">
						<?php echo esc_html('API Endpoint: ' . $api_endpoint_title) ?>
					</p>
					<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline-block; padding-left: 5px;">
						<?php wp_nonce_field('payday_settings_disconnect_nonce_action', 'payday_settings_disconnect_nonce'); ?>
						<input type="hidden" name="action" value="payday_settings_disconnect">
						<input type="submit" id="Disconnect" style="display: inline-block; vertical-align: baseline; border-radius: 12px;" name="disconnect" class="button" value="<?php echo esc_html__("Disconnect", 'payday'); ?>">
					</form>
				</span>
			</span>
			<form method="POST" action="options.php">
				<?php
				// Allocate all fields for a option group
				settings_fields(Payday_Settings::OPTION_GROUP);

				// // Displays all the sections that are assigned to a certain page
				$this->custom_do_settings_sections(Payday_Settings::PAGE);

				// Render a Save Change button
				submit_button();
				?>
			</form>
		</div>

<?php
	}

	function custom_do_settings_sections($page)
	{
		global $wp_settings_sections, $wp_settings_fields;

		if (!isset($wp_settings_sections[$page])) {
			return;
		}
		echo '<hr>';
		foreach ((array) $wp_settings_sections[$page] as $section) {
			if ('' !== $section['before_section']) {
				if ('' !== $section['section_class']) {
					echo wp_kses_post(sprintf($section['before_section'], esc_attr($section['section_class'])));
				} else {
					echo wp_kses_post($section['before_section']);
				}
			}


			if ($section['title']) {
				echo "<h3>{$section['title']}</h3>\n";
			}

			if ($section['callback']) {
				call_user_func($section['callback'], $section);
			}

			if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
				continue;
			}
			echo '<table class="form-table" role="presentation">';
			self::custom_do_settings_fields($page, $section['id']);
			echo '</table>';

			if ('' !== $section['after_section']) {
				echo wp_kses_post($section['after_section']);
			}
			echo '<hr>';
		}
	}

	function custom_do_settings_fields($page, $section)
	{
		global $wp_settings_fields;

		if (!isset($wp_settings_fields[$page][$section])) {
			return;
		}

		foreach ((array) $wp_settings_fields[$page][$section] as $field) {
			$class = '';

			if (!empty($field['args']['class'])) {
				$class = ' class="' . esc_attr($field['args']['class']) . '"';
			}

			echo "<tr{$class}>";

			if (!empty($field['args']['label_for'])) {
				echo '<th scope="row"><label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label></th>';
			} else {
				echo '<th scope="row">' . $field['title'] . '</th>';
			}

			echo '<td>';
			call_user_func($field['callback'], $field['args']);
			echo '</td>';
			echo '</tr>';
		}
	}
}
