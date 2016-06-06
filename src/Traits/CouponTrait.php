<?php

namespace LukePOLO\LaraCart\Traits;

use Carbon\Carbon;
use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Exceptions\CouponException;
use LukePOLO\LaraCart\Exceptions\InvalidPrice;
use LukePOLO\LaraCart\LaraCart;

/**
 * Class CouponTrait
 * @package LukePOLO\LaraCart\Traits
 */
trait CouponTrait
{
    /**
     * @var bool
     */
    public $appliedToCart = true;

    use CartOptionsMagicMethodsTrait;

    /**
     * Sets all the options for the coupon
     * @param $options
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Checks to see if we can apply the coupon
     * @return bool
     */
    public function canApply()
    {
        try {
            $this->discount(true);
            return true;
        } catch (CouponException $e) {
            return false;
        }
    }

    /**
     * Gets the failed message for a coupon
     * @return null|string
     */
    public function getFailedMessage()
    {
        try {
            $this->discount(true);
        } catch (CouponException $e) {
            return $e->getMessage();
        }

        return null;
    }

    /**
     * Checks the minimum subtotal needed to apply the coupon
     * @param $minAmount
     * @param $throwErrors
     * @return bool
     * @throws CouponException
     */
    public function checkMinAmount($minAmount, $throwErrors = true)
    {
        $laraCart = \App::make(LaraCart::SERVICE);

        if ($laraCart->subTotal(false, false, false) >= $minAmount) {
            return true;
        }

        if ($throwErrors) {
            throw new CouponException('You must have at least a total of ' . $laraCart->formatMoney($minAmount));
        }
        return false;
    }

    /**
     * Returns either the max discount or the discount applied based on what is passed through
     * @param $maxDiscount
     * @param $discount
     * @param $throwErrors
     * @return mixed
     * @throws CouponException
     */
    public function maxDiscount($maxDiscount, $discount, $throwErrors = true)
    {
        if ($maxDiscount == 0 || $maxDiscount > $discount) {
            return $discount;
        }

        if ($throwErrors) {
            throw new CouponException('This has a max discount of ' . $this->formatMoney($maxDiscount));
        }
        return $maxDiscount;
    }

    /**
     * Checks to see if the times are valid for the coupon
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $throwErrors
     * @return bool
     * @throws CouponException
     */
    public function checkValidTimes(Carbon $startDate, Carbon $endDate, $throwErrors = true)
    {
        if (Carbon::now()->between($startDate, $endDate)) {
            return true;
        }

        if ($throwErrors) {
            throw new CouponException('This coupon has expired');
        }
        return false;
    }

    /**
     * Sets a discount to an item with what code was used and the discount amount
     * @param CartItem $item
     * @param $discountAmount
     * @throws InvalidPrice
     */
    public function setDiscountOnItem(CartItem $item, $discountAmount)
    {
        if (!is_numeric($discountAmount)) {
            throw new InvalidPrice('You must use a discount amount.');
        }
        $this->appliedToCart = false;
        $item->code = $this->code;
        $item->discount = $discountAmount;
        $item->couponInfo = $this->options;
    }
}
