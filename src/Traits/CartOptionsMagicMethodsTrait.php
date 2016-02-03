<?php

namespace LukePOLO\LaraCart\Traits;

use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\Exceptions\InvalidQuantity;
use LukePOLO\LaraCart\LaraCart;

/**
 * Class CartOptionsMagicMethodsTrait
 *
 * @package LukePOLO\LaraCart\Traits
 */
trait CartOptionsMagicMethodsTrait
{
    public $options = [];

    /**
     * Magic Method allows for user input as an object
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
     * Magic Method allows for user input to set a value inside the options array
     *
     * @param $option
     * @param $value
     *
     * @throws InvalidPrice
     * @throws InvalidQuantity
     */
    public function __set($option, $value)
    {
        switch ($option) {
            case LaraCart::QTY:
                if (!is_numeric($value) || $value < 0) {
                    throw new InvalidQuantity('The quantity must be a valid number');
                }
                break;
            case LaraCart::PRICE:
                if (!is_numeric($value)) {
                    throw new InvalidPrice('The price must be a valid number');
                }
                break;
        }
        array_set($this->options, $option, $value);

        if (is_callable(array($this, 'generateHash'))) {
            $this->generateHash();
        }
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
        if (!empty($this->options[$option])) {
            return true;
        } else {
            return false;
        }
    }
}
