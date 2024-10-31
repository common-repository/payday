<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'classes/class-payday-logger.php';
require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'enums/enum-payday-api-endpoint.php';
require_once PAYDAY_DIR_PATH . 'utils/class-payday-utils.php';

class Payday_Login_Page
{
	use Payday_Singleton_Trait;

	const PAGE = 'payday-login-page';
	const OPTION_GROUP_NAME = 'payday_login_page_options';
	const SECTION = 'payday_api_section';

	// Constructor is private to prevent direct creation of object.
	private function __construct()
	{
	}

	public function add_settings_sections()
	{
		// Add a settings section.
		add_settings_section(
			self::SECTION, // ID used to identify this section and with which to register options.
			'',
			array($this, 'display_section_content'),
			self::PAGE // The page where to display the section.
		);

		// Add ClientId settings field.
		$this->add_client_id_field();

		// Add ClientSecret settings field.
		$this->add_client_secret_field();

		// Add API Endpoint settings field.
		$this->add_api_endpoint_field();
	}

	public function display_section_content()
	{
		echo '<p>' . esc_html__('Access your ClientId and ClientSecret on Payday.is underneath Settings -> Company Settings -> API', 'payday') . '</p>';
	}

	public function add_client_id_field()
	{
		$field_id = 'payday_client_id';
		$label = esc_html__('ClientID', 'payday');
		$args = array(
			'type' => 'text',
			'id' => $field_id,
			'class' => 'input-lg',
			'name' => 'payday_client_id',
			'style' => 'width: 250px;',
			'value' => get_option($field_id, '')
		);

		$this->add_settings_field($field_id, $label, array($this, 'display_field'), self::SECTION, $args);
		register_setting(self::OPTION_GROUP_NAME, $field_id, array($this, 'validate_clientId_field'));
	}

	public function add_client_secret_field()
	{
		$field_id = 'payday_client_secret';
		$label = esc_html__('ClientSecret', 'payday');
		$args = array(
			'type' => 'password',
			'id' => $field_id,
			'class' => 'input-lg',
			'name' => 'payday_client_secret',
			'style' => 'width: 250px;',
			'value' => get_option($field_id, '')
		);

		$this->add_settings_field($field_id, $label, array($this, 'display_field'), self::SECTION, $args);
		register_setting(self::OPTION_GROUP_NAME, $field_id, array($this, 'validate_clientSecret_field'));
	}

	public function add_api_endpoint_field()
	{
		$field_id = 'payday_api_endpoint';
		$label = esc_html__('API Endpoint', 'payday');
		$args = array(
			'name' => 'payday_api_endpoint',
			'options' => array(
				esc_html__("Production", 'payday') => Payday_API_Endpoint::PRODUCTION,
				esc_html__("Test", 'payday') => Payday_API_Endpoint::TEST,
				esc_html__("Localhost", 'payday') => Payday_API_Endpoint::LOCALHOST
			),
			'style' => 'width: 250px;',
			'value' => get_option($field_id, '')
		);

		$this->add_settings_field($field_id, $label, array($this, 'display_select_field'), self::SECTION, $args);
		register_setting(self::OPTION_GROUP_NAME, $field_id, array($this, 'validate_api_endpoint_field'));
	}

	public function add_settings_field($field_id, $label, $callback, $section, $args = array())
	{
		$args['label_for'] = $field_id;
		add_settings_field(
			$field_id,
			$label,
			$callback,
			self::PAGE,
			$section,
			$args
		);
	}

	public function display_field($args)
	{
		$type = isset($args['type']) ? $args['type'] : 'text';
		$id = isset($args['id']) ? $args['id'] : '';
		$class = isset($args['class']) ? $args['class'] : '';
		$name = isset($args['name']) ? $args['name'] : '';
		$style = isset($args['style']) ? $args['style'] : '';
		$value = isset($args['value']) ? $args['value'] : '';

		switch ($type) {
			case 'text':
			case 'password':
				printf(
					'<input type="%s" id="%s" class="%s" name="%s" style="%s" value="%s" />',
					$type,
					$id,
					$class,
					$name,
					$style,
					esc_attr($value)
				);
				break;
			case 'textarea':
				printf(
					'<textarea id="%s" class="%s" name="%s">%s</textarea>',
					$id,
					$class,
					$name,
					esc_textarea($value)
				);
				break;
			case 'checkbox':
				$checked = checked($value, '1', false);
				printf(
					'<input type="%s" id="%s" class="%s" name="%s" value="%s" %s />',
					$type,
					$id,
					$class,
					$name,
					'1',
					$checked
				);
				break;
		}
	}

	public function display_select_field($args)
	{
		$class = isset($args['class']) ? ' class="' . esc_attr($args['class']) . '"' : '';
		$style = isset($args['style']) ? ' style="' . esc_attr($args['style']) . '"' : '';

		print("<select name=" . $args['name'] . $class . $style . ">");
		foreach ($args['options'] as $key => $value) {
			print("<option value=" . esc_html($value) . (selected(get_option(esc_html($args['name'])), esc_html($value)) ? "  selected>" : ">") . esc_html__($key, 'payday') . "</option>");
		}
		print("</select>");
	}


	public function validate_clientId_field($input)
	{
		$value = trim($input);

		if (empty($value)) {
			return ""; // Support empty string when we clear cache and set value to empty string
		}

		// Sanitize and convert to lowercase
		$value = strtolower(sanitize_text_field($value));

		// Validate length
		if (strlen($value) != 32) {
			add_settings_error(
				self::OPTION_GROUP_NAME,
				'payday_clientId_error',
				esc_html__('Invalid value for ClientID. Value has to be 32 characters long.', 'payday')
			);
			return get_option('payday_client_id');
		}

		// Validate hexadecimals and guid
		if (!ctype_xdigit($value) || !Payday_Utils::is_valid_guid($value)) {
			add_settings_error(
				self::OPTION_GROUP_NAME,
				'payday_clientId_error',
				esc_html__('Invalid value for ClientID. Value has to be a hexadecimal number.', 'payday')
			);
			return get_option('payday_client_id');
		}

		return $value;
	}


	public function validate_clientSecret_field($input)
	{
		$value = trim($input);

		if (empty($value)) {
			return ""; // Support empty string when we clear cache and set value to empty string
		}

		// Sanitize and convert to lowercase
		$value = strtolower(sanitize_text_field($value));

		// Validate length
		if (strlen($value) != 32) {
			add_settings_error(
				self::OPTION_GROUP_NAME,
				'payday_clientSecret_error',
				esc_html__('Invalid value for ClientSecret. Value has to be 32 characters long.', 'payday')
			);
			return get_option('payday_client_secret');
		}

		// Validate hexadecimals and guid
		if (!ctype_xdigit($value) || !Payday_Utils::is_valid_guid($value)) {
			add_settings_error(
				self::OPTION_GROUP_NAME,
				'payday_clientSecret_error',
				esc_html__('Invalid value for ClientSecret. Value has to be a hexadecimal number.', 'payday')
			);
			return get_option('payday_client_secret');
		}

		return $value;
	}


	public function validate_api_endpoint_field($input)
	{
		$value = trim($input);

		if (empty($value)) {
			return ""; // Support empty string when we clear cache and set value to empty string
		}

		// Validate if the value is a valid option
		$valid_options = array(
			Payday_API_Endpoint::PRODUCTION,
			Payday_API_Endpoint::TEST,
			Payday_API_Endpoint::LOCALHOST
		);

		if (!in_array($value, $valid_options)) {
			add_settings_error(
				self::OPTION_GROUP_NAME,
				'payday_api_endpoint_error',
				esc_html__('Invalid value for API Endpoint.', 'payday')
			);
			return get_option('payday_api_endpoint');
		}

		return $value;
	}

	public function handle_login()
	{
		// Check if the payday_login form is being submitted
		if (isset($_POST['payday_login']) && wp_verify_nonce($_POST['payday_login_nonce'], 'payday_login')) {

			// Sanitize the POST data
			$client_id = sanitize_text_field($_POST['payday_client_id']);
			$client_secret = sanitize_text_field($_POST['payday_client_secret']);
			$api_endpoint = sanitize_text_field($_POST['payday_api_endpoint']);

			// Check if credentials are not empty
			if (!empty($client_id) && !empty($client_secret) && !empty($api_endpoint)) {
				// Create a new authentication model
				$auth_model = new Payday_Auth_Model();

				// Validate the credentials using the authentication model
				try {
					$is_valid_credentials = $auth_model->verify_credentials($client_id, $client_secret, $api_endpoint);
				} catch (Payday_Gateway_Error $e) {
					if ($e->getErrorCode() == 503) {
						$class = 'notice notice-error';
						$message = esc_html__('Login failed. Unable to connect to the Payday API on the selected API Endpoint:', 'payday') . ' ' . $api_endpoint . '.' . ' ' . esc_html__('Please check the status of the Payday API and try again.', 'payday');
						set_transient('payday_login_unsucessful_admin_notice', array($class, $message), 5);
						return;
					}
					$is_valid_credentials = false;
				}

				if ($is_valid_credentials) {
					// Credentials are valid, proceed with the login process
					// ...

					// Create a new settings model
					$settings_model = new Payday_Settings_Model();
					$settings_model->set_client_id($client_id);
					$settings_model->set_client_secret($client_secret);
					$settings_model->set_api_endpoint($api_endpoint);

					// Retrieve and update payday payment types
					$settings_model->retrieve_and_update_payday_payment_types();

					// Delete the admin notices, if exists
					delete_transient('payday_login_invalid_credentials_admin_notice');
					delete_transient('payday_login_unsucessful_admin_notice');
				} else {
					// Credentials are invalid, store an admin notice to transient
					$class = 'notice notice-error';
					$message = esc_html__('Login failed. Invalid credentials. Please check your ClientID, ClientSecret, and API Endpoint.', 'payday');
					set_transient('payday_login_invalid_credentials_admin_notice', array($class, $message), 5);
				}
			}
		}
	}



	/**
	 * Display admin notice for invalid credentials.
	 */
	public function display_invalid_credentials_notice()
	{
		// Retrieve admin notice from transient
		$notice = get_transient('payday_login_invalid_credentials_admin_notice');

		if ($notice) {
			// Notice exists, display it and delete the transient
			printf('<div class="%1$s"><p>%2$s</p></div>', $notice[0], $notice[1]);
			delete_transient('payday_login_invalid_credentials_admin_notice');
		}

		$notice = get_transient('payday_login_unsucessful_admin_notice');

		if ($notice) {
			// Notice exists, display it and delete the transient
			printf('<div class="%1$s"><p>%2$s</p></div>', $notice[0], $notice[1]);
			delete_transient('payday_login_unsucessful_admin_notice');
		}
	}

	/**
	 * Display login page content.
	 */
	public function display_page_content()
	{
		$this->add_settings_sections();
		settings_errors(self::OPTION_GROUP_NAME);
?>
		<div class="wrap">
			<h2>
				<?php echo esc_html__("Login", 'payday'); ?>
			</h2>
			<form id="payday-login-form" method="post" action="options.php">
				<?php
				// Output nonce, action, and option_page fields for a settings page.
				settings_fields(self::OPTION_GROUP_NAME);
				// Add the nonce field
				wp_nonce_field('payday_login', 'payday_login_nonce');
				// Prints out all settings sections added to a particular settings page.
				do_settings_sections(self::PAGE);
				// Output save settings button
				submit_button(__("Login", 'payday'), "primary", "payday_login", true);
				?>
			</form>
			<div class="external-links">
				<a href="<?php echo esc_url(__('https://hjalp.payday.is/article/72-woocommerce', 'payday')); ?>" target="_blank"><?php echo esc_html__('Setup Guide', 'payday'); ?></a> |
				<a href="<?php echo esc_url(__('https://payday.is/en/terms', 'payday')); ?>" target="_blank"><?php echo esc_html__('Terms of Service', 'payday'); ?></a> |
				<a href="<?php echo esc_url(__('https://payday.is/en/privacy-policy', 'payday')); ?>" target="_blank"><?php echo esc_html__('Privacy Policy', 'payday'); ?></a>
			</div>
		</div>
<?php
	}
}

add_action('admin_init', array(Payday_Login_Page::instance(), 'add_settings_sections'));
add_action('admin_notices', array(Payday_Login_Page::instance(), 'display_invalid_credentials_notice'));
add_action('init', [Payday_Login_Page::instance(), 'handle_login']);
