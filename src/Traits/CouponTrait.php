<?php

namespace LukePOLO\LaraCart\Traits;

use Carbon\Carbon;
use LaraCart;

trait CouponTrait
{
    public $attributes = [];

    /**
     * Magic Method allows for user input as an object
     *
     * @param $attribute
     *
     * @return mixed | null
     */
    public function __get($attribute)
    {
        return array_get($this->attributes, $attribute);
    }

    /**
     * Magic Method allows for user input to set a value inside a object
     *
     * @param $attribute
     * @param $value
     */
    public function __set($attribute, $value)
    {
        array_set($this->attributes, $attribute, $value);
    }

    /**
     * Magic Method allows for user to check if an option isset
     *
     * @param $attribute
     *
     * @return bool
     */
    public function __isset($attribute)
    {
        if (empty($this->attributes[$attribute]) === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets all the attributes for the coupon
     *
     * @param $attributes
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get the reason why a coupon has failed to apply
     *
     * @return bool|string
     */
    public function getMessage()
    {
        try {
            $this->discount(true);

            return true;
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
        // TODO - remove coupon from the cart
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
    public function setDiscountToItem($item, $code, $discount)
    {
        $item->code = $code;
        $item->discount = $discount;
    }
}
