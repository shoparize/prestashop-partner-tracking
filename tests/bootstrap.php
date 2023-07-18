<?php

if (file_exists(__DIR__ . '/../PrestaShop/tests-legacy')) {
    require_once __DIR__ . '/../PrestaShop/tests-legacy/bootstrap.php';
} else {
    require_once __DIR__ . '/../PrestaShop/tests/bootstrap.php';
}

require_once __DIR__ . '/../PrestaShop/config/config.inc.php';
require_once __DIR__ . '/../PrestaShop/config/defines_uri.inc.php';
require_once __DIR__ . '/../PrestaShop/init.php';
require_once __DIR__ . '/../shoparizepartner.php';
