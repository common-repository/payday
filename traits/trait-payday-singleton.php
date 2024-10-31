<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

trait Payday_Singleton_Trait
{
	// Singleton instance
	private static $_instance = null;

	/**
	 * Singleton pattern implementation.
	 * Returns an instance of the class.
	 * 
	 * @return static
	 */
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new static();
		}
		return self::$_instance;
	}

	/**
	 * Singleton classes should not be cloneable.
	 */
	private function __clone()
	{
		throw new \RuntimeException("Cannot clone a singleton instance");
	}

	/**
	 * Singleton classes should not be serializable.
	 * As per PHP's requirements, magic method __wakeup should have public visibility.
	 */
	public function __wakeup()
	{
		throw new \RuntimeException("Cannot unserialize a singleton instance");
	}

	/**
	 * Constructor should be protected to prevent direct object creation
	 * outside of the class, ensuring the singleton pattern.
	 */
	protected function __construct()
	{
	}
}
