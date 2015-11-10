<?php

namespace LukePOLO\LaraCart\Traits;

use Carbon\Carbon;
use LaraCart;

trait CouponTrait
{
    use CartOptionsMagicMethodsTrait;

    /**
     * Sets all the options for the coupon
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
     * Get the reason why a coupon has failed to apply
     *
     * @return string
     */
    public function getMessage()
    {
        try {
            $this->discount(true);

            return 'Coupon Applied';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Checks the minimum amount needed to apply the coupon
     *
     * @param $minAmount
     * @param $throwErrors
     *
     * @return bool
     * @throws \Exception
     */
    public function checkMinAmount($minAmount, $throwErrors)
    {
        if (LaraCart::total(false, false) >= $minAmount) {
            return true;
        } else {
            if ($throwErrors) {
                throw new \Exception('You must have at least a total of ' . LaraCart::formatMoney($minAmount));
            } else {
                return false;
            }
        }
    }

    /**
     * Returns either the max discount or the discount applied based on what is passed through
     *
     * @param $maxDiscount
     * @param $discount
     * @param $throwErrors
     *
     * @return mixed
     * @throws \Exception
     */
    public function maxDiscount($maxDiscount, $discount, $throwErrors)
    {
        if ($maxDiscount == 0 || $maxDiscount > $discount) {
            return $discount;
        } else {
            if ($throwErrors) {
                throw new \Exception('This has a max discount of ' . LaraCart::formatMoney($maxDiscount));
            } else {
                return $maxDiscount;
            }
        }
    }

    /**
     * Checks to see if the times are valid for the coupon
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param $throwErrors
     *
     * @return bool
     * @throws \Exception
     */
    public function checkValidTimes(Carbon $startDate, Carbon $endDate, $throwErrors)
    {
        if (Carbon::now()->between($startDate, $endDate)) {
            return true;
        } else {
            if ($throwErrors) {
                throw new \Exception('This coupon has expired');
            } else {
                return false;
            }
        }
    }

    /**
     * Sets a discount to an item with what code was used and the discount amount
     *
     * @param $item
     * @param $code
     * @param $discount
     */
    public function setDiscountOnItem($item, $code, $discount)
    {
        $item->code = $code;
        $item->discount = $discount;
    }
}
