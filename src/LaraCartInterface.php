<?php

namespace LukePOLO\LaraCart;

/**
 * Interface LaraCartInterface
 *
 * @package LukePOLO\LaraCart
 */
interface LaraCartInterface
{
    public function formatMoney($number, $locale, $displayLocale);
}