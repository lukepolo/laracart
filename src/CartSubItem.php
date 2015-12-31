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
    public $price = 0;
    public $items = [];
    public $internationalFormat;
    private $itemHash;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->itemHash = app(LaraCart::HASH, $options);
        if (isset($options[LaraCart::PRICE])) {
            $this->price = $options[LaraCart::PRICE];
            array_forget($options, LaraCart::PRICE);
        }

        if (isset($options[self::ITEMS])) {
            $this->items = $options[self::ITEMS];
            array_forget($options, self::ITEMS);
        }

        $this->options = $options;
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
