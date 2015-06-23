<?php

namespace LukePOLO\LaraCart;

/**
 * Class LaraCart
 *
 * @package LukePOLO\LaraCart
 */
class LaraCart implements LaraCartInterface
{

    // TODO - make it usable via public
    // TODO - make available for windows, since money_format does not work without additonal packages
    /**
     * @param $number
     * @param $locale
     * @param $displayLocale
     *
     * @return string
     */
    public static function formatMoney($number, $locale, $displayLocale)
    {
        setlocale(LC_MONETARY, $locale);
        if($displayLocale) {
            return money_format('%i', $number);
        } else {
            return money_format('%n', $number);
        }
    }
}
