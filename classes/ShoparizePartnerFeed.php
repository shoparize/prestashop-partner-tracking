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
    protected $csvHelper;

    public const AVAILABILITY_IN_STOCK = 'in_stock';
    public const AVAILABILITY_OUT_OF_STOCK = 'out_of_stock';

    public function __construct()
    {
        $this->csvHelper = new ShoparizePartnerCsvHelper();
    }

    public function run()
    {
        $data = [];
        $cacheKey = 'ShoparizePartnerFeed::run';
        if (!Cache::isStored($cacheKey)) {
            foreach (Shop::getShops() as $shop) {
                $rows = $this->getProducts(
                    Configuration::get('PS_LANG_DEFAULT'),
                    0,
                    0,
                    'id_product',
                    'ASC',
                    null,
                    null,
                    null,
                    $shop['id_shop']
                );

                $idLang = Configuration::get('PS_LANG_DEFAULT', null, null, $shop['id_shop']);
                $currency = new Currency(
                    Configuration::get('PS_CURRENCY_DEFAULT', null, null, $shop['id_shop']),
                    $idLang,
                    $shop['id_shop']
                );
                foreach ($rows as $row) {
                    $product = new Product($row['id_product'], false, $idLang, $shop['id_shop']);
                    $images = $product->getImages(
                        Configuration::get('PS_LANG_DEFAULT', $idLang, null, $shop['id_shop'])
                    );
                    $coverImage = $this->findCoverImage($images);
                    $additionalImageLink = $this->getAdditionalImageUrl($product->name, $images);
                    $productLink = Context::getContext()->link->getProductLink(
                        $row['id_product'],
                        null,
                        null,
                        null,
                        $idLang,
                        $shop['id_shop']
                    );
                    $row = [
                        ShoparizePartnerCsvHelper::ORDER_ID => sprintf(
                            '%s_%d',
                            Configuration::get('SHOPARIZEPARTNER_SHOP_ID', null, null, $shop['id_shop']),
                            $product->id
                        ),
                        ShoparizePartnerCsvHelper::ORDER_TITLE => $product->name,
                        ShoparizePartnerCsvHelper::ORDER_DESCRIPTION => strip_tags($product->description),
                        ShoparizePartnerCsvHelper::ORDER_LINK => $productLink,
                        ShoparizePartnerCsvHelper::ORDER_IMAGE_LINK => Context::getContext()->link->getImageLink(
                            $product->link_rewrite,
                            $coverImage['id_image'],
                            ImageType::getFormattedName('large')
                        ),
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK => $additionalImageLink[0] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_2 => $additionalImageLink[1] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_3 => $additionalImageLink[2] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_4 => $additionalImageLink[3] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_5 => $additionalImageLink[4] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_6 => $additionalImageLink[5] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_7 => $additionalImageLink[6] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_8 => $additionalImageLink[7] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_9 => $additionalImageLink[8] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_ADDITIONAL_IMAGE_LINK_10 => $additionalImageLink[9] ?? '',
                        ShoparizePartnerCsvHelper::ORDER_MOBILE_LINK => $productLink,
                        ShoparizePartnerCsvHelper::ORDER_AVAILABILITY => StockAvailable::getStockAvailableIdByProductId(
                            $product->id,
                            null,
                            $shop['id_shop']
                        ) > 0
                            ? self::AVAILABILITY_IN_STOCK
                            : self::AVAILABILITY_OUT_OF_STOCK,
                        ShoparizePartnerCsvHelper::ORDER_PRICE => sprintf(
                            '%.2f %s',
                            $product->price,
                            $currency->iso_code
                        ),
                        ShoparizePartnerCsvHelper::ORDER_BRAND => $product->manufacturer_name,
                        ShoparizePartnerCsvHelper::ORDER_GTIN => $product->reference,
                        ShoparizePartnerCsvHelper::ORDER_CONDITION => $product->condition,
                    ];

                    $data[] = $row;
                }
            }

            Cache::store($cacheKey, $data);
        }

        $data = Cache::retrieve($cacheKey);

        $this->csvHelper->setData($data);
        $feed = $this->csvHelper->createFile();
        $this->saveToFile($feed);
    }

    /**
     * @param $id_lang
     * @param $start
     * @param $limit
     * @param $order_by
     * @param $order_way
     * @param $id_category
     * @param $only_active
     * @param $id_shop
     *
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
        $id_shop = null
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
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)' .
            ($id_category ? 'LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON (c.`id_product` = p.`id_product`)' : '') .
            ' WHERE pl.`id_lang` = ' . (int) $id_lang .
            ($id_category ? ' AND c.`id_category` = ' . (int) $id_category : '') .
            ($front && $id_shop ? ' AND ps.`visibility` IN ("both", "catalog")' : '') .
            ($only_active ? ' AND product_shop.`active` = 1' : '') .
            ($id_shop ? ' AND ps.`id_shop` = ' . (int) $id_shop : '') .
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

    public function saveToFile(string $data): void
    {
        $filename = sprintf('%s/shoparize_partner_feed.csv', _PS_ROOT_DIR_);
        file_put_contents($filename, $data);
        Tools::chmodr($filename, 0777);
    }
}
