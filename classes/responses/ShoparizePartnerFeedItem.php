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
class ShoparizePartnerFeedItem
{
    use ShoparizePartnerFormatter;

    public $id;

    public $title;

    public $description;

    public $link;

    public $images = [];

    public $mobile_link;

    public $availability;

    public $price;

    public $brand;

    public $gtin;

    public $condition;

    public $currency_code;

    public $shipping_length;

    public $shipping_width;

    public $shipping_height;

    public $shipping_weight;

    public $size_unit;

    public $sale_price;

    public $colors;

    public $sizes;

    /**
     * @var ShoparizePartnerFeedShipping[]
     */
    public $shipping = [];

    public $weight_unit;

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @param mixed $link
     */
    public function setLink($link): void
    {
        $this->link = $link;
    }

    /**
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->images[] = $image;
    }

    /**
     * @param mixed $mobile_link
     */
    public function setMobileLink($mobile_link): void
    {
        $this->mobile_link = $mobile_link;
    }

    /**
     * @param mixed $availability
     */
    public function setAvailability($availability): void
    {
        $this->availability = $availability;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $this->priceFormat($price);
    }

    /**
     * @param mixed $brand
     */
    public function setBrand($brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @param mixed $gtin
     */
    public function setGtin($gtin): void
    {
        $this->gtin = $gtin;
    }

    /**
     * @param mixed $condition
     */
    public function setCondition($condition): void
    {
        $this->condition = $condition;
    }

    /**
     * @param mixed $currency_code
     */
    public function setCurrencyCode($currency_code): void
    {
        $this->currency_code = $currency_code;
    }

    /**
     * @param mixed $shipping_length
     */
    public function setShippingLength($shipping_length): void
    {
        $this->shipping_length = $shipping_length;
    }

    /**
     * @param mixed $shipping_width
     */
    public function setShippingWidth($shipping_width): void
    {
        $this->shipping_width = $shipping_width;
    }

    /**
     * @param mixed $shipping_height
     */
    public function setShippingHeight($shipping_height): void
    {
        $this->shipping_height = $shipping_height;
    }

    /**
     * @param mixed $shipping_weight
     */
    public function setShippingWeight($shipping_weight): void
    {
        $this->shipping_weight = $shipping_weight;
    }

    /**
     * @param mixed $size_unit
     */
    public function setSizeUnit($size_unit): void
    {
        $this->size_unit = $size_unit;
    }

    /**
     * @param mixed $sale_price
     */
    public function setSalePrice($sale_price): void
    {
        $this->sale_price = $this->priceFormat($sale_price);
    }

    /**
     * @param ShoparizePartnerFeedShipping $shipping
     */
    public function setShipping(ShoparizePartnerFeedShipping $shipping): void
    {
        $this->shipping[] = $shipping;
    }

    /**
     * @param mixed $weight_unit
     */
    public function setWeightUnit($weight_unit): void
    {
        $this->weight_unit = $weight_unit;
    }

    /**
     * @param mixed $colors
     */
    public function setColors($colors): void
    {
        $this->colors = $colors;
    }

    /**
     * @param mixed $sizes
     */
    public function setSizes($sizes): void
    {
        $this->sizes = $sizes;
    }
}
