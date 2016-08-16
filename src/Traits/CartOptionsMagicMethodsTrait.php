<?php

namespace LukePOLO\LaraCart\Traits;

use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\Exceptions\InvalidQuantity;
use LukePOLO\LaraCart\Exceptions\InvalidTaxableValue;

/**
 * Class CartOptionsMagicMethodsTrait.
 */
trait CartOptionsMagicMethodsTrait
{
    public $options = [];

    /**
     * Magic Method allows for user input as an object.
     *
     * @param $option
     *
     * @return mixed | null
     */
    public function __get($option)
    {
        try {
            return $this->$option;
        } catch (\ErrorException $e) {
            return array_get($this->options, $option);
        }
    }

    /**
     * Magic Method allows for user input to set a value inside the options array.
     *
     * @param $option
     * @param $value
     *
     * @throws InvalidPrice
     * @throws InvalidQuantity
     * @throws InvalidTaxableValue
     */
    public function __set($option, $value)
    {
        switch ($option) {
            case CartItem::ITEM_QTY:
                if (!is_numeric($value) || $value < 0) {
                    throw new InvalidQuantity('The quantity must be a valid number');
                }
                break;
            case CartItem::ITEM_PRICE:
                if (!is_numeric($value)) {
                    throw new InvalidPrice('The price must be a valid number');
                }
                break;
            case CartItem::ITEM_TAX:
                if (!empty($value) && (!is_numeric($value) || $value > 1)) {
                    throw new InvalidTaxableValue('The tax must be a float less than 1');
                }
                break;
            case CartItem::ITEM_TAXABLE:
                if (!is_bool($value) && $value != 0 && $value != 1) {
                    throw new InvalidTaxableValue('The taxable option must be a boolean');
                }
                break;
        }
        array_set($this->options, $option, $value);

        if (is_callable([$this, 'generateHash'])) {
            $this->generateHash();
        }
    }

    /**
     * Magic Method allows for user to check if an option isset.
     *
     * @param $option
     *
     * @return bool
     */
    public function __isset($option)
    {
        if (!empty($this->options[$option])) {
            return true;
        } else {
            return false;
        }
    }
}
