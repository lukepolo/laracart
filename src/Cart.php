<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\LaraCartInterface as LaraCartService;

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

    public $cart;

    public $tax;

    public $locale;
    public $internationalFormat;

    /**
     * @param LaraCartInterface $laraCartService | LukePOLO\LaraCart\LaraCart $laraCartService
     */
    function __construct(LaraCartService $laraCartService)
    {
        $this->laraCartService = $laraCartService;
        $this->session = app('session');
        $this->events = app('events');

        // Sets the tax for the cart
        $this->tax = config('laracart.tax');

        // Set a default instance of the cart
        $instance = $this->session->get('laracart.instance', 'default');

        $this->setInstance($instance);
    }

    /**
     * Sets and Gets the instance of the cart in the session we should be using
     *
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
            $options,
            false
        ));
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
    public function addLine($itemID, $name = null, $qty = 1, $price = '0.00', $options = [])
    {
        return $this->addItem(new CartItem(
            $itemID,
            $name,
            $qty,
            $price,
            $options,
            true
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
        if($this->findItem($itemHash)) {
            if($cartItem->lineItem === false) {
                $this->findItem($itemHash)->qty += $cartItem->qty;
            } else {
                // regenerate a hash till its unique
                $cartItem->itemHash = $cartItem->generatehash(true);
                // Re-add the item
                $this->addItem($cartItem);
            }
        } else {
            $this->cart->items[] = $cartItem;
            $this->events->fire('laracart.addItem', $cartItem);
        }

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
        $items = [];
        if (isset($this->cart->items) === true) {
            foreach($this->cart->items as $item) {
                $items[$item->getHash()] = $item;
            }
        }

        return $items;
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
        return array_get($this->getItems(), $itemHash);
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
     * @return string $newHash
     */
    public function updateItem($itemHash, $key, $value)
    {
        if(empty($item = $this->findItem($itemHash)) === false) {
            $item->update($key, $value);
        }

        $newHash = $item->generateHash();

        $this->events->fire('laracart.updateItem', [
            'item' => $item,
            'newHash' => $newHash
        ]);

        return $newHash;
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
        foreach($this->cart->items as $itemKey => $item) {
           if($item->getHash() == $itemHash) {
               unset($this->cart->items[$itemKey]);
               break;
           }
        }

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
            foreach ($this->getItems() as $item) {
                $total += $item->subTotal($tax, false);
            }
        }

        return $this->laraCartService->formatMoney($total, $this->locale, $this->internationalFormat);
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
