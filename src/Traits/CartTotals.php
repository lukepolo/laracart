<?php

namespace LukePOLO\LaraCart\Traits;

/**
 * Class CartItems
 *
 * @package LukePOLO\LaraCart\Traits
 */
trait CartTotals
{
    /**
     * Gets the total amount discounted
     * @param boolean $format
     * @return string
     */
    public function totalDiscount($format = true)
    {
        $total = 0;

        foreach ($this->cart->coupons as $coupon) {
            if ($coupon->appliedToCart) {
                $total += $coupon->discount();
            }
        }

        return $this->formatMoney($total, null, null, $format);
    }

    /**
     * Gets all the fee totals
     * @param boolean $format
     * @return string
     */
    public function feeTotals($format = true, $withTax = false)
    {
        $feeTotal = 0;

        foreach ($this->getFees() as $fee) {
            $feeTotal += $fee->amount;

            if ($withTax && $fee->taxable && $fee->tax > 0) {
                $feeTotal += $fee->amount * $fee->tax;
            }
        }

        return $this->formatMoney($feeTotal, null, null, $format);
    }

    /**
     * Gets the total tax for the cart
     * @param bool|true $format
     * @return string
     */
    public function taxTotal($format = true)
    {
        $totalTax = 0;
        $discounted = 0;
        $totalDiscount = $this->totalDiscount(false);

        if ($this->count() != 0) {
            foreach ($this->getItems() as $item) {
                if ($discounted >= $totalDiscount) {
                    $totalTax += $item->tax();
                } else {
                    $itemPrice = $item->subTotal(false);

                    if (($discounted + $itemPrice) > $totalDiscount) {
                        $totalTax += $item->tax($totalDiscount - $discounted);
                    }

                    $discounted += $itemPrice;
                }
            }
        }

        foreach ($this->getFees() as $fee) {
            if ($fee->taxable) {
                $totalTax += $fee->amount * $fee->tax;
            }
        }

        return $this->formatMoney($totalTax, null, null, $format);
    }
    
    /**
     * Gets the subtotal of the cart with or without tax
     * @param boolean $format
     * @param boolean $withDiscount
     * @return string
     */
    public function subTotal($format = true, $withDiscount = true)
    {
        $total = 0;

        if ($this->count() != 0) {
            foreach ($this->getItems() as $item) {
                $total += $item->subTotal(false, $withDiscount);
            }
        }

        return $this->formatMoney($total, null, null, $format);
    }

    /**
     * Gets the total of the cart with or without tax
     * @param boolean $format
     * @param boolean $withDiscount
     * @return string
     */
    public function total($format = true, $withDiscount = true, $withTax = true)
    {
        $total = $this->subTotal(false) + $this->feeTotals(false);

        if ($withDiscount) {
            $total -= $this->totalDiscount(false);
        }

        if ($withTax) {
            $total += $this->taxTotal(false);
        }

        return $this->formatMoney($total, null, null, $format);
    }
}
