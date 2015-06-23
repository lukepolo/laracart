<?php

namespace LukePOLO\LaraCart;

interface LaraCartInterface
{
    public static function formatMoney($number, $locale, $displayLocale);
}