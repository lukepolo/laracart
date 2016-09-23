<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethods;

/**
 * Class CartItemOption.
 *
 * @property float price
 * @property array options
 * @property array items
 */
class CartItemModifier
{
    use CartOptionsMagicMethods;

    const ITEMS = 'items';

    public $locale;
    public $internationalFormat;

    private $itemHash;

    /**
     * CartItemModifier constructor.
     *
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
     * Gets the hash for the item.
     *
     * @return mixed
     */
    public function hash()
    {
        return $this->itemHash;
    }

    /**
     * Search for matching options on the item.
     *
     * @param $data
     *
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
     * Gets the formatted price.
     *
     * @param bool $taxedItemsOnly
     *
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

    /**
     * Adds an item to the modifier.
     *
     * @param CartItem $cartItem
     */
    public function addItem(CartItem $cartItem)
    {
        $this->items[$cartItem->hash()] = $cartItem;

        $this->updateCart();
    }

    /**
     * Removes a item from the sub item.
     *
     * @param $itemHash
     */
    public function removeItem($itemHash)
    {
        unset($this->items[$itemHash]);

        $this->updateCart();
    }
}
