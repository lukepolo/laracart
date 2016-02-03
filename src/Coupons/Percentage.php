<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\LaraCart;
use LukePOLO\LaraCart\Traits\CouponTrait;

/**
 * Class Percentage
 *
 * @package LukePOLO\LaraCart\Coupons
 */

/**
 * Class Percentage
 * @package LukePOLO\LaraCart\Coupons
 */
class Percentage implements CouponContract
{
    use CouponTrait;

    public $code;
    public $value;


    /**
     * Percentage constructor.
     *
     * @param $code
     * @param $value
     *
     * @param array $options
     */
    public function __construct($code, $value, $options = [])
    {
        $this->code = $code;
        $this->value = $value;

        $this->setOptions($options);
    }

    /**
     * Gets the discount amount
     *
     * @return string
     */
    public function discount()
    {
        return LaraCart::formatMoney(
            app(LaraCart::SERVICE)->subTotal(false) * $this->value,
            null,
            null,
            false
        );
    }


    /**
     * @return mixed
     */
    public function displayValue()
    {
        return ($this->value * 100) . '%';
    }
}
