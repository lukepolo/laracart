<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\LaraCart;
use LukePOLO\LaraCart\Traits\CouponTrait;

/**
 * Class Fixed.
 */
class Fixed implements CouponContract
{
    use CouponTrait;

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
     * @return string
     */
    public function discount($item)
    {
        $discount = $this->value - $this->discounted;
        if ($discount > $item->price) {
            return $item->price;
        }

        return LaraCart::formatMoney(
            $discount,
            null,
            null,
            false
        );
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
            $this->value,
            $locale,
            $currencyCode,
            $format
        );
    }
}
