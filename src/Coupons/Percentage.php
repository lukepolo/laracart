<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\Cart;
use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\Traits\CouponTrait;

class Percentage implements CouponContract
{
    use CouponTrait;

    public $code;
    public $value;

    /**
     * @param $code
     * @param $value
     */
    public function __construct($code, $value, $attributes = [])
    {
        $this->code = $code;
        $this->value = $value;

        $this->setAttributes($attributes);
    }

    /**
     * Gets the discount amount
     *
     * @return string
     */
    public function discount()
    {
        return \LaraCart::total(false, false) * $this->value;
    }

    /**
     * Displays the type of value it is for the user
     *
     * @return mixed
     */
    public function displayValue()
    {
        return $this->value;
    }
}