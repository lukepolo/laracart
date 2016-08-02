<?php

namespace LukePOLO\LaraCart;

use Illuminate\Database\Eloquent\Model;
use LukePOLO\LaraCart\Traits\Buyable;
use LukePOLO\LaraCart\Traits\CartOptionsMagicMethods;
use LukePOLO\LaraCart\Traits\ItemModelBinding;

/**
 * Class CartItem
 * @property int id
 * @property int qty
 * @property float tax
 * @property float price
 * @property string name
 * @property array options
 * @property boolean taxable
 * @package LukePOLO\LaraCart
 */
class CartItem
{
    use CartOptionsMagicMethods, ItemModelBinding;

    const ITEM_ID = 'id';
    const ITEM_QTY = 'qty';
    const ITEM_TAX = 'tax';
    const ITEM_NAME = 'name';
    const ITEM_PRICE = 'price';
    const ITEM_TAXABLE = 'taxable';
    const ITEM_OPTIONS = 'options';

    protected $itemHash;

    public $locale;
    public $lineItem;
    public $discount = 0;
    public $subItems = [];
    public $couponInfo = [];
    public $internationalFormat;

    /**
     * CartItem constructor.
     * @param $id
     * @param $name
     * @param integer $qty
     * @param string $price
     * @param array $options
     * @param boolean $taxable
     * @param bool|false $lineItem
     */
    public function __construct($id, $name, $qty, $price, array $options = [], $taxable = true, $lineItem = false)
    {
        $this->id = $id;
        $this->qty = $qty;
        $this->name = $name;
        $this->taxable = $taxable;
        $this->lineItem = $lineItem;
        $this->price = floatval($price);
        $this->tax = config('laracart.tax');

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }

        if($id instanceof Model && class_uses(Buyable::class)) {
            $this->bindModelToItem($id);
        }
    }

    /**
     * // TODO - I would like to move this into a helper if possible
     * Generates a hash based on the cartItem array
     * @param bool $force
     * @return string itemHash
     */
    public function generateHash($force = false)
    {
        if ($this->lineItem === false) {
            $this->itemHash = null;

            $cartItemArray = (array)$this;

            unset($cartItemArray['options']['qty']);

            ksort($cartItemArray['options']);

            $this->itemHash = $this->hash($cartItemArray);
        } elseif ($force || empty($this->itemHash)) {
            $this->itemHash = $this->randomHash();
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
     * @return mixed
     */
    public function getHash()
    {
        return $this->itemHash;
    }

    /**
     * Search for matching options on the item
     * @param $data
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
     *  A way to find sub items
     * @param $data
     * @return array
     */
    public function search($data)
    {
        $matches = [];

        foreach ($this->subItems as $subItem) {
            if ($subItem->find($data)) {
                $matches[] = $subItem;
            }
        }

        return $matches;
    }

    /**
     * Finds a sub item by its hash
     * @param $subItemHash
     * @return mixed
     */
    public function findSubItem($subItemHash)
    {
        return array_get($this->subItems, $subItemHash);
    }

    /**
     * Adds an sub item to a item
     * @param array $subItem
     * @return CartSubItem
     */
    public function addSubItem(array $subItem)
    {
        $subItem = new CartSubItem($subItem);

        $this->subItems[$subItem->getHash()] = $subItem;

        $this->generateHash();

        app('laracart')->update();

        return $subItem;
    }

    /**
     * Removes a sub item from the item
     * @param $subItemHash
     */
    public function removeSubItem($subItemHash)
    {
        unset($this->subItems[$subItemHash]);

        $this->generateHash();
    }

    /**
     * Gets the tax for the item
     * @param int $amountNotTaxable
     * @return int|mixed
     */
    public function tax($amountNotTaxable = 0)
    {
        if (!$this->taxable) {
            $amountNotTaxable = $amountNotTaxable + ($this->price * $this->qty);
        }

        return $this->tax * ($this->subTotal(false, config('laracart.discountTaxable', true),
                true) - $amountNotTaxable);
    }

    /**
     * Gets the price of the item with or without tax, with the proper format
     * @param bool $taxedItemsOnly
     * @return string
     */
    public function price($taxedItemsOnly = false)
    {
        return $this->formatMoney($this->price + $this->subItemsTotal($taxedItemsOnly)->amount(), $this->locale,
            $this->internationalFormat);
    }

    /**
     * Gets the sub total of the item based on the qty with or without tax in the proper format
     * @param bool $withDiscount
     * @param bool $taxedItemsOnly
     * @return string
     */
    public function subTotal($withDiscount = true, $taxedItemsOnly = false)
    {
        $total = $this->price($taxedItemsOnly)->amount() * $this->qty;

        if ($withDiscount) {
            $total -= $this->getDiscount()->amount();
        }

        return $this->formatMoney($total, $this->locale, $this->internationalFormat);
    }


    /**
     * Gets the totals for the options
     * @param bool $taxedItemsOnly
     * @return string
     */
    public function subItemsTotal($taxedItemsOnly = false)
    {
        $total = 0;

        foreach ($this->subItems as $subItem) {
            $total += $subItem->price($taxedItemsOnly)->amount();
        }

        return $this->formatMoney($total, $this->locale, $this->internationalFormat);
    }

    /**
     * Gets the discount of an item
     * @return string
     */
    public function getDiscount()
    {
        $amount = 0;

        if (app('laracart')->findCoupon($this->code)) {
            $amount = $this->discount;
        }

        return $this->formatMoney(
            $amount,
            $this->locale,
            $this->internationalFormat
        );
    }
}
