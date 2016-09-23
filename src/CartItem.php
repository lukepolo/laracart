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

    protected $hash;

    public $locale;
    public $lineItem;
    public $discount = 0;
    public $modifiers = [];
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
     * Gets the hash for the item
     * @return mixed
     */
    public function hash()
    {
        return $this->hash;
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

        foreach ($this->modifiers as $modifier) {
            if ($modifier->find($data)) {
                $matches[] = $modifier;
            }
        }

        return $matches;
    }

    /**
     * Finds a sub item by its hash
     * @param $modifierHash
     * @return mixed
     */
    public function findModifier($modifierHash)
    {
        return array_get($this->modifiers, $modifierHash);
    }

    /**
     * Adds an sub item to a item
     * @param array $modifier
     * @return CartModifier
     */
    public function addModifier(array $modifier)
    {
        $modifier = new CartItemModifier($modifier);

        $this->modifiers[$modifier->hash()] = $modifier;

        $this->updateCart();

        return $modifier;
    }

    /**
     * Removes a sub item from the item
     * @param $modifierHash
     */
    public function removeModifier($modifierHash)
    {
        unset($this->modifiers[$modifierHash]);

        $this->updateCart();
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

        return $this->tax * ($this->subTotal(false, config('laracart.discountTaxable', true))->amount() - $amountNotTaxable);
    }

    /**
     * Gets the price of the item with or without tax, with the proper format
     * @param bool $taxedItemsOnly
     * @return string
     */
    public function price($taxedItemsOnly = false)
    {
        return $this->formatMoney($this->price + $this->modifiersTotal($taxedItemsOnly)->amount(), $this->locale,
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
            $total -= $this->discount()->amount();
        }

        return $this->formatMoney($total, $this->locale, $this->internationalFormat);
    }


    /**
     * Gets the totals for the options
     * @param bool $taxedItemsOnly
     * @return string
     */
    public function modifiersTotal($taxedItemsOnly = false)
    {
        $total = 0;

        foreach ($this->modifiers as $modifier) {
            $total += $modifier->price($taxedItemsOnly)->amount();
        }

        return $this->formatMoney($total, $this->locale, $this->internationalFormat);
    }

    /**
     * Gets the discount of an item
     * @return string
     */
    public function discount()
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
