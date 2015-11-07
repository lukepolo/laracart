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
    public $cart;

    /**
     * @param LaraCartContract $laraCartService | LukePOLO\LaraCart\LaraCart $laraCartService
     */
    function __construct()
    {
        $this->setInstance(\Session::get('laracart.instance', 'default'));
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
        \Session::set('laracart.instance', $instance);

        \Event::fire('laracart.new');
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

        return $this->cart;
    }

    /**
     * Formats the number into a money format based on the locale and international formats
     *
     * @param $number
     * @param $locale
     * @param $internationalFormat
     *
     * @return string
     */
    public function formatMoney($number, $locale = null, $internationalFormat = null)
    {
        if (empty($locale) === true) {
            $locale = config('laracart.locale', 'en_US');
        }

        if (empty($internationalFormat) === true) {
            $internationalFormat = config('laracart.international_format');
        }

        setlocale(LC_MONETARY, $locale);
        if ($internationalFormat) {
            return money_format('%i', $number);
        } else {
            return money_format('%n', $number);
        }
    }

    /**
     * Generates a hash for an object
     *
     * @param $object
     * @return string
     */
    public function generateHash($object)
    {
        return md5(json_encode($object));
    }

    /**
     * Generates a random hash
     *
     * @return string
     */
    public function generateRandomHash()
    {
        return str_random(40);
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
     * Updates cart session
     */
    public function update()
    {
        \Session::set(config('laracart.cache_prefix', 'laracart.') . $this->cart->instance, $this->cart);
        \Event::fire('laracart.update', $this->cart);
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
            $this->cart->items[] = $cartItem;
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
        foreach ($this->cart->items as $itemKey => $item) {
            if ($item->getHash() == $itemHash) {
                unset($this->cart->items[$itemKey]);
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
        unset($this->cart->items);

        $this->update();

        \Event::fire('laracart.empty', $this->cart->instance);
    }

    /**
     * Completely destroys cart and anything associated with it
     */
    public function destroyCart()
    {
        unset($this->cart->items);

        $this->update();

        \Event::fire('laracart.destroy', $this->cart->instance);
    }

    /**
     * Gets the total of the cart with or without tax
     * @return string
     */
    public function total($formatted = true, $withDiscount = true)
    {
        $total = $this->subTotal(true, false, $withDiscount) + $this->getFeeTotals(false);

        if ($formatted) {
            return $this->formatMoney($total);
        } else {
            return number_format($total, 2);
        }
    }

    /**
     * Gets the total tax for the cart
     *
     * @param bool|true $formatted
     *
     * @return string
     */
    public function taxTotal($formatted = true)
    {
        $totalTax = $this->total(false, false) - $this->subTotal(false, false, false) - $this->getFeeTotals(false);

        if ($formatted) {
            return $this->formatMoney($totalTax);
        } else {
            return number_format($totalTax, 2);
        }
    }


    /**
     * Gets all the fee totals
     *
     * @param bool|true $formatted
     *
     * @return string
     */
    public function getFeeTotals($formatted = true)
    {
        $feeTotal = 0;

        foreach($this->getFees() as $fee) {
            $feeTotal += $fee->amount;
            if($fee->taxable) {
                $feeTotal += $fee->amount * $this->tax;
            }
        }

        if ($formatted) {
            return $this->formatMoney($feeTotal);
        } else {
            return number_format($feeTotal, 2);
        }
    }

    /**
     * Gets the subtotal of the cart with or without tax
     *
     * @param bool|false $tax
     * @param bool|true $formatted
     * @param bool|true $withDiscount
     *
     * @return string
     */
    public function subTotal($tax = false, $formatted = true, $withDiscount = true)
    {
        $total = 0;
        if ($this->count() != 0) {
            foreach ($this->getItems() as $item) {
                $total += $item->subTotal($tax, false, $withDiscount);
            }
        }

        if ($formatted) {
            return $this->formatMoney($total);
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
     * @param bool|true $formatted
     *
     * @return int|string
     */
    public function getTotalDiscount($formatted = true)
    {
        $total = 0;
        foreach ($this->cart->coupons as $coupon) {
            $total += $coupon->discount();
        }

        if ($formatted) {
            return $this->formatMoney($total);
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
        $this->cart->coupons[$coupon->code] = $coupon;

        $this->update();
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
     * Getes all the fees on the cart object
     *
     * @return mixed
     */
    public function getFees()
    {
        return $this->cart->fees;
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
}
