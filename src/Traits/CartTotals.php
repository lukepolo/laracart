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
     * @return string
     */
    public function totalDiscount()
    {
        $total = 0;

        foreach ($this->cart->coupons as $coupon) {
            if ($coupon->appliedToCart) {
                $total += $coupon->discount()->amount();
            }
        }

        return $this->formatMoney($total);
    }

    /**
     * Gets all the fee totals
     * @param bool $withTax
     * @return string
     */
    public function feeTotals($withTax = false)
    {
        $feeTotal = 0;

        foreach ($this->fees() as $fee) {
            $feeTotal += $fee->amount;

            if ($withTax && $fee->taxable && $fee->tax > 0) {
                $feeTotal += $fee->amount * $fee->tax;
            }
        }

        return $this->formatMoney($feeTotal);
    }

    /**
     * Gets the total tax for the cart
     * @return string
     */
    public function taxTotal()
    {
        $totalTax = 0;
        $discounted = 0;
        $totalDiscount = $this->totalDiscount()->amount();

        if ($this->count() != 0) {
            foreach ($this->items() as $item) {
                if ($discounted >= $totalDiscount) {
                    $totalTax += $item->tax();
                } else {
                    $itemPrice = $item->subTotal()->amount();

                    if (($discounted + $itemPrice) > $totalDiscount) {
                        $totalTax += $item->tax($totalDiscount - $discounted);
                    }

                    $discounted += $itemPrice;
                }
            }
        }

        foreach ($this->fees() as $fee) {
            if ($fee->taxable) {
                $totalTax += $fee->amount * $fee->tax;
            }
        }

        return $this->formatMoney($totalTax);
    }

    /**
     * Gets the subtotal of the cart with or without tax
     * @param boolean $withDiscount
     * @return string
     */
    public function subTotal($withDiscount = true)
    {
        $total = 0;

        if ($this->count() != 0) {
            foreach ($this->items() as $item) {
                $total += $item->subTotal($withDiscount)->amount();
            }
        }

        return $this->formatMoney($total);
    }

    /**
     * Gets the total of the cart with or without tax
     * @param boolean $withDiscount
     * @param bool $withTax
     * @return string
     */
    public function total($withDiscount = true, $withTax = true)
    {
        $total = $this->subTotal()->amount() + $this->feeTotals()->amount();

        if ($withDiscount) {
            $total -= $this->totalDiscount()->amount();
        }

        if ($withTax) {
            $total += $this->taxTotal()->amount();
        }

        return $this->formatMoney($total);
    }
}
