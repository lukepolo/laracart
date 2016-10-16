<?php

namespace LukePOLO\LaraCart\Traits;

use LukePOLO\LaraCart\CartMoneyFormatter;

/**
 * Class CartHelpers
 * @package LukePOLO\LaraCart\Traits
 */
trait CartHelpers
{
    /**
     * Formats the amount into a money format based on the locale and international formats
     * @param $amount
     * @param $locale
     * @param $internationalFormat
     * @return CartMoneyFormatter
     */
    public static function formatMoney($amount, $locale = null, $internationalFormat = null)
    {
        return app(CartMoneyFormatter::CART_FORMATTER, [$amount, $locale, $internationalFormat]);
    }

    /**
     * Generates a has based on the data given
     * @param $data
     * @return string
     */
    function hash($data)
    {
        return md5(json_encode($data));
    }

    /**
     * Generates a random hash
     * @param int $length
     * @return string
     */
    function randomHash($length = 40)
    {
        return str_random($length);
    }
}
