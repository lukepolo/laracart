<?php

namespace LukePOLO\LaraCart;

/**
 * Class Cart.
 */
class Cart
{
    public $tax;
    public $fees = [];
    public $items;
    public $locale;
    public $instance;
    public $coupons = [];
    public $attributes = [];
    public $multipleCoupons;
    public $currencyCode;

    /**
     * Cart constructor.
     *
     * @param string $instance
     */
    public function __construct($instance = 'default')
    {
        $this->instance = $instance;
        $this->tax = config('laracart.tax');
        $this->locale = config('laracart.locale');
        $this->multipleCoupons = config('laracart.multiple_coupons');
        $this->currencyCode = config('laracart.currency_code');
    }
}
