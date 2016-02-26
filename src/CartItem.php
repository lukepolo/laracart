<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Exceptions\ModelNotFound;
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
    protected $itemModel;
    protected $itemModelRelations;

    public $locale;
    public $taxable;
    public $lineItem;
    public $subItems = [];
    public $couponInfo = [];
    public $internationalFormat;

    /**
     * CartItem constructor.
     *
     * @param $id
     * @param $name
     * @param integer $qty
     * @param string $price
     * @param array $options
     * @param boolean $taxable
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
        $this->itemModel = config('laracart.item_model');
        $this->itemModelRelations = config('laracart.item_model_relations');

        foreach ($options as $option => $value) {
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

        app('events')->fire(
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
     * Search for matching options on the item
     *
     * @return mixed
     */
    public function find($data)
    {
        foreach ($data as $key => $value) {
            if ($this->$key !== $value) {
                return false;
            }
        }

        return $this;
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
     * @return CartSubItem $itemHash
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
     * @param bool $format
     *
     * @return string
     */
    public function price($format = true)
    {
        return LaraCart::formatMoney(
            $this->price + $this->subItemsTotal(false),
            $this->locale,
            $this->internationalFormat, $format
        );
    }

    /**
     * Gets the sub total of the item based on the qty with or without tax in the proper format
     *
     * @param bool $format
     * @param bool $withDiscount
     *
     * @return string
     */
    public function subTotal($format = true, $withDiscount = true)
    {
        $total = $this->price(false) * $this->qty;

        if ($withDiscount) {
            $total -= $this->getDiscount(false);
        }

        return LaraCart::formatMoney($total, $this->locale, $this->internationalFormat, $format);
    }


    /**
     * Gets the totals for the options
     *
     * @param boolean $format
     *
     * @return string
     */
    public function subItemsTotal($format = true)
    {
        $total = 0;

        foreach ($this->subItems as $subItem) {
            $total += $subItem->price(false);
        }

        $total *= $this->qty;

        return LaraCart::formatMoney($total, $this->locale, $this->internationalFormat, $format);
    }

    /**
     * Gets the discount of an item
     *
     * @param boolean $format
     *
     * @return string
     */
    public function getDiscount($format = true)
    {
        $amount = 0;

        if (app('laracart')->findCoupon($this->code)) {
            $amount = $this->discount;
        }

        return LaraCart::formatMoney(
            $amount,
            $this->locale,
            $this->internationalFormat,
            $format
        );
    }

    /**
     * Gets the tax for the item
     *
     * @return int|mixed
     */
    public function tax($amountNotTaxable = 0)
    {
        $tax = 0;

        if ($this->taxable) {
            return $this->tax * ($this->subTotal(false) - $amountNotTaxable);
        }

        return $tax;
    }

    /**
     * Sets the related model to the item
     *
     * @param $itemModel
     * @param array $relations
     *
     * @throws ModelNotFound
     */
    public function setModel($itemModel, $relations = [])
    {
        if (!class_exists($itemModel)) {
            throw new ModelNotFound('Could not find relation model');
        }

        $this->itemModel = $itemModel;
        $this->itemModelRelations = $relations;
    }

    /**
     * Returns a Model
     *
     * @throws
     */
    public function getModel()
    {
        $itemModel = new $this->itemModel;

        return $itemModel->with($this->itemModelRelations)->findOrFail($this->id);
    }
}
