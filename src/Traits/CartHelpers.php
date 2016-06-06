<?php

namespace LukePOLO\LaraCart\Traits;

/**
 * Class CartHelpers
 * @package LukePOLO\LaraCart\Traits
 */
trait CartHelpers
{
    /**
     * Formats the number into a money format based on the locale and international formats
     * @param $number
     * @param $locale
     * @param $internationalFormat
     * @param $format
     * @return string
     */
    public static function formatMoney($number, $locale = null, $internationalFormat = null, $format = true)
    {
        $number = number_format($number, 2, '.', '');

        if ($format) {
            setlocale(LC_MONETARY, null);
            setlocale(LC_MONETARY, empty($locale) ? config('laracart.locale', 'en_US.UTF-8') : $locale);

            if (empty($internationalFormat) === true) {
                $internationalFormat = config('laracart.international_format', false);
            }

            $number = money_format($internationalFormat ? '%i' : '%n', $number);
        }

        return $number;
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
