<?php
/**
 * 2022-2023 PrestaShop.
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
 * @author    shoparize <partner@shoparize.com>
 * @copyright 2022-2023 shoparize
 * @license   http://www.gnu.org/licenses/gpl-3.0.html (GPLv3 or later License)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Shoparize\PartnerPluginProductApi\Helper;
use Shoparize\PartnerPluginProductApi\Responses\FeedResponse;

class ShoparizepartnerFeedsModuleFrontController extends ModuleFrontController
{
    /**
     * @var ShoparizePartnerFeed
     */
    protected $shoparizeFeedHelper;

    /**
     * @var ShoparizePartnerCsvHelper
     */
    protected $csvHelper;

    /**
     * @var Helper
     */
    protected $shoparizeHelper;

    public function __construct()
    {
        parent::__construct();

        $this->shoparizeFeedHelper = new ShoparizePartnerFeed();
        $this->csvHelper = new ShoparizePartnerCsvHelper();

        $this->shoparizeHelper = new Helper(Configuration::get('SHOPARIZEPARTNER_SHOP_ID', null, null, Shop::getContextShopID()));
    }

    /**
     * @throws Exception
     */
    public function initContent()
    {
        if (!$this->shoparizeHelper->isAllow()) {
            http_response_code(400);
            exit;
        }

        $shopId = Shop::getContextShopID();
        $page = Tools::getValue('page', 1);
        $limit = Tools::getValue('limit', 100);
        $updatedAfter = Tools::getValue('updated_after', '');
        if (!empty($updatedAfter) && !$this->shoparizeHelper->validateDate($updatedAfter, DateTime::ATOM)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => sprintf('shoparize partner error: not valid date: %s, should be: %s', $updatedAfter, DateTime::ATOM)]);
            exit;
        }

        if (!empty($updatedAfter)) {
            $dt = new DateTime($updatedAfter);
            $dt->setTimezone(new DateTimeZone(Configuration::get('PS_TIMEZONE')));
            $updatedAfter = $dt->format('Y-m-d H:i:s');
        }

        $cacheKey = 'ShoparizePartnerFeed::run_' . $shopId . '_' . $page . '_' . $limit . '_' . $updatedAfter;
        if (!Cache::isStored($cacheKey)) {
            try {
                $response = new FeedResponse();
                $data = $this->shoparizeFeedHelper->getFeedData($shopId, $page, $limit, $updatedAfter);
                $response->setItems($data);
                Cache::store($cacheKey, $response->getJson());
            } catch (Exception $e) {
                PrestaShopLogger::addLog(sprintf('shoparize partner error: %s, file: %s, like: %s', $e->getMessage(), $e->getFile(), $e->getLine()));
            }
        }

        header('Content-Type: application/json');
        echo Cache::retrieve($cacheKey);
        exit;
    }
}
