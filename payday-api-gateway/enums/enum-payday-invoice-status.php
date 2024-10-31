<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

abstract class Payday_Invoice_Status
{
	const DRAFT = 'DRAFT';
	const SENT = 'SENT';
	const PAID = 'PAID';
	const CANCELLED = 'CANCELLED';
	const CREDIT = 'CREDIT';
	const DELETED = 'DELETED';
}
