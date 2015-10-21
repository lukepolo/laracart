<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Contracts\CouponContract;

/**
 * Class Cart
 *
 * @package LukePOLO\LaraCart
 */
class Cart
{
    public $tax;
    public $fees = [];
    public $items;
    public $locale;
    public $coupons = [];
    public $attributes = [];
    public $internationalFormat;
    protected $instance;

    function __construct($instance)
    {
        $this->instance = $instance;
        $this->tax = config('laracart.tax');
        $this->locale = config('laracart.locale');
        $this->multipleCoupons = config('laracart.multiple_coupons');
        $this->internationalFormat = config('laracart.international_format');
    }

    /**
     * Adds an Attribute to the cart
     *
     * @param $attribute
     * @param $value
     */
    public function setAttribute($attribute, $value)
    {
        array_set($this->attributes, $attribute, $value);

        $this->update();
    }

    /**
     * Updates cart session
     */
    public function update()
    {
        \Session::set(config('laracart.cache_prefix', 'laracart.') . $this->instance, $this);
        \Event::fire('laracart.update', $this);
    }

    /**
     * Removes an attribute from the cart
     *
     * @param $attribute
     */
    public function removeAttribute($attribute)
    {
        array_forget($this->attributes, $attribute);

        $this->update();
    }

    /**
     * Gets an an attribute from the cart
     *
     * @param $attribute
     * @param $defaultValue
     *
     * @return mixed
     */
    public function getAttribute($attribute, $defaultValue = null)
    {
        return array_get($this->attributes, $attribute, $defaultValue);
    }

    /**
     * Gets all the carts attributes
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
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
     * Adds the cartItem into the cart session
     *
     * @param $cartItem
     *
     * @return string itemHash
     */
    public function addItem($cartItem)
    {
        $itemHash = $cartItem->generateHash();

        if ($this->getItem($itemHash)) {
            if ($cartItem->lineItem === false) {
                $this->getItem($itemHash)->qty += $cartItem->qty;
            } else {
                $cartItem->itemHash = $cartItem->generatehash(true);
                $this->addItem($cartItem);
            }
        } else {
            $this->items[] = $cartItem;
            \Event::fire('laracart.addItem', $cartItem);
        }

        $this->update();

        return $cartItem;
    }

    /**
     * Finds a cartItem based on the itemHash
     *
     * @param $itemHash
     *
     * @return CartItem | null
     */
    public function getItem($itemHash)
    {
        return array_get($this->getItems(), $itemHash);
    }

    /**
     * Gets all the items within the cart
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];
        if (isset($this->items) === true) {
            foreach ($this->items as $item) {
                $items[$item->getHash()] = $item;
            }
        }

        return $items;
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
        if (empty($item = $this->getItem($itemHash)) === false) {
            $item->update($key, $value);
        }

        $newHash = $item->generateHash();

        \Event::fire('laracart.updateItem', [
            'item' => $item,
            'newHash' => $newHash
        ]);

        return $newHash;
    }

    /**
     * Updates all item hashes within the cart
     */
    public function updateItemHashes()
    {
        foreach ($this->getItems() as $itemHash => $item) {
            $this->updateItemHash($itemHash);
        }
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
        $item = $this->getItem($itemHash);

        $this->removeItem($itemHash);

        \Event::fire('laracart.updateHash', $itemHash);

        return $this->addItem($item);
    }

    /**
     * Removes a CartItem based on the itemHash
     *
     * @param $itemHash
     */
    public function removeItem($itemHash)
    {
        foreach ($this->items as $itemKey => $item) {
            if ($item->getHash() == $itemHash) {
                unset($this->items[$itemKey]);
                break;
            }
        }

        \Event::fire('laracart.removeItem', $itemHash);
    }

    /**
     * Empties the carts items
     */
    public function emptyCart()
    {
        unset($this->items);

        $this->update();

        \Event::fire('laracart.empty', $this->instance);
    }

    /**
     * Completely destroys cart and anything associated with it
     */
    public function destroyCart()
    {
        unset($this->items);

        $this->update();

        \Event::fire('laracart.destroy', $this->instance);
    }

    /**
     * Gets the total of the cart with or without tax
     * @return string
     */
    public function total($formatted = true, $withDiscount = true)
    {
        $total = $this->subTotal(true, false);

        if ($withDiscount) {
            $total -= $this->getTotalDiscount(false);
        }

        $total += $this->getFeeTotals();

        if ($formatted) {
            return \LaraCart::formatMoney($total, $this->locale, $this->internationalFormat);
        } else {
            return number_format($total, 2);
        }
    }

    /**
     * Gets all the fee totals
     *
     * @return int
     */
    public function getFeeTotals()
    {
        $feeTotal = 0;

        foreach($this->getFees() as $fee) {
            $feeTotal += $fee->amount;
            if($fee->taxable) {
                $feeTotal += $fee->amount * $this->tax;
            }
        }

        return $feeTotal;
    }

    /**
     * Gets the subtotal of the cart with or without tax
     *
     * @param bool $tax
     *
     * @return string
     */
    public function subTotal($tax = false, $formatted = true)
    {
        $total = 0;
        if ($this->count() != 0) {
            foreach ($this->getItems() as $item) {
                $total += $item->subTotal($tax, false) + $item->subItemsTotal($tax, false);
            }
        }

        if ($formatted) {
            return \LaraCart::formatMoney($total, $this->locale, $this->internationalFormat);
        } else {
            return number_format($total, 2);
        }

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
        foreach ($this->getItems() as $item) {
            if ($withQty) {
                $count += $item->qty;
            } else {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Gets the total amount discounted
     *
     * @return int
     */
    public function getTotalDiscount($formatted = true)
    {
        $total = 0;
        foreach ($this->coupons as $coupon) {
            $total += $coupon->discount();
        }

        if ($formatted) {
            return \LaraCart::formatMoney($total, $this->locale, $this->internationalFormat);
        } else {
            return $total;
        }
    }

    /**
     * Applies a coupon to the cart
     *
     * @param CouponContract $coupon
     */
    public function applyCoupon(CouponContract $coupon)
    {
        $this->coupons[$coupon->code] = $coupon;

        $this->update();
    }

    /**
     * Gets the coupons for the current cart
     *
     * @return array
     */
    public function getCoupons()
    {
        return $this->coupons;
    }

    /**
     * Removes a coupon in the cart
     *
     * @param $code
     */
    public function removeCoupon($code)
    {
        array_forget($this->coupons, $code);

        $this->update();
    }

    /**
     * Finds a specific coupon in the cart
     *
     * @param $code
     * @return mixed
     */
    public function findCoupon($code)
    {
        return array_get($this->coupons, $code);
    }

    /**
     * Getes all the fees on the cart object
     *
     * @return mixed
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Gets a speific fee from the fees array
     *
     * @param $name
     *
     * @return mixed
     */
    public function getFee($name)
    {
        return array_get($this->fees, $name, new CartFee(null, false));
    }

    /**
     * Allows to charge for additional fees that may or may not be taxable
     * ex - service fee , delivery fee, tips
     *
     * @param $name
     * @param $amount
     * @param bool|false $taxable
     * @param array $options
     */
    public function addFee($name, $amount, $taxable = false, Array $options = [])
    {
        array_set($this->fees, $name, new CartFee($amount, $taxable, $options));

        $this->update();
    }

    /**
     * Reemoves a fee from the fee array
     *
     * @param $name
     */
    public function removeFee($name)
    {
        array_forget($this->fees, $name);

        $this->update();
    }
}
