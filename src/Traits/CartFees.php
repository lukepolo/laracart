<?php

namespace LukePOLO\LaraCart\Traits;

use LukePOLO\LaraCart\CartFee;

/**
 * Class CartItems
 * @package LukePOLO\LaraCart\Traits
 */
trait CartFees
{
    /**
     * Allows to charge for additional fees that may or may not be taxable : ex - service fee , delivery fee, tips
     * @param $name
     * @param $amount
     * @param bool|false $taxable
     * @param array $options
     */
    public function addFee($name, $amount, $taxable = false, array $options = [])
    {
        array_set($this->cart->fees, $name, new CartFee($amount, $taxable, $options));

        $this->update();
    }

    /**
     * Gets a specific fee from the fees array
     * @param $name
     * @return mixed
     */
    public function getFee($name)
    {
        return array_get($this->cart->fees, $name, new CartFee(null, false));
    }

    /**
     * Gets all the fees on the cart object
     *
     * @return mixed
     */
    public function getFees()
    {
        return $this->cart->fees;
    }

    /**
     * Removes a fee from the fee array
     * @param $name
     * @return void
     */
    public function removeFee($name)
    {
        array_forget($this->cart->fees, $name);

        $this->update();
    }

    /**
     * Removes all the fees set in the cart
     * @return void
     */
    public function removeFees()
    {
        $this->cart->fees = [];

        $this->update();
    }
}
