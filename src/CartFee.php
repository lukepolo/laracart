<?php

namespace LukePOLO\LaraCart;

/**
 * Class CartFee
 *
 * @package LukePOLO\LaraCart
 */
class CartFee
{
    public $locale;
    public $amount;
    public $taxable;
    public $options = [];
    public $internationalFormat;

    /**
     * CartFee constructor.
     *
     * @param $amount
     * @param $taxable
     * @param array $options
     */
    public function __construct($amount, $taxable, $options = [])
    {
        $this->amount = floatval($amount);
        $this->taxable = $taxable;
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
     * Gets the formatted amount
     *
     * @return string
     */
    public function getAmount()
    {
        return \LaraCart::formatMoney($this->amount, $this->locale, $this->internationalFormat);
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
