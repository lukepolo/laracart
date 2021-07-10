<?php

namespace LukePOLO\LaraCart;

use Illuminate\Support\Arr;
use LukePOLO\LaraCart\Traits\CartOptionsMagicMethodsTrait;

/**
 * Class CartItemOption.
 *
 * @property float price
 * @property array options
 * @property array items
 */
class CartSubItem
{
    use CartOptionsMagicMethodsTrait;

    const ITEMS = 'items';

    public $locale;
    public $currencyCode;

    private $itemHash;

    /**
     * @param $options
     */
    public function __construct($options)
    {
        $this->options['items'] = [];

        foreach ($options as $option => $value) {
            Arr::set($this->options, $option, $value);
        }

        $this->tax = isset($options['tax']) ? $options['tax'] == 0 ? config('laracart.tax') : $options['tax'] : config('laracart.tax');

        $this->itemHash = app(LaraCart::HASH)->hash($this->options);
    }

    /**
     * Gets the hash for the item.
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->itemHash;
    }

    /**
     * Gets the formatted price.
     *
     * @return float
     */
    public function subTotal()
    {
        $price = $this->price * ($this->qty || 1);

        if (isset($this->items)) {
            foreach ($this->items as $item) {
                $price += $item->subTotal(false);
            }
        }

        return $price;
    }

    /**
     * Search for matching options on the item.
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
}
