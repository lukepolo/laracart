<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethodsTrait;

/**
 * Class CartFee
 *
 * @package LukePOLO\LaraCart
 */
class CartFee
{
    use CartOptionsMagicMethodsTrait;

    public $locale;
    public $amount;
    public $taxable;
    public $internationalFormat;

    /**
     * CartFee constructor.
     *
     * @param $amount
     * @param $taxable
     * @param array $options
     */
    public function __construct($amount, $taxable, $options = [])
    {
        $this->amount = floatval($amount);
        $this->taxable = $taxable;
        $this->options = $options;
    }

    /**
     * Gets the formatted amount
     *
     * @return string
     */
    public function getAmount()
    {
        return \App::make(LaraCart::SERVICE)->formatMoney($this->amount, $this->locale, $this->internationalFormat);
    }
}
