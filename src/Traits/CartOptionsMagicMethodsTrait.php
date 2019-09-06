<?php

namespace LukePOLO\LaraCart\Traits;

use Illuminate\Support\Arr;
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
        return Arr::get($this->options, $option);
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
                if (!is_numeric($value) || $value <= 0) {
                    throw new InvalidQuantity('The quantity must be a valid number');
                }
                break;
            case CartItem::ITEM_PRICE:
                if (!is_numeric($value)) {
                    throw new InvalidPrice('The price must be a valid number');
                }
                break;
            case CartItem::ITEM_TAX:
                if (!empty($value) && (!is_numeric($value))) {
                    throw new InvalidTaxableValue('The tax must be a number');
                }
                break;
            case CartItem::ITEM_TAXABLE:
                if (!is_bool($value) && $value != 0 && $value != 1) {
                    throw new InvalidTaxableValue('The taxable option must be a boolean');
                }
                break;
        }

        $changed = (!empty(Arr::get($this->options, $option)) && Arr::get($this->options, $option) != $value);
        Arr::set($this->options, $option, $value);

        if ($changed) {
            if (is_callable([$this, 'generateHash'])) {
                $this->generateHash();
            }
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
        if (isset($this->options[$option])) {
            return true;
        } else {
            return false;
        }
    }
}
