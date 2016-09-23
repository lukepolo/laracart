<?php

namespace LukePOLO\LaraCart\Traits;

use LukePOLO\LaraCart\CartFee;

/**
 * Class CartItems.
 */
trait CartFees
{
    /**
     * Adding a fee.
     *
     * @param $name
     * @param $amount
     * @param array $options
     */
    public function addFee($name, $amount, array $options = [])
    {
        array_set($this->cart->fees, $name, new CartFee($amount, $options));

        $this->update();
    }

    /**
     * Adding a non-taxable fee.
     *
     * @param $name
     * @param $amount
     * @param array $options
     */
    public function addNonTaxableFee($name, $amount, array $options = [])
    {
        $options['taxable'] = false;

        array_set($this->cart->fees, $name, new CartFee($amount, $options));
        $this->update();
    }

    /**
     * Gets a specific fee from the fees array.
     *
     * @param $name
     *
     * @return mixed
     */
    public function getFee($name)
    {
        return array_get($this->cart->fees, $name);
    }

    /**
     * Gets all the fees on the cart object.
     *
     * @return mixed
     */
    public function fees()
    {
        return $this->cart->fees;
    }

    /**
     * Removes a fee from the fee array.
     *
     * @param $name
     *
     * @return void
     */
    public function removeFee($name)
    {
        array_forget($this->cart->fees, $name);

        $this->update();
    }

    /**
     * Removes all the fees set in the cart.
     *
     * @return void
     */
    public function removeFees()
    {
        $this->cart->fees = [];

        $this->update();
    }
}
