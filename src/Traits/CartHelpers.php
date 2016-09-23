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
        return new CartMoneyFormatter($amount, $locale, $internationalFormat);
    }

    /**
     * Generates a has based on the data given
     * @param $data
     * @return string
     */
    public function generateHash($data)
    {
        return $this->setHash(md5($this->uniqueData($data)));
    }

    /**
     * Generates a random hash
     * @param int $length
     * @return string
     */
    public function randomHash($length = 40)
    {
        return $this->setHash(str_random($length));
    }

    function updateCart()
    {
        app('laracart')->update();
    }

    private function uniqueData($data)
    {
        $clonedData = clone $data;

        unset($clonedData->hash);
        unset($clonedData->options['qty']);

        ksort($clonedData->options);

        return json_encode($clonedData);
    }

    public function setHash($hash)
    {
        if ($this->hash) {
            $this->hash = $hash;
        }

        return $hash;
    }

}
