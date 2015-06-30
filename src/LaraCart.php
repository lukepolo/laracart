<?php

namespace LukePOLO\LaraCart;

/**
 * Class LaraCart
 *
 * @package LukePOLO\LaraCart
 */
class LaraCart implements LaraCartInterface
{
    /**
     * @param $number
     * @param $locale
     * @param $internationalFormat
     *
     * @return string
     */
    public static function formatMoney($number, $locale, $internationalFormat)
    {
        setlocale(LC_MONETARY, $locale);
        if($internationalFormat) {
            return money_format('%i', $number);
        } else {
            return money_format('%n', $number);
        }
    }
}