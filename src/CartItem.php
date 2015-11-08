<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\Exceptions\InvalidQuantity;

/**
 * Class CartItem
 *
 * @package LukePOLO\LaraCart
 */
class CartItem
{
    protected $itemHash;

    public $id;
    public $tax;
    public $qty;
    public $name;
    public $code;
    public $price;
    public $locale;
    public $discount;
    public $lineItem;
    public $options = [];
    public $subItems = [];
    public $internationalFormat;

    /**
     * @param string $id
     * @param string $name
     * @param int $qty
     * @param float $price
     * @param array $options
     * @param bool $lineItem
     */
    public function __construct($id, $name, $qty, $price, $options = [], $lineItem = false)
    {
        $this->id = $id;
        $this->qty = $qty;
        $this->name = $name;
        $this->options = $options;
        $this->lineItem = $lineItem;
        $this->price = floatval($price);
        $this->tax = config('laracart.tax');

        $this->generateHash();
    }

    /**
     * Magic Method allows for user input as an object
     *
     * @param $option
     *
     * @return mixed | null
     */
    public function __get($option)
    {
        return array_get($this->options, $option);
    }

    /**
     * Magic Method allows for user input to set a value inside the options array
     *
     * @param $option
     * @param $value
     */
    public function __set($option, $value)
    {
        array_set($this->options, $option, $value);
    }

    /**
     * Magic Method allows for user to check if an option isset
     *
     * @param $option
     *
     * @return bool
     */
    public function __isset($option)
    {
        if (empty($this->options[$option]) === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * // TODO - badly named
     * Generates a hash based on the cartItem array
     *
     * @param bool $force
     *
     * @return string itemHash
     */
    public function generateHash($force = false)
    {
        if ($this->lineItem === false) {
            $this->itemHash = null;

            $cartItemArray = (array)$this;

            ksort($cartItemArray['options']);

            $this->itemHash = app(LaraCart::HASH, $cartItemArray);
        } elseif ($force || empty($this->itemHash) === true) {
            $this->itemHash = app(LaraCart::RANHASH);
        }
        return $this->itemHash;
    }

    /**
     * Gets the hash for the item
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->itemHash;
    }

    /**
     * Finds an items option by its key and value
     *
     * @param $itemHash
     * @return mixed
     */
    public function findSubItem($itemHash)
    {
        return array_get($this->subItems, $itemHash);
    }

    /**
     * Adds an option to a cart item
     *
     * @param array $subItem
     *
     * @return string $itemHash
     */
    public function addSubItem(array $subItem)
    {
        $subItem = new CartSubItem($subItem);

        $this->subItems[$subItem->getHash()] = $subItem;

        $this->generateHash();

        return $subItem;
    }

    /**
     * Gets the price of the item with or without tax, with the proper format
     *
     * @param bool $tax
     * @param bool $format
     *
     * @return float|string
     */
    public function getPrice($tax = false, $format = true)
    {
        $price = $this->price;

        foreach ($this->subItems as $subItem) {
            $price += $subItem->getPrice(false);
        }

        if ($tax) {
            $price += $price * $this->tax;
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($price, $this->locale, $this->internationalFormat, $format);
    }

    /**
     * Updates an items properties
     *
     * @param $key
     * @param $value
     *
     * @throws InvalidQuantity | InvalidPrice
     *
     * @return string $itemHash
     */
    public function update($key, $value)
    {
        switch ($key) {
            case LaraCart::QTY:
                if (is_int($value) === false) {
                    throw new InvalidQuantity();
                }
                break;
            case LaraCart::PRICE:
                if (is_numeric($value) === false || preg_match('/\.(\d){3}/', $value)) {
                    throw new InvalidPrice();
                }
                break;
        }

        $this->$key = $value;

        return $this->generateHash();
    }

    /**
     * Gets the sub total of the item based on the qty with or without tax in the proper format
     *
     * @param bool $tax
     * @param bool $format
     * @param bool $withDiscount
     *
     * @return float|string
     */
    public function subTotal($tax = false, $format = true, $withDiscount = true)
    {
        $total = ($this->getPrice($tax, false) + $this->subItemsTotal($tax, false)) * $this->qty;
        if ($withDiscount) {
            $total -= $this->getDiscount(false);
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($total, $this->locale, $this->internationalFormat, $format);
    }


    /**
     * Gets the totals for the options
     *
     * @param bool|false $tax
     * @param bool|true $format
     *
     * @return int|mixed|string
     */
    public function subItemsTotal($tax = false, $format = true)
    {
        $total = 0;
        foreach ($this->subItems as $item) {
            if (isset($item->price)) {
                $total += array_get($item->options, LaraCart::PRICE);
            }
        }

        if ($tax) {
            $total = $total + ($total * $this->tax);
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($total, $this->locale, $this->internationalFormat, $format);
    }

    /**
     * Gets the discount of an item
     *
     * @param bool|true $format
     *
     * @return mixed|null|string
     */
    public function getDiscount($format = true)
    {
        // TODO - move to main laracart should not be in here
        if (\App::make(LaraCart::SERVICE)->findCoupon($this->code)) {
            $discount = $this->discount;
        } else {
            $discount = 0;
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($discount, $this->locale, $this->internationalFormat, $format);
    }
}
