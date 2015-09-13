<?php

namespace LukePOLO\LaraCart;

use LukePOLO\LaraCart\Contracts\LaraCartContract;

/**
 * Class LaraCart
 *
 * @package LukePOLO\LaraCart
 */
class LaraCart implements LaraCartContract
{
    /**
     * Formats the number into a money format based on the locale and international formats
     *
     * @param $number
     * @param $locale
     * @param $internationalFormat
     *
     * @return string
     */
    public function formatMoney($number, $locale = null, $internationalFormat = null)
    {
        if (empty($locale) === true) {
            $locale = config('laracart.locale', 'en_US');
        }

        if (empty($internationalFormat) === true) {
            $internationalFormat = config('laracart.international_format');
        }

        setlocale(LC_MONETARY, $locale);
        if ($internationalFormat) {
            return money_format('%i', $number);
        } else {
            return money_format('%n', $number);
        }
    }

    /**
     * Generates a hash for an object
     *
     * @param $object
     * @return string
     */
    public function generateHash($object)
    {
        return md5(json_encode($object));
    }

    /**
     * Generates a random hash
     *
     * @return string
     */
    public function generateRandomHash()
    {
        return str_random(40);
    }
}