<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\Contracts\LaraCartContract;

/**
 * Class LaraCart
 *
 * @package LukePOLO\LaraCart
 */
class LaraCart implements LaraCartContract
{
    const SERVICE = 'laracart';
    const QTY = 'qty';
    const PRICE = 'price';
    const HASH = 'generateCartHash';
    const RANHASH = 'generateRandomCartItemHash';

    public $cart;

    /**
     * LaraCart constructor.
     */
    public function __construct()
    {
        $this->setInstance(\Session::get('laracart.instance', 'default'));
    }

    /**
     * Sets and Gets the instance of the cart in the session we should be using
     *
     * @param string $instance
     *
     * @return LaraCart
     */
    public function setInstance($instance = 'default')
    {
        $this->get($instance);

        \Session::set('laracart.instance', $instance);

        \Event::fire('laracart.new');

        return $this;
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
        if (empty($this->cart = \Session::get(config('laracart.cache_prefix', 'laracart.') . $instance))) {
            $this->cart = new Cart($instance);
        }

        return $this;
    }

    /**
     * Updates cart session
     */
    public function update()
    {
        \Session::set(config('laracart.cache_prefix', 'laracart.') . $this->cart->instance, $this->cart);

        \Event::fire('laracart.update', $this->cart);
    }

    /**
     *
     * Formats the number into a money format based on the locale and international formats
     *
     * @param $number
     * @param $locale
     * @param $internationalFormat
     * @param $format
     *
     * @return string
     */
    public function formatMoney($number, $locale = null, $internationalFormat = null, $format = true)
    {
        if ($format) {
            setlocale(LC_MONETARY, empty($locale) ? config('laracart.locale', 'en_US.UTF-8') : $locale);

            if (empty($internationalFormat) === true) {
                $internationalFormat = config('laracart.international_format', false);
            }

            return money_format($internationalFormat ? '%i' : '%n', $number);
        }

        return number_format($number, 2);
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
        return array_get($this->cart->attributes, $attribute, $defaultValue);
    }

    /**
     * Gets all the carts attributes
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->cart->attributes;
    }

    /**
     * Adds an Attribute to the cart
     *
     * @param $attribute
     * @param $value
     */
    public function setAttribute($attribute, $value)
    {
        array_set($this->cart->attributes, $attribute, $value);

        $this->update();
    }

    /**
     * Removes an attribute from the cart
     *
     * @param $attribute
     */
    public function removeAttribute($attribute)
    {
        array_forget($this->cart->attributes, $attribute);

        $this->update();
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
        if (isset($this->cart->items) === true) {
            foreach ($this->cart->items as $item) {
                $items[$item->getHash()] = $item;
            }
        }

        return $items;
    }

    /**
     * Creates a CartItem and then adds it to cart
     *
     * @param $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @param bool|false $lineItem
     *
     * @return CartItem
     */
    public function add($itemID, $name = null, $qty = 1, $price = '0.00', $options = [], $lineItem = false)
    {
        return $this->addItem(
            new CartItem(
                $itemID,
                $name,
                $qty,
                $price,
                $options,
                $lineItem
            )
        );
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
     * @return CartItem
     */
    public function addLine($itemID, $name = null, $qty = 1, $price = '0.00', $options = [])
    {
        return $this->add($itemID, $name, $qty, $price, $options, true);
    }

    /**
     * Adds the cartItem into the cart session
     *
     * @param CartItem $cartItem
     *
     * @return CartItem
     */
    public function addItem($cartItem)
    {
        $itemHash = $cartItem->generateHash();

        if ($this->getItem($itemHash)) {
            $this->getItem($itemHash)->qty += $cartItem->qty;
        } else {
            $this->cart->items[] = $cartItem;
            \Event::fire('laracart.addItem', $cartItem);
        }

        $this->update();

        return $cartItem;
    }

    /**
     * Updates an items attributes
     *
     * @param $itemHash
     * @param $key
     * @param $value
     *
     * @return CartItem
     *
     * @throws Exceptions\InvalidPrice
     * @throws Exceptions\InvalidQuantity
     */
    public function updateItem($itemHash, $key, $value)
    {
        if (empty($item = $this->getItem($itemHash)) === false) {
            $item->update($key, $value);
        }

        $newHash = $item->generateHash();

        \Event::fire(
            'laracart.updateItem',
            [
                'item' => $item,
                'newHash' => $newHash,
            ]
        );

        return $item;
    }

    /**
     * Removes a CartItem based on the itemHash
     *
     * @param $itemHash
     */
    public function removeItem($itemHash)
    {
        foreach ($this->cart->items as $itemKey => $item) {
            if ($item->getHash() == $itemHash) {
                unset($this->cart->items[$itemKey]);
                break;
            }
        }

        \Event::fire('laracart.removeItem', $itemHash);
    }

    /**
     * Get the count based on qty, or number of unique items
     *
     * @param bool $withItemQty
     *
     * @return int
     */
    public function count($withItemQty = true)
    {
        $count = 0;
        foreach ($this->getItems() as $item) {
            if ($withItemQty) {
                $count += $item->qty;
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Empties the carts items
     */
    public function emptyCart()
    {
        unset($this->cart->items);

        $this->update();

        \Event::fire('laracart.empty', $this->cart->instance);
    }

    /**
     * Completely destroys cart and anything associated with it
     */
    public function destroyCart()
    {
        $this->cart = new Cart();

        $this->update();

        \Event::fire('laracart.destroy', $this->cart->instance);
    }

    /**
     * Gets the coupons for the current cart
     *
     * @return array
     */
    public function getCoupons()
    {
        return $this->cart->coupons;
    }

    /**
     * // TODO - badly named
     * Finds a specific coupon in the cart
     *
     * @param $code
     * @return mixed
     */
    public function findCoupon($code)
    {
        return array_get($this->cart->coupons, $code);
    }

    /**
     * // todo - badly named
     * Applies a coupon to the cart
     *
     * @param CouponContract $coupon
     */
    public function addCoupon(CouponContract $coupon)
    {
        $this->cart->coupons[$coupon->code] = $coupon;

        $this->update();
    }

    /**
     * Removes a coupon in the cart
     *
     * @param $code
     */
    public function removeCoupon($code)
    {
        array_forget($this->cart->coupons, $code);

        $this->update();
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
        return array_get($this->cart->fees, $name, new CartFee(null, false));
    }

    /**
     * Getes all the fees on the cart object
     *
     * @return mixed
     */
    public function getFees()
    {
        return $this->cart->fees;
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
        array_set($this->cart->fees, $name, new CartFee($amount, $taxable, $options));

        $this->update();
    }

    /**
     * Reemoves a fee from the fee array
     *
     * @param $name
     */
    public function removeFee($name)
    {
        array_forget($this->cart->fees, $name);

        $this->update();
    }

    /**
     * Gets all the fee totals
     *
     * @param boolean $format
     *
     * @return string
     */
    public function feeTotals($format = true)
    {
        $feeTotal = 0;

        foreach ($this->getFees() as $fee) {
            $feeTotal += $fee->amount;
            if ($fee->taxable) {
                $feeTotal += $fee->amount * $this->cart->tax;
            }
        }

        if ($format) {
            return $this->formatMoney($feeTotal);
        }

        return number_format($feeTotal, 2);
    }

    /**
     * Gets the total amount discounted
     *
     * @param bool|true $format
     *
     * @return int|string
     */
    public function totalDiscount($format = true)
    {
        $total = 0;
        foreach ($this->cart->coupons as $coupon) {
            $total += $coupon->discount();
        }

        if ($format) {
            return $this->formatMoney($total);
        }

        return $total;
    }


    /**
     * Gets the total tax for the cart
     *
     * @param bool|true $format
     *
     * @return string
     */
    public function taxTotal($format = true)
    {
        $totalTax = $this->total(false, false) - $this->subTotal(false, false, false) - $this->feeTotals(false);

        if ($format) {
            return $this->formatMoney($totalTax);
        }

        return number_format($totalTax, 2);
    }

    /**
     * Gets the subtotal of the cart with or without tax
     *
     * @param bool|false $tax
     * @param boolean $format
     * @param boolean $withDiscount
     *
     * @return string
     */
    public function subTotal($tax = false, $format = true, $withDiscount = true)
    {
        $total = 0;
        if ($this->count() != 0) {
            foreach ($this->getItems() as $item) {
                $total += $item->subTotal($tax, false, $withDiscount);
            }
        }

        if ($format) {
            return $this->formatMoney($total);
        }

        return number_format($total, 2);
    }

    /**
     * Gets the total of the cart with or without tax
     *
     * @param boolean $format
     * @param boolean $withDiscount
     * @return string
     */
    public function total($format = true, $withDiscount = true)
    {
        $total = $this->subTotal(true, false, $withDiscount) + $this->feeTotals(false);

        if ($format) {
            return $this->formatMoney($total);
        }

        return number_format($total, 2);
    }
}
