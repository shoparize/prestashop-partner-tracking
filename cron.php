<?php

require_once dirname(__FILE__).'/../../config/config.inc.php';
require_once dirname(__FILE__) . '/../../init.php';
require_once dirname(__FILE__) . '/include.php';

if (Tools::isPHPCLI()) {
    $feed = new ShoparizePartnerFeed();
    $feed->run();
}