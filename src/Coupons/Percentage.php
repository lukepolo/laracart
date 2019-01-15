<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\CartItem;
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
     * If an item is supplied it will get its discount value.
     *
     * @param CartItem $item
     *
     * @return float
     */
    public function forItem(CartItem $item)
    {
        if (config('laracart.tax_item_before_discount')) {
            return $item->subTotal(false, false, false, true) * $this->value;
        }

        return $item->price * $this->value;
    }

    /**
     * Gets the discount amount.
     *
     * @param $throwErrors boolean this allows us to capture errors in our code if we wish,
     * that way we can spit out why the coupon has failed
     *
     * @return string
     */
    public function discount($throwErrors = false)
    {
        $subTotal = app(LaraCart::SERVICE)->subTotal(false);
        if (config('laracart.discountOnFees', false)) {
            $subTotal = $subTotal + app(LaraCart::SERVICE)->feeTotals(false);
        }

        return LaraCart::formatMoney(
            $subTotal * $this->value,
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
