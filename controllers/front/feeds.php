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
class ShoparizepartnerFeedsModuleFrontController extends ModuleFrontController
{
    use ShoparizePartnerApi;

    /**
     * @var ShoparizePartnerFeed
     */
    protected ShoparizePartnerFeed $shoparizeFeedHelper;

    public function __construct()
    {
        parent::__construct();

        $this->shoparizeFeedHelper = new ShoparizePartnerFeed();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        if (!$this->isAllow()) {
            http_response_code(400);
            exit;
        }

        $shopId = Shop::getContextShopID();
        $page = Tools::getValue('page', 0);
        $limit = Tools::getValue('limit', 0);

        $cacheKey = 'ShoparizePartnerFeed::run_' . $shopId . '_' . $page . '_' . $limit;
        if (!Cache::isStored($cacheKey)) {
            $data = $this->shoparizeFeedHelper->getFeedData($shopId, $page, $limit);
            if (empty($data)) {
                http_response_code(400);
                exit;
            }
            Cache::store($cacheKey, $this->shoparizeFeedHelper->getPartOfFeed($data));
        }

        $feed = Cache::retrieve($cacheKey);

        header('Content-Type: text/plain');
        echo $feed;
        exit;
    }
}
