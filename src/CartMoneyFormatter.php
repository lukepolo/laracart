<?php

namespace LukePOLO\LaraCart;

/**
 * Class CartMoneyFormatter.
 */
class CartMoneyFormatter
{
    const CART_FORMATTER = 'laracart_formatter';

    public $number;
    public $locale;
    public $internationalFormat;

    /**
     * CartMoneyFormatter constructor.
     *
     * @param $number
     * @param null $locale
     * @param null $internationalFormat
     */
    public function __construct($number, $locale = null, $internationalFormat = null)
    {
        $this->number = $number;

        setlocale(LC_MONETARY, null);
        setlocale(LC_MONETARY, empty($locale) ? config('laracart.locale', 'en_US.UTF-8') : $this->locale);

        $this->internationalFormat = empty($internationalFormat) ? config('laracart.international_format', false) : $internationalFormat;
    }

    /**
     * Shows the string version of the amount.
     *
     * @return string
     */
    public function __toString()
    {
        return money_format($this->internationalFormat ? '%i' : '%n', $this->amount());
    }

    /**
     * Formats as an integer.
     *
     * @return int
     */
    public function asInt()
    {
        return (int) ($this->number * 100);
    }

    /**
     * Gets the amount.
     *
     * @return string
     */
    public function amount()
    {
        return $this->formatNumber();
    }

    /**
     * Formats the number in money formats.
     *
     * @return string
     */
    private function formatNumber()
    {
        return number_format($this->number, 2, '.', '');
    }
}
