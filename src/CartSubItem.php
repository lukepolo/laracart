<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethods;

/**
 * Class CartItemOption
 * @property float price
 * @property array options
 * @property array items
 * @package LukePOLO\LaraCart
 */
class CartSubItem
{
    use CartOptionsMagicMethods;

    const ITEMS = 'items';

    public $locale;
    public $internationalFormat;

    private $itemHash;

    /**
     * CartSubItem constructor.
     * @param $options
     */
    public function __construct($options)
    {
        $this->options['items'] = [];

        foreach ($options as $option => $value) {
            array_set($this->options, $option, $value);
        }

        $this->itemHash = $this->hash($this->options);
    }

    /**
     * Gets the hash for the item
     * @return mixed
     */
    public function getHash()
    {
        return $this->itemHash;
    }

    /**
     * Search for matching options on the item
     * @return mixed
     */
    public function find($data)
    {
        foreach ($data as $key => $value) {
            if ($this->$key === $value) {
                return $this;
            }
        }
    }

    /**
     * Gets the formatted price
     * @param bool $taxedItemsOnly
     * @return string
     */
    public function price($taxedItemsOnly = true)
    {
        $price = $this->price;

        if (isset($this->items)) {
            foreach ($this->items as $item) {
                if ($taxedItemsOnly && !$item->taxable) {
                    continue;
                }
                $price += $item->price(false, $taxedItemsOnly);
            }
        }

        return $this->formatMoney($price, $this->locale, $this->internationalFormat);
    }
}
