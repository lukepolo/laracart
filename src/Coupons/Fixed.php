<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\Cart;
use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\Traits\CouponTrait;

class Fixed implements CouponContract
{
    use CouponTrait;

    public $code;
    public $value;

    /**
     * @param $code
     * @param $value
     */
    public function __construct($code, $value)
    {
        $this->code = $code;
        $this->value = $value;
    }

    /**
     * Gets the discount amount
     *
     * @param Cart $cart
     * @return string
     */
    public function discount(Cart $cart)
    {
        return $this->value;
    }
}