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
    public $internationalFormat;

    private $itemHash;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->options['items'] = [];

        foreach ($options as $option => $value) {
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
     * @return string
     */
    public function price($format = true)
    {
        $price = $this->price;

        if (isset($this->items)) {
            foreach ($this->items as $item) {
                $price += $item->price(false);
            }
        }

        return LaraCart::formatMoney($price, $this->locale, $this->internationalFormat, $format);
    }
}
