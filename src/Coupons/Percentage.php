<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\Exceptions\CouponException;
use LukePOLO\LaraCart\LaraCart;
use LukePOLO\LaraCart\Traits\CouponTrait;

/**
 * Class Percentage.
 */

/**
 * Class Percentage.
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
     * @param array $options
     *
     * @throws \Exception
     */
    public function __construct($code, $value, $options = [])
    {
        $this->code = $code;
        if ($value > 1) {
            throw new CouponException('Invalid value for a percentage coupon. The value must be between 0 and 1.');
        }
        $this->value = $value;

        $this->setOptions($options);
    }

    /**
     * Gets the discount amount.
     *
     * @return string
     */
    public function discount($item, $amountApplied) {
        return LaraCart::formatMoney(
            $item->subTotal(false) * $this->value,
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
        return ($this->value * 100).'%';
    }
}
