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
     * that way we can spit out why the coupon has failed
     *
     * @throws CouponException
     *
     * @return string
     */
    public function discount($item, $amountApplied)
    {
        throw new CouponException('Sorry, you must have at least 100 dollars!');
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
