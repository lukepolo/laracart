<?php

namespace LukePOLO\LaraCart\Traits;

use Illuminate\Database\Eloquent\Model;
use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\Exceptions\InvalidQuantity;
use LukePOLO\LaraCart\Exceptions\ModelNotFound;

/**
 * Class CartItems
 *
 * @package LukePOLO\LaraCart\Traits
 */
trait CartItems
{
    /**
     * Creates a CartItem and then adds it to cart
     * @param $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @param bool|false $taxable
     * @param bool|false $lineItem
     * @return CartItem
     * @throws ModelNotFound
     */
    public function add(
        $itemID,
        $name = null,
        $qty = 1,
        $price = '0.00',
        $options = [],
        $taxable = true,
        $lineItem = false
    ) {

        if (!empty(config('laracart.item_model'))) {

            $itemModel = $itemID;

            if (!$this->isItemModel($itemModel)) {
                $itemModel = (new $this->itemModel)->with($this->itemModelRelations)->find($itemID);
            }

            if (empty($itemModel)) {
                throw new ModelNotFound('Could not find the item ' . $itemID);
            }

            $bindings = config('laracart.item_model_bindings');

            $itemID = $itemModel[$bindings[CartItem::ITEM_ID]];
            $name = $itemModel[$bindings[CartItem::ITEM_NAME]];

            if (empty($qty = $name) || !is_int($name)) {
                $qty = 1;
            }

            $price = $itemModel[$bindings[CartItem::ITEM_PRICE]];

            $options = array_merge($options, $this->getItemModelOptions($itemModel, $bindings[CartItem::ITEM_OPTIONS]));

            $taxable = $itemModel[$bindings[CartItem::ITEM_TAXABLE]] ? true : false;
        }

        $item = $this->addItem(new CartItem(
            $itemID,
            $name,
            $qty,
            $price,
            $options,
            $taxable,
            $lineItem
        ));

        $this->update();

        return $this->getItem($item->getHash());
    }

    /**
     * Adds the cartItem into the cart session
     * @param CartItem $cartItem
     * @return CartItem
     */
    public function addItem(CartItem $cartItem)
    {
        $itemHash = $cartItem->generateHash();

        if ($this->getItem($itemHash)) {
            $this->getItem($itemHash)->qty += $cartItem->qty;
        } else {
            $this->cart->items[] = $cartItem;
        }

        $this->events->fire('laracart.addItem', $cartItem);

        return $cartItem;
    }

    /**
     * Creates a CartItem and then adds it to cart
     * @param string|int $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @param bool|true $taxable
     * @return CartItem
     */
    public function addLine($itemID, $name = null, $qty = 1, $price = '0.00', $options = [], $taxable = true)
    {
        return $this->add($itemID, $name, $qty, $price, $options, $taxable, true);
    }

    /**
     * Finds a cartItem based on the itemHash
     * @param $itemHash
     * @return CartItem|null
     */
    public function getItem($itemHash)
    {
        return array_get($this->getItems(), $itemHash);
    }

    /**
     * Gets all the items within the cart
     * @return array
     */
    public function getItems()
    {
        $items = [];

        if (isset($this->cart->items) === true) {
            foreach ($this->cart->items as $item) {
                $items[$item->getHash()] = $item;
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
        if (empty($item = $this->getItem($itemHash)) === false) {
            $item->$key = $value;
        }

        $item->generateHash();

        $this->update();

        return $item;
    }

    /**
     * Removes a CartItem based on the itemHash
     * @param $itemHash
     * return void
     */
    public function removeItem($itemHash)
    {
        foreach ($this->cart->items as $itemKey => $item) {
            if ($item->getHash() == $itemHash) {
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

    /**
     * Gets a option from the model
     * @param Model $itemModel
     * @param $attr
     * @param null $defaultValue
     * @return Model|null
     */
    private function getFromModel(Model $itemModel, $attr, $defaultValue = null)
    {
        $variable = $itemModel;

        if (!empty($attr)) {
            foreach (explode('.', $attr) as $attr) {
                $variable = array_get($variable, $attr, $defaultValue);
            }
        }

        return $variable;
    }

    /**
     * Gets the item models options based the config
     * @param Model $itemModel
     * @param array $options
     * @return array
     */
    private function getItemModelOptions(Model $itemModel, $options = [])
    {
        $itemOptions = [];
        foreach ($options as $option) {
            $itemOptions[$option] = $this->getFromModel($itemModel, $option);
        }

        return array_filter($itemOptions, function ($value) {
            if ($value !== false && empty($value)) {
                return false;
            }
            return true;
        });
    }

    /**
     * Checks to see if its an item model
     * @param $itemModel
     * @return bool
     */
    private function isItemModel($itemModel)
    {
        if (is_object($itemModel) && get_class($itemModel) == config('laracart.item_model')) {
            return true;
        }

        return false;
    }
}
