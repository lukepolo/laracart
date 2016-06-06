<?php

namespace LukePOLO\LaraCart\Traits;

use LukePOLO\LaraCart\Contracts\CouponContract;

/**
 * Class CartItems
 *
 * @package LukePOLO\LaraCart\Traits
 */
trait CartCoupons
{
    /**
     * Applies a coupon to the cart
     * @param CouponContract $coupon
     * return void
     */
    public function addCoupon(CouponContract $coupon)
    {
        if (!$this->cart->multipleCoupons) {
            $this->cart->coupons = [];
        }

        $this->cart->coupons[$coupon->code] = $coupon;

        $this->update();
    }

    /**
     * Gets the coupons for the current cart
     * @return array
     */
    public function getCoupons()
    {
        return $this->cart->coupons;
    }

    /**
     * Finds a specific coupon in the cart
     * @param $code
     * @return mixed
     */
    public function findCoupon($code)
    {
        return array_get($this->cart->coupons, $code);
    }

    /**
     * Removes a coupon in the cart
     * @param $code
     * return void
     */
    public function removeCoupon($code)
    {
        $this->removeCouponFromItems($code);
        array_forget($this->cart->coupons, $code);
        $this->update();
    }

    /**
     * Removes all coupons from the cart
     * return void
     */
    public function removeCoupons()
    {
        $this->removeCouponFromItems();
        $this->cart->coupons = [];
        $this->update();
    }

    /**
     * Removes a coupon from the item
     *
     * @param null $code
     */
    private function removeCouponFromItems($code = null)
    {
        foreach ($this->getItems() as $item) {
            if (isset($item->code) && (empty($code) || $item->code == $code)) {
                $item->code = null;
                $item->discount = null;
                $item->couponInfo = [];
            }
        }
    }
}
