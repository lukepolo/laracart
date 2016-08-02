<?php

namespace LukePOLO\LaraCart\Contracts;

/**
 * Interface CartItemContract
 * @package LukePOLO\LaraCart\Contracts
 */
interface CartItemContract
{
    /**
     * Gets the item name
     * @return mixed
     */
    public function getName();

    /**
     * Checks to see if the item is taxable
     * @return mixed
     */
    public function isTaxable();

    /**
     * Checks to see if the it should be an line item
     * @return mixed
     */
    public function isLineItem();

    /**
     * Gets the items price
     * @return mixed
     */
    public function getPrice();

    /**
     * Gets the items tax (ex .07)
     * @return mixed
     */
    public function getTax();
}
