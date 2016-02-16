<?php

namespace LukePOLO\LaraCart\Contracts;

use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\Exceptions\InvalidQuantity;
use LukePOLO\LaraCart\LaraCart;

/**
 * Interface LaraCartContract
 *
 * @package LukePOLO\LaraCart
 */
interface LaraCartContract
{
    /**
     * Sets and Gets the instance of the cart in the session we should be using
     *
     * @param string $instance
     *
     * @return LaraCart
     */
    public function setInstance($instance = 'default');


    /**
     * Gets the instance in the session
     *
     * @param string $instance
     *
     * @return $this cart instance
     */
    public function get($instance = 'default');


    /**
     * Gets an an attribute from the cart
     *
     * @param $attribute
     * @param $defaultValue
     *
     * @return mixed
     */
    public function getAttribute($attribute, $defaultValue = null);

    /**
     * Gets all the carts attributes
     *
     * @return mixed
     */
    public function getAttributes();

    /**
     * Adds an Attribute to the cart
     *
     * @param $attribute
     * @param $value
     */
    public function setAttribute($attribute, $value);

    /**
     * Updates cart session
     */
    public function update();

    /**
     * Removes an attribute from the cart
     *
     * @param $attribute
     */
    public function removeAttribute($attribute);

    /**
     * Creates a CartItem and then adds it to cart
     *
     * @param string|int $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @param bool|true $taxable
     *
     * @return CartItem
     */
    public function addLine($itemID, $name = null, $qty = 1, $price = '0.00', $options = [], $taxable = true);

    /**
     * Creates a CartItem and then adds it to cart
     *
     * @param $itemID
     * @param null $name
     * @param int $qty
     * @param string $price
     * @param array $options
     * @param bool|false $taxable
     * @param bool|false $lineItem
     *
     * @return CartItem
     */
    public function add(
        $itemID,
        $name = null,
        $qty = 1,
        $price = '0.00',
        $options = [],
        $taxable = true,
        $lineItem = false
    );

    /**
     * Adds the cartItem into the cart session
     *
     * @param CartItem $cartItem
     *
     * @return CartItem
     */
    public function addItem(CartItem $cartItem);

    /**
     * Finds a cartItem based on the itemHash
     *
     * @param $itemHash
     *
     * @return CartItem | null
     */
    public function getItem($itemHash);

    /**
     * Gets all the items within the cart
     *
     * @return array
     */
    public function getItems();


    /**
     * Updates an items attributes
     *
     * @param $itemHash
     * @param $key
     * @param $value
     *
     * @return CartItem
     *
     * @throws InvalidPrice
     * @throws InvalidQuantity
     */
    public function updateItem($itemHash, $key, $value);

    /**
     * Removes a CartItem based on the itemHash
     *
     * @param $itemHash
     */
    public function removeItem($itemHash);

    /**
     * Empties the carts items
     */
    public function emptyCart();

    /**
     * Completely destroys cart and anything associated with it
     */
    public function destroyCart();

    /**
     * Gets the coupons for the current cart
     *
     * @return array
     */
    public function getCoupons();

    /**
     * Finds a specific coupon in the cart
     *
     * @param $code
     * @return mixed
     */
    public function findCoupon($code);

    /**
     * Applies a coupon to the cart
     *
     * @param CouponContract $coupon
     */
    public function addCoupon(CouponContract $coupon);

    /**
     * Removes a coupon in the cart
     *
     * @param $code
     */
    public function removeCoupon($code);

    /**
     * Gets a speific fee from the fees array
     *
     * @param $name
     *
     * @return mixed
     */
    public function getFee($name);

    /**
     * Allows to charge for additional fees that may or may not be taxable
     * ex - service fee , delivery fee, tips
     *
     * @param $name
     * @param $amount
     * @param bool|false $taxable
     * @param array $options
     */
    public function addFee($name, $amount, $taxable = false, array $options = []);

    /**
     * Reemoves a fee from the fee array
     *
     * @param $name
     */
    public function removeFee($name);

    /**
     * Gets the total tax for the cart
     *
     * @param bool|true $format
     *
     * @return string
     */
    public function taxTotal($format = true);

    /**
     * Gets the total of the cart with or without tax
     *
     * @param boolean $format
     * @param boolean $withDiscount
     *
     * @return string
     */
    public function total($format = true, $withDiscount = true);

    /**
     * Gets the subtotal of the cart with or without tax
     *
     * @param boolean $format
     * @param boolean $withDiscount
     *
     * @return string
     */
    public function subTotal($format = true, $withDiscount = true);

    /**
     * Get the count based on qty, or number of unique items
     *
     * @param bool $withItemQty
     *
     * @return int
     */
    public function count($withItemQty = true);

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
    public static function formatMoney($number, $locale = null, $internationalFormat = null, $format = true);

    /**
     * Gets all the fee totals
     *
     * @param boolean $format
     *
     * @return string
     */
    public function feeTotals($format = true);

    /**
     * Getes all the fees on the cart object
     *
     * @return mixed
     */
    public function getFees();

    /**
     * Gets the total amount discounted
     *
     * @param bool|true $format
     *
     * @return int|string
     */
    public function totalDiscount($format = true);
}
