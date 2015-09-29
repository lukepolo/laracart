<?php

namespace LukePOLO\LaraCart\Contracts;

interface CouponContract
{
    /**
     * @param $code
     * @param $value
     */
    public function __construct($code, $value);

    /**
     * Gets the discount amount
     *
     * @return string
     */
    public function discount();

    /**
     * Displays the type of value it is for the user
     *
     * @return mixed
     */
    public function displayValue();
}
