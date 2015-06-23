<?php

namespace LukePOLO\LaraCart;

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

    // Currency for the item
    public function __construct($itemID, $name, $qty, $price, $options = [])
    {
        $this->itemID = $itemID;
        $this->name = $name;
        $this->qty = $qty;
        $this->price = (float) $price;

        $this->tax = config('laracart.tax');

        $this->locale = config('laracart.locale', 'en_US');
        $this->displayLocale = config('laracart.display_locale');

        foreach($options as $option) {
            $this->options[] = new CartItemOption($option);
        }
    }

    public function getPrice($tax = false, $format = true)
    {
        $price = $this->price;

        if($tax) {
            $tax = $this->tax;
        } else {
            $tax = 0;
        }

        foreach($this->options as $option) {
            $price += $option->search('price');
        }

        $price += $price * $tax;

        if($format) {
            return LaraCart::formatMoney($price, $this->locale, $this->displayLocale);
        } else {
            return $price;
        }
    }

    public function subTotal($tax = false, $format = true)
    {
        if($format) {
            return LaraCart::formatMoney($this->getPrice($tax, false) * $this->qty, $this->locale, $this->displayLocale);
        } else {
            return $this->getPrice($tax, false) * $this->qty;
        }
    }
}