<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethodsTrait;

/**
 * Class CartItem
 *
 * @package LukePOLO\LaraCart
 */
class CartItem
{
    use CartOptionsMagicMethodsTrait;

    protected $itemHash;

    public $lineItem;
    public $taxable;
    public $subItems = [];
    public $couponInfo = [];

    /**
     * CartItem constructor.
     *
     * @param $id
     * @param $name
     * @param $qty
     * @param $price
     * @param array $options
     * @param bool|true $taxable
     * @param bool|false $lineItem
     */
    public function __construct($id, $name, $qty, $price, $options = [], $taxable = true, $lineItem = false)
    {
        $this->id = $id;
        $this->qty = $qty;
        $this->name = $name;
        $this->taxable = $taxable;
        $this->lineItem = $lineItem;
        $this->price = floatval($price);
        $this->tax = config('laracart.tax');

        foreach($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
     * Generates a hash based on the cartItem array
     *
     * @param bool $force
     *
     * @return string itemHash
     */
    public function generateHash($force = false)
    {
        if ($this->lineItem === false) {
            $this->itemHash = null;

            $cartItemArray = (array)$this;
            
            unset($cartItemArray['options']['qty']);
            
            ksort($cartItemArray['options']);

            $this->itemHash = app(LaraCart::HASH, $cartItemArray);
        } elseif ($force || empty($this->itemHash) === true) {
            $this->itemHash = app(LaraCart::RANHASH);
        }

        \Event::fire(
            'laracart.updateItem', [
                'item' => $this,
                'newHash' => $this->itemHash
            ]
        );

        return $this->itemHash;
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
     * Finds a sub item by its hash
     *
     * @param $subItemHash
     * @return mixed
     */
    public function findSubItem($subItemHash)
    {
        return array_get($this->subItems, $subItemHash);
    }

    /**
     * Adds an sub item to a item
     *
     * @param array $subItem
     *
     * @return string $itemHash
     */
    public function addSubItem(array $subItem)
    {
        $subItem = new CartSubItem($subItem);

        $this->subItems[$subItem->getHash()] = $subItem;

        $this->generateHash();

        return $subItem;
    }

    /**
     * Removes a sub item from the item
     *
     * @param $subItemHash
     */
    public function removeSubItem($subItemHash)
    {
        unset($this->subItems[$subItemHash]);

        $this->generateHash();
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
        $price = $this->price + $this->subItemsTotal($tax, false);
        if ($tax && $this->taxable) {
            $price += $price * $this->tax;
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($price, $this->locale, $this->internationalFormat, $format);
    }

    /**
     * Gets the sub total of the item based on the qty with or without tax in the proper format
     *
     * @param bool $tax
     * @param bool $format
     * @param bool $withDiscount
     *
     * @return float|string
     */
    public function subTotal($tax = false, $format = true, $withDiscount = true)
    {
        $total = $this->getPrice($tax, false) * $this->qty;

        if ($withDiscount) {
            $total -= $this->getDiscount(false);
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($total, $this->locale, $this->internationalFormat, $format);
    }


    /**
     * Gets the totals for the options
     *
     * @param bool|false $tax
     * @param bool|true $format
     *
     * @return int|mixed|string
     */
    public function subItemsTotal($tax = false, $format = true)
    {
        $total = 0;

        foreach ($this->subItems as $item) {
                $total += $item->getPrice(false);
        }

        $total *= $this->qty;


        if ($tax && $this->taxable) {
            $total = $total + ($total * $this->tax);
        }

        return \App::make(LaraCart::SERVICE)->formatMoney($total, $this->locale, $this->internationalFormat, $format);
    }

    /**
     * Gets the discount of an item
     *
     * @param bool|true $format
     *
     * @return mixed|null|string
     */
    public function getDiscount($format = true)
    {
        return \App::make(LaraCart::SERVICE)->formatMoney(
            $this->discount,
            $this->locale,
            $this->internationalFormat,
            $format
        );
    }
}
