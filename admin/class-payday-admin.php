<?php

// Exit if accessed directly.
(defined('ABSPATH') || exit);

require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';
require_once PAYDAY_DIR_PATH . 'pages/class-payday-settings-page.php';

/**
 * Class Payday_Admin
 */
class Payday_Admin
{
	// Make this class a singleton
	use Payday_Singleton_Trait;

	/**
	 * The constructor is private to prevent initiation with outer code.
	 */
	private function __construct()
	{
	}


	public function admin_footer_text()
	{
		// Get the current screen.
		$current_screen = get_current_screen();

		// Get the base id of the current screen.
		$base = $current_screen->parent_base;

		// Check if the base id begins with "payday".
		if (substr($base, 0, 6) === "payday") {
			// If so, modify the footer text to include Developer credits.
			echo '<span id="footer-thankyou">' . esc_html__('Developed by Payday ehf. Version ', 'payday') . PAYDAY_VERSION . '</span>';
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param array $links Plugin Action links.
	 * @param string $plugin_file Plugin Base file.
	 *
	 * @return array
	 */
	public static function plugin_action_links($actions, $plugin_file)
	{
		if ($plugin_file !== PAYDAY_BASE_NAME) {
			return $actions;
		}

		$action_links = array(
			'settings' => '<a href="' . admin_url('admin.php?page=payday-settings') . '" aria-label="' . esc_attr__('View Payday settings', 'payday') . '">' . esc_html__('Settings', 'payday') . '</a>',
		);

		return array_merge($action_links, $actions);
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param array $plugin_meta Plugin Row Meta.
	 * @param string $plugin_file  Plugin Base file.
	 *
	 * @return array
	 */
	public static function plugin_row_meta($plugin_meta, $plugin_file)
	{
		if ($plugin_file  !==  PAYDAY_BASE_NAME) {
			return $plugin_meta;
		}

		$setup_guide_url = 'https://hjalp.payday.is/article/72-woocommerce';

		$row_meta = array(
			'setupguide'    => '<a href="' . esc_url($setup_guide_url) . '" aria-label="' . esc_attr__('View Payday plugin setup guide', 'payday') . '">' . esc_html__('Setup guide', 'payday') . '</a>'
		);

		return array_merge($plugin_meta, $row_meta);
	}
}
