<?php

namespace LukePOLO\LaraCart;

class LaraCart implements LaraCartInterface
{

    // TODO - make it usable via public
    // TODO - make available for windows ?
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
