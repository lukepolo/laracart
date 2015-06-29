<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Exceptions\InvalidOption;

/**
 * Class CartItem
 *
 * @package LukePOLO\LaraCart
 */
class CartItem
{
    public $id;
    public $name;
    public $qty;
    public $price;
    public $options = [];

    public $tax;
    public $locale;
    public $displayLocale;

    /**
     * @param $id
     * @param $name
     * @param $qty
     * @param $price
     * @param array $options
     */
    public function __construct($id, $name, $qty, $price, $options = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->qty = $qty;
        $this->price = (float) $price;

        // Sets the tax and Locale for the item
        $this->tax = config('laracart.tax');
        $this->locale = config('laracart.locale', 'en_US');
        $this->displayLocale = config('laracart.display_locale');

        if(empty($options) === false) {
            // Generates all the options for the cart item
            foreach($options as $option) {
                $this->addOption($option);
            }
        }

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
        // Initial  price of the item
        $price = $this->price;

        // Check to see if any of the sub options have a price associated with it
        foreach($this->options as $option) {
            $price += $option->price;
        }

        // add tax to the item
        if($tax) {
            $price += $price * $this->tax;
        }

        // Formats the price based on the locale
        if($format) {
            return LaraCart::formatMoney($price, $this->locale, $this->displayLocale);
        } else {
            return $price;
        }
    }

    /**
     * TODO - move from cart to here
     *
     * @param $key
     * @param $value
     *
     * @throws UnknownItemProperty
     */
    public function update($key, $value)
    {
        Dump($key);
        die;
        if(isset($item->$attr) === true) {
            $item->$attr = $value;
            array_forget($this->cart->items, $itemHash);
            $this->addItem($item);
        } else {
            throw new UnknownItemProperty();
        }
    }

    /**
     * Gets the sub total of the item based on the qty with or without tax in the proper format
     *
     * @param bool $tax
     * @param bool $format
     *
     * @return float|string
     */
    public function subTotal($tax = false, $format = true)
    {
        // Formats the total basd on the locale
        if($format) {
            return LaraCart::formatMoney($this->getPrice($tax, false) * $this->qty, $this->locale, $this->displayLocale);
        } else {
            return $this->getPrice($tax, false) * $this->qty;
        }
    }

    /**
     * Adds an option to a cart item
     *
     * @param array $option
     */
    public function addOption(array $option)
    {
        $cartItemOption = new CartItemOption($option);

        $this->options[] = $cartItemOption;
    }

    /**
     * Updates an items option by a key value pair
     *
     * @param $keyValue - the value that is used to search for a specific option
     * @param $updateKey - the key that you wish to update
     * @param $updateValue - the value to replace inside the key
     * @param string $updateByKey - the key that it searches for to find the option
     *
     * @throws InvalidOption
     */
    public function updateOption($keyValue, $updateKey, $updateValue, $updateByKey = 'id')
    {
        $option = $this->findOption($updateByKey, $keyValue);

        if(empty($option) === false) {
            $option->update($updateKey, $updateValue);
        } else {
            throw new InvalidOption();
        }
    }

    /**
     * Finds an items option by its key and value
     *
     * @param $updateByKey
     * @param $keyValue
     *
     * @return mixed
     */
    public function findOption($updateByKey, $keyValue)
    {
        return array_first($this->options, function($optionKey, $optionValue) use($updateByKey, $keyValue)
        {
            if($optionValue->$updateByKey == $keyValue) {
                return true;
            }
        });
    }

    /**
     * Updates all options for an item
     * @param $options
     */
    public function updateOptions($options)
    {
        $this->options = [];

        if(empty($options) === false) {
            // Generates all the options for the cart item
            foreach($options as $option) {
                $this->addOption($option);
            }
        }
    }
}