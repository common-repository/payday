<?php

// Exit if accessed directly.
defined('ABSPATH') || exit;

$entityFiles = glob(PAYDAY_DIR_PATH . 'entities/entity-*.php');
foreach ($entityFiles as $file) {
    require_once $file;
}
