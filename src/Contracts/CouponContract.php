<?php

namespace LukePOLO\LaraCart\Contracts;

/**
 * Interface CouponContract.
 */
interface CouponContract
{
    /**
     * CouponContract constructor.
     *
     * @param $code
     * @param $value
     */
    public function __construct($code, $value);

    /**
     * Gets the discount amount.
     *
     * @return number
     */
    public function discount($price);

    /**
     * Displays the type of value it is for the user.
     *
     * @return mixed
     */
    public function displayValue();
}
