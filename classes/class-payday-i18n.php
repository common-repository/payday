<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

require_once PAYDAY_DIR_PATH . 'traits/trait-payday-singleton.php';

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 */
class Payday_i18n
{
	// Make this class a singleton
	use Payday_Singleton_Trait;

	/**
	 * The constructor is private to prevent initiation with outer code.
	 */
	private function __construct()
	{
	}
	
	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain()
	{
		$locale = apply_filters('plugin_locale', get_locale(), PAYDAY_NAME);
		load_textdomain(PAYDAY_NAME, PAYDAY_DIR_PATH . '/languages/' . PAYDAY_NAME . '-' . $locale . '.mo');
	}
}
