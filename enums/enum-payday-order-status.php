<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

abstract class Payday_Order_Status
{
	const NONE = "None";
	const DO_NOT_SEND = "do-not-send";
	const PENDING = "pending";
	const PROCESSING = "processing";
	const ON_HOLD = "on-hold";
	const COMPLETED = "completed";
	const CANCELLED = "cancelled";
	const REFUNDED = "refunded";
	const FAILED = "failed";
}