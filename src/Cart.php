<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Exceptions\UnknownItemProperty;

class Cart
{
    protected $session;
    protected $instance;
    public $locale;
    public $displayLocale;
    public $cart;
    public $tax;

    function __construct()
    {
        // TODO -- allow for differnt type of sessions
        $this->session = app('session');

        $this->locale = config('laracart.locale', 'en_US');
        $this->displayLocale = config('laracart.display_locale');
        $this->tax = config('laracart.tax');

        $this->setInstance();
    }

    public function setInstance($instance = 'default')
    {
        $this->instance = $instance;

        $this->get($instance);
    }

    public function get($instance = 'default')
    {
        $this->cart = \Session::get(config('laracart.cache_prefix', 'laracart_').$instance);
    }

    public function add($itemID, $name = null, $qty = 1, $price = '0.00', $options = [])
    {
        $this->addItem(new CartItem(
            $itemID,
            $name,
            $qty,
            $price,
            $options
        ));
    }

    public function addItem($cartItem)
    {
        $itemHash = $this->generateHash($cartItem);

        if(isset($this->cart->items) && array_get($this->cart->items, $itemHash)) {
            $this->findItem($itemHash)->qty++;
        } else {
            array_set($this->cart->items, $itemHash, $cartItem);
        }

        $this->update();
    }

    protected function generateHash(CartItem $cartItem)
    {
        $cartItemArray = (array) $cartItem;
        ksort($cartItemArray['options']);

        return md5(json_encode($cartItemArray));
    }

    public function findItem($itemHash)
    {
        if(isset($this->cart->items)) {
            return array_get($this->cart->items, $itemHash);
        } else {
            return null;
        }
    }

    public function update()
    {
        // todo add fires
        \Session::set(config('laracart.cache_prefix', 'laracart_').$this->instance, $this->cart);
    }

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

    public function getItems()
    {
        if (isset($this->cart->items) === true) {
            return $this->cart->items;
        } else {
            return [];
        }
    }

    public function emptyCart()
    {
        unset($this->cart->items);
    }

    public function removeItem($itemHash)
    {
        array_forget($this->cart->items, $itemHash);
    }

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
    }

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

    public function total()
    {
        return $this->subTotal(true);
    }
}
