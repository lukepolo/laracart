<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethodsTrait;

/**
 * Class CartItemOption
 *
 * @package LukePOLO\LaraCart
 */
class CartSubItem
{
    use CartOptionsMagicMethodsTrait;

    const ITEMS = 'items';

    public $locale;

    public $items = [];
    public $internationalFormat;
    private $itemHash;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        foreach($options as $option => $value) {
            array_set($this->options, $option, $value);
        }

        $this->itemHash = app(LaraCart::HASH, $this->options);
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
     * @param bool|true $format
     *
     * @return mixed
     */
    public function getPrice($format = true)
    {
        $price = $this->price;

        foreach ($this->items as $item) {
            $price += $item->getPrice(false, false) + $item->subItemsTotal(false, false);
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($price, $this->locale, $this->internationalFormat, $format);
    }
}
