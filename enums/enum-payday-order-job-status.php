<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

abstract class Payday_Order_Job_Status
{
	const PROCESSING = "Processing";
	const SUCCESSFUL = "Successful";
	const UNSUCCESSFUL = "Unsuccessful";
}
