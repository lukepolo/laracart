<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\LaraCart;
use LukePOLO\LaraCart\Traits\CouponTrait;

/**
 * Class Fixed.
 */
class Fixed implements CouponContract
{
    use CouponTrait;

    public $code;
    public $value;

    /**
     * Fixed constructor.
     *
     * @param $code
     * @param $value
     * @param array $options
     */
    public function __construct($code, $value, $options = [])
    {
        $this->code = $code;
        $this->value = $value;

        $this->setOptions($options);
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
        return $this->value;
    }

    /**
     * If an item is supplied it will get its discount value.
     *
     * @param CartItem $item
     *
     * @return float
     */
    // TODO
    public function forItem(CartItem $item)
    {
        return $this->value;
    }

    /**
     * Displays the value in a money format.
     *
     * @param null $locale
     * @param null $currencyCode
     *
     * @return string
     */
    public function displayValue($locale = null, $currencyCode = null, $format = true)
    {
        return LaraCart::formatMoney(
            $this->discount(),
            $locale,
            $currencyCode,
            $format
        );
    }
}
