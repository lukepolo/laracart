<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Exceptions\UnknownItemProperty;

/**
 * Class Cart
 *
 * @package LukePOLO\LaraCart
 */
class Cart
{
    /**
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    protected $instance;
    public $locale;
    public $displayLocale;
    public $cart;
    public $tax;

    function __construct()
    {
        // TODO -- allow for different type of sessions
        $this->session = app('session');

        // Setup the Locale and Tax Variables for the Cart
        $this->locale = config('laracart.locale', 'en_US');
        $this->displayLocale = config('laracart.display_locale');
        $this->tax = config('laracart.tax');

        // Set a default instance of the cart
        $this->setInstance();
    }

    /**
     * Sets and Gets the instance of the cart in the session we should be using
     * @param string $instance
     */
    public function setInstance($instance = 'default')
    {
        $this->instance = $instance;

        $this->get($instance);

        // TODO - fire event that there was a new instance of the cart
    }

    /**
     * Gets the instance in the session
     *
     * @param string $instance
     */
    public function get($instance = 'default')
    {
        $this->cart = \Session::get(config('laracart.cache_prefix', 'laracart_').$instance);
    }

    /**
     * Creates a CartItem and then adds it to cart
     *
     * @param $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     *
     * @return string
     */
    public function add($itemID, $name = null, $qty = 1, $price = '0.00', $options = [])
    {
        return $this->addItem(new CartItem(
            $itemID,
            $name,
            $qty,
            $price,
            $options
        ));
    }

    /**
     * Adds the cartItem into the cart session
     *
     * @param $cartItem
     *
     * @return string
     */
    public function addItem($cartItem)
    {
        // We need to generate the item hash to uniquely identify the item
        $itemHash = $this->generateHash($cartItem);

        // If an item is a duplicate we know we need to bump the quantity
        if(isset($this->cart->items) && array_get($this->cart->items, $itemHash)) {
            $this->findItem($itemHash)->qty += $cartItem->qty;
        } else {
            array_set($this->cart->items, $itemHash, $cartItem);
        }

        // TODO - add fire event

        // Update the cart session
        $this->update();

        return $itemHash;
    }

    /**
     * Geneates a hash based on the cartItem array
     *
     * @param CartItem $cartItem
     *
     * @return string
     */
    protected function generateHash(CartItem $cartItem)
    {
        $cartItemArray = (array) $cartItem;

        if(empty($cartItemArray['options']) === false) {
            ksort($cartItemArray['options']);
        }

        return md5(json_encode($cartItemArray));
    }

    /**
     * Finds a cartItem based on the itemHash
     * @param $itemHash
     *
     * @return CartItem | null
     */
    public function findItem($itemHash)
    {
        if(isset($this->cart->items)) {
            return array_get($this->cart->items, $itemHash);
        } else {
            return null;
        }
    }

    /**
     * Updates cart session
     */
    public function update()
    {
        // todo add fire event
        \Session::set(config('laracart.cache_prefix', 'laracart_').$this->instance, $this->cart);
    }

    /**
     * Get the count based on qty, or number of unique items
     *
     * @param bool $withQty
     *
     * @return int
     */
    public function count($withQty = true)
    {
        $count = 0;
        foreach($this->getItems() as $item)
        {
            if($withQty) {
                $count+=$item->qty;
            } else {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Gets all the items within the cart
     *
     * @return array
     */
    public function getItems()
    {
        if (isset($this->cart->items) === true) {
            return $this->cart->items;
        } else {
            return [];
        }
    }

    /**
     * Empties the carts items
     */
    public function emptyCart()
    {
        unset($this->cart->items);
        // TODO - fire event
    }

    /**
     * Removes a CartItem based on the itemHash
     * @param $itemHash
     */
    public function removeItem($itemHash)
    {
        array_forget($this->cart->items, $itemHash);
        // TODO - fire event
    }

    /**
     * Updates an items attributes
     *
     * @param $itemHash
     * @param $attr
     * @param $value
     *
     * @throws UnknownItemProperty
     */
    public function updateItem($itemHash, $attr, $value)
    {
        // TODO - validation for each of the item types
        if(empty($item = $this->findItem($itemHash)) === false) {
            if(isset($item->$attr) === true) {
                $item->$attr = $value;
                array_forget($this->cart->items, $itemHash);
                $this->addItem($item);
            } else {
                throw new UnknownItemProperty();
            }
        }
        // TODO - fire event
    }

    /**
     * Gets the subtotal of the cart with or without tax
     *
     * @param bool $tax
     *
     * @return string
     */
    public function subTotal($tax = false)
    {
        $total = 0;
        if($this->count() != 0) {
            foreach ($this->cart->items as $item) {
                $total += $item->subTotal($tax, false);
            }
        }

        return LaraCart::formatMoney($total, $this->locale, $this->displayLocale);
    }

    /**
     * Gets the total of the cart with or without tax
     * @return string
     */
    public function total($tax = true)
    {
        return $this->subTotal($tax);
    }
}
