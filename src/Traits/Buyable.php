<?php

namespace LukePOLO\LaraCart\Traits;

/**
 * Class Buyable
 * @package LukePOLO\LaraCart\Traits
 */
trait Buyable
{
    /**
     * Gets the item name
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Checks to see if the item is taxable
     * @return mixed
     */
    public function isTaxable()
    {
        return $this->taxable;
    }

    /**
     * Checks to see if the it should be an line item
     * @return mixed
     */
    public function isLineItem()
    {
        return $this->lineItem;
    }

    /**
     * Gets the items price
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Gets the items tax (ex .07)
     * @return mixed
     */
    public function getTax()
    {
        return $this->tax;
    }
}
