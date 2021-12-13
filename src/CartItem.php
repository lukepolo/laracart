<?php

namespace LukePOLO\LaraCart;

use Illuminate\Support\Arr;
use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\Exceptions\ModelNotFound;
use LukePOLO\LaraCart\Traits\CartOptionsMagicMethodsTrait;

/**
 * Class CartItem.
 *
 * @property int    id
 * @property int    qty
 * @property float  tax
 * @property float  price
 * @property string name
 * @property array  options
 * @property bool   taxable
 */
class CartItem
{
    const ITEM_ID = 'id';
    const ITEM_QTY = 'qty';
    const ITEM_TAX = 'tax';
    const ITEM_NAME = 'name';
    const ITEM_PRICE = 'price';
    const ITEM_TAXABLE = 'taxable';
    const ITEM_OPTIONS = 'options';

    use CartOptionsMagicMethodsTrait;

    protected $itemHash;
    protected $itemModel;
    protected $excludeFromHash;
    protected $itemModelRelations;

    public $locale;
    public $coupon;
    public $lineItem;
    public $active = true;
    public $subItems = [];
    public $currencyCode;

    /**
     * This tracks the discounts per item , we do this so we can properly
     * round taxes when you have a qty > 0.
     */
    public $discounted = [];

    /**
     * CartItem constructor.
     *
     * @param            $id
     * @param            $name
     * @param int        $qty
     * @param string     $price
     * @param array      $options
     * @param bool       $taxable
     * @param bool|false $lineItem
     */
    public function __construct($id, $name, $qty, $price, $options = [], $taxable = true, $lineItem = false)
    {
        $this->id = $id;
        $this->qty = $qty;
        $this->name = $name;
        $this->taxable = $taxable;
        $this->lineItem = $lineItem;
        $this->price = (config('laracart.prices_in_cents', false) === true ? intval($price) : floatval($price));
        $this->tax = config('laracart.tax');
        $this->itemModel = config('laracart.item_model', null);
        $this->itemModelRelations = config('laracart.item_model_relations', []);
        $this->excludeFromHash = config('laracart.exclude_from_hash', []);

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
     * Generates a hash based on the cartItem array.
     *
     * @param bool $force
     *
     * @return string itemHash
     */
    public function generateHash($force = false)
    {
        if ($this->lineItem === false) {
            $this->itemHash = null;

            $cartItemArray = (array) clone $this;

            unset($cartItemArray['discounted']);
            unset($cartItemArray['options']['qty']);

            foreach ($this->excludeFromHash as $option) {
                unset($cartItemArray['options'][$option]);
            }

            ksort($cartItemArray['options']);

            $this->itemHash = app(LaraCart::HASH)->hash($cartItemArray);
        } elseif ($force || empty($this->itemHash) === true) {
            $this->itemHash = app(LaraCart::RANHASH);
        }

        app('events')->dispatch(
            'laracart.updateItem',
            [
                'item'    => $this,
                'newHash' => $this->itemHash,
            ]
        );

        return $this->itemHash;
    }

    /**
     * Gets the hash for the item.
     *
     * @return mixed
     */
    public function getHash()
    {
        return $this->itemHash;
    }

    /**
     * Search for matching options on the item.
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
     * Finds a sub item by its hash.
     *
     * @param $subItemHash
     *
     * @return mixed
     */
    public function findSubItem($subItemHash)
    {
        return Arr::get($this->subItems, $subItemHash);
    }

    /**
     * Adds an sub item to a item.
     *
     * @param array $subItem
     *
     * @return CartSubItem
     */
    public function addSubItem(array $subItem)
    {
        $subItem = new CartSubItem($subItem);

        $this->subItems[$subItem->getHash()] = $subItem;

        $this->update();

        return $subItem;
    }

    /**
     * Removes a sub item from the item.
     *
     * @param $subItemHash
     */
    public function removeSubItem($subItemHash)
    {
        unset($this->subItems[$subItemHash]);

        $this->update();
    }

    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Gets the price of the item with or without tax, with the proper format.
     *
     * @return string
     */
    public function total()
    {
        $total = 0;

        if ($this->active) {
            for ($qty = 0; $qty < $this->qty; $qty++) {
                $total += LaraCart::formatMoney($this->subTotalPerItem(false) + array_sum($this->taxSummary()[$qty]), null, null, false);
            }

            $total -= $this->getDiscount(false);

            if ($total < 0) {
                $total = 0;
            }
        }

        return $total;
    }

    public function taxTotal()
    {
        $total = 0;

        foreach ($this->taxSummary() as $itemSummary) {
            $total += array_sum($itemSummary);
        }

        return $total;
    }

    /**
     * Gets the sub total of the item based on the qty.
     *
     * @param bool $format
     *
     * @return float|string
     */
    public function subTotal()
    {
        return $this->subTotalPerItem() * $this->qty;
    }

    public function subTotalPerItem()
    {
        $subTotal = $this->active ? ($this->price + $this->subItemsTotal()) : 0;

        return $subTotal;
    }

    /**
     * Gets the totals for the options.
     *
     * @return float
     */
    public function subItemsTotal()
    {
        $total = 0;

        foreach ($this->subItems as $subItem) {
            $total += $subItem->subTotal(false);
        }

        return $total;
    }

    /**
     * Gets the discount of an item.
     *
     * @return string
     */
    public function getDiscount()
    {
        return array_sum($this->discounted);
    }

    /**
     * @param CouponContract $coupon
     *
     * @return $this
     */
    public function addCoupon(CouponContract $coupon)
    {
        $coupon->appliedToCart = false;
        app('laracart')->addCoupon($coupon);
        $this->coupon = $coupon;

        return $this;
    }

    public function taxSummary()
    {
        $taxed = [];
        // tax item by item
        for ($qty = 0; $qty < $this->qty; $qty++) {
            // keep track of what is discountable
            $discountable = $this->discounted[$qty] ?? 0;
            $price = ($this->taxable ? $this->price : 0);

            $taxable = $price - ($discountable > 0 ? $discountable : 0);
            // track what has been discounted so far
            $discountable = $discountable - $price;

            $taxed[$qty] = [];
            if ($taxable > 0) {
                if (!isset($taxed[$qty][(string) $this->tax])) {
                    $taxed[$qty][(string) $this->tax] = 0;
                }
                $taxed[$qty][(string) $this->tax] += $taxable * $this->tax;
            }

            // tax sub item item by sub item
            foreach ($this->subItems as $subItem) {
                $subItemTaxable = 0;
                for ($subItemQty = 0; $subItemQty < ($subItem->qty || 1); $subItemQty++) {
                    $subItemPrice = ($subItem->taxable ?? true) ? $subItem->price : 0;
                    $subItemTaxable = $subItemPrice - ($discountable > 0 ? $discountable : 0);
                    $discountable = $discountable - $subItemPrice;
                }

                if ($subItemTaxable > 0) {
                    if (!isset($taxed[$qty][(string) $subItem->tax])) {
                        $taxed[$qty][(string) $subItem->tax] = 0;
                    }
                    $taxed[$qty][(string) $subItem->tax] += $subItemTaxable * $subItem->tax;
                }

                // discount sub items ... items
                if (isset($subItem->items)) {
                    foreach ($subItem->items as $item) {
                        if ($item->taxable) {
                            foreach ($item->taxSummary() as $itemTaxSummary) {
                                foreach ($itemTaxSummary as $taxRate => $amount) {
                                    if (!isset($taxed[$qty][(string) $taxRate])) {
                                        $taxed[$qty][(string) $taxRate] = 0;
                                    }
                                    $taxed[$qty][(string) $taxRate] += $amount;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $taxed;
    }

    /**
     * Sets the related model to the item.
     *
     * @param       $itemModel
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
     * Gets the items model class.
     */
    public function getItemModel()
    {
        return $this->itemModel;
    }

    /**
     * Returns a Model.
     *
     * @throws ModelNotFound
     */
    public function getModel()
    {
        $itemModel = (new $this->itemModel())->with($this->itemModelRelations)->find($this->id);

        if (empty($itemModel)) {
            throw new ModelNotFound('Could not find the item model for '.$this->id);
        }

        return $itemModel;
    }

    /**
     *  A way to find sub items.
     *
     * @param $data
     *
     * @return array
     */
    public function searchForSubItem($data)
    {
        $matches = [];

        foreach ($this->subItems as $subItem) {
            if ($subItem->find($data)) {
                $matches[] = $subItem;
            }
        }

        return $matches;
    }

    public function disable()
    {
        $this->active = false;
        $this->update();
    }

    public function enable()
    {
        $this->active = true;
        $this->update();
    }

    public function update()
    {
        $this->generateHash();
        app('laracart')->update();
    }
}
