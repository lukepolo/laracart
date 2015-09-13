<?php

namespace LukePOLO\LaraCart\Contracts;

use LukePOLO\LaraCart\Cart;

interface CouponContract
{
    /**
     * @param $code
     * @param $value
     */
    public function __construct($code, $value);

    /**
     * Gets the discount amount
     *
     * @param Cart $cart
     * @return string
     */
    public function discount(Cart $cart);

    /**
     * Displays the type of value it is for the user
     *
     * @return mixed
     */
    public function displayValue();
}
