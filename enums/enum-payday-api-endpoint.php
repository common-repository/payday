<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

abstract class Payday_API_Endpoint
{
	const PRODUCTION = "https://api.payday.is";
	const TEST = "https://api.test.payday.is";
	const LOCALHOST = "http://localhost:57283";
}