<?php
/**
 * 2007-2023 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    shoparize <contact@prestashop.com>
 * @copyright 2007-2023 shoparize
 * @license   http://www.gnu.org/licenses/gpl-3.0.html (GPLv3 or later License)
 */
trait ShoparizePartnerApi
{
    /**
     * @return bool
     */
    public function isAllow(): bool
    {
        $header = strtoupper(str_replace('-', '_', 'Shoparize-Partner-Shop-Id'));
        $shopId = $_SERVER['HTTP_' . $header] ?? null;
        if ($shopId != Configuration::get('SHOPARIZEPARTNER_SHOP_ID', null, null, Shop::getContextShopID())
            || $_SERVER['REQUEST_METHOD'] != 'GET') {
            return false;
        }

        return true;
    }
}
