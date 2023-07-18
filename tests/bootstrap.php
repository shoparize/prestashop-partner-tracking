<?php

// to support reading local files from plugin code
// eg. see how $this->version is evaluated by bkn_301_payment.php
define('_PS_MODULE_DIR_', getcwd() . '../../');

if (file_exists(__DIR__ . '/../../PrestaShop/tests-legacy')) {
    require_once __DIR__ . '/../../PrestaShop/tests-legacy/bootstrap.php';
} else {
    require_once __DIR__ . '/../../PrestaShop/tests/bootstrap.php';
}

require_once __DIR__ . '/../../PrestaShop/config/config.inc.php';
require_once __DIR__ . '/../../PrestaShop/config/defines_uri.inc.php';
require_once __DIR__ . '/../../PrestaShop/init.php';
require_once __DIR__ . '/../shoparizepartner.php';
