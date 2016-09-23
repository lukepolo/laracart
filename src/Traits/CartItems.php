<?php

namespace LukePOLO\LaraCart\Traits;

use Illuminate\Database\Eloquent\Model;
use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\Exceptions\InvalidQuantity;
use LukePOLO\LaraCart\Exceptions\ModelNotFound;

/**
 * Class CartItems
 * @package LukePOLO\LaraCart\Traits
 */
trait CartItems
{
    /**
     * Adds the cartItem into the cart session
     * @param CartItem $cartItem
     * @return CartItem
     */
    public function addItem(CartItem $cartItem)
    {
        $itemHash = $cartItem->hash();

        if ($item = $this->getItem($itemHash)) {
            $item->qty += $cartItem->qty;
        } else {
            $this->cart->items[] = $cartItem;
        }

        $this->events->fire('laracart.addItem', $cartItem);

        $this->update();

        return $cartItem;
    }

    /**
     * Creates a CartItem and then adds it to cart
     * @param $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @return CartItem
     * @throws ModelNotFound
     */
    public function add($itemID, $name = null, $qty = 1, $price = '0.00', $options = [])
    {
        return $this->addItem(new CartItem($itemID, $name, $qty, $price, $options));
    }

    /**
     * Creates a CartItem and then adds it to cart
     * @param Model $model
     * @param int $qty
     * @param array $options
     * @return CartItem|null
     */
    public function addByModel(Model $model, $qty = 1, $options = [])
    {
        return $this->addItem(new CartItem($model, null, $qty, null, $options));
    }

    /**
     * Creates a CartItem and then adds it to cart
     * @param $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @return CartItem
     * @throws ModelNotFound
     */
    public function addNonTaxableItem($itemID, $name = null, $qty = 1, $price = '0.00', $options = [])
    {
        return $this->addItem(new CartItem($itemID, $name, $qty, $price, $options, false));
    }


    /**
     * Creates a CartItem and then adds it to cart
     * @param string|int $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @return CartItem
     */
    public function addLine($itemID, $name = null, $qty = 1, $price = '0.00', array $options = [])
    {
        return $this->addItem(new CartItem($itemID, $name, $qty, $price, $options, true, true));
    }

    /**
     * Creates a CartItem and then adds it to cart
     * @param string|int $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @return CartItem
     */
    public function addNonTaxableLine($itemID, $name = null, $qty = 1, $price = '0.00', array $options = [])
    {
        $item = $this->addItem(new CartItem($itemID, $name, $qty, $price, $options, false, true));

        return $this->getItem($item->hash());
    }

    /**
     * Finds a cartItem based on the itemHash
     * @param $itemHash
     * @return CartItem|null
     */
    public function getItem($itemHash)
    {
        return array_get($this->items(), $itemHash);
    }

    /**
     * Gets all the items within the cart
     * @return array
     */
    public function items()
    {
        $items = [];

        if (isset($this->cart->items)) {
            foreach ($this->cart->items as $item) {
                $items[$item->hash()] = $item;
            }
        }

        return $items;
    }

    /**
     * Updates an items attributes
     * @param $itemHash
     * @param $key
     * @param $value
     * @return CartItem
     * @throws InvalidPrice
     * @throws InvalidQuantity
     */
    public function updateItem($itemHash, $key, $value)
    {
        if (!empty($item = $this->getItem($itemHash))) {
            $item->$key = $value;
            $item->generateHash($item);
            $this->update();
        }

        return $item;
    }

    /**
     * Removes a CartItem based on the itemHash
     * @param $itemHash
     * @return void
     */
    public function removeItem($itemHash)
    {
        foreach ($this->cart->items as $itemKey => $item) {
            if ($item->hash() == $itemHash) {
                unset($this->cart->items[$itemKey]);
                break;
            }
        }

        $this->events->fire('laracart.removeItem', $item);

        $this->update();
    }

    /**
     * Increase the quantity of a cartItem based on the itemHash
     * @param $itemHash
     * @param int $qty
     * @return CartItem|null
     */
    public function increaseQty($itemHash, $qty = 1)
    {
        $item = $this->getItem($itemHash);

        $item->qty = $item->qty + $qty;

        $this->update();

        return $item;
    }

    /**
     * Decreases the quantity of a cartItem based on the itemHash
     * @param $itemHash
     * @param int $qty
     * @return CartItem|null
     */
    public function decreaseQty($itemHash, $qty = 1)
    {
        $item = $this->getItem($itemHash);

        if ($item->qty > 1) {
            $item->qty = $item->qty - $qty;

            $this->update();

            return $item;
        }

        $this->removeItem($itemHash);
        $this->update();
    }
}
