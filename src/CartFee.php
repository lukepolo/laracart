<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethods;

/**
 * Class CartFee.
 */
class CartFee
{
    use CartOptionsMagicMethods;

    public $locale;
    public $amount;
    public $taxable;
    public $tax;
    public $internationalFormat;

    /**
     * CartFee constructor.
     *
     * @param $amount
     * @param array $options
     */
    public function __construct($amount, array $options = [])
    {
        $this->amount = floatval($amount);
        $this->taxable = isset($options['taxable']) ? $options['taxable'] : true;
        $this->tax = isset($options['tax']) ? $options['tax'] == 0 ? config('laracart.tax') : $options['tax'] : config('laracart.tax');
        $this->options = $options;
    }

    /**
     * Gets the formatted amount.
     *
     * @return string
     */
    public function amount()
    {
        return $this->formatMoney($this->amount, $this->locale, $this->internationalFormat);
    }
}
