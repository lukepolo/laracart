<?php

namespace LukePOLO\LaraCart\Tests\Coupons;

use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\Exceptions\CouponException;
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
     * @param       $code
     * @param       $value
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
     * that way we can spit out why the coupon has failed
     *
     * @return string
     */
    public function discount($price)
    {
        if ($this->canApply()) {
            return 100;
        }

        return 0;
    }

    /**
     * Checks if you can apply the coupon.
     *
     * @throws CouponException
     *
     * @return bool
     */
    public function canApply($throw = false)
    {
        if ($this->discounted === 0) {
            throw new CouponException('Sorry, you must have at least 100 dollars!');
        }

        return true;
    }

    /**
     * Displays the value in a money format.
     *
     * @param null $locale
     * @param null $currencyCode
     *
     * @return string
     */
    public function displayValue($locale = null, $currencyCode = null)
    {
        return LaraCart::formatMoney(
            $this->discount(),
            $locale,
            $currencyCode
        );
    }
}
