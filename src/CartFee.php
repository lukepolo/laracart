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
    public $tax;
    public $internationalFormat;

    /**
     * CartFee constructor.
     *
     * @param $amount
     * @param $taxable
     * @param $tax
     * @param array $options
     */
    public function __construct($amount, $taxable = false, $options = [])
    {
        $this->amount = floatval($amount);
        $this->taxable = $taxable;
        $this->tax = isset($options['tax']) ? $options['tax'] == 0 ? config('laracart.tax') : $options['tax'] : config('laracart.tax');
        $this->options = $options;
    }

    /**
     * Gets the formatted amount
     *
     * @return string
     */
    public function getAmount()
    {
        return LaraCart::formatMoney($this->amount, $this->locale, $this->internationalFormat);
    }
}
