<?php

namespace LukePOLO\LaraCart\Coupons;

use LukePOLO\LaraCart\CartMoneyFormatter;
use LukePOLO\LaraCart\Contracts\CouponContract;
use LukePOLO\LaraCart\LaraCart;
use LukePOLO\LaraCart\Traits\CouponActions;

/**
 * Class Fixed
 * @package LukePOLO\LaraCart\Coupons
 */
class Fixed implements CouponContract
{
    use CouponActions;

    public $code;
    public $value;

    /**
     * Fixed constructor.
     * @param $code
     * @param $value
     * @param array $options
     */
    public function __construct($code, $value, array $options = [])
    {
        $this->code = $code;
        $this->value = $value;

        $this->setOptions($options);
    }

    /**
     * Gets the discount amount
     * @param $throwErrors boolean this allows us to capture errors in our code if we wish, that way we can spit out why the coupon has failed
     * @return string
     */
    public function discount($throwErrors = false)
    {
        $total = app(LaraCart::SERVICE)->subTotal(false)->amount() - $this->value;

        if ($total < 0) {
            return 0;
        }

        return new CartMoneyFormatter($this->value);
    }

    /**
     * Displays the value in a money format
     * @param null $locale
     * @param null $internationalFormat
     * @return string
     */
    public function displayValue($locale = null, $internationalFormat = null)
    {
        return $this->discount();
    }
}
