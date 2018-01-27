<?php

namespace LukePOLO\LaraCart\Contracts;

use LukePOLO\LaraCart\CartItem;

/**
 * Interface CouponContract.
 */
interface CouponContract
{
    /**
     * CouponContract constructor.
     *
     * @param $code
     * @param $value
     */
    public function __construct($code, $value);

    /**
     * Gets the discount amount.
     *
     * @return string
     */
    public function discount();

    /**
     * If an item is supplied it will get its discount value.
     *
     * @param CartItem $cartItem
     *
     * @return mixed
     */
    public function forItem(CartItem $cartItem);

    /**
     * Displays the type of value it is for the user.
     *
     * @return mixed
     */
    public function displayValue();
}
