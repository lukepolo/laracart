<?php

namespace LukePOLO\LaraCart;

/**
 * Interface LaraCartInterface
 *
 * @package LukePOLO\LaraCart
 */
interface LaraCartInterface
{
    public static function formatMoney($number, $locale, $displayLocale);
}