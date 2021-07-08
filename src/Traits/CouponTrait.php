<?php

namespace LukePOLO\LaraCart\Traits;

use Carbon\Carbon;
use LukePOLO\LaraCart\CartItem;
use LukePOLO\LaraCart\Exceptions\CouponException;
use LukePOLO\LaraCart\LaraCart;

/**
 * Class CouponTrait.
 */
trait CouponTrait
{
    public $code;
    public $value;
    public $discounted;
    public $appliedToCart = true;

    use CartOptionsMagicMethodsTrait;

    /**
     * Sets all the options for the coupon.
     *
     * @param $options
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Checks to see if we can apply the coupon.
     *
     * @return bool
     */
    public function canApply()
    {
        try {
            // TODO
//            $this->discount();
            return true;
        } catch (CouponException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Checks the minimum subtotal needed to apply the coupon.
     *
     * @param $minAmount
     * @param $throwErrors
     *
     * @throws CouponException
     *
     * @return bool
     */
    public function checkMinAmount($minAmount, $throwErrors = true)
    {
        $laraCart = \App::make(LaraCart::SERVICE);

        if ($laraCart->subTotal(false, false, false) >= $minAmount) {
            return true;
        } else {
            if ($throwErrors) {
                throw new CouponException('You must have at least a total of '.$laraCart->formatMoney($minAmount));
            } else {
                return false;
            }
        }
    }

    /**
     * Returns either the max discount or the discount applied based on what is passed through.
     *
     * @param $maxDiscount
     * @param $discount
     * @param $throwErrors
     *
     * @throws CouponException
     *
     * @return mixed
     */
    public function maxDiscount($maxDiscount, $discount, $throwErrors = true)
    {
        if ($maxDiscount == 0 || $maxDiscount > $discount) {
            return $discount;
        } else {
            if ($throwErrors) {
                throw new CouponException('This has a max discount of '.\App::make(\LukePOLO\Laracart\LaraCart::SERVICE)->formatMoney($maxDiscount));
            } else {
                return $maxDiscount;
            }
        }
    }

    /**
     * Checks to see if the times are valid for the coupon.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $throwErrors
     *
     * @throws CouponException
     *
     * @return bool
     */
    public function checkValidTimes(Carbon $startDate, Carbon $endDate, $throwErrors = true)
    {
        if (Carbon::now()->between($startDate, $endDate)) {
            return true;
        } else {
            if ($throwErrors) {
                throw new CouponException('This coupon has expired');
            } else {
                return false;
            }
        }
    }

    /**
     * Sets a discount to an item with what code was used and the discount amount.
     *
     * @param CartItem $item
     */
    public function setDiscountOnItem(CartItem $item)
    {
        $this->appliedToCart = false;
        $item->coupon = $this;
        $item->update();
    }
}
