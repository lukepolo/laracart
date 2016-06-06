<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethodsTrait;

/**
 * Class CartFee
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
     * @param $amount
     * @param array $options
     */
    public function __construct($amount, $options = [])
    {
        $this->amount = floatval($amount);
        $this->taxable = isset($options['taxable']) ? $options['taxable'] : true;
        $this->tax = isset($options['tax']) ? $options['tax'] == 0 ? config('laracart.tax') : $options['tax'] : config('laracart.tax');
        $this->options = $options;
    }

    /**
     * Gets the formatted amount
     * @return string
     */
    public function getAmount()
    {
        return $this->formatMoney($this->amount, $this->locale, $this->internationalFormat);
    }
}
