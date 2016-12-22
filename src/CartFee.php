<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Traits\CartOptionsMagicMethodsTrait;

/**
 * Class CartFee.
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
     * Gets the formatted amount.
     *
     * @param bool $format
     * @param bool $withTax
     *
     * @return string
     */
    public function getAmount($format = true, $withTax = false)
    {
        $total = $this->amount;

        if ($withTax) {
            $total += $this->tax * $total;
        }

        return LaraCart::formatMoney($total, $this->locale, $this->internationalFormat, $format);
    }
}
