<?php

namespace LukePOLO\LaraCart;

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
        $this->session = app('session');
        $this->events = app('events');

        // Setup the Locale and Tax Variables for the Cart
        $this->locale = config('laracart.locale', 'en_US');
        $this->displayLocale = config('laracart.display_locale');
        $this->tax = config('laracart.tax');

        // Set a default instance of the cart
        $instance = $this->session->get('laracart.instance', 'default');

        $this->setInstance($instance);
    }

    /**
     * Sets and Gets the instance of the cart in the session we should be using
     * @param string $instance
     */
    public function setInstance($instance = 'default')
    {
        $this->instance = $instance;

        $this->get($instance);

        // set in the session that we are using a different instance
        $this->session->set('laracart.instance', $instance);

        $this->events->fire('laracart.new');
    }

    /**
     * Creates a CartItem and then adds it to cart
     *
     * @param string|int $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     *
     * @return string itemHash
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
     * @return string itemHash
     */
    public function addItem($cartItem)
    {
        // We need to generate the item hash to uniquely identify the item
        $itemHash = $cartItem->generateHash();

        // If an item is a duplicate we know we need to bump the quantity
        if(isset($this->cart->items) && array_get($this->cart->items, $itemHash)) {
            $this->findItem($itemHash)->qty += $cartItem->qty;
        } else {
            array_set($this->cart->items, $itemHash, $cartItem);
        }

        $this->events->fire('laracart.addItem', $cartItem);

        // Update the cart session
        $this->update();

        return $itemHash;
    }

    /**
     * Gets the instance in the session
     *
     * @param string $instance
     *
     * @return $this cart instance
     */
    public function get($instance = 'default')
    {
        return $this->cart = $this->session->get(config('laracart.cache_prefix', 'laracart_').$instance);
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
     * Finds a cartItem based on the itemHash
     *
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
        $this->session->set(config('laracart.cache_prefix', 'laracart_').$this->instance, $this->cart);

        $this->events->fire('laracart.update', $this->cart);
    }

    /**
     * Updates an items attributes
     *
     * @param $itemHash
     * @param $key
     * @param $value
     *
     */
    public function updateItem($itemHash, $key, $value)
    {
        if(empty($item = $this->findItem($itemHash)) === false) {
            $item->update($key, $value);
        }
        $this->events->fire('laracart.updateItem', $item);
    }

    /**
     * Updates an items hash
     *
     * @param $itemHash
     *
     * @return string ItemHash
     */
    public function updateItemHash($itemHash)
    {
        // Gets the item with its current hash
        $item = $this->findItem($itemHash);

        // removes the item
        $this->removeItem($itemHash);

        $this->events->fire('laracart.updateHash', $itemHash);

        // Adds the item with its new hash
        return $this->addItem($item);
    }

    /**
     * Updates all item hashes within the cart
     */
    public function updateItemHashes()
    {
        foreach($this->getItems() as $itemHash => $item) {
            $this->updateItemHash($itemHash);
        }
    }

    /**
     * Removes a CartItem based on the itemHash
     *
     * @param $itemHash
     */
    public function removeItem($itemHash)
    {
        array_forget($this->cart->items, $itemHash);

        $this->events->fire('laracart.removeItem', $itemHash);
    }

    /**
     * Empties the carts items
     */
    public function emptyCart()
    {
        unset($this->cart->items);

        $this->events->fire('laracart.empty', $this->instance);
    }

    /**
     * Completely destroys cart and anything associated with it
     */
    public function destroyCart()
    {
        unset($this->cart);

        $this->events->fire('laracart.destroy', $this->instance);
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
