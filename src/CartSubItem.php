<?php

namespace LukePOLO\LaraCart;

/**
 * Class CartItemOption
 *
 * @package LukePOLO\LaraCart
 */
class CartSubItem
{
    private $itemHash;

    public $locale;
    public $price = 0;
    public $items = [];
    public $options = [];
    public $internationalFormat;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->itemHash = app(LaraCart::HASH, $options);
        if (isset($options[LaraCart::PRICE]) === true) {
            $this->price = floatval($options[LaraCart::PRICE]);
        }
        $this->options = $options;
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
     * Gets the hash for the item
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->itemHash;
    }

    /**
     * Gets the formatted price
     *
     * @param $format $tax
     *
     * @return string
     */
    public function getPrice($format = true)
    {
        $price = $this->price;

        foreach ($this->items as $item) {
            $price += $item->getPrice(false, false);
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($price, $this->locale, $this->internationalFormat, $format);
    }

    /**
     * Magic Method allows for user input to set a value inside a object
     *
     * @param $option
     * @param $value
     */
    public function __set($option, $value)
    {
        array_set($this->options, $option, $value);
    }

    /**
     * Updates an option by its key
     *
     * @param $key
     * @param $value
     *
     * @return string
     */
    public function update($key, $value)
    {
        $this->$key = $value;

        return md5(json_encode($this->options));
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
}