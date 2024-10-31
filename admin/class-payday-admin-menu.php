<?php

// Exit if accessed directly.
(defined('ABSPATH') || exit);

require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-auth-model.php';
require_once PAYDAY_DIR_PATH . 'pages/class-payday-log-viewer-page.php';
require_once PAYDAY_DIR_PATH . 'pages/class-payday-login-page.php';
require_once PAYDAY_DIR_PATH . 'pages/class-payday-settings-page.php';
require_once PAYDAY_DIR_PATH . 'models/class-payday-settings-model.php';

/**
 * Class Payday_Admin_Menu
 */
class Payday_Admin_Menu
{
	// Make this class a singleton
	use Payday_Singleton_Trait;

	/**
	 * The constructor is private to prevent initiation with outer code.
	 */
	public function __construct()
	{
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu()
	{
		$this->add_top_level_menu();
		$this->add_settings_submenu();
		$this->add_claim_service_settings_submenu();
		$this->add_log_viewer_submenu();
		$this->remove_auto_created_submenu();
	}

	/**
	 * Add top-level menu.
	 */
	private function add_top_level_menu()
	{
		$payday_icon = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCAxMCAxMCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxuczpzZXJpZj0iaHR0cDovL3d3dy5zZXJpZi5jb20vIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjI7Ij48cGF0aCBkPSJNMTAsMmMwLC0xLjEwNCAtMC44OTYsLTIgLTIsLTJsLTYsMGMtMS4xMDQsMCAtMiwwLjg5NiAtMiwybDAsNmMwLDEuMTA0IDAuODk2LDIgMiwybDYsMGMxLjEwNCwwIDIsLTAuODk2IDIsLTJsMCwtNlptLTQuOTk4LDQuOTYyYy0xLjExMywwIC0xLjY2MiwtMC43MjcgLTEuNjYyLC0wLjcyN2wtMCwyLjc2NWwtMS40NzEsLTBsLTAsLTUuMDA0bDAuMDA0LC0wbC0wLC0wLjAxMmMtMCwtMC41OTcgMC4xNDMsLTEuMTI1IDAuNDMsLTEuNTg1YzAuMjg4LC0wLjQ1OSAwLjY2NywtMC44MDcgMS4xMzksLTEuMDQ0YzAuNDcyLC0wLjIzNyAwLjk5MiwtMC4zNTUgMS41NiwtMC4zNTVjMC41NjQsLTAgMS4wODIsMC4xMTggMS41NTQsMC4zNTVjMC40NzIsMC4yMzcgMC44NTMsMC41ODUgMS4xNDIsMS4wNDRjMC4yODksMC40NiAwLjQzMywwLjk4OCAwLjQzMywxLjU4NWMwLDAuNTk2IC0wLjE0NCwxLjEyNCAtMC40MzMsMS41ODJjLTAuMjg5LDAuNDU3IC0wLjY3LDAuODA0IC0xLjE0MiwxLjA0MWMtMC40NzIsMC4yMzcgLTAuOTksMC4zNTUgLTEuNTU0LDAuMzU1Wm0xLjE3MiwtMS43NDRjMC4zMjMsLTAuMzI2IDAuNDg1LC0wLjczOCAwLjQ4NSwtMS4yMzdjLTAsLTAuNDk5IC0wLjE2MSwtMC45MSAtMC40ODIsLTEuMjM1Yy0wLjMyMiwtMC4zMjQgLTAuNzEzLC0wLjQ4NiAtMS4xNzUsLTAuNDg2Yy0wLjQ2NSwwIC0wLjg1OCwwLjE2MSAtMS4xNzcsMC40ODRjLTAuMzIsMC4zMjIgLTAuNDgsMC43MzUgLTAuNDgsMS4yMzdjMCwwLjUwMiAwLjE2LDAuOTE2IDAuNDgsMS4yNGMwLjMxOSwwLjMyNCAwLjcxMiwwLjQ4NiAxLjE3NywwLjQ4NmMwLjQ1OCwtMCAwLjg0OSwtMC4xNjMgMS4xNzIsLTAuNDg5WiIgc3R5bGU9ImZpbGw6I2EzYWFiMjsiLz48L3N2Zz4=';

		add_menu_page(
			__('Payday', 'payday'), // Page title.
			__('Payday', 'payday'), // Menu title.
			'manage_options', // Capability.
			'payday', // Menu slug.
			array($this, 'display_payday_page_content'), // Function.
			// 'dashicons-building', // 
			$payday_icon, // Icon.
			'27' // Position.
		);
	}

	/**
	 * Display Payday page content.
	 */
	public function display_payday_page_content()
	{
		// Get the instance of the auth model
		$auth_model = new Payday_Auth_Model();

		// Check if the user is logged in using your custom method
		if (!$auth_model->is_connected()) {
			// If not, display the login page
			Payday_Login_Page::instance()->display_page_content();
		} else {
			// If logged in, display the intended content
			Payday_Settings_Page::instance()->display_page_content();
		}
	}

	/**
	 * Add submenu for the settings page.
	 */
	private function add_settings_submenu()
	{
		add_submenu_page(
			'payday', // Parent slug.
			__('Payday Settings', 'payday'), // Page title.
			__('Settings', 'payday'), // Menu title.
			'manage_options', // Capability.
			'payday-settings', // Menu slug.
			array($this, 'display_settings_page_content') // Function.
		);
	}

	/**
	 * Display settings page content.
	 */
	public function display_settings_page_content()
	{
		// Get the instance of the auth model
		$auth_model = new Payday_Auth_Model();

		// Check if the user is logged in using your custom method
		if (!$auth_model->is_connected()) {
			// If not, display the login page
			Payday_Login_Page::instance()->display_page_content();
		} else {
			// Credentials are valid, proceed with the login process
			// ...
			// $settings_model = new Payday_Settings_Model();
			// $settings_model->retrieve_and_update_payday_payment_types();

			// If logged in, display the intended content
			Payday_Settings_Page::instance()->display_page_content();
		}
	}

	/**
	 * Add submenu for the claim service settings page.
	 */
	private function add_claim_service_settings_submenu()
	{
		add_submenu_page(
			'payday', // Parent slug.
			__('Bank Claim Service', 'payday'), // Page title.
			__('Bank Claim Service', 'payday'), // Menu title.
			'manage_options', // Capability.
			'payday-claim-service-settings', // Menu slug.
			array($this, 'display_claim_service_settings_page_content') // Function.
		);
	}

	public function display_claim_service_settings_page_content()
	{
		// Get the instance of the auth model
		$auth_model = new Payday_Auth_Model();

		// Check if the user is logged in using your custom method
		if (!$auth_model->is_connected()) {
			// If not, display the login page
			Payday_Login_Page::instance()->display_page_content();
		} else {
			// Redirect to the WooCommerce settings page for the Payday claim service payment gateway
			wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=payday'));
			exit; // Make sure to exit after the redirect
		}
	}

	/**
	 * Add submenu for the log viewer page.
	 */
	private function add_log_viewer_submenu()
	{
		add_submenu_page(
			'payday', // Parent slug.
			__('Payday Log Viewer', 'payday'), // Page title.
			__('Log', 'payday'), // Menu title.
			'manage_options', // Capability.
			'payday-log-viewer', // Menu slug.
			array($this, 'display_log_viewer_page_content') // Function.
		);
	}

	/**
	 * Display log viewer page content.
	 */
	public function display_log_viewer_page_content()
	{
		Payday_Log_Viewer_Page::instance()->display_page_content();
		// // Get the instance of the auth model
		// $auth_model = new Payday_Auth_Model();

		// // Check if the user is logged in using your custom method
		// if (!$auth_model->is_connected()) {
		// 	// If not, display the login page
		// 	Payday_Login_Page::instance()->display_page_content();
		// } else {
		// 	// If logged in, display the intended content
		// 	Payday_Log_Viewer_Page::instance()->display_page_content();
		// }
	}

	/**
	 * Remove the automatically created 'Payday' submenu.
	 */
	private function remove_auto_created_submenu()
	{
		remove_submenu_page('payday', 'payday');
	}
}
