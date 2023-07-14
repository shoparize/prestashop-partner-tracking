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
class ShoparizePartnerFeed
{
    /**
     * @var ShoparizePartnerCsvHelper
     */
    protected $csvHelper;
    public const AVAILABILITY_IN_STOCK = 'in_stock';
    public const AVAILABILITY_OUT_OF_STOCK = 'out_of_stock';
    public function __construct()
    {
        $this->csvHelper = new ShoparizePartnerCsvHelper();
    }
    public function createFeedFile()
    {
        foreach (Shop::getShops() as $shop) {
            $cacheKey = 'ShoparizePartnerFeed::run_' . $shop['id_shop'];
            if (!Cache::isStored($cacheKey)) {
                $shoparizePartnerId = Configuration::get('SHOPARIZEPARTNER_SHOP_ID', null, null, $shop['id_shop']);
                if (empty($shoparizePartnerId)) {
                    continue;
                }

                $data = $this->getFeedData($shop['id_shop']);

                Cache::store($cacheKey, [$shoparizePartnerId, $data]);
            }

            list($shoparizePartnerId, $data) = Cache::retrieve($cacheKey);

            $feed = $this->getPartOfFeed($data);

            $this->saveToFile($feed, $shoparizePartnerId);
        }
    }

    /**
     * @param $id_lang
     * @param $start
     * @param $limit
     * @param $order_by
     * @param $order_way
     * @param bool $id_category
     * @param bool $only_active
     * @param Context|null $context
     * @param null $id_shop
     * @param string $time
     * @return array|bool|mysqli_result|PDOStatement|resource|void|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getProducts(
        $id_lang,
        $start,
        $limit,
        $order_by,
        $order_way,
        $id_category = false,
        $only_active = false,
        Context $context = null,
        $id_shop = null,
        $time = ''
    ) {
        if (!$context) {
            $context = Context::getContext();
        }

        $front = true;
        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            exit(Tools::displayError());
        }
        if ('id_product' == $order_by || 'price' == $order_by || 'date_add' == $order_by || 'date_upd' == $order_by) {
            $order_by_prefix = 'p';
        } elseif ('name' == $order_by) {
            $order_by_prefix = 'pl';
        } elseif ('position' == $order_by) {
            $order_by_prefix = 'c';
        }

        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by_prefix = $order_by[0];
            $order_by = $order_by[1];
        }
        $sql = 'SELECT p.*, ' . ($id_shop ? ' ps.*, ' : '') . ' pl.* , m.`name` AS manufacturer_name, s.`name` AS supplier_name
                FROM `' . _DB_PREFIX_ . 'product` p
                LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON (p.`id_product` = ps.`id_product`)
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' .
            ($id_shop ? ' AND pl.`id_shop` = ' . (int) $id_shop : Shop::addSqlRestrictionOnLang('pl')) .
            ') LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)' .
            ($id_category ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON (c.`id_product` = p.`id_product`)' : '') .
            ' WHERE pl.`id_lang` = ' . (int) $id_lang .
            ($id_category ? ' AND c.`id_category` = ' . (int) $id_category : '') .
            ($front && $id_shop ? ' AND ps.`visibility` IN ("both", "catalog")' : '') .
            ($only_active ? ' AND product_shop.`active` = 1' : '') .
            ($id_shop ? ' AND ps.`id_shop` = ' . (int) $id_shop : '') .
            ($time ? ' AND ps.`date_upd` >= "' . pSQL($time) . '"': '') .
            ' ORDER BY ' . (isset($order_by_prefix) ? pSQL($order_by_prefix) . '.' : '') . '`' . pSQL($order_by) .
            '` ' . pSQL($order_way) .
            ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if ('price' == $order_by) {
            Tools::orderbyPrice($rq, $order_way);
        }

        foreach ($rq as &$row) {
            $row = Product::getTaxesInformations($row);
        }

        return $rq;
    }

    /**
     * @return mixed|null
     */
    public function findCoverImage(array $images)
    {
        $cover = null;
        foreach ($images as $image) {
            if ($image['cover']) {
                $cover = $image;
                break;
            }
        }
        if (!$cover && !empty($images)) {
            $cover = $images[0];
        }

        return $cover;
    }

    public function getAdditionalImageUrl(string $productName, array $images): array
    {
        $urls = [];
        foreach ($images as $image) {
            if ($image['cover']) {
                continue;
            }
            $urls[] = Context::getContext()->link->getImageLink(
                $productName,
                $image['id_image'],
                ImageType::getFormattedName('large')
            );
        }

        return $urls;
    }

    public function saveToFile(string $data, string $shoparizePartnerId): void
    {
        $filename = sprintf('%s/%s.csv', _PS_ROOT_DIR_, $shoparizePartnerId);
        file_put_contents($filename, $data);
        Tools::chmodr($filename, 0777);
    }

    /**
     * @param $shopId
     * @param int $page
     * @param int $limit
     * @return array
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getFeedData($shopId, int $page = 0, int $limit = 0, string $time = ''): array
    {
        $page = $page > 1 && $limit > 0 ? ($page - 1) * $limit : 0;
        $rows = $this->getProducts(
            Configuration::get('PS_LANG_DEFAULT'),
            $page,
            $limit,
            'id_product',
            'ASC',
            null,
            null,
            null,
            $shopId,
            $time
        );

        $idLang = Configuration::get('PS_LANG_DEFAULT', null, null, $shopId);
        $currency = new Currency(
            Configuration::get('PS_CURRENCY_DEFAULT', null, null, $shopId),
            $idLang,
            $shopId
        );
        $data = [];
        foreach ($rows as $row) {
            $product = new Product($row['id_product'], false, $idLang, $shopId);
            $images = $product->getImages(
                Configuration::get('PS_LANG_DEFAULT', $idLang, null, $shopId)
            );
            $coverImage = $this->findCoverImage($images);
            $additionalImageLink = $this->getAdditionalImageUrl($product->name, $images);
            $productLink = Context::getContext()->link->getProductLink(
                $row['id_product'],
                null,
                null,
                null,
                $idLang,
                $shopId
            );

            $item = new ShoparizePartnerFeedItem();
            $item->setId($product->id);
            $item->setTitle($product->name);
            $item->setDescription(strip_tags($product->description_short));
            $item->setLink($productLink);
            $item->setMobileLink($productLink);
            $item->setImage(Context::getContext()->link->getImageLink(
                $product->link_rewrite,
                $coverImage['id_image'],
                ImageType::getFormattedName('large')
            ));
            foreach ($additionalImageLink as $image) {
                $item->setImage($image);
            }
            $availability = StockAvailable::getStockAvailableIdByProductId(
                $product->id,
                null,
                $shopId
            ) > 0
                ? self::AVAILABILITY_IN_STOCK
                : self::AVAILABILITY_OUT_OF_STOCK;
            $item->setAvailability($availability);

            $regularPrice = Product::getPriceStatic($product->id, true, null, 2, null, false, false);
            $item->setPrice(number_format($regularPrice, 2));
            $item->setCurrencyCode($currency->iso_code);
            $item->setBrand($product->manufacturer_name);
            $item->setGtin($product->ean13);
            $item->setCondition($product->condition);
            $item->setProductLength($product->depth);
            $item->setProductWidth($product->width);
            $item->setProductHeight($product->height);
            $item->setProductWeight($product->weight);
            $item->setSizeUnit(Configuration::get('PS_DIMENSION_UNIT'));

            $originalPrice = Product::getPriceStatic($product->id, true, null, 2);
            if ($regularPrice > $originalPrice) {
                $item->setSalePrice(number_format($originalPrice, 2));
            }

            $carriers = Carrier::getAvailableCarrierList($product, null);
            file_put_contents('./log.txt', print_r($carriers, true));

            $data[] = $item;
        }

        return $data;
    }

    public function getPartOfFeed(array $data): string
    {
        $this->csvHelper->cleanData();
        $this->csvHelper->setData($data);
        return $this->csvHelper->createFile();
    }
}
