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

class ShoparizePartnerCsvHelper
{
    const ORDER_ID = 0;

    const HEADER_ID = 'id';

    const ORDER_TITLE = 1;

    const HEADER_TITLE = 'title';

    const ORDER_DESCRIPTION = 2;

    const HEADER_DESCRIPTION = 'description';

    const ORDER_LINK = 3;

    const HEADER_LINK = 'link';

    const ORDER_IMAGE_LINK = 4;

    const HEADER_IMAGE_LINK = 'image link';

    const ORDER_ADDITIONAL_IMAGE_LINK = 5;

    const HEADER_ADDITIONAL_IMAGE_LINK = 'additional image link';

    const ORDER_ADDITIONAL_IMAGE_LINK_2 = 6;

    const ORDER_ADDITIONAL_IMAGE_LINK_3 = 7;

    const ORDER_ADDITIONAL_IMAGE_LINK_4 = 8;

    const ORDER_ADDITIONAL_IMAGE_LINK_5 = 9;

    const ORDER_ADDITIONAL_IMAGE_LINK_6 = 10;

    const ORDER_ADDITIONAL_IMAGE_LINK_7 = 11;

    const ORDER_ADDITIONAL_IMAGE_LINK_8 = 12;

    const ORDER_ADDITIONAL_IMAGE_LINK_9 = 13;

    const ORDER_ADDITIONAL_IMAGE_LINK_10 = 14;

    const ORDER_MOBILE_LINK = 15;

    const HEADER_MOBILE_LINK = 'mobile link';

    const ORDER_AVAILABILITY = 16;

    const HEADER_AVAILABILITY = 'availability';

    const ORDER_PRICE = 17;

    const HEADER_PRICE = 'price';

    const ORDER_BRAND = 18;

    const HEADER_BRAND = 'brand';

    const ORDER_GTIN = 19;

    const HEADER_GTIN = 'gtin';

    const ORDER_CONDITION = 20;

    const HEADER_CONDITION = 'condition';

    const FILE_HEADERS = [
        self::ORDER_ID => self::HEADER_ID,
        self::ORDER_TITLE => self::HEADER_TITLE,
        self::ORDER_DESCRIPTION => self::HEADER_DESCRIPTION,
        self::ORDER_LINK => self::HEADER_LINK,
        self::ORDER_IMAGE_LINK => self::HEADER_IMAGE_LINK,
        self::ORDER_MOBILE_LINK => self::HEADER_MOBILE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_2 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_3 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_4 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_5 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_6 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_7 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_8 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_9 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_ADDITIONAL_IMAGE_LINK_10 => self::HEADER_ADDITIONAL_IMAGE_LINK,
        self::ORDER_AVAILABILITY => self::HEADER_AVAILABILITY,
        self::ORDER_PRICE => self::HEADER_PRICE,
        self::ORDER_BRAND => self::HEADER_BRAND,
        self::ORDER_GTIN => self::HEADER_GTIN,
        self::ORDER_CONDITION => self::HEADER_CONDITION,
    ];

    protected $delimiter;

    protected $enclosure;

    protected $data = [];

    public function __construct($delimiter = "\t", $enclosure = '"')
    {
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;

        $this->data[] = self::FILE_HEADERS;
        ksort($this->data);
    }

    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    public function getData()
    {
        return $this->data;
    }

    public function initFileHeader()
    {
        $this->data = [];
        $this->data[] = self::FILE_HEADERS;
        ksort($this->data);
    }

    public function cleanData()
    {
        $this->data = [];
    }

    public function getHeader()
    {
        $header = self::FILE_HEADERS;
        ksort($header);

        return $header;
    }

    public function createFile()
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($this->data as $line) {
            ksort($line);
            fputcsv($handle, $line, $this->delimiter, $this->enclosure);
        }
        rewind($handle);

        $contents = '';
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);

        return $contents;
    }

    public function associateFields()
    {
        $headers = $this->getHeader();
        $associatedData = [];
        foreach ($this->data as $itemKey => $item) {
            foreach ($item as $key => $value) {
                $associatedData[$itemKey][$headers[$key]] = $value;
            }
        }

        $this->data = $associatedData;
    }
}
