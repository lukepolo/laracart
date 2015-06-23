<?php

namespace LukePOLO\LaraCart;

/**
 * Class CartItem
 *
 * @package LukePOLO\LaraCart
 */
class CartItem
{
    public $itemID;
    public $name;
    public $qty;
    public $price;
    public $options;

    public $tax;
    public $locale;
    public $displayLocale;

    /**
     * @param $itemID
     * @param $name
     * @param $qty
     * @param $price
     * @param array $options
     */
    public function __construct($itemID, $name, $qty, $price, $options = [])
    {
        $this->itemID = $itemID;
        $this->name = $name;
        $this->qty = $qty;
        $this->price = (float) $price;

        // Sets the tax and Locale for the item
        $this->tax = config('laracart.tax');
        $this->locale = config('laracart.locale', 'en_US');
        $this->displayLocale = config('laracart.display_locale');

        // Generates all the options for the cart item
        foreach($options as $option) {
            $this->options[] = new CartItemOption($option);
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
            $price += $option->search('price');
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
}