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
    public function __construct($code, $value, $attributes = [])
    {
        $this->code = $code;
        $this->value = $value;

        $this->setAttributes($attributes);
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

    /**
     * Displays the type of value it is for the user
     *
     * @return mixed
     */
    public function displayValue($locale = null, $internationalFormat = null)
    {
        return \LaraCart::formatMoney($this->value, $locale, $internationalFormat);
    }
}