<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

abstract class Payday_Update_Invoice_Mode
{
    const MARK_AS_PAID = 0;
    const MARK_AS_UNPAID = 1;
    const CANCEL_INVOICE = 2;
    const CANCEL_INVOICE_CLAIM = 3;
    const PAYMENT_EXTENSION = 4;
    const RESEND_EMAIL = 5;
}
